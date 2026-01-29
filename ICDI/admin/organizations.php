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
            // Handle social media JSON
            $socialMedia = null;
            if (!empty($_POST['social_media']['json'])) {
                $decoded = json_decode($_POST['social_media']['json'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $socialMedia = json_encode($decoded);
                }
            }
            
            $data = [
                'name' => $_POST['name'] ?? '',
                'acronym' => $_POST['acronym'] ?? '',
                'description' => $_POST['description'] ?? '',
                'content' => $_POST['content'] ?? null,
                'website' => $_POST['website'] ?? '',
                'social_media' => $socialMedia,
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

$pageTitle = 'Manage Organizations - PROWLWAY Admin';
$hideHeader = true;
$bodyClass = 'admin-panel-page';
include '../includes/header.php';
?>

<div class="admin-panel-wrapper min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <div class="max-w-6xl mx-auto px-6 py-8">
        <!-- Page Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Manage Student Organizations</h1>
                <p class="text-sm text-gray-500 mt-1">Create, update, and manage student organizations.</p>
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
                    <?php echo $editOrg ? 'Edit Organization' : 'Create New Organization'; ?>
                </h2>
            </div>
            <form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
                <input type="hidden" name="action" value="<?php echo $editOrg ? 'update' : 'create'; ?>">
                <?php if ($editOrg): ?>
                    <input type="hidden" name="id" value="<?php echo $editOrg['id']; ?>">
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Name *</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($editOrg['name'] ?? ''); ?>" required placeholder="Organization name" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Acronym</label>
                        <input type="text" name="acronym" value="<?php echo htmlspecialchars($editOrg['acronym'] ?? ''); ?>" placeholder="e.g., ICDISG" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                    <textarea name="description" rows="3" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-y"><?php echo htmlspecialchars($editOrg['description'] ?? ''); ?></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Core Values (one per line)</label>
                    <textarea name="content" rows="6" placeholder="Enter core values, one per line:&#10;Value 1&#10;Value 2&#10;Value 3" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-y"><?php echo htmlspecialchars($editOrg['content'] ?? ''); ?></textarea>
                    <p class="mt-2 text-xs text-gray-500">Enter each core value on a separate line</p>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Logo</label>
                    <?php if ($editOrg && !empty($editOrg['logo'])): ?>
                        <div class="mb-3">
                            <img src="<?php echo getImageUrl($editOrg['logo']); ?>" alt="Current logo" class="max-w-xs max-h-48 rounded-xl border border-gray-200">
                            <input type="hidden" name="old_logo" value="<?php echo htmlspecialchars($editOrg['logo']); ?>">
                        </div>
                        <p class="text-xs text-gray-500 mb-2">Upload a new logo to replace the current one.</p>
                    <?php endif; ?>
                    <input type="file" name="logo" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    <p class="mt-2 text-xs text-gray-500">Max size: 5MB. Formats: JPEG, PNG, GIF, WebP.</p>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Social Media Links (JSON format or leave blank)</label>
                    <textarea name="social_media[json]" rows="2" placeholder='{"facebook": "https://...", "instagram": "https://..."}' class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-y"><?php echo $editOrg && $editOrg['social_media'] ? json_encode($editOrg['social_media'], JSON_PRETTY_PRINT) : ''; ?></textarea>
                    <p class="mt-2 text-xs text-gray-500">Optional: Enter as JSON object</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Display Order</label>
                        <input type="number" name="display_order" value="<?php echo $editOrg['display_order'] ?? 0; ?>" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="active" <?php echo ($editOrg['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="draft" <?php echo ($editOrg['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="archived" <?php echo ($editOrg['status'] ?? '') === 'archived' ? 'selected' : ''; ?>>Archived</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="px-6 py-3 bg-indigo-600 text-white font-semibold rounded-xl shadow hover:bg-indigo-700 transition-colors">
                        <?php echo $editOrg ? 'Update Organization' : 'Create Organization'; ?>
                    </button>
                    <?php if ($editOrg): ?>
                        <a href="<?php echo ADMIN_URL; ?>/organizations.php" class="px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors">
                            Cancel
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Organizations List -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-900">All Organizations</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Name</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Acronym</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Order</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($organizations)): ?>
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-gray-500">No organizations found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($organizations as $org): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3"><?php echo htmlspecialchars($org['name']); ?></td>
                                    <td class="px-4 py-3"><?php echo htmlspecialchars($org['acronym'] ?? '-'); ?></td>
                                    <td class="px-4 py-3"><?php echo ucfirst($org['status']); ?></td>
                                    <td class="px-4 py-3"><?php echo $org['display_order']; ?></td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <a href="<?php echo ADMIN_URL; ?>/organizations.php?edit=<?php echo $org['id']; ?>" class="px-3 py-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-100">
                                                Edit
                                            </a>
                                            <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this organization?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $org['id']; ?>">
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

