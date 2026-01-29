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

// Fetch organizations for dropdowns
$orgOptions = dbFetchAll("SELECT id, name, acronym FROM student_organizations WHERE status != 'archived' ORDER BY display_order ASC, name ASC");

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create' || $_POST['action'] === 'update') {
            $data = [
                'organization_id' => !empty($_POST['organization_id']) ? intval($_POST['organization_id']) : null,
                'academic_year' => $_POST['academic_year'] ?? '',
                'start_year' => $_POST['start_year'] ?? date('Y'),
                'end_year' => $_POST['end_year'] ?? date('Y') + 1,
                'description' => $_POST['description'] ?? '',
                'target_group' => $_POST['target_group'] ?? 'all',
                'status' => $_POST['status'] ?? 'active',
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
                        $message = 'Batch created successfully!';
                        $messageType = 'success';
                    } else {
                        $message = 'Error creating batch.';
                        $messageType = 'error';
                    }
                } else {
                    $id = intval($_POST['id']);
                    $result = dbUpdate('batches', $data, 'id = :id', ['id' => $id]);
                    if ($result) {
                        $message = 'Batch updated successfully!';
                        $messageType = 'success';
                    } else {
                        $message = 'Error updating batch.';
                        $messageType = 'error';
                    }
                }
            }
        } elseif ($_POST['action'] === 'delete') {
            $id = intval($_POST['id']);
            // Get batch to delete image
            $batch = dbFetchOne("SELECT image FROM batches WHERE id = ?", [$id]);
            if ($batch && !empty($batch['image'])) {
                deleteUploadedFile($batch['image']);
            }
            $result = dbDelete('batches', 'id = :id', ['id' => $id]);
            if ($result) {
                $message = 'Batch deleted successfully!';
                $messageType = 'success';
            } else {
                $message = 'Error deleting batch.';
                $messageType = 'error';
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
if (isset($_GET['edit'])) {
    $editBatch = dbFetchOne("SELECT * FROM batches WHERE id = ?", [intval($_GET['edit'])]);
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
                            <option value="archived" <?php echo ($editBatch['status'] ?? '') === 'archived' ? 'selected' : ''; ?>>Archived</option>
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
                            <option value="all" <?php echo $tg === 'all' ? 'selected' : ''; ?>>All (Advisers + Executive Officers + Executive Associates)</option>
                            <option value="adviser" <?php echo $tg === 'adviser' ? 'selected' : ''; ?>>Advisers</option>
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
                                            <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this batch?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $batch['id']; ?>">
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
    </div>
</div>

<?php include '../includes/footer.php'; ?>
