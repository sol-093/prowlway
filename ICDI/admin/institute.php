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
            // Lock display names for core sections so renaming doesn't change headings
            $lockedTitles = [
                'about' => 'About',
                'mission' => 'Mission',
                'vision' => 'Vision',
            ];
            $lockedTitle = $lockedTitles[$section] ?? null;
            $data = [
                // For about/mission/vision, always use locked title; otherwise allow custom title
                'title' => $lockedTitle ?? ($_POST['title'] ?? ''),
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

$pageTitle = 'Manage Institute - PROWLWAY Admin';
$hideHeader = true;
$bodyClass = 'admin-panel-page';
include '../includes/header.php';
?>

<div class="admin-panel-wrapper min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <div class="max-w-6xl mx-auto px-6 py-8">
        <!-- Page Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Manage Institute Information</h1>
                <p class="text-sm text-gray-500 mt-1">Manage institute details, faculty, admin representatives, and programs.</p>
            </div>
            <a href="<?php echo ADMIN_URL; ?>/index.php" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50 hover:border-gray-300 transition-colors">
                <span>← Back to Dashboard</span>
            </a>
        </div>

        <?php if ($message): ?>
            <div class="mb-6">
                <div class="flex items-center gap-3 p-4 rounded-lg border <?php echo $messageType === 'success' ? 'bg-green-50 border-green-500 text-green-800' : 'bg-red-50 border-red-500 text-red-800'; ?>">
                    <span class="font-medium"><?php echo htmlspecialchars($message); ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Tabs -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex" aria-label="Tabs">
                    <a href="?tab=info" class="px-6 py-4 text-sm font-bold <?php echo $activeTab === 'info' ? 'text-indigo-600 border-b-3 border-indigo-600' : 'text-gray-600 border-b-3 border-transparent hover:text-gray-900 hover:border-gray-300'; ?> transition-colors">
                        About/Mission/Vision
                    </a>
                    <a href="?tab=faculty" class="px-6 py-4 text-sm font-bold <?php echo $activeTab === 'faculty' ? 'text-indigo-600 border-b-3 border-indigo-600' : 'text-gray-600 border-b-3 border-transparent hover:text-gray-900 hover:border-gray-300'; ?> transition-colors">
                        Faculty Unit
                    </a>
                    <a href="?tab=admin" class="px-6 py-4 text-sm font-bold <?php echo $activeTab === 'admin' ? 'text-indigo-600 border-b-3 border-indigo-600' : 'text-gray-600 border-b-3 border-transparent hover:text-gray-900 hover:border-gray-300'; ?> transition-colors">
                        Admin Representative
                    </a>
                    <a href="?tab=program" class="px-6 py-4 text-sm font-bold <?php echo $activeTab === 'program' ? 'text-indigo-600 border-b-3 border-indigo-600' : 'text-gray-600 border-b-3 border-transparent hover:text-gray-900 hover:border-gray-300'; ?> transition-colors">
                        Program
                    </a>
                </nav>
            </div>
        </div>

        <?php if ($activeTab === 'info'): ?>
            <!-- About/Mission/Vision Forms -->
            <div class="mb-8 bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">About</h2>
                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="action" value="update_info">
                    <input type="hidden" name="section" value="about">
                    <?php if ($about && !empty($about['image'])): ?>
                        <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($about['image']); ?>">
                    <?php endif; ?>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Content *</label>
                        <textarea name="content" rows="5" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-y"><?php echo htmlspecialchars($about['content'] ?? ''); ?></textarea>
                    </div>
                    <button type="submit" class="px-6 py-3 bg-indigo-600 text-white font-semibold rounded-xl shadow hover:bg-indigo-700 transition-colors">Update About</button>
                </form>
            </div>

            <div class="mb-8 bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Mission</h2>
                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="action" value="update_info">
                    <input type="hidden" name="section" value="mission">
                    <?php if ($mission && !empty($mission['image'])): ?>
                        <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($mission['image']); ?>">
                    <?php endif; ?>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Content *</label>
                        <textarea name="content" rows="5" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-y"><?php echo htmlspecialchars($mission['content'] ?? ''); ?></textarea>
                    </div>
                    <button type="submit" class="px-6 py-3 bg-indigo-600 text-white font-semibold rounded-xl shadow hover:bg-indigo-700 transition-colors">Update Mission</button>
                </form>
            </div>

            <div class="mb-8 bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Vision</h2>
                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="action" value="update_info">
                    <input type="hidden" name="section" value="vision">
                    <?php if ($vision && !empty($vision['image'])): ?>
                        <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($vision['image']); ?>">
                    <?php endif; ?>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Content *</label>
                        <textarea name="content" rows="5" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-y"><?php echo htmlspecialchars($vision['content'] ?? ''); ?></textarea>
                    </div>
                    <button type="submit" class="px-6 py-3 bg-indigo-600 text-white font-semibold rounded-xl shadow hover:bg-indigo-700 transition-colors">Update Vision</button>
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
            <div class="mb-8 bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4"><?php echo $editSection ? 'Edit ' . ucfirst($activeTab) : 'Create New ' . ucfirst($activeTab); ?></h2>
                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="action" value="<?php echo $editSection ? 'update_section' : 'create_section'; ?>">
                    <input type="hidden" name="type" value="<?php echo $type; ?>">
                    <?php if ($editSection): ?>
                        <input type="hidden" name="id" value="<?php echo $editSection['id']; ?>">
                    <?php endif; ?>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Title *</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($editSection['title'] ?? ''); ?>" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                        <textarea name="description" rows="3" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-y"><?php echo htmlspecialchars($editSection['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Content</label>
                        <textarea name="content" rows="5" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-y"><?php echo htmlspecialchars($editSection['content'] ?? ''); ?></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Image</label>
                        <?php if ($editSection && !empty($editSection['image'])): ?>
                            <div class="mb-3">
                                <img src="<?php echo getImageUrl($editSection['image']); ?>" alt="Current image" class="max-w-xs max-h-48 rounded-xl border border-gray-200">
                                <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($editSection['image']); ?>">
                            </div>
                            <p class="text-xs text-gray-500 mb-2">Upload a new image to replace the current one.</p>
                        <?php endif; ?>
                        <input type="file" name="image" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <p class="mt-2 text-xs text-gray-500">Max size: 5MB. Formats: JPEG, PNG, GIF, WebP.</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Display Order</label>
                            <input type="number" name="display_order" value="<?php echo $editSection['display_order'] ?? 0; ?>" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                            <select name="status" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="published" <?php echo ($editSection['status'] ?? 'published') === 'published' ? 'selected' : ''; ?>>Published</option>
                                <option value="draft" <?php echo ($editSection['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" class="px-6 py-3 bg-indigo-600 text-white font-semibold rounded-xl shadow hover:bg-indigo-700 transition-colors">
                            <?php echo $editSection ? 'Update' : 'Create'; ?>
                        </button>
                        <?php if ($editSection): ?>
                            <a href="<?php echo ADMIN_URL; ?>/institute.php?tab=<?php echo $activeTab; ?>" class="px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors">
                                Cancel
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Sections List -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-900">All <?php echo ucfirst($activeTab); ?>s</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Title</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Order</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if (empty($sections)): ?>
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-gray-500">No items found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($sections as $section): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3"><?php echo htmlspecialchars($section['title']); ?></td>
                                        <td class="px-4 py-3"><?php echo $section['display_order']; ?></td>
                                        <td class="px-4 py-3"><?php echo ucfirst($section['status']); ?></td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                <a href="<?php echo ADMIN_URL; ?>/institute.php?tab=<?php echo $activeTab; ?>&edit=<?php echo $section['id']; ?>" class="px-3 py-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-100">
                                                    Edit
                                                </a>
                                                <form method="POST" class="inline" onsubmit="return confirm('Are you sure?');">
                                                    <input type="hidden" name="action" value="delete_section">
                                                    <input type="hidden" name="id" value="<?php echo $section['id']; ?>">
                                                    <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-white bg-red-500 rounded-lg hover:bg-red-600">
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

