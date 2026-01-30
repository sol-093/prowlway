<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/upload.php';

requireAdmin(null, false);

$message = '';
$messageType = '';
$activeTab = $_GET['tab'] ?? 'info'; // 'info' or 'sections'

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create_info' || $_POST['action'] === 'update_info') {
            $requestedStatus = $_POST['status'] ?? 'draft';
            $data = [
                'section' => $_POST['section'] ?? '',
                'title' => $_POST['title'] ?? '',
                'content' => $_POST['content'] ?? null,
                'display_order' => intval($_POST['display_order'] ?? 0),
                'status' => normalizeStatusByRole($requestedStatus, ['published']),
                'updated_by' => $_SESSION['admin_id'] ?? null
            ];
            
            // Handle image upload (for logo and banner sections)
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = uploadImage($_FILES['image'], 'images');
                if ($uploadResult['success']) {
                    if ($_POST['action'] === 'update_info' && !empty($_POST['old_image'])) {
                        deleteUploadedFile($_POST['old_image']);
                    }
                    $data['image'] = $uploadResult['path'];
                } else {
                    $message = 'Image upload failed: ' . $uploadResult['error'];
                    $messageType = 'error';
                }
            } elseif ($_POST['action'] === 'update_info' && !empty($_POST['old_image'])) {
                $data['image'] = $_POST['old_image'];
            }
            
            if ($_POST['action'] === 'create_info') {
                $newId = dbInsert('institute_info', $data);
                if ($newId) {
                    if ($data['status'] === 'published') {
                        auditLog('publish', 'Institute info section created and published', 'institute_info', $newId);
                    }
                    $message = 'Institute info section created successfully!';
                    $messageType = 'success';
                } else {
                    $message = 'Error creating institute info section.';
                    $messageType = 'error';
                }
            } else {
                $id = intval($_POST['id']);
                $existing = dbFetchOne("SELECT status FROM institute_info WHERE id = ?", [$id]);
                $result = dbUpdate('institute_info', $data, 'id = :id', ['id' => $id]);
                if ($result) {
                    if ($existing && $data['status'] === 'published' && $existing['status'] !== 'published') {
                        auditLog('publish', 'Institute info section published', 'institute_info', $id);
                    } elseif ($existing && $data['status'] === 'draft' && $existing['status'] === 'published') {
                        auditLog('archive', 'Institute info section set to draft', 'institute_info', $id);
                    }
                    $message = 'Institute info section updated successfully!';
                    $messageType = 'success';
                } else {
                    $message = 'Error updating institute info section.';
                    $messageType = 'error';
                }
            }
        } elseif ($_POST['action'] === 'create_section' || $_POST['action'] === 'update_section') {
            $requestedStatus = $_POST['status'] ?? 'draft';
            $data = [
                'type' => $_POST['type'] ?? '',
                'title' => $_POST['title'] ?? '',
                'position_title' => $_POST['position_title'] ?? null,
                'description' => $_POST['description'] ?? null,
                'content' => $_POST['content'] ?? null,
                'display_order' => intval($_POST['display_order'] ?? 0),
                'status' => normalizeStatusByRole($requestedStatus, ['published']),
                'created_by' => $_SESSION['admin_id'] ?? null
            ];
            
            // Handle image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = uploadImage($_FILES['image'], 'images');
                if ($uploadResult['success']) {
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
                $newId = dbInsert('institute_sections', $data);
                if ($newId) {
                    if ($data['status'] === 'published') {
                        auditLog('publish', 'Institute section created and published', 'institute_section', $newId);
                    }
                    $message = 'Institute section created successfully!';
                    $messageType = 'success';
                } else {
                    $message = 'Error creating institute section.';
                    $messageType = 'error';
                }
            } else {
                $id = intval($_POST['id']);
                $existing = dbFetchOne("SELECT status FROM institute_sections WHERE id = ?", [$id]);
                $result = dbUpdate('institute_sections', $data, 'id = :id', ['id' => $id]);
                if ($result) {
                    if ($existing && $data['status'] === 'published' && $existing['status'] !== 'published') {
                        auditLog('publish', 'Institute section published', 'institute_section', $id);
                    } elseif ($existing && $data['status'] === 'draft' && $existing['status'] === 'published') {
                        auditLog('unpublish', 'Institute section set to draft', 'institute_section', $id);
                    }
                    $message = 'Institute section updated successfully!';
                    $messageType = 'success';
                } else {
                    $message = 'Error updating institute section.';
                    $messageType = 'error';
                }
            }
        } elseif ($_POST['action'] === 'delete_info') {
            $id = intval($_POST['id']);
            $info = dbFetchOne("SELECT image, status, updated_by FROM institute_info WHERE id = ?", [$id]);
            if (!$info) {
                $message = 'Institute info section not found.';
                $messageType = 'error';
            } elseif (!canPublish() && ($info['status'] !== 'draft' || (int)($info['updated_by'] ?? 0) !== (int)($_SESSION['admin_id'] ?? 0))) {
                $message = 'Only draft sections you created can be deleted, or you need publish rights.';
                $messageType = 'error';
            } else {
                if (!empty($info['image'])) deleteUploadedFile($info['image']);
                $result = dbDelete('institute_info', 'id = :id', ['id' => $id]);
                if ($result) {
                    auditLog('delete', 'Institute info section deleted', 'institute_info', $id);
                    $message = 'Institute info section deleted successfully!';
                    $messageType = 'success';
                } else {
                    $message = 'Error deleting institute info section.';
                    $messageType = 'error';
                }
            }
        } elseif ($_POST['action'] === 'delete_section') {
            $id = intval($_POST['id']);
            $section = dbFetchOne("SELECT image, status, created_by FROM institute_sections WHERE id = ?", [$id]);
            if (!$section) {
                $message = 'Institute section not found.';
                $messageType = 'error';
            } elseif (!canPublish() && ($section['status'] !== 'draft' || (int)($section['created_by'] ?? 0) !== (int)($_SESSION['admin_id'] ?? 0))) {
                $message = 'Only draft sections you created can be deleted, or you need publish rights.';
                $messageType = 'error';
            } else {
                if (!empty($section['image'])) deleteUploadedFile($section['image']);
                $result = dbDelete('institute_sections', 'id = :id', ['id' => $id]);
                if ($result) {
                    auditLog('delete', 'Institute section deleted', 'institute_section', $id);
                    $message = 'Institute section deleted successfully!';
                    $messageType = 'success';
                } else {
                    $message = 'Error deleting institute section.';
                    $messageType = 'error';
                }
            }
        }
    }
}

// Fetch institute info sections
$instituteInfo = dbFetchAll("SELECT * FROM institute_info ORDER BY display_order ASC, section ASC");

// Fetch institute sections
$instituteSections = dbFetchAll("SELECT * FROM institute_sections ORDER BY type ASC, display_order ASC, title ASC");

// Fetch single item for editing
$editInfo = null;
$editSection = null;
if (isset($_GET['edit_info'])) {
    $editInfo = dbFetchOne("SELECT * FROM institute_info WHERE id = ?", [intval($_GET['edit_info'])]);
    $activeTab = 'info';
} elseif (isset($_GET['edit_section'])) {
    $editSection = dbFetchOne("SELECT * FROM institute_sections WHERE id = ?", [intval($_GET['edit_section'])]);
    $activeTab = 'sections';
}

$canPublish = canPublish();
$isEditor = isEditor();

$pageTitle = 'Manage Institute Content - PROWLWAY Admin';
$hideHeader = true;
$bodyClass = 'admin-panel-page';
include '../includes/header.php';
?>

<div class="admin-panel-wrapper min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <div class="max-w-6xl mx-auto px-6 py-8">
        <!-- Page Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Manage Institute Content</h1>
                <p class="text-sm text-gray-500 mt-1">Edit About, Mission, Vision, Logo, Banner, Faculty Units, Admin Representatives, and Programs.</p>
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
        <div class="mb-6 border-b border-gray-200">
            <div class="flex gap-4">
                <a href="?tab=info<?php echo $editInfo ? '&edit_info=' . $editInfo['id'] : ''; ?>" class="px-4 py-2 font-semibold <?php echo $activeTab === 'info' ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-700'; ?> transition-colors">
                    Institute Info
                </a>
                <a href="?tab=sections<?php echo $editSection ? '&edit_section=' . $editSection['id'] : ''; ?>" class="px-4 py-2 font-semibold <?php echo $activeTab === 'sections' ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-700'; ?> transition-colors">
                    Sections (Faculty/Admin/Programs)
                </a>
            </div>
        </div>

        <!-- Institute Info Tab -->
        <?php if ($activeTab === 'info'): ?>
            <!-- Create/Edit Form -->
            <div class="mb-8 bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-900">
                        <?php echo $editInfo ? 'Edit Institute Info Section' : 'Create New Institute Info Section'; ?>
                    </h2>
                    <?php if ($editInfo): ?>
                        <a href="?tab=info" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
                    <?php endif; ?>
                </div>
                <form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="action" value="<?php echo $editInfo ? 'update_info' : 'create_info'; ?>">
                    <?php if ($editInfo): ?>
                        <input type="hidden" name="id" value="<?php echo $editInfo['id']; ?>">
                        <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($editInfo['image'] ?? ''); ?>">
                    <?php endif; ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Section *</label>
                            <select name="section" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" <?php echo $editInfo ? 'disabled' : ''; ?>>
                                <option value="">Select section...</option>
                                <option value="about" <?php echo ($editInfo['section'] ?? '') === 'about' ? 'selected' : ''; ?>>About</option>
                                <option value="mission" <?php echo ($editInfo['section'] ?? '') === 'mission' ? 'selected' : ''; ?>>Mission</option>
                                <option value="vision" <?php echo ($editInfo['section'] ?? '') === 'vision' ? 'selected' : ''; ?>>Vision</option>
                                <option value="logo" <?php echo ($editInfo['section'] ?? '') === 'logo' ? 'selected' : ''; ?>>Logo</option>
                                <option value="banner" <?php echo ($editInfo['section'] ?? '') === 'banner' ? 'selected' : ''; ?>>Banner</option>
                            </select>
                            <?php if ($editInfo): ?>
                                <input type="hidden" name="section" value="<?php echo htmlspecialchars($editInfo['section']); ?>">
                            <?php endif; ?>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Display Order</label>
                            <input type="number" name="display_order" value="<?php echo htmlspecialchars($editInfo['display_order'] ?? 0); ?>" min="0" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Title</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($editInfo['title'] ?? ''); ?>" placeholder="Section title" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Content</label>
                        <textarea name="content" rows="6" placeholder="Section content (for About, Mission, Vision)" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-y"><?php echo htmlspecialchars($editInfo['content'] ?? ''); ?></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Image (for Logo and Banner sections)</label>
                        <?php if ($editInfo && !empty($editInfo['image'])): ?>
                            <div class="mb-3">
                                <img src="<?php echo getImageUrl($editInfo['image']); ?>" alt="Current image" class="max-w-xs max-h-48 rounded-xl border border-gray-200">
                            </div>
                            <p class="text-xs text-gray-500 mb-2">Upload a new image to replace the current one.</p>
                        <?php endif; ?>
                        <input type="file" name="image" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <p class="mt-2 text-xs text-gray-500">Max size: 5MB. Formats: JPEG, PNG, GIF, WebP.</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Status *</label>
                        <select name="status" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="draft" <?php echo ($editInfo['status'] ?? 'draft') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <?php if ($canPublish): ?>
                                <option value="published" <?php echo ($editInfo['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Published</option>
                            <?php endif; ?>
                        </select>
                        <?php if ($isEditor): ?>
                            <p class="mt-1 text-xs text-gray-500">Editors can only save as Draft. Administrators can publish.</p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="flex gap-3 pt-4">
                        <button type="submit" class="px-8 py-3.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                            <?php echo $editInfo ? 'Update Section' : 'Create Section'; ?>
                        </button>
                        <?php if ($editInfo): ?>
                            <a href="?tab=info" class="px-8 py-3.5 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition-colors inline-flex items-center">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Institute Info List -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Institute Info Sections</h2>
                <div class="space-y-3">
                    <?php if (empty($instituteInfo)): ?>
                        <p class="text-gray-500 text-center py-8">No institute info sections found.</p>
                    <?php else: ?>
                        <?php foreach ($instituteInfo as $info): ?>
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-xl hover:shadow-md transition-shadow">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3">
                                        <span class="px-2 py-1 text-xs font-medium rounded <?php echo $info['status'] === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                            <?php echo htmlspecialchars($info['status']); ?>
                                        </span>
                                        <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($info['section']); ?></h3>
                                        <?php if ($info['title']): ?>
                                            <span class="text-sm text-gray-500">- <?php echo htmlspecialchars($info['title']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($info['content']): ?>
                                        <p class="text-sm text-gray-600 mt-1 line-clamp-2"><?php echo htmlspecialchars(substr($info['content'], 0, 100)); ?>...</p>
                                    <?php endif; ?>
                                </div>
                                <div class="flex gap-2">
                                    <a href="?tab=info&edit_info=<?php echo $info['id']; ?>" class="px-3 py-1.5 text-xs font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">Edit</a>
                                    <form method="POST" action="" class="inline" onsubmit="return confirm('Are you sure you want to delete this section?');">
                                        <input type="hidden" name="action" value="delete_info">
                                        <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
                                        <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-white bg-red-500 rounded-lg hover:bg-red-600 transition-colors">Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Sections Tab -->
        <?php if ($activeTab === 'sections'): ?>
            <!-- Create/Edit Form -->
            <div class="mb-8 bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-900">
                        <?php echo $editSection ? 'Edit Institute Section' : 'Create New Institute Section'; ?>
                    </h2>
                    <?php if ($editSection): ?>
                        <a href="?tab=sections" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
                    <?php endif; ?>
                </div>
                <form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="action" value="<?php echo $editSection ? 'update_section' : 'create_section'; ?>">
                    <?php if ($editSection): ?>
                        <input type="hidden" name="id" value="<?php echo $editSection['id']; ?>">
                        <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($editSection['image'] ?? ''); ?>">
                    <?php endif; ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Type *</label>
                            <select name="type" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" <?php echo $editSection ? 'disabled' : ''; ?>>
                                <option value="">Select type...</option>
                                <option value="faculty_unit" <?php echo ($editSection['type'] ?? '') === 'faculty_unit' ? 'selected' : ''; ?>>Faculty Unit</option>
                                <option value="admin_representative" <?php echo ($editSection['type'] ?? '') === 'admin_representative' ? 'selected' : ''; ?>>Admin Representative</option>
                                <option value="program" <?php echo ($editSection['type'] ?? '') === 'program' ? 'selected' : ''; ?>>Program</option>
                            </select>
                            <?php if ($editSection): ?>
                                <input type="hidden" name="type" value="<?php echo htmlspecialchars($editSection['type']); ?>">
                            <?php endif; ?>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Display Order</label>
                            <input type="number" name="display_order" value="<?php echo htmlspecialchars($editSection['display_order'] ?? 0); ?>" min="0" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Name/Title *</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($editSection['title'] ?? ''); ?>" required placeholder="Faculty member name" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="mt-1 text-xs text-gray-500">Full name of the faculty member</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Position Title</label>
                        <input type="text" name="position_title" value="<?php echo htmlspecialchars($editSection['position_title'] ?? ''); ?>" placeholder="e.g., Program Head, Faculty Member" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="mt-1 text-xs text-gray-500">Position or role title (displayed below name)</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                        <textarea name="description" rows="3" placeholder="Short description" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-y"><?php echo htmlspecialchars($editSection['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Content</label>
                        <textarea name="content" rows="6" placeholder="Detailed content" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-y"><?php echo htmlspecialchars($editSection['content'] ?? ''); ?></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Image</label>
                        <?php if ($editSection && !empty($editSection['image'])): ?>
                            <div class="mb-3">
                                <img src="<?php echo getImageUrl($editSection['image']); ?>" alt="Current image" class="max-w-xs max-h-48 rounded-xl border border-gray-200">
                            </div>
                            <p class="text-xs text-gray-500 mb-2">Upload a new image to replace the current one.</p>
                        <?php endif; ?>
                        <input type="file" name="image" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <p class="mt-2 text-xs text-gray-500">Max size: 5MB. Formats: JPEG, PNG, GIF, WebP.</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Status *</label>
                        <select name="status" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="draft" <?php echo ($editSection['status'] ?? 'draft') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <?php if ($canPublish): ?>
                                <option value="published" <?php echo ($editSection['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Published</option>
                            <?php endif; ?>
                        </select>
                        <?php if ($isEditor): ?>
                            <p class="mt-1 text-xs text-gray-500">Editors can only save as Draft. Administrators can publish.</p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="flex gap-3 pt-4">
                        <button type="submit" class="px-8 py-3.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                            <?php echo $editSection ? 'Update Section' : 'Create Section'; ?>
                        </button>
                        <?php if ($editSection): ?>
                            <a href="?tab=sections" class="px-8 py-3.5 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition-colors inline-flex items-center">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Sections List -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Institute Sections</h2>
                <div class="space-y-3">
                    <?php if (empty($instituteSections)): ?>
                        <p class="text-gray-500 text-center py-8">No institute sections found.</p>
                    <?php else: ?>
                        <?php foreach ($instituteSections as $section): ?>
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-xl hover:shadow-md transition-shadow">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3">
                                        <span class="px-2 py-1 text-xs font-medium rounded <?php echo $section['status'] === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                            <?php echo htmlspecialchars($section['status']); ?>
                                        </span>
                                        <span class="px-2 py-1 text-xs font-medium rounded bg-blue-100 text-blue-800">
                                            <?php echo htmlspecialchars(str_replace('_', ' ', $section['type'])); ?>
                                        </span>
                                        <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($section['title']); ?></h3>
                                        <?php if (!empty($section['position_title'])): ?>
                                            <span class="text-sm text-gray-500 ml-2">- <?php echo htmlspecialchars($section['position_title']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($section['description']): ?>
                                        <p class="text-sm text-gray-600 mt-1 line-clamp-2"><?php echo htmlspecialchars(substr($section['description'], 0, 100)); ?>...</p>
                                    <?php endif; ?>
                                </div>
                                <div class="flex gap-2">
                                    <a href="?tab=sections&edit_section=<?php echo $section['id']; ?>" class="px-3 py-1.5 text-xs font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">Edit</a>
                                    <form method="POST" action="" class="inline" onsubmit="return confirm('Are you sure you want to delete this section?');">
                                        <input type="hidden" name="action" value="delete_section">
                                        <input type="hidden" name="id" value="<?php echo $section['id']; ?>">
                                        <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-white bg-red-500 rounded-lg hover:bg-red-600 transition-colors">Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
