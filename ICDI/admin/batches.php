<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/upload.php';

requireAdmin(null, false);

$message = '';
$messageType = '';

// Handle success messages from redirects
if (isset($_GET['message'])) {
    switch ($_GET['message']) {
        case 'member_added':
            $message = 'Batch member added successfully!';
            $messageType = 'success';
            break;
        case 'member_added_no_image':
            $message = 'Batch member added successfully! (Note: Image upload failed, but member was added without image)';
            $messageType = 'success';
            break;
        case 'member_updated':
            $message = 'Batch member updated successfully!';
            $messageType = 'success';
            break;
        case 'member_updated_no_image':
            $message = 'Batch member updated successfully! (Note: Image upload failed, but member was updated)';
            $messageType = 'success';
            break;
        case 'member_deleted':
            $message = 'Batch member deleted successfully!';
            $messageType = 'success';
            break;
    }
}

// Fetch organizations for dropdowns
$orgOptions = dbFetchAll("SELECT id, name, acronym FROM student_organizations WHERE status != 'archived' ORDER BY display_order ASC, name ASC");

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create' || $_POST['action'] === 'update') {
            $requestedStatus = $_POST['status'] ?? 'active';
            $data = [
                'organization_id' => !empty($_POST['organization_id']) ? intval($_POST['organization_id']) : null,
                'academic_year' => $_POST['academic_year'] ?? '',
                'start_year' => $_POST['start_year'] ?? date('Y'),
                'end_year' => $_POST['end_year'] ?? date('Y') + 1,
                'description' => $_POST['description'] ?? '',
                'target_group' => $_POST['target_group'] ?? 'all',
                'status' => normalizeStatusByRole($requestedStatus, ['active']),
                'display_order' => intval($_POST['display_order'] ?? 0),
                'created_by' => $_SESSION['admin_id'] ?? null
            ];
            
            // Handle image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = uploadImage($_FILES['image'], 'images');
                if ($uploadResult['success']) {
                    // Delete old image if updating
                    if ($_POST['action'] === 'update' && !empty($_POST['old_image'])) {
                        deleteUploadedFile($_POST['old_image']);
                    }
                    $data['image'] = $uploadResult['path'];
                } else {
                    $message = 'Image upload failed: ' . $uploadResult['error'];
                    $messageType = 'error';
                }
            } elseif ($_POST['action'] === 'update' && !empty($_POST['old_image'])) {
                // Keep existing image if no new upload
                $data['image'] = $_POST['old_image'];
            }
            
            if (empty($message)) {
                if ($_POST['action'] === 'create') {
                    $result = dbInsert('batches', $data);
                    if ($result) {
                        if ($data['status'] === 'active') {
                            auditLog('publish', 'Batch created and published', 'batch', $result);
                        }
                        $message = 'Batch created successfully!';
                        $messageType = 'success';
                    } else {
                        $message = 'Error creating batch.';
                        $messageType = 'error';
                    }
                } else {
                    $id = intval($_POST['id']);
                    $existing = dbFetchOne("SELECT status FROM batches WHERE id = ?", [$id]);
                    $result = dbUpdate('batches', $data, 'id = :id', ['id' => $id]);
                    if ($result) {
                        if ($existing && $data['status'] === 'active' && $existing['status'] !== 'active') {
                            auditLog('publish', 'Batch published', 'batch', $id);
                        }
                        $message = 'Batch updated successfully!';
                        $messageType = 'success';
                    } else {
                        $message = 'Error updating batch.';
                        $messageType = 'error';
                    }
                }
            }
        } elseif ($_POST['action'] === 'delete') {
            // Only super_admin can delete batches
            if (!isSuperAdmin()) {
                $message = 'Only super administrators can delete batches.';
                $messageType = 'error';
            } else {
                $id = intval($_POST['id']);
                $batch = dbFetchOne("SELECT * FROM batches WHERE id = ?", [$id]);
                if (!$batch) {
                    $message = 'Batch not found.';
                    $messageType = 'error';
                } else {
                    // Delete batch image if exists
                    if (!empty($batch['image'])) {
                        deleteUploadedFile($batch['image']);
                    }
                    
                    // Delete batch members and their images
                    $batchMembers = dbFetchAll("SELECT * FROM batch_members WHERE batch_id = ?", [$id]);
                    foreach ($batchMembers as $member) {
                        if (!empty($member['image'])) {
                            deleteUploadedFile($member['image']);
                        }
                    }
                    
                    // Delete the batch (batch_members will be deleted via CASCADE)
                    $result = dbDelete('batches', 'id = :id', ['id' => $id]);
                    if ($result) {
                        auditLog('delete', 'Batch permanently deleted', 'batch', $id);
                        $message = 'Batch deleted successfully!';
                        $messageType = 'success';
                    } else {
                        $message = 'Error deleting batch.';
                        $messageType = 'error';
                    }
                }
            }
        } elseif ($_POST['action'] === 'add_member' || $_POST['action'] === 'update_member') {
            $batchId = intval($_POST['batch_id'] ?? 0);
            if ($batchId <= 0) {
                $message = 'Invalid batch ID.';
                $messageType = 'error';
            } else {
                $memberData = [
                    'batch_id' => $batchId,
                    'group_type' => $_POST['group_type'] ?? '',
                    'name' => trim($_POST['name'] ?? ''),
                    'position_title' => trim($_POST['position_title'] ?? ''),
                    'display_order' => intval($_POST['display_order'] ?? 0)
                ];
                
                // Validate group_type
                $validTypes = ['adviser', 'co_adviser', 'executive_officer', 'executive_associate'];
                if (!in_array($memberData['group_type'], $validTypes)) {
                    $message = 'Invalid member type.';
                    $messageType = 'error';
                } elseif (empty($memberData['name']) || empty($memberData['position_title'])) {
                    $message = 'Name and position title are required.';
                    $messageType = 'error';
                } else {
                    // Handle image upload (optional - don't block submission if it fails)
                    $imageUploadError = false;
                    if (isset($_FILES['member_image']) && $_FILES['member_image']['error'] === UPLOAD_ERR_OK) {
                        $uploadResult = uploadImage($_FILES['member_image'], 'images');
                        if ($uploadResult['success']) {
                            // Delete old image if updating
                            if ($_POST['action'] === 'update_member' && !empty($_POST['old_member_image'])) {
                                deleteUploadedFile($_POST['old_member_image']);
                            }
                            $memberData['image'] = $uploadResult['path'];
                        } else {
                            // Image upload failed, but don't block member creation
                            // Just log it and continue without image
                            $imageUploadError = true;
                            error_log('Batch member image upload failed: ' . $uploadResult['error']);
                        }
                    } elseif ($_POST['action'] === 'update_member' && !empty($_POST['old_member_image'])) {
                        // Keep existing image if no new upload
                        $memberData['image'] = $_POST['old_member_image'];
                    }
                    
                    // Proceed with member creation/update (image is optional)
                    if ($_POST['action'] === 'add_member') {
                        $result = dbInsert('batch_members', $memberData);
                        if ($result) {
                            if ($imageUploadError) {
                                header('Location: ' . ADMIN_URL . '/batches.php?edit=' . $batchId . '&message=member_added_no_image');
                            } else {
                                header('Location: ' . ADMIN_URL . '/batches.php?edit=' . $batchId . '&message=member_added');
                            }
                            exit;
                        } else {
                            $message = 'Error adding batch member.';
                            $messageType = 'error';
                        }
                    } else {
                        $memberId = intval($_POST['member_id'] ?? 0);
                        if ($memberId <= 0) {
                            $message = 'Invalid member ID.';
                            $messageType = 'error';
                        } else {
                            $result = dbUpdate('batch_members', $memberData, 'id = :id', ['id' => $memberId]);
                            if ($result) {
                                if ($imageUploadError) {
                                    header('Location: ' . ADMIN_URL . '/batches.php?edit=' . $batchId . '&message=member_updated_no_image');
                                } else {
                                    header('Location: ' . ADMIN_URL . '/batches.php?edit=' . $batchId . '&message=member_updated');
                                }
                                exit;
                            } else {
                                $message = 'Error updating batch member.';
                                $messageType = 'error';
                            }
                        }
                    }
                }
            }
        } elseif ($_POST['action'] === 'delete_member') {
            $memberId = intval($_POST['member_id'] ?? 0);
            $batchId = intval($_POST['batch_id'] ?? 0);
            if ($memberId <= 0) {
                $message = 'Invalid member ID.';
                $messageType = 'error';
            } else {
                // Get member image and batch_id before deletion
                $member = dbFetchOne("SELECT image, batch_id FROM batch_members WHERE id = ?", [$memberId]);
                if ($member) {
                    // Use batch_id from database if not provided in POST
                    if ($batchId <= 0) {
                        $batchId = $member['batch_id'];
                    }
                    $result = dbDelete('batch_members', 'id = :id', ['id' => $memberId]);
                    if ($result) {
                        // Delete associated image
                        if (!empty($member['image'])) {
                            deleteUploadedFile($member['image']);
                        }
                        if ($batchId > 0) {
                            header('Location: ' . ADMIN_URL . '/batches.php?edit=' . $batchId . '&message=member_deleted');
                            exit;
                        } else {
                            $message = 'Batch member deleted successfully!';
                            $messageType = 'success';
                        }
                    } else {
                        $message = 'Error deleting batch member.';
                        $messageType = 'error';
                    }
                } else {
                    $message = 'Member not found.';
                    $messageType = 'error';
                }
            }
        }
    }
}

// Fetch batches
$batches = dbFetchAll("
    SELECT 
        b.*,
        so.name AS org_name,
        so.acronym AS org_acronym
    FROM batches b
    LEFT JOIN student_organizations so ON so.id = b.organization_id
    ORDER BY b.display_order DESC, b.start_year DESC
");

// Fetch single batch for editing
$editBatch = null;
$batchMembers = [];
$editMember = null;
if (isset($_GET['edit'])) {
    $editBatch = dbFetchOne("SELECT * FROM batches WHERE id = ?", [intval($_GET['edit'])]);
    if ($editBatch) {
        $batchMembers = dbFetchAll("SELECT * FROM batch_members WHERE batch_id = ? ORDER BY group_type ASC, display_order ASC, id ASC", [$editBatch['id']]);
        
        // Check if editing a specific member
        if (isset($_GET['edit_member'])) {
            $editMember = dbFetchOne("SELECT * FROM batch_members WHERE id = ? AND batch_id = ?", [intval($_GET['edit_member']), $editBatch['id']]);
        }
    }
}

$pageTitle = 'Manage Batches - PROWLWAY Admin';
$hideHeader = true;
$bodyClass = 'admin-panel-page';
include '../includes/header.php';
?>

<div class="admin-panel-wrapper min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <div class="max-w-6xl mx-auto px-6 py-8">
        <!-- Page Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Manage Batches</h1>
                <p class="text-sm text-gray-500 mt-1">Create, update, and manage student batches.</p>
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

        <!-- Create/Edit Form -->
        <div class="mb-8 bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900">
                    <?php echo $editBatch ? 'Edit Batch' : 'Create New Batch'; ?>
                </h2>
            </div>
            <form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
                <input type="hidden" name="action" value="<?php echo $editBatch ? 'update' : 'create'; ?>">
                <?php if ($editBatch): ?>
                    <input type="hidden" name="id" value="<?php echo $editBatch['id']; ?>">
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Academic Year *</label>
                        <input type="text" name="academic_year" value="<?php echo htmlspecialchars($editBatch['academic_year'] ?? ''); ?>" required placeholder="A.Y. 2025-2026" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="active" <?php echo ($editBatch['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="draft" <?php echo ($editBatch['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Organization</label>
                        <select name="organization_id" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">— Not set —</option>
                            <?php foreach ($orgOptions as $org): ?>
                                <?php
                                    $label = trim(($org['acronym'] ? $org['acronym'] . ' — ' : '') . ($org['name'] ?? ''));
                                    $selected = !empty($editBatch['organization_id']) && intval($editBatch['organization_id']) === intval($org['id']);
                                ?>
                                <option value="<?php echo (int)$org['id']; ?>" <?php echo $selected ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="mt-2 text-xs text-gray-500">Which student organization this batch is for (ex: ICDISG).</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">This batch is for</label>
                        <?php $tg = $editBatch['target_group'] ?? 'all'; ?>
                        <select name="target_group" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="all" <?php echo $tg === 'all' ? 'selected' : ''; ?>>All (Advisers + Co-Advisers + Executive Officers + Executive Associates)</option>
                            <option value="adviser" <?php echo $tg === 'adviser' ? 'selected' : ''; ?>>Advisers</option>
                            <option value="co_adviser" <?php echo $tg === 'co_adviser' ? 'selected' : ''; ?>>Co-Advisers</option>
                            <option value="executive_officer" <?php echo $tg === 'executive_officer' ? 'selected' : ''; ?>>Executive Officers</option>
                            <option value="executive_associate" <?php echo $tg === 'executive_associate' ? 'selected' : ''; ?>>Executive Associates</option>
                        </select>
                        <p class="mt-2 text-xs text-gray-500">Controls what group is emphasized/shown on the batch detail page.</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Start Year *</label>
                        <input type="number" name="start_year" value="<?php echo $editBatch['start_year'] ?? date('Y'); ?>" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">End Year *</label>
                        <input type="number" name="end_year" value="<?php echo $editBatch['end_year'] ?? date('Y') + 1; ?>" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Image</label>
                    <?php if ($editBatch && !empty($editBatch['image'])): ?>
                        <div class="mb-3">
                            <img src="<?php echo getImageUrl($editBatch['image']); ?>" alt="Current image" class="max-w-xs max-h-48 rounded-xl border border-gray-200">
                            <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($editBatch['image']); ?>">
                        </div>
                        <p class="text-xs text-gray-500 mb-2">Upload a new image to replace the current one.</p>
                    <?php endif; ?>
                    <input type="file" name="image" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    <p class="mt-2 text-xs text-gray-500">Max size: 5MB. Formats: JPEG, PNG, GIF, WebP.</p>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                    <textarea name="description" rows="3" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-y"><?php echo htmlspecialchars($editBatch['description'] ?? ''); ?></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Display Order</label>
                    <input type="number" name="display_order" value="<?php echo $editBatch['display_order'] ?? 0; ?>" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="px-6 py-3 bg-indigo-600 text-white font-semibold rounded-xl shadow hover:bg-indigo-700 transition-colors">
                        <?php echo $editBatch ? 'Update Batch' : 'Create Batch'; ?>
                    </button>
                    <?php if ($editBatch): ?>
                        <a href="<?php echo ADMIN_URL; ?>/batches.php" class="px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors">
                            Cancel
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Batch Members Management -->
        <?php if ($editBatch): ?>
            <div class="mb-8 bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Batch Members</h2>
                        <p class="text-sm text-gray-500 mt-1">Add multiple members to this batch. Each member can have their own name, picture, and position title. You can add multiple advisers, co-advisers, executive officers, and executive associates.</p>
                    </div>
                </div>

                <!-- Add/Edit Member Form -->
                <div id="member-form-section" class="mb-6 p-4 bg-gray-50 rounded-xl border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">
                        <?php echo $editMember ? 'Edit Member' : 'Add New Member'; ?>
                    </h3>
                    <?php if (!$editMember): ?>
                        <p class="text-sm text-gray-600 mb-4">Fill in the form below to add a member. You can add multiple members - just fill out the form and click "Add Member" for each one. The form will reset automatically after each addition.</p>
                    <?php endif; ?>
                    <form id="memberForm" method="POST" action="" enctype="multipart/form-data" class="space-y-4">
                        <input type="hidden" name="action" value="<?php echo $editMember ? 'update_member' : 'add_member'; ?>">
                        <input type="hidden" name="batch_id" value="<?php echo $editBatch['id']; ?>">
                        <?php if ($editMember): ?>
                            <input type="hidden" name="member_id" value="<?php echo $editMember['id']; ?>">
                            <input type="hidden" name="old_member_image" value="<?php echo htmlspecialchars($editMember['image'] ?? ''); ?>">
                        <?php endif; ?>
                        <?php if (function_exists('generateCSRFToken')): ?><input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>"><?php endif; ?>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Name *</label>
                                <input type="text" name="name" value="<?php echo htmlspecialchars($editMember['name'] ?? ''); ?>" required class="w-full px-4 py-2 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Position Title *</label>
                                <input type="text" name="position_title" value="<?php echo htmlspecialchars($editMember['position_title'] ?? ''); ?>" required class="w-full px-4 py-2 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Member Type *</label>
                                <?php $currentType = $editMember['group_type'] ?? ''; ?>
                                <select name="group_type" required class="w-full px-4 py-2 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Select type...</option>
                                    <option value="adviser" <?php echo $currentType === 'adviser' ? 'selected' : ''; ?>>Adviser</option>
                                    <option value="co_adviser" <?php echo $currentType === 'co_adviser' ? 'selected' : ''; ?>>Co-Adviser</option>
                                    <option value="executive_officer" <?php echo $currentType === 'executive_officer' ? 'selected' : ''; ?>>Executive Officer</option>
                                    <option value="executive_associate" <?php echo $currentType === 'executive_associate' ? 'selected' : ''; ?>>Executive Associate</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Display Order</label>
                                <input type="number" name="display_order" value="<?php echo $editMember['display_order'] ?? 0; ?>" class="w-full px-4 py-2 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Profile Image</label>
                            <?php if ($editMember && !empty($editMember['image'])): ?>
                                <div class="mb-2">
                                    <img src="<?php echo getImageUrl($editMember['image']); ?>" alt="Current image" class="max-w-xs max-h-32 rounded-xl border border-gray-200">
                                </div>
                            <?php endif; ?>
                            <input type="file" name="member_image" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            <p class="mt-1 text-xs text-gray-500">Max size: 5MB. Formats: JPEG, PNG, GIF, WebP.</p>
                        </div>
                        
                        <div class="flex items-center gap-3 pt-2">
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors">
                                <?php echo $editMember ? 'Update Member' : 'Add Member'; ?>
                            </button>
                            <?php if ($editMember): ?>
                                <a href="<?php echo ADMIN_URL; ?>/batches.php?edit=<?php echo $editBatch['id']; ?>" class="px-4 py-2 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors">
                                    Cancel
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- Members List -->
                <?php
                $membersByType = [
                    'adviser' => [],
                    'co_adviser' => [],
                    'executive_officer' => [],
                    'executive_associate' => []
                ];
                foreach ($batchMembers as $member) {
                    if (isset($membersByType[$member['group_type']])) {
                        $membersByType[$member['group_type']][] = $member;
                    }
                }
                ?>
                
                <!-- Advisers & Co-Advisers (Profile Cards) -->
                <?php if (!empty($membersByType['adviser']) || !empty($membersByType['co_adviser'])): ?>
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Advisers & Co-Advisers</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <!-- Adviser Cards (Show all advisers) -->
                            <?php if (!empty($membersByType['adviser'])): ?>
                                <?php foreach ($membersByType['adviser'] as $adviser): ?>
                                <div class="flex items-center gap-3 p-4 bg-white border-2 border-gray-200 rounded-lg hover:shadow-md transition-all">
                                    <?php if (!empty($adviser['image'])): ?>
                                        <img src="<?php echo getImageUrl($adviser['image']); ?>" alt="<?php echo htmlspecialchars($adviser['name']); ?>" class="w-12 h-12 rounded-full object-cover border-2 border-gray-200 flex-shrink-0">
                                    <?php else: ?>
                                        <div class="w-12 h-12 rounded-full bg-gray-200 border-2 border-gray-300 flex items-center justify-center text-gray-400 text-sm flex-shrink-0">👤</div>
                                    <?php endif; ?>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-gray-900 truncate" title="<?php echo htmlspecialchars($adviser['name']); ?>"><?php echo htmlspecialchars($adviser['name']); ?></p>
                                        <p class="text-xs text-gray-500 truncate" title="<?php echo htmlspecialchars($adviser['position_title']); ?>"><?php echo htmlspecialchars($adviser['position_title']); ?></p>
                                    </div>
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <a href="<?php echo ADMIN_URL; ?>/batches.php?edit=<?php echo $editBatch['id']; ?>&edit_member=<?php echo $adviser['id']; ?>" class="px-2.5 py-1 text-xs font-semibold text-white bg-indigo-600 rounded hover:bg-indigo-700 transition-colors shadow-sm">
                                            Edit
                                        </a>
                                        <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this member?');">
                                            <input type="hidden" name="action" value="delete_member">
                                            <input type="hidden" name="member_id" value="<?php echo $adviser['id']; ?>">
                                            <input type="hidden" name="batch_id" value="<?php echo $editBatch['id']; ?>">
                                            <?php if (function_exists('generateCSRFToken')): ?><input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>"><?php endif; ?>
                                            <button type="submit" class="px-2.5 py-1 text-xs font-semibold text-white bg-red-600 rounded hover:bg-red-700 transition-colors shadow-sm">
                                                Del
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            
                            <!-- Co-Adviser Cards (Show all co-advisers) -->
                            <?php if (!empty($membersByType['co_adviser'])): ?>
                                <?php foreach ($membersByType['co_adviser'] as $coAdviser): ?>
                                <div class="flex items-center gap-3 p-4 bg-white border-2 border-gray-200 rounded-lg hover:shadow-md transition-all">
                                    <?php if (!empty($coAdviser['image'])): ?>
                                        <img src="<?php echo getImageUrl($coAdviser['image']); ?>" alt="<?php echo htmlspecialchars($coAdviser['name']); ?>" class="w-12 h-12 rounded-full object-cover border-2 border-gray-200 flex-shrink-0">
                                    <?php else: ?>
                                        <div class="w-12 h-12 rounded-full bg-gray-200 border-2 border-gray-300 flex items-center justify-center text-gray-400 text-sm flex-shrink-0">👤</div>
                                    <?php endif; ?>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-gray-900 truncate" title="<?php echo htmlspecialchars($coAdviser['name']); ?>"><?php echo htmlspecialchars($coAdviser['name']); ?></p>
                                        <p class="text-xs text-gray-500 truncate" title="<?php echo htmlspecialchars($coAdviser['position_title']); ?>"><?php echo htmlspecialchars($coAdviser['position_title']); ?></p>
                                    </div>
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <a href="<?php echo ADMIN_URL; ?>/batches.php?edit=<?php echo $editBatch['id']; ?>&edit_member=<?php echo $coAdviser['id']; ?>" class="px-2.5 py-1 text-xs font-semibold text-white bg-indigo-600 rounded hover:bg-indigo-700 transition-colors shadow-sm">
                                            Edit
                                        </a>
                                        <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this member?');">
                                            <input type="hidden" name="action" value="delete_member">
                                            <input type="hidden" name="member_id" value="<?php echo $coAdviser['id']; ?>">
                                            <input type="hidden" name="batch_id" value="<?php echo $editBatch['id']; ?>">
                                            <?php if (function_exists('generateCSRFToken')): ?><input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>"><?php endif; ?>
                                            <button type="submit" class="px-2.5 py-1 text-xs font-semibold text-white bg-red-600 rounded hover:bg-red-700 transition-colors shadow-sm">
                                                Del
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Executive Officers -->
                <?php if (!empty($membersByType['executive_officer'])): ?>
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Executive Officers</h3>
                        <?php
                        // Group officers by position_title
                        $officersByPosition = [];
                        foreach ($membersByType['executive_officer'] as $member) {
                            $position = $member['position_title'] ?: 'Member';
                            if (!isset($officersByPosition[$position])) {
                                $officersByPosition[$position] = [];
                            }
                            $officersByPosition[$position][] = $member;
                        }
                        ?>
                        <div class="space-y-6">
                            <?php foreach ($officersByPosition as $position => $officers): ?>
                                <div class="bg-white border-2 border-gray-200 rounded-xl p-6">
                                    <h4 class="text-lg font-bold text-gray-900 mb-4 uppercase"><?php echo htmlspecialchars($position); ?></h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        <?php foreach ($officers as $member): ?>
                                            <div class="flex items-center gap-3 p-4 bg-white border-2 border-gray-200 rounded-lg hover:shadow-md transition-all">
                                                <?php if (!empty($member['image'])): ?>
                                                    <img src="<?php echo getImageUrl($member['image']); ?>" alt="<?php echo htmlspecialchars($member['name']); ?>" class="w-12 h-12 rounded-full object-cover border-2 border-gray-200 flex-shrink-0">
                                                <?php else: ?>
                                                    <div class="w-12 h-12 rounded-full bg-gray-200 border-2 border-gray-300 flex items-center justify-center text-gray-400 text-sm flex-shrink-0">👤</div>
                                                <?php endif; ?>
                                                <div class="flex-1 min-w-0">
                                                    <p class="font-semibold text-gray-900 truncate" title="<?php echo htmlspecialchars($member['name']); ?>"><?php echo htmlspecialchars($member['name']); ?></p>
                                                    <p class="text-xs text-gray-500 truncate" title="<?php echo htmlspecialchars($member['position_title']); ?>"><?php echo htmlspecialchars($member['position_title']); ?></p>
                                                </div>
                                                <div class="flex items-center gap-2 flex-shrink-0">
                                                    <a href="<?php echo ADMIN_URL; ?>/batches.php?edit=<?php echo $editBatch['id']; ?>&edit_member=<?php echo $member['id']; ?>" class="px-2.5 py-1 text-xs font-semibold text-white bg-indigo-600 rounded hover:bg-indigo-700 transition-colors shadow-sm">
                                                        Edit
                                                    </a>
                                                    <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this member?');">
                                                        <input type="hidden" name="action" value="delete_member">
                                                        <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                                        <input type="hidden" name="batch_id" value="<?php echo $editBatch['id']; ?>">
                                                        <?php if (function_exists('generateCSRFToken')): ?><input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>"><?php endif; ?>
                                                        <button type="submit" class="px-2.5 py-1 text-xs font-semibold text-white bg-red-600 rounded hover:bg-red-700 transition-colors shadow-sm">
                                                            Del
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Executive Associates -->
                <?php if (!empty($membersByType['executive_associate'])): ?>
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Executive Associates</h3>
                        <?php
                        // Group associates by position_title
                        $associatesByPosition = [];
                        foreach ($membersByType['executive_associate'] as $member) {
                            $position = $member['position_title'] ?: 'Member';
                            if (!isset($associatesByPosition[$position])) {
                                $associatesByPosition[$position] = [];
                            }
                            $associatesByPosition[$position][] = $member;
                        }
                        ?>
                        <div class="space-y-6">
                            <?php foreach ($associatesByPosition as $position => $associates): ?>
                                <div class="bg-white border-2 border-gray-200 rounded-xl p-6">
                                    <h4 class="text-lg font-bold text-gray-900 mb-4 uppercase"><?php echo htmlspecialchars($position); ?></h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        <?php foreach ($associates as $member): ?>
                                            <div class="flex items-center gap-3 p-4 bg-white border-2 border-gray-200 rounded-lg hover:shadow-md transition-all">
                                                <?php if (!empty($member['image'])): ?>
                                                    <img src="<?php echo getImageUrl($member['image']); ?>" alt="<?php echo htmlspecialchars($member['name']); ?>" class="w-12 h-12 rounded-full object-cover border-2 border-gray-200 flex-shrink-0">
                                                <?php else: ?>
                                                    <div class="w-12 h-12 rounded-full bg-gray-200 border-2 border-gray-300 flex items-center justify-center text-gray-400 text-sm flex-shrink-0">👤</div>
                                                <?php endif; ?>
                                                <div class="flex-1 min-w-0">
                                                    <p class="font-semibold text-gray-900 truncate" title="<?php echo htmlspecialchars($member['name']); ?>"><?php echo htmlspecialchars($member['name']); ?></p>
                                                    <p class="text-xs text-gray-500 truncate" title="<?php echo htmlspecialchars($member['position_title']); ?>"><?php echo htmlspecialchars($member['position_title']); ?></p>
                                                </div>
                                                <div class="flex items-center gap-2 flex-shrink-0">
                                                    <a href="<?php echo ADMIN_URL; ?>/batches.php?edit=<?php echo $editBatch['id']; ?>&edit_member=<?php echo $member['id']; ?>" class="px-2.5 py-1 text-xs font-semibold text-white bg-indigo-600 rounded hover:bg-indigo-700 transition-colors shadow-sm">
                                                        Edit
                                                    </a>
                                                    <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this member?');">
                                                        <input type="hidden" name="action" value="delete_member">
                                                        <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                                        <input type="hidden" name="batch_id" value="<?php echo $editBatch['id']; ?>">
                                                        <?php if (function_exists('generateCSRFToken')): ?><input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>"><?php endif; ?>
                                                        <button type="submit" class="px-2.5 py-1 text-xs font-semibold text-white bg-red-600 rounded hover:bg-red-700 transition-colors shadow-sm">
                                                            Del
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Batches List -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-900">All Batches</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Organization</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Academic Year</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Years</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">For</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Order</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($batches)): ?>
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-gray-500">No batches found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($batches as $batch): ?>
                                <?php
                                    $orgLabel = '-';
                                    if (!empty($batch['org_acronym']) || !empty($batch['org_name'])) {
                                        $orgLabel = trim(($batch['org_acronym'] ? $batch['org_acronym'] . ' — ' : '') . ($batch['org_name'] ?? ''));
                                    }
                                    $tgLabel = match ($batch['target_group'] ?? 'all') {
                                        'adviser' => 'Advisers',
                                        'co_adviser' => 'Co-Advisers',
                                        'executive_officer' => 'Executive Officers',
                                        'executive_associate' => 'Executive Associates',
                                        default => 'All',
                                    };
                                ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3"><?php echo htmlspecialchars($orgLabel); ?></td>
                                    <td class="px-4 py-3"><?php echo htmlspecialchars($batch['academic_year']); ?></td>
                                    <td class="px-4 py-3"><?php echo $batch['start_year']; ?> - <?php echo $batch['end_year']; ?></td>
                                    <td class="px-4 py-3"><?php echo htmlspecialchars($tgLabel); ?></td>
                                    <td class="px-4 py-3"><?php echo ucfirst($batch['status']); ?></td>
                                    <td class="px-4 py-3"><?php echo $batch['display_order']; ?></td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <a href="<?php echo ADMIN_URL; ?>/batches.php?edit=<?php echo $batch['id']; ?>" class="px-3 py-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-100">
                                                Edit
                                            </a>
                                            <?php if (isSuperAdmin()): ?>
                                            <form method="POST" class="inline" onsubmit="return confirm('WARNING: This will permanently delete this batch and all its members. This action cannot be undone. Are you sure?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $batch['id']; ?>">
                                                <?php if (function_exists('generateCSRFToken')): ?><input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>"><?php endif; ?>
                                                <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors shadow-sm">
                                                    Delete
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Reset form after successful member addition
document.addEventListener('DOMContentLoaded', function() {
    const memberForm = document.getElementById('memberForm');
    const urlParams = new URLSearchParams(window.location.search);
    const message = urlParams.get('message');
    
    // If we just added/updated a member successfully, reset the form
    if (message === 'member_added' || message === 'member_updated' || message === 'member_added_no_image' || message === 'member_updated_no_image') {
        if (memberForm && !urlParams.has('edit_member')) {
            // Reset form fields
            memberForm.reset();
            
            // Clear file input (can't be done via reset)
            const fileInput = memberForm.querySelector('input[type="file"]');
            if (fileInput) {
                fileInput.value = '';
            }
            
            // Reset select dropdowns to default
            const groupTypeSelect = memberForm.querySelector('select[name="group_type"]');
            if (groupTypeSelect) {
                groupTypeSelect.selectedIndex = 0;
            }
            
            // Reset display order to 0
            const displayOrderInput = memberForm.querySelector('input[name="display_order"]');
            if (displayOrderInput) {
                displayOrderInput.value = '0';
            }
            
            // Scroll to form section
            setTimeout(function() {
                const formSection = document.getElementById('member-form-section');
                if (formSection) {
                    formSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    // Highlight the form briefly
                    formSection.style.transition = 'background-color 0.3s';
                    formSection.style.backgroundColor = '#dbeafe';
                    setTimeout(function() {
                        formSection.style.backgroundColor = '#f9fafb';
                    }, 1000);
                }
            }, 300);
            
            // Focus on first input
            setTimeout(function() {
                const firstInput = memberForm.querySelector('input[name="name"]');
                if (firstInput) {
                    firstInput.focus();
                }
            }, 500);
        }
    }
    
    // Ensure form is visible and accessible
    if (memberForm) {
        memberForm.addEventListener('submit', function(e) {
            // Validate required fields before submission
            const name = memberForm.querySelector('input[name="name"]').value.trim();
            const positionTitle = memberForm.querySelector('input[name="position_title"]').value.trim();
            const groupType = memberForm.querySelector('select[name="group_type"]').value;
            
            if (!name || !positionTitle || !groupType) {
                e.preventDefault();
                alert('Please fill in all required fields: Name, Position Title, and Member Type.');
                return false;
            }
            
            // Form will submit normally, page will reload after redirect
            return true;
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>
