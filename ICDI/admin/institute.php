<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/upload.php';

// Check authentication
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: ' . ADMIN_URL . '/index.php');
    exit;
}

$message = '';
$messageType = '';
$activeTab = $_GET['tab'] ?? 'info';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'update_info') {
            $section = $_POST['section'];
            $data = [
                'title' => $_POST['title'] ?? '',
                'content' => $_POST['content'] ?? '',
                'updated_by' => $_SESSION['admin_id'] ?? null
            ];
            
            // Handle image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = uploadImage($_FILES['image'], 'images');
                if ($uploadResult['success']) {
                    // Delete old image if updating
                    $existing = dbFetchOne("SELECT image FROM institute_info WHERE section = ?", [$section]);
                    if ($existing && !empty($existing['image'])) {
                        deleteUploadedFile($existing['image']);
                    }
                    $data['image'] = $uploadResult['path'];
                } else {
                    $message = 'Image upload failed: ' . $uploadResult['error'];
                    $messageType = 'error';
                }
            } elseif (!empty($_POST['old_image'])) {
                $data['image'] = $_POST['old_image'];
            }
            
            if (empty($message)) {
                $existing = dbFetchOne("SELECT id FROM institute_info WHERE section = ?", [$section]);
                if ($existing) {
                    $result = dbUpdate('institute_info', $data, 'section = :section', ['section' => $section]);
                } else {
                    $data['section'] = $section;
                    $data['status'] = 'published';
                    $result = dbInsert('institute_info', $data);
                }
                
                if ($result) {
                    $message = 'Information updated successfully!';
                    $messageType = 'success';
                } else {
                    $message = 'Error updating information.';
                    $messageType = 'error';
                }
            }
        } elseif ($_POST['action'] === 'create_section' || $_POST['action'] === 'update_section') {
            $data = [
                'type' => $_POST['type'],
                'title' => $_POST['title'] ?? '',
                'description' => $_POST['description'] ?? '',
                'content' => $_POST['content'] ?? '',
                'display_order' => intval($_POST['display_order'] ?? 0),
                'status' => $_POST['status'] ?? 'published',
                'created_by' => $_SESSION['admin_id'] ?? null
            ];
            
            // Handle image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = uploadImage($_FILES['image'], 'images');
                if ($uploadResult['success']) {
                    // Delete old image if updating
                    if ($_POST['action'] === 'update_section' && !empty($_POST['old_image'])) {
                        deleteUploadedFile($_POST['old_image']);
                    }
                    $data['image'] = $uploadResult['path'];
                } else {
                    $message = 'Image upload failed: ' . $uploadResult['error'];
                    $messageType = 'error';
                }
            } elseif ($_POST['action'] === 'update_section' && !empty($_POST['old_image'])) {
                $data['image'] = $_POST['old_image'];
            }
            
            if ($_POST['action'] === 'create_section') {
                $result = dbInsert('institute_sections', $data);
                if ($result) {
                    $message = 'Section created successfully!';
                    $messageType = 'success';
                }
            } else {
                $id = intval($_POST['id']);
                $result = dbUpdate('institute_sections', $data, 'id = :id', ['id' => $id]);
                if ($result) {
                    $message = 'Section updated successfully!';
                    $messageType = 'success';
                }
            }
        } elseif ($_POST['action'] === 'delete_section') {
            $id = intval($_POST['id']);
            // Get section to delete image
            $section = dbFetchOne("SELECT image FROM institute_sections WHERE id = ?", [$id]);
            if ($section && !empty($section['image'])) {
                deleteUploadedFile($section['image']);
            }
            $result = dbDelete('institute_sections', 'id = :id', ['id' => $id]);
            if ($result) {
                $message = 'Section deleted successfully!';
                $messageType = 'success';
            }
        }
    }
}

// Fetch data
$about = dbFetchOne("SELECT * FROM institute_info WHERE section = 'about'");
$mission = dbFetchOne("SELECT * FROM institute_info WHERE section = 'mission'");
$vision = dbFetchOne("SELECT * FROM institute_info WHERE section = 'vision'");
$logo = dbFetchOne("SELECT * FROM institute_info WHERE section = 'logo'");

$facultyUnits = dbFetchAll("SELECT * FROM institute_sections WHERE type = 'faculty_unit' ORDER BY display_order ASC");
$adminReps = dbFetchAll("SELECT * FROM institute_sections WHERE type = 'admin_representative' ORDER BY display_order ASC");
$programs = dbFetchAll("SELECT * FROM institute_sections WHERE type = 'program' ORDER BY display_order ASC");

$editSection = null;
if (isset($_GET['edit'])) {
    $editSection = dbFetchOne("SELECT * FROM institute_sections WHERE id = ?", [intval($_GET['edit'])]);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Institute - PROWLWAY Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.1/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css">
</head>
<body>
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 2rem;">
        <div class="header" style="margin-bottom: 2rem;">
            <h1>Manage Institute Information</h1>
            <a href="<?php echo ADMIN_URL; ?>/index.php" class="btn-goto">← Back to Dashboard</a>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>" style="margin-bottom: 1rem; padding: 1rem; border-radius: 8px; background: <?php echo $messageType === 'success' ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $messageType === 'success' ? '#155724' : '#721c24'; ?>;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Tabs -->
        <div class="tabs" style="margin-bottom: 2rem;">
            <button class="tab <?php echo $activeTab === 'info' ? 'active' : ''; ?>" onclick="window.location.href='?tab=info'">About/Mission/Vision</button>
            <button class="tab <?php echo $activeTab === 'faculty' ? 'active' : ''; ?>" onclick="window.location.href='?tab=faculty'">Faculty Unit</button>
            <button class="tab <?php echo $activeTab === 'admin' ? 'active' : ''; ?>" onclick="window.location.href='?tab=admin'">Admin Representative</button>
            <button class="tab <?php echo $activeTab === 'program' ? 'active' : ''; ?>" onclick="window.location.href='?tab=program'">Program</button>
        </div>

        <?php if ($activeTab === 'info'): ?>
            <!-- About/Mission/Vision Forms -->
            <div class="form-section" style="background: var(--color-card-dark); padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem;">
                <h2>About</h2>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="update_info">
                    <input type="hidden" name="section" value="about">
                    <?php if ($about && !empty($about['image'])): ?>
                        <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($about['image']); ?>">
                    <?php endif; ?>
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($about['title'] ?? 'About'); ?>">
                    </div>
                    <div class="form-group">
                        <label>Content *</label>
                        <textarea name="content" rows="5" required><?php echo htmlspecialchars($about['content'] ?? ''); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Update About</button>
                </form>
            </div>

            <div class="form-section" style="background: var(--color-card-dark); padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem;">
                <h2>Mission</h2>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="update_info">
                    <input type="hidden" name="section" value="mission">
                    <?php if ($mission && !empty($mission['image'])): ?>
                        <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($mission['image']); ?>">
                    <?php endif; ?>
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($mission['title'] ?? 'Mission'); ?>">
                    </div>
                    <div class="form-group">
                        <label>Content *</label>
                        <textarea name="content" rows="5" required><?php echo htmlspecialchars($mission['content'] ?? ''); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Mission</button>
                </form>
            </div>

            <div class="form-section" style="background: var(--color-card-dark); padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem;">
                <h2>Vision</h2>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="update_info">
                    <input type="hidden" name="section" value="vision">
                    <?php if ($vision && !empty($vision['image'])): ?>
                        <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($vision['image']); ?>">
                    <?php endif; ?>
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($vision['title'] ?? 'Vision'); ?>">
                    </div>
                    <div class="form-group">
                        <label>Content *</label>
                        <textarea name="content" rows="5" required><?php echo htmlspecialchars($vision['content'] ?? ''); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Vision</button>
                </form>
            </div>

        <?php else: ?>
            <!-- Section Management (Faculty/Admin/Program) -->
            <?php
            $sections = [];
            $type = '';
            if ($activeTab === 'faculty') {
                $sections = $facultyUnits;
                $type = 'faculty_unit';
            } elseif ($activeTab === 'admin') {
                $sections = $adminReps;
                $type = 'admin_representative';
            } elseif ($activeTab === 'program') {
                $sections = $programs;
                $type = 'program';
            }
            ?>

            <!-- Create/Edit Form -->
            <div class="form-section" style="background: var(--color-card-dark); padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem;">
                <h2><?php echo $editSection ? 'Edit ' . ucfirst($activeTab) : 'Create New ' . ucfirst($activeTab); ?></h2>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?php echo $editSection ? 'update_section' : 'create_section'; ?>">
                    <input type="hidden" name="type" value="<?php echo $type; ?>">
                    <?php if ($editSection): ?>
                        <input type="hidden" name="id" value="<?php echo $editSection['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>Title *</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($editSection['title'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="3"><?php echo htmlspecialchars($editSection['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Content</label>
                        <textarea name="content" rows="5"><?php echo htmlspecialchars($editSection['content'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Image</label>
                        <?php if ($editSection && !empty($editSection['image'])): ?>
                            <div style="margin-bottom: 0.5rem;">
                                <img src="<?php echo getImageUrl($editSection['image']); ?>" alt="Current image" style="max-width: 200px; max-height: 200px; border-radius: 8px;">
                                <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($editSection['image']); ?>">
                            </div>
                            <small style="color: var(--color-text-muted); display: block; margin-bottom: 0.5rem;">Upload a new image to replace the current one</small>
                        <?php endif; ?>
                        <input type="file" name="image" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                        <small style="color: var(--color-text-muted); display: block; margin-top: 0.5rem;">Max size: 5MB. Formats: JPEG, PNG, GIF, WebP</small>
                    </div>
                    
                    <div class="grid-2">
                        <div class="form-group">
                            <label>Display Order</label>
                            <input type="number" name="display_order" value="<?php echo $editSection['display_order'] ?? 0; ?>">
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status">
                                <option value="published" <?php echo ($editSection['status'] ?? 'published') === 'published' ? 'selected' : ''; ?>>Published</option>
                                <option value="draft" <?php echo ($editSection['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            </select>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary"><?php echo $editSection ? 'Update' : 'Create'; ?></button>
                    <?php if ($editSection): ?>
                        <a href="<?php echo ADMIN_URL; ?>/institute.php?tab=<?php echo $activeTab; ?>" class="btn btn-toggle">Cancel</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Sections List -->
            <div class="list-section">
                <h2>All <?php echo ucfirst($activeTab); ?>s</h2>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: var(--color-card-dark);">
                            <th style="padding: 1rem; text-align: left;">Title</th>
                            <th style="padding: 1rem; text-align: left;">Order</th>
                            <th style="padding: 1rem; text-align: left;">Status</th>
                            <th style="padding: 1rem; text-align: left;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sections)): ?>
                            <tr>
                                <td colspan="4" style="padding: 2rem; text-align: center; color: var(--color-text-muted);">No items found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($sections as $section): ?>
                                <tr style="border-bottom: 1px solid var(--color-border);">
                                    <td style="padding: 1rem;"><?php echo htmlspecialchars($section['title']); ?></td>
                                    <td style="padding: 1rem;"><?php echo $section['display_order']; ?></td>
                                    <td style="padding: 1rem;"><?php echo ucfirst($section['status']); ?></td>
                                    <td style="padding: 1rem;">
                                        <a href="<?php echo ADMIN_URL; ?>/institute.php?tab=<?php echo $activeTab; ?>&edit=<?php echo $section['id']; ?>" class="btn btn-small">Edit</a>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?');">
                                            <input type="hidden" name="action" value="delete_section">
                                            <input type="hidden" name="id" value="<?php echo $section['id']; ?>">
                                            <button type="submit" class="btn btn-small" style="background: #dc3545;">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

