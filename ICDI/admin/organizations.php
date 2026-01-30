<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/upload.php';

requireAdmin(null, false);

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
            $requestedStatus = $_POST['status'] ?? 'active';
            $data = [
                'name' => $_POST['name'] ?? '',
                'acronym' => $_POST['acronym'] ?? '',
                'description' => $_POST['description'] ?? '',
                'mission' => $_POST['mission'] ?? null,
                'vision' => $_POST['vision'] ?? null,
                'website' => $_POST['website'] ?? '',
                'social_media' => $socialMedia,
                'display_order' => intval($_POST['display_order'] ?? 0),
                'status' => normalizeStatusByRole($requestedStatus, ['active', 'archived']),
                'created_by' => $_SESSION['admin_id'] ?? null
            ];
            
            // Handle banner image upload (landscape hero above About)
            if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = uploadImage($_FILES['banner_image'], 'images');
                if ($uploadResult['success']) {
                    if ($_POST['action'] === 'update' && !empty($_POST['old_banner_image'])) {
                        deleteUploadedFile($_POST['old_banner_image']);
                    }
                    $data['banner_image'] = $uploadResult['path'];
                } else {
                    $message = 'Banner image upload failed: ' . $uploadResult['error'];
                    $messageType = 'error';
                }
            } elseif ($_POST['action'] === 'update' && !empty($_POST['old_banner_image'])) {
                $data['banner_image'] = $_POST['old_banner_image'];
            }
            
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
            
            $orgIdForCoreValues = null;
            if ($_POST['action'] === 'create') {
                $newId = dbInsert('student_organizations', $data);
                if ($newId) {
                    $orgIdForCoreValues = (int) $newId;
                    if ($data['status'] === 'active') {
                        auditLog('publish', 'Organization created and published', 'organization', $newId);
                    }
                    $message = 'Organization created successfully!';
                    $messageType = 'success';
                } else {
                    $message = 'Error creating organization.';
                    $messageType = 'error';
                }
            } else {
                $id = intval($_POST['id']);
                $orgIdForCoreValues = $id;
                $existing = dbFetchOne("SELECT status FROM student_organizations WHERE id = ?", [$id]);
                $result = dbUpdate('student_organizations', $data, 'id = :id', ['id' => $id]);
                if ($result) {
                    if ($existing && $data['status'] === 'active' && $existing['status'] !== 'active') {
                        auditLog('publish', 'Organization published', 'organization', $id);
                    } elseif ($existing && $data['status'] === 'archived') {
                        auditLog('archive', 'Organization archived', 'organization', $id);
                    }
                    $message = 'Organization updated successfully!';
                    $messageType = 'success';
                } else {
                    $message = 'Error updating organization.';
                    $messageType = 'error';
                }
            }

            // Save dynamic core values (after create or update so we have organization_id)
            $orgId = $orgIdForCoreValues;
            if ($orgId && $messageType === 'success') {
                $titles = isset($_POST['core_value_title']) && is_array($_POST['core_value_title']) ? $_POST['core_value_title'] : [];
                if ($_POST['action'] === 'update') {
                    dbDelete('organization_core_values', 'organization_id = :oid', ['oid' => $orgId]);
                }
                foreach ($titles as $i => $title) {
                    $title = trim($title ?? '');
                    if ($title === '') continue;
                    $description = isset($_POST['core_value_description'][$i]) ? trim($_POST['core_value_description'][$i]) : '';
                    $iconPath = null;
                    if (!empty($_FILES['core_value_icon']['name'][$i]) && $_FILES['core_value_icon']['error'][$i] === UPLOAD_ERR_OK) {
                        $uploadResult = uploadImage([
                            'name' => $_FILES['core_value_icon']['name'][$i],
                            'type' => $_FILES['core_value_icon']['type'][$i],
                            'tmp_name' => $_FILES['core_value_icon']['tmp_name'][$i],
                            'error' => $_FILES['core_value_icon']['error'][$i],
                            'size' => $_FILES['core_value_icon']['size'][$i]
                        ], 'images');
                        if ($uploadResult['success']) $iconPath = $uploadResult['path'];
                    } elseif (!empty($_POST['core_value_existing_icon'][$i])) {
                        $iconPath = $_POST['core_value_existing_icon'][$i];
                    }
                    dbInsert('organization_core_values', [
                        'organization_id' => $orgId,
                        'icon' => $iconPath,
                        'title' => $title,
                        'description' => $description,
                        'display_order' => (int) $i
                    ]);
                }
            }
        } elseif ($_POST['action'] === 'archive') {
            $id = intval($_POST['id']);
            $org = dbFetchOne("SELECT status, created_by FROM student_organizations WHERE id = ?", [$id]);
            if (!$org) {
                $message = 'Organization not found.';
                $messageType = 'error';
            } elseif (!canPublish() && ($org['status'] !== 'draft' || (int)($org['created_by'] ?? 0) !== (int)($_SESSION['admin_id'] ?? 0))) {
                $message = 'Only draft organizations you created can be archived, or you need publish rights.';
                $messageType = 'error';
            } else {
                $result = dbUpdate('student_organizations', ['status' => 'archived'], 'id = :id', ['id' => $id]);
                if ($result) {
                    auditLog('archive', 'Organization archived', 'organization', $id);
                    $message = 'Organization archived successfully!';
                    $messageType = 'success';
                } else {
                    $message = 'Error archiving organization.';
                    $messageType = 'error';
                }
            }
        }
    }
}

// Fetch organizations
$organizations = dbFetchAll("SELECT * FROM student_organizations ORDER BY display_order ASC, name ASC");

// Fetch single organization for editing (and its core values)
$editOrg = null;
$editCoreValues = [];
if (isset($_GET['edit'])) {
    $editOrg = dbFetchOne("SELECT * FROM student_organizations WHERE id = ?", [intval($_GET['edit'])]);
    if ($editOrg) {
        if ($editOrg['social_media']) {
            $editOrg['social_media'] = json_decode($editOrg['social_media'], true);
        }
        $editCoreValues = dbFetchAll("SELECT * FROM organization_core_values WHERE organization_id = ? ORDER BY display_order ASC, id ASC", [$editOrg['id']]);
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
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Mission</label>
                    <textarea name="mission" rows="4" placeholder="Organization mission statement" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-y"><?php echo htmlspecialchars($editOrg['mission'] ?? ''); ?></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Vision</label>
                    <textarea name="vision" rows="4" placeholder="Organization vision statement" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-y"><?php echo htmlspecialchars($editOrg['vision'] ?? ''); ?></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Core Values</label>
                    <p class="text-xs text-gray-500 mb-3">Add core values with an icon/logo, title, and description. Each appears on the public organization page.</p>
                    <div id="core-values-list" class="space-y-4">
                        <?php
                        $coreRows = $editOrg && !empty($editCoreValues) ? $editCoreValues : [['icon' => null, 'title' => '', 'description' => '']];
                        foreach ($coreRows as $idx => $cv):
                            $cvIcon = $cv['icon'] ?? null;
                            $cvTitle = $cv['title'] ?? '';
                            $cvDesc = $cv['description'] ?? '';
                        ?>
                        <div class="core-value-row flex flex-wrap items-start gap-4 p-4 border-2 border-gray-200 rounded-xl bg-gray-50/50">
                            <div class="flex-shrink-0">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Icon / Logo</label>
                                <input type="hidden" name="core_value_existing_icon[]" value="<?php echo $cvIcon ? htmlspecialchars($cvIcon) : ''; ?>">
                                <?php if ($cvIcon): ?>
                                    <div class="mb-2">
                                        <img src="<?php echo getImageUrl($cvIcon); ?>" alt="" class="w-24 h-24 object-contain rounded-lg">
                                        <p class="text-xs text-gray-500 mb-1">Replace:</p>
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="core_value_icon[]" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" class="block w-full text-sm text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700">
                            </div>
                            <div class="flex-1 min-w-0 space-y-2">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Title</label>
                                    <input type="text" name="core_value_title[]" value="<?php echo htmlspecialchars($cvTitle); ?>" placeholder="e.g. Connect, Innovate, Empower" class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Description / Definition</label>
                                    <textarea name="core_value_description[]" rows="2" placeholder="Short description of this core value" class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-y text-sm"><?php echo htmlspecialchars($cvDesc); ?></textarea>
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                <button type="button" class="remove-core-value mt-6 px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 border-2 border-red-700 shadow-sm">Remove</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" id="add-core-value" class="mt-3 px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-xl border-2 border-indigo-700 hover:bg-indigo-700 shadow-sm transition-colors">
                        + Add Core Value
                    </button>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Banner / Hero image (landscape only)</label>
                    <p class="text-xs text-gray-500 mb-2">Shown above the About section on the organization page. Use a wide (landscape) image.</p>
                    <?php if ($editOrg && !empty($editOrg['banner_image'])): ?>
                        <div class="mb-3">
                            <img src="<?php echo getImageUrl($editOrg['banner_image']); ?>" alt="Current banner" class="max-w-full h-32 object-cover rounded-xl border border-gray-200">
                            <input type="hidden" name="old_banner_image" value="<?php echo htmlspecialchars($editOrg['banner_image']); ?>">
                        </div>
                        <p class="text-xs text-gray-500 mb-2">Upload a new image to replace the current one.</p>
                    <?php endif; ?>
                    <input type="file" name="banner_image" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
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
                                            <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to archive this organization?');">
                                                <input type="hidden" name="action" value="archive">
                                                <input type="hidden" name="id" value="<?php echo $org['id']; ?>">
                                                <?php if (function_exists('generateCSRFToken')): ?><input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>"><?php endif; ?>
                                                <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-gray-800 bg-gray-200 rounded-lg hover:bg-gray-300 border-2 border-gray-300">
                                                    Archive
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

<script>
(function() {
    var list = document.getElementById('core-values-list');
    var addBtn = document.getElementById('add-core-value');
    if (!list || !addBtn) return;

    addBtn.addEventListener('click', function() {
        var row = document.createElement('div');
        row.className = 'core-value-row flex flex-wrap items-start gap-4 p-4 border-2 border-gray-200 rounded-xl bg-gray-50/50';
        row.innerHTML = '<div class="flex-shrink-0">' +
            '<label class="block text-xs font-medium text-gray-600 mb-1">Icon / Logo</label>' +
            '<input type="file" name="core_value_icon[]" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" class="block w-full text-sm text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700">' +
            '<input type="hidden" name="core_value_existing_icon[]" value="">' +
            '</div>' +
            '<div class="flex-1 min-w-0 space-y-2">' +
            '<div><label class="block text-xs font-medium text-gray-600 mb-1">Title</label>' +
            '<input type="text" name="core_value_title[]" value="" placeholder="e.g. Connect, Innovate, Empower" class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"></div>' +
            '<div><label class="block text-xs font-medium text-gray-600 mb-1">Description / Definition</label>' +
            '<textarea name="core_value_description[]" rows="2" placeholder="Short description of this core value" class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-y text-sm"></textarea></div>' +
            '</div>' +
            '<div class="flex-shrink-0"><button type="button" class="remove-core-value mt-6 px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 border-2 border-red-700 shadow-sm">Remove</button></div>';
        list.appendChild(row);
    });

    list.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-core-value')) {
            var row = e.target.closest('.core-value-row');
            if (row && list.querySelectorAll('.core-value-row').length > 1) row.remove();
        }
    });
})();
</script>

<?php include '../includes/footer.php'; ?>

