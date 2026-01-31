<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/upload.php';

requireAdmin(null, false);

$message = '';
$messageType = '';
$activeTab = $_GET['tab'] ?? 'info'; // 'info', 'faculty', 'admin', 'programs'

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
            
            // For faculty_unit type, store unit in description field
            // For program type, store category in description field
            // For faculty_subcategory type, auto-generate URL from title (slug)
            $description = null;
            if (($_POST['type'] ?? '') === 'faculty_unit' && !empty($_POST['faculty_unit'])) {
                $description = $_POST['faculty_unit']; // Store unit (IS, CS, DS, Higher Ups) in description
            } elseif (($_POST['type'] ?? '') === 'program' && !empty($_POST['program_category'])) {
                $description = $_POST['program_category']; // Store program category in description
            } elseif (($_POST['type'] ?? '') === 'faculty_subcategory') {
                // Auto-generate URL from title (convert to lowercase slug)
                $title = $_POST['title'] ?? '';
                if (!empty($title)) {
                    // Convert title to URL-friendly slug
                    $slug = strtolower(trim($title));
                    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
                    $slug = trim($slug, '-');
                    // Generate URL path (relative to public directory)
                    $description = '/public/faculty-' . $slug . '.php';
                }
            } elseif (!empty($_POST['description'])) {
                $description = $_POST['description'];
            }
            
            $data = [
                'type' => $_POST['type'] ?? '',
                'title' => $_POST['title'] ?? '',
                'position_title' => $_POST['position_title'] ?? null,
                'description' => $description,
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
                    // Redirect to appropriate tab based on deleted section type
                    $redirectTab = 'faculty';
                    if ($section['type'] === 'admin_representative') {
                        $redirectTab = 'admin';
                    } elseif ($section['type'] === 'program') {
                        $redirectTab = 'programs';
                    }
                    header("Location: ?tab=" . $redirectTab . "&message=" . urlencode($message) . "&messageType=" . $messageType);
                    exit;
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
    // Determine tab based on section type
    if ($editSection) {
        $sectionType = $editSection['type'] ?? '';
        if ($sectionType === 'faculty_unit' || $sectionType === 'faculty_subcategory') {
            $activeTab = 'faculty';
        } elseif ($sectionType === 'admin_representative') {
            $activeTab = 'admin';
        } elseif ($sectionType === 'program') {
            $activeTab = 'programs';
        } else {
            $activeTab = 'faculty'; // Default fallback
        }
    }
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

        <?php 
        // Get message from URL if redirected
        if (isset($_GET['message'])) {
            $message = urldecode($_GET['message']);
            $messageType = $_GET['messageType'] ?? 'success';
        }
        if ($message): ?>
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
                <a href="?tab=faculty<?php echo ($editSection && ($editSection['type'] === 'faculty_unit' || $editSection['type'] === 'faculty_subcategory')) ? '&edit_section=' . $editSection['id'] : ''; ?>" class="px-4 py-2 font-semibold <?php echo $activeTab === 'faculty' ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-700'; ?> transition-colors">
                    Faculty Unit
                </a>
                <a href="?tab=admin<?php echo ($editSection && $editSection['type'] === 'admin_representative') ? '&edit_section=' . $editSection['id'] : ''; ?>" class="px-4 py-2 font-semibold <?php echo $activeTab === 'admin' ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-700'; ?> transition-colors">
                    Admin Representative
                </a>
                <a href="?tab=programs<?php echo ($editSection && $editSection['type'] === 'program') ? '&edit_section=' . $editSection['id'] : ''; ?>" class="px-4 py-2 font-semibold <?php echo $activeTab === 'programs' ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-700'; ?> transition-colors">
                    Programs
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

        <!-- Faculty Unit Tab -->
        <?php if ($activeTab === 'faculty'): 
            // Filter sections for faculty
            $facultySections = array_filter($instituteSections, function($s) {
                return $s['type'] === 'faculty_unit' || $s['type'] === 'faculty_subcategory';
            });
            // If editing, ensure type is set correctly
            if ($editSection && !in_array($editSection['type'] ?? '', ['faculty_unit', 'faculty_subcategory'])) {
                $editSection = null;
            }
            $currentType = $editSection['type'] ?? '';
        ?>
            <!-- Create/Edit Form -->
            <div class="mb-8 bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-900">
                        <?php echo $editSection ? 'Edit Faculty Section' : 'Create New Faculty Section'; ?>
                    </h2>
                    <?php if ($editSection): ?>
                        <a href="?tab=faculty" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
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
                                <option value="faculty_unit" <?php echo ($editSection['type'] ?? '') === 'faculty_unit' ? 'selected' : ''; ?>>Faculty Member</option>
                                <option value="faculty_subcategory" <?php echo ($editSection['type'] ?? '') === 'faculty_subcategory' ? 'selected' : ''; ?>>Faculty Subcategory</option>
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
                        <input type="text" name="title" value="<?php echo htmlspecialchars($editSection['title'] ?? ''); ?>" required placeholder="<?php echo ($currentType === 'faculty_subcategory') ? 'Subcategory name (e.g., RESEARCH, AWARDS)' : 'Faculty member name'; ?>" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="mt-1 text-xs text-gray-500"><?php echo ($currentType === 'faculty_subcategory') ? 'Name of the subcategory (displayed in sidebar)' : 'Full name of the faculty member'; ?></p>
                    </div>
                    
                    <?php if ($currentType === 'faculty_unit'): ?>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Position Title</label>
                        <input type="text" name="position_title" value="<?php echo htmlspecialchars($editSection['position_title'] ?? ''); ?>" placeholder="e.g., Program Head, Faculty Member" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="mt-1 text-xs text-gray-500">Position or role title (displayed below name)</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Faculty Unit *</label>
                        <select name="faculty_unit" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select unit...</option>
                            <option value="IS" <?php echo (($editSection['description'] ?? '') === 'IS') ? 'selected' : ''; ?>>IS (Information Systems)</option>
                            <option value="CS" <?php echo (($editSection['description'] ?? '') === 'CS') ? 'selected' : ''; ?>>CS (Computer Science)</option>
                            <option value="DS" <?php echo (($editSection['description'] ?? '') === 'DS') ? 'selected' : ''; ?>>DS (Data Science)</option>
                            <option value="Higher Ups" <?php echo (($editSection['description'] ?? '') === 'Higher Ups') ? 'selected' : ''; ?>>Higher Ups</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Select the faculty unit this member belongs to</p>
                    </div>
                    <?php elseif ($currentType === 'faculty_subcategory'): ?>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Subcategory URL *</label>
                        <input type="text" name="subcategory_url" value="<?php echo htmlspecialchars($editSection['description'] ?? ''); ?>" required placeholder="e.g., /public/faculty-research.php or /public/faculty-awards.php" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="mt-1 text-xs text-gray-500">Enter the URL path for this subcategory (e.g., /public/faculty-research.php). This will appear in the sidebar below Faculty Unit.</p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($currentType === 'faculty_unit'): ?>
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
                    <?php endif; ?>
                    
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
                            <a href="?tab=faculty" class="px-8 py-3.5 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition-colors inline-flex items-center">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Faculty Sections List -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Faculty Sections</h2>
                <?php if (empty($facultySections)): ?>
                    <p class="text-gray-500 text-center py-8">No faculty sections found.</p>
                <?php else: 
                    // Group faculty members by unit
                    $facultyByUnit = [];
                    $facultySubcategories = [];
                    foreach ($facultySections as $section) {
                        if ($section['type'] === 'faculty_unit') {
                            $unit = $section['description'] ?? 'Unassigned';
                            if (!isset($facultyByUnit[$unit])) {
                                $facultyByUnit[$unit] = [];
                            }
                            $facultyByUnit[$unit][] = $section;
                        } elseif ($section['type'] === 'faculty_subcategory') {
                            $facultySubcategories[] = $section;
                        }
                    }
                    // Define unit order
                    $unitOrder = ['IS', 'CS', 'DS', 'Higher Ups', 'Unassigned'];
                ?>
                    <div class="space-y-4">
                        <?php 
                        // Display faculty subcategories first
                        if (!empty($facultySubcategories)): ?>
                            <div class="mb-6">
                                <h3 class="text-lg font-semibold text-gray-800 mb-3">Faculty Subcategories</h3>
                                <div class="space-y-2">
                                    <?php foreach ($facultySubcategories as $subcat): ?>
                                        <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:shadow-md transition-shadow">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-3">
                                                    <span class="px-2 py-1 text-xs font-medium rounded <?php echo $subcat['status'] === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                                        <?php echo htmlspecialchars($subcat['status']); ?>
                                                    </span>
                                                    <h4 class="font-semibold text-gray-900"><?php echo htmlspecialchars($subcat['title']); ?></h4>
                                                    <?php if ($subcat['description']): ?>
                                                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded"><?php echo htmlspecialchars($subcat['description']); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="flex gap-2">
                                                <a href="?tab=faculty&edit_section=<?php echo $subcat['id']; ?>" class="px-3 py-1.5 text-xs font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">Edit</a>
                                                <form method="POST" action="" class="inline" onsubmit="return confirm('Are you sure you want to delete this subcategory?');">
                                                    <input type="hidden" name="action" value="delete_section">
                                                    <input type="hidden" name="id" value="<?php echo $subcat['id']; ?>">
                                                    <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-white bg-red-500 rounded-lg hover:bg-red-600 transition-colors">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Faculty Units with Hover Menu -->
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Faculty Units</h3>
                        <?php foreach ($unitOrder as $unit): 
                            if (!isset($facultyByUnit[$unit]) || empty($facultyByUnit[$unit])) continue;
                            $unitMembers = $facultyByUnit[$unit];
                        ?>
                            <div class="faculty-unit-group mb-4">
                                <div class="faculty-unit-header flex items-center justify-between p-4 border-2 border-gray-200 rounded-xl hover:border-indigo-400 hover:shadow-md transition-all cursor-pointer">
                                    <div class="flex items-center gap-3">
                                        <h4 class="text-lg font-bold text-gray-900"><?php echo htmlspecialchars($unit); ?></h4>
                                        <span class="px-2 py-1 text-xs font-medium rounded bg-indigo-100 text-indigo-800">
                                            <?php echo count($unitMembers); ?> member<?php echo count($unitMembers) !== 1 ? 's' : ''; ?>
                                        </span>
                                    </div>
                                    <svg class="w-5 h-5 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                                <div class="faculty-unit-members hidden mt-2 space-y-2 pl-4">
                                    <?php foreach ($unitMembers as $member): ?>
                                        <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:shadow-md transition-shadow bg-gray-50">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-3">
                                                    <span class="px-2 py-1 text-xs font-medium rounded <?php echo $member['status'] === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                                        <?php echo htmlspecialchars($member['status']); ?>
                                                    </span>
                                                    <h5 class="font-semibold text-gray-900"><?php echo htmlspecialchars($member['title']); ?></h5>
                                                    <?php if (!empty($member['position_title'])): ?>
                                                        <span class="text-sm text-gray-500">- <?php echo htmlspecialchars($member['position_title']); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="flex gap-2">
                                                <a href="?tab=faculty&edit_section=<?php echo $member['id']; ?>" class="px-3 py-1.5 text-xs font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">Edit</a>
                                                <form method="POST" action="" class="inline" onsubmit="return confirm('Are you sure you want to delete this faculty member?');">
                                                    <input type="hidden" name="action" value="delete_section">
                                                    <input type="hidden" name="id" value="<?php echo $member['id']; ?>">
                                                    <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-white bg-red-500 rounded-lg hover:bg-red-600 transition-colors">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Admin Representative Tab -->
        <?php if ($activeTab === 'admin'): 
            // Filter sections for admin
            $adminSections = array_filter($instituteSections, function($s) {
                return $s['type'] === 'admin_representative';
            });
            $currentType = $editSection['type'] ?? '';
            // If editing, ensure type is set correctly
            if ($editSection && $currentType !== 'admin_representative') {
                $editSection = null;
            }
        ?>
            <!-- Create/Edit Form -->
            <div class="mb-8 bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-900">
                        <?php echo $editSection ? 'Edit Admin Representative' : 'Create New Admin Representative'; ?>
                    </h2>
                    <?php if ($editSection): ?>
                        <a href="?tab=admin" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
                    <?php endif; ?>
                </div>
                <form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="action" value="<?php echo $editSection ? 'update_section' : 'create_section'; ?>">
                    <input type="hidden" name="type" value="admin_representative">
                    <?php if ($editSection): ?>
                        <input type="hidden" name="id" value="<?php echo $editSection['id']; ?>">
                        <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($editSection['image'] ?? ''); ?>">
                    <?php endif; ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Display Order</label>
                            <input type="number" name="display_order" value="<?php echo htmlspecialchars($editSection['display_order'] ?? 0); ?>" min="0" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Name/Title *</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($editSection['title'] ?? ''); ?>" required placeholder="Admin representative name" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="mt-1 text-xs text-gray-500">Full name of the admin representative</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Position Title</label>
                        <input type="text" name="position_title" value="<?php echo htmlspecialchars($editSection['position_title'] ?? ''); ?>" placeholder="e.g., Director, Administrator" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
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
                            <?php echo $editSection ? 'Update Admin Representative' : 'Create Admin Representative'; ?>
                        </button>
                        <?php if ($editSection): ?>
                            <a href="?tab=admin" class="px-8 py-3.5 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition-colors inline-flex items-center">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Admin Sections List -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Admin Representatives</h2>
                <div class="space-y-3">
                    <?php if (empty($adminSections)): ?>
                        <p class="text-gray-500 text-center py-8">No admin representatives found.</p>
                    <?php else: ?>
                        <?php foreach ($adminSections as $section): ?>
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-xl hover:shadow-md transition-shadow">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3">
                                        <span class="px-2 py-1 text-xs font-medium rounded <?php echo $section['status'] === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                            <?php echo htmlspecialchars($section['status']); ?>
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
                                    <a href="?tab=admin&edit_section=<?php echo $section['id']; ?>" class="px-3 py-1.5 text-xs font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">Edit</a>
                                    <form method="POST" action="" class="inline" onsubmit="return confirm('Are you sure you want to delete this admin representative?');">
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

        <!-- Programs Tab -->
        <?php if ($activeTab === 'programs'): 
            // Filter sections for programs
            $programSections = array_filter($instituteSections, function($s) {
                return $s['type'] === 'program';
            });
            $currentType = $editSection['type'] ?? '';
            // If editing, ensure type is set correctly
            if ($editSection && $currentType !== 'program') {
                $editSection = null;
            }
        ?>
            <!-- Create/Edit Form -->
            <div class="mb-8 bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-900">
                        <?php echo $editSection ? 'Edit Program' : 'Create New Program'; ?>
                    </h2>
                    <?php if ($editSection): ?>
                        <a href="?tab=programs" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
                    <?php endif; ?>
                </div>
                <form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="action" value="<?php echo $editSection ? 'update_section' : 'create_section'; ?>">
                    <input type="hidden" name="type" value="program">
                    <?php if ($editSection): ?>
                        <input type="hidden" name="id" value="<?php echo $editSection['id']; ?>">
                        <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($editSection['image'] ?? ''); ?>">
                    <?php endif; ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Display Order</label>
                            <input type="number" name="display_order" value="<?php echo htmlspecialchars($editSection['display_order'] ?? 0); ?>" min="0" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Program Name/Title *</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($editSection['title'] ?? ''); ?>" required placeholder="Program name" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="mt-1 text-xs text-gray-500">Name of the program</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Program Category *</label>
                        <input type="text" name="program_category" value="<?php echo htmlspecialchars($editSection['description'] ?? ''); ?>" required placeholder="e.g., Undergraduate, Graduate, Certificate" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="mt-1 text-xs text-gray-500">Enter the program category/unit (e.g., Undergraduate Programs, Graduate Programs, Certificate Programs)</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Content</label>
                        <textarea name="content" rows="6" placeholder="Program description and details" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-y"><?php echo htmlspecialchars($editSection['content'] ?? ''); ?></textarea>
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
                            <?php echo $editSection ? 'Update Program' : 'Create Program'; ?>
                        </button>
                        <?php if ($editSection): ?>
                            <a href="?tab=programs" class="px-8 py-3.5 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition-colors inline-flex items-center">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Programs List -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Programs</h2>
                <?php if (empty($programSections)): ?>
                    <p class="text-gray-500 text-center py-8">No programs found.</p>
                <?php else: 
                    // Group programs by category
                    $programsByCategory = [];
                    foreach ($programSections as $section) {
                        $category = $section['description'] ?? 'Uncategorized';
                        if (!isset($programsByCategory[$category])) {
                            $programsByCategory[$category] = [];
                        }
                        $programsByCategory[$category][] = $section;
                    }
                    // Sort categories alphabetically
                    ksort($programsByCategory);
                ?>
                    <div class="space-y-4">
                        <?php foreach ($programsByCategory as $category => $programs): ?>
                            <div class="program-category-group mb-4">
                                <div class="program-category-header flex items-center justify-between p-4 border-2 border-gray-200 rounded-xl hover:border-indigo-400 hover:shadow-md transition-all cursor-pointer">
                                    <div class="flex items-center gap-3">
                                        <h4 class="text-lg font-bold text-gray-900"><?php echo htmlspecialchars($category); ?></h4>
                                        <span class="px-2 py-1 text-xs font-medium rounded bg-indigo-100 text-indigo-800">
                                            <?php echo count($programs); ?> program<?php echo count($programs) !== 1 ? 's' : ''; ?>
                                        </span>
                                    </div>
                                    <svg class="w-5 h-5 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                                <div class="program-category-items hidden mt-2 space-y-2 pl-4">
                                    <?php foreach ($programs as $program): ?>
                                        <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:shadow-md transition-shadow bg-gray-50">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-3">
                                                    <span class="px-2 py-1 text-xs font-medium rounded <?php echo $program['status'] === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                                        <?php echo htmlspecialchars($program['status']); ?>
                                                    </span>
                                                    <h5 class="font-semibold text-gray-900"><?php echo htmlspecialchars($program['title']); ?></h5>
                                                </div>
                                                <?php if ($program['content']): ?>
                                                    <p class="text-sm text-gray-600 mt-1 line-clamp-2"><?php echo htmlspecialchars(substr($program['content'], 0, 100)); ?>...</p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex gap-2">
                                                <a href="?tab=programs&edit_section=<?php echo $program['id']; ?>" class="px-3 py-1.5 text-xs font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">Edit</a>
                                                <form method="POST" action="" class="inline" onsubmit="return confirm('Are you sure you want to delete this program?');">
                                                    <input type="hidden" name="action" value="delete_section">
                                                    <input type="hidden" name="id" value="<?php echo $program['id']; ?>">
                                                    <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-white bg-red-500 rounded-lg hover:bg-red-600 transition-colors">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Faculty Unit and Program Category Hover/Click Functionality
document.addEventListener('DOMContentLoaded', function() {
    // Faculty Unit Groups
    const facultyUnitHeaders = document.querySelectorAll('.faculty-unit-header');
    facultyUnitHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const members = this.nextElementSibling;
            const arrow = this.querySelector('svg');
            if (members) {
                members.classList.toggle('hidden');
                if (arrow) {
                    arrow.style.transform = members.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
                }
            }
        });
        
        // Hover functionality
        header.addEventListener('mouseenter', function() {
            const members = this.nextElementSibling;
            if (members && members.classList.contains('hidden')) {
                members.classList.remove('hidden');
                const arrow = this.querySelector('svg');
                if (arrow) {
                    arrow.style.transform = 'rotate(180deg)';
                }
            }
        });
    });
    
    // Program Category Groups
    const programCategoryHeaders = document.querySelectorAll('.program-category-header');
    programCategoryHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const items = this.nextElementSibling;
            const arrow = this.querySelector('svg');
            if (items) {
                items.classList.toggle('hidden');
                if (arrow) {
                    arrow.style.transform = items.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
                }
            }
        });
        
        // Hover functionality
        header.addEventListener('mouseenter', function() {
            const items = this.nextElementSibling;
            if (items && items.classList.contains('hidden')) {
                items.classList.remove('hidden');
                const arrow = this.querySelector('svg');
                if (arrow) {
                    arrow.style.transform = 'rotate(180deg)';
                }
            }
        });
    });
    
    // Keep items visible when hovering over them
    const facultyUnitGroups = document.querySelectorAll('.faculty-unit-group');
    facultyUnitGroups.forEach(group => {
        group.addEventListener('mouseleave', function() {
            const members = this.querySelector('.faculty-unit-members');
            const arrow = this.querySelector('.faculty-unit-header svg');
            if (members && !members.classList.contains('hidden')) {
                members.classList.add('hidden');
                if (arrow) {
                    arrow.style.transform = 'rotate(0deg)';
                }
            }
        });
    });
    
    const programCategoryGroups = document.querySelectorAll('.program-category-group');
    programCategoryGroups.forEach(group => {
        group.addEventListener('mouseleave', function() {
            const items = this.querySelector('.program-category-items');
            const arrow = this.querySelector('.program-category-header svg');
            if (items && !items.classList.contains('hidden')) {
                items.classList.add('hidden');
                if (arrow) {
                    arrow.style.transform = 'rotate(0deg)';
                }
            }
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>
