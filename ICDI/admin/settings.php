<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireSuperAdmin(false);

$message = '';
$messageType = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    requireCSRFToken(false);
    
    $siteName = trim($_POST['site_name'] ?? '');
    $siteDescription = trim($_POST['site_description'] ?? '');
    $contactEmail = trim($_POST['contact_email'] ?? '');
    $maintenanceMode = isset($_POST['maintenance_mode']) ? '1' : '0';
    
    // Validate email if provided
    if (!empty($contactEmail) && !filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid contact email address.';
        $messageType = 'error';
    } else {
        dbUpdate('site_settings', ['setting_value' => $siteName, 'updated_by' => $_SESSION['admin_id']], 'setting_key = :k', ['k' => 'site_name']);
        dbUpdate('site_settings', ['setting_value' => $siteDescription, 'updated_by' => $_SESSION['admin_id']], 'setting_key = :k', ['k' => 'site_description']);
        dbUpdate('site_settings', ['setting_value' => $contactEmail, 'updated_by' => $_SESSION['admin_id']], 'setting_key = :k', ['k' => 'contact_email']);
        dbUpdate('site_settings', ['setting_value' => $maintenanceMode, 'updated_by' => $_SESSION['admin_id']], 'setting_key = :k', ['k' => 'maintenance_mode']);
        auditLog('settings_update', 'Site settings updated', 'settings', null);
        $message = 'Settings saved.';
        $messageType = 'success';
    }
}

$rows = dbFetchAll("SELECT setting_key, setting_value FROM site_settings WHERE setting_key IN ('site_name','site_description','contact_email','maintenance_mode')");
$settings = [];
foreach ($rows as $r) {
    $settings[$r['setting_key']] = $r['setting_value'];
}

$pageTitle = 'Settings - PROWLWAY Admin';
$hideHeader = true;
$bodyClass = 'admin-panel-page';
include __DIR__ . '/../includes/header.php';
?>
<div class="admin-panel-wrapper min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <div class="max-w-2xl mx-auto px-6 py-8">
        <a href="<?php echo ADMIN_URL; ?>/index.php" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50 mb-6">← Back to Dashboard</a>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">System settings</h1>
            <?php if ($message): ?>
                <p class="mb-4 p-3 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>
            <form method="post" class="space-y-4">
                <?php if (function_exists('generateCSRFToken')): ?>
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <?php endif; ?>
                <div>
                    <label for="site_name" class="block text-sm font-bold text-gray-700 mb-2">Site name</label>
                    <input type="text" id="site_name" name="site_name" value="<?php echo htmlspecialchars($settings['site_name'] ?? 'PROWLWAY'); ?>" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="site_description" class="block text-sm font-bold text-gray-700 mb-2">Site description</label>
                    <input type="text" id="site_description" name="site_description" value="<?php echo htmlspecialchars($settings['site_description'] ?? ''); ?>" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="contact_email" class="block text-sm font-bold text-gray-700 mb-2">Contact email</label>
                    <input type="email" id="contact_email" name="contact_email" value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="maintenance_mode" value="1" class="w-5 h-5 text-indigo-600 border-2 border-gray-300 rounded" <?php echo ($settings['maintenance_mode'] ?? '0') === '1' ? 'checked' : ''; ?>>
                        <span class="text-sm font-bold text-gray-700">Maintenance mode</span>
                    </label>
                </div>
                <button type="submit" class="px-6 py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700">Save settings</button>
            </form>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
