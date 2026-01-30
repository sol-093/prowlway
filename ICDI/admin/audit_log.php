<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Admin or Super Admin can view audit log
requireAdmin(['admin', 'super_admin'], false);

$logs = [];
try {
    $logs = dbFetchAll("SELECT a.*, ad.email AS admin_email FROM audit_log a LEFT JOIN admins ad ON ad.id = a.admin_id ORDER BY a.created_at DESC LIMIT 500");
} catch (Exception $e) {
    // Table may not exist yet
}

$pageTitle = 'Audit log - PROWLWAY Admin';
$hideHeader = true;
$bodyClass = 'admin-panel-page';
include __DIR__ . '/../includes/header.php';
?>
<div class="admin-panel-wrapper min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <div class="max-w-6xl mx-auto px-6 py-8">
        <a href="<?php echo ADMIN_URL; ?>/index.php" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50 mb-6">← Back to Dashboard</a>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Audit log</h1>
            <p class="text-sm text-gray-500 mb-6">Recent activity (login, publish, archive, settings, role changes).</p>
            <?php if (empty($logs)): ?>
                <p class="text-gray-500 py-8">No audit entries yet. Run migration_rbac_audit_inquiries.sql if the audit_log table is missing.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse border border-gray-200 rounded-lg overflow-hidden text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left px-4 py-3 font-bold text-gray-700 border-b border-gray-200">Time</th>
                                <th class="text-left px-4 py-3 font-bold text-gray-700 border-b border-gray-200">User</th>
                                <th class="text-left px-4 py-3 font-bold text-gray-700 border-b border-gray-200">Action</th>
                                <th class="text-left px-4 py-3 font-bold text-gray-700 border-b border-gray-200">Entity</th>
                                <th class="text-left px-4 py-3 font-bold text-gray-700 border-b border-gray-200">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                            <tr class="border-b border-gray-200">
                                <td class="px-4 py-3 text-gray-600"><?php echo htmlspecialchars($log['created_at']); ?></td>
                                <td class="px-4 py-3 text-gray-700"><?php echo htmlspecialchars($log['admin_email'] ?? '—'); ?></td>
                                <td class="px-4 py-3 font-medium text-gray-900"><?php echo htmlspecialchars($log['action']); ?></td>
                                <td class="px-4 py-3 text-gray-600"><?php echo htmlspecialchars(($log['entity_type'] ?? '') . ($log['entity_id'] ? ' #' . $log['entity_id'] : '')); ?></td>
                                <td class="px-4 py-3 text-gray-600 max-w-xs truncate" title="<?php echo htmlspecialchars($log['details'] ?? ''); ?>"><?php echo htmlspecialchars($log['details'] ?? '—'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
