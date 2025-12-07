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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create' || $_POST['action'] === 'update') {
            $data = [
                'name' => $_POST['name'] ?? '',
                'acronym' => $_POST['acronym'] ?? '',
                'description' => $_POST['description'] ?? '',
                'website' => $_POST['website'] ?? '',
                'social_media' => !empty($_POST['social_media']) ? json_encode($_POST['social_media']) : null,
                'display_order' => intval($_POST['display_order'] ?? 0),
                'status' => $_POST['status'] ?? 'active',
                'created_by' => $_SESSION['admin_id'] ?? null
            ];
            
            // Handle logo upload
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = uploadImage($_FILES['logo'], 'images');
                if ($uploadResult['success']) {
                    // Delete old logo if updating
                    if ($_POST['action'] === 'update' && !empty($_POST['old_logo'])) {
                        deleteUploadedFile($_POST['old_logo']);
                    }
                    $data['logo'] = $uploadResult['path'];
                } else {
                    $message = 'Logo upload failed: ' . $uploadResult['error'];
                    $messageType = 'error';
                }
            } elseif ($_POST['action'] === 'update' && !empty($_POST['old_logo'])) {
                // Keep existing logo if no new upload
                $data['logo'] = $_POST['old_logo'];
            }
            
            if ($_POST['action'] === 'create') {
                $result = dbInsert('student_organizations', $data);
                if ($result) {
                    $message = 'Organization created successfully!';
                    $messageType = 'success';
                } else {
                    $message = 'Error creating organization.';
                    $messageType = 'error';
                }
            } else {
                $id = intval($_POST['id']);
                $result = dbUpdate('student_organizations', $data, 'id = :id', ['id' => $id]);
                if ($result) {
                    $message = 'Organization updated successfully!';
                    $messageType = 'success';
                } else {
                    $message = 'Error updating organization.';
                    $messageType = 'error';
                }
            }
        } elseif ($_POST['action'] === 'delete') {
            $id = intval($_POST['id']);
            // Get organization to delete logo
            $org = dbFetchOne("SELECT logo FROM student_organizations WHERE id = ?", [$id]);
            if ($org && !empty($org['logo'])) {
                deleteUploadedFile($org['logo']);
            }
            $result = dbDelete('student_organizations', 'id = :id', ['id' => $id]);
            if ($result) {
                $message = 'Organization deleted successfully!';
                $messageType = 'success';
            } else {
                $message = 'Error deleting organization.';
                $messageType = 'error';
            }
        }
    }
}

// Fetch organizations
$organizations = dbFetchAll("SELECT * FROM student_organizations ORDER BY display_order ASC, name ASC");

// Fetch single organization for editing
$editOrg = null;
if (isset($_GET['edit'])) {
    $editOrg = dbFetchOne("SELECT * FROM student_organizations WHERE id = ?", [intval($_GET['edit'])]);
    if ($editOrg && $editOrg['social_media']) {
        $editOrg['social_media'] = json_decode($editOrg['social_media'], true);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Organizations - PROWLWAY Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.1/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css">
</head>
<body>
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 2rem;">
        <div class="header" style="margin-bottom: 2rem;">
            <h1>Manage Student Organizations</h1>
            <a href="<?php echo ADMIN_URL; ?>/index.php" class="btn-goto">← Back to Dashboard</a>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>" style="margin-bottom: 1rem; padding: 1rem; border-radius: 8px; background: <?php echo $messageType === 'success' ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $messageType === 'success' ? '#155724' : '#721c24'; ?>;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Create/Edit Form -->
        <div class="form-section" style="background: var(--color-card-dark); padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem;">
            <h2><?php echo $editOrg ? 'Edit Organization' : 'Create New Organization'; ?></h2>
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?php echo $editOrg ? 'update' : 'create'; ?>">
                <?php if ($editOrg): ?>
                    <input type="hidden" name="id" value="<?php echo $editOrg['id']; ?>">
                <?php endif; ?>
                
                <div class="grid-2">
                    <div class="form-group">
                        <label>Name *</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($editOrg['name'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Acronym</label>
                        <input type="text" name="acronym" value="<?php echo htmlspecialchars($editOrg['acronym'] ?? ''); ?>" placeholder="e.g., ICDISG">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3"><?php echo htmlspecialchars($editOrg['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Logo</label>
                    <?php if ($editOrg && !empty($editOrg['logo'])): ?>
                        <div style="margin-bottom: 0.5rem;">
                            <img src="<?php echo getImageUrl($editOrg['logo']); ?>" alt="Current logo" style="max-width: 200px; max-height: 200px; border-radius: 8px;">
                            <input type="hidden" name="old_logo" value="<?php echo htmlspecialchars($editOrg['logo']); ?>">
                        </div>
                        <small style="color: var(--color-text-muted); display: block; margin-bottom: 0.5rem;">Upload a new logo to replace the current one</small>
                    <?php endif; ?>
                    <input type="file" name="logo" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                    <small style="color: var(--color-text-muted); display: block; margin-top: 0.5rem;">Max size: 5MB. Formats: JPEG, PNG, GIF, WebP</small>
                </div>
                
                <div class="form-group">
                    <label>Website URL</label>
                    <input type="url" name="website" value="<?php echo htmlspecialchars($editOrg['website'] ?? ''); ?>" placeholder="https://example.com">
                </div>
                
                <div class="form-group">
                    <label>Social Media Links (JSON format or leave blank)</label>
                    <textarea name="social_media[json]" rows="2" placeholder='{"facebook": "https://...", "instagram": "https://..."}'><?php echo $editOrg && $editOrg['social_media'] ? json_encode($editOrg['social_media'], JSON_PRETTY_PRINT) : ''; ?></textarea>
                    <small style="color: var(--color-text-muted); display: block; margin-top: 0.5rem;">Optional: Enter as JSON object</small>
                </div>
                
                <div class="grid-2">
                    <div class="form-group">
                        <label>Display Order</label>
                        <input type="number" name="display_order" value="<?php echo $editOrg['display_order'] ?? 0; ?>">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="active" <?php echo ($editOrg['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="draft" <?php echo ($editOrg['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="archived" <?php echo ($editOrg['status'] ?? '') === 'archived' ? 'selected' : ''; ?>>Archived</option>
                        </select>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary"><?php echo $editOrg ? 'Update Organization' : 'Create Organization'; ?></button>
                <?php if ($editOrg): ?>
                    <a href="<?php echo ADMIN_URL; ?>/organizations.php" class="btn btn-toggle">Cancel</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Organizations List -->
        <div class="list-section">
            <h2>All Organizations</h2>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: var(--color-card-dark);">
                        <th style="padding: 1rem; text-align: left;">Name</th>
                        <th style="padding: 1rem; text-align: left;">Acronym</th>
                        <th style="padding: 1rem; text-align: left;">Status</th>
                        <th style="padding: 1rem; text-align: left;">Order</th>
                        <th style="padding: 1rem; text-align: left;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($organizations)): ?>
                        <tr>
                            <td colspan="5" style="padding: 2rem; text-align: center; color: var(--color-text-muted);">No organizations found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($organizations as $org): ?>
                            <tr style="border-bottom: 1px solid var(--color-border);">
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($org['name']); ?></td>
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($org['acronym'] ?? '-'); ?></td>
                                <td style="padding: 1rem;"><?php echo ucfirst($org['status']); ?></td>
                                <td style="padding: 1rem;"><?php echo $org['display_order']; ?></td>
                                <td style="padding: 1rem;">
                                    <a href="<?php echo ADMIN_URL; ?>/organizations.php?edit=<?php echo $org['id']; ?>" class="btn btn-small">Edit</a>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this organization?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $org['id']; ?>">
                                        <button type="submit" class="btn btn-small" style="background: #dc3545;">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

