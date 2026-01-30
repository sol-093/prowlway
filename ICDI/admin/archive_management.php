<?php
session_start();
require_once '../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin(['admin', 'super_admin']);

$pageTitle = 'Archive Management - PROWLWAY Admin';
$hideHeader = true;
$bodyClass = 'admin-panel-page';
include '../includes/header.php';

$canPublish = canPublish();
$isSuperAdmin = isSuperAdmin();

// Get academic years from all content
$academicYears = dbFetchAll("
    SELECT DISTINCT academic_year 
    FROM (
        SELECT academic_year FROM documents WHERE academic_year IS NOT NULL AND academic_year != ''
        UNION
        SELECT academic_year FROM announcements WHERE academic_year IS NOT NULL AND academic_year != ''
        UNION
        SELECT academic_year FROM events WHERE academic_year IS NOT NULL AND academic_year != ''
    ) AS years
    ORDER BY academic_year DESC
");

// Get archive statistics
$archiveStats = [
    'documents' => dbFetchOne("SELECT COUNT(*) as count FROM documents WHERE status = 'archived'")['count'] ?? 0,
    'announcements' => dbFetchOne("SELECT COUNT(*) as count FROM announcements WHERE status = 'archived'")['count'] ?? 0,
    'events' => dbFetchOne("SELECT COUNT(*) as count FROM events WHERE status = 'archived'")['count'] ?? 0,
    'inquiries' => dbFetchOne("SELECT COUNT(*) as count FROM contact_inquiries WHERE status = 'archived'")['count'] ?? 0,
    'users' => dbFetchOne("SELECT COUNT(*) as count FROM admins WHERE status = 'archived'")['count'] ?? 0,
];

$selectedYear = $_GET['year'] ?? '';
$selectedType = $_GET['type'] ?? 'all';
?>

<script>
    window.ADMIN_URL = '<?php echo ADMIN_URL; ?>';
    window.ADMIN_CAN_PUBLISH = <?php echo $canPublish ? 'true' : 'false'; ?>;
    window.CSRF_TOKEN = '<?php echo function_exists("generateCSRFToken") ? generateCSRFToken() : ""; ?>';
</script>

<div class="admin-panel-wrapper min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Archive Management</h1>
                    <p class="text-gray-600 mt-2">Bulk archive content by academic year and manage archived items</p>
                </div>
                <a href="<?php echo ADMIN_URL; ?>/index.php" class="px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors">
                    ← Back to Dashboard
                </a>
            </div>
            
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-xl p-6 border-2 border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Total Archived</p>
                            <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo array_sum($archiveStats); ?></p>
                        </div>
                        <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-600">
                                <path d="M5 8h14M5 8a2 2 0 1 0 0-4h14a2 2 0 1 0 0 4M5 8v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8m-9 4h4"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-6 border-2 border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Documents</p>
                            <p class="text-3xl font-bold text-indigo-600 mt-1"><?php echo $archiveStats['documents']; ?></p>
                        </div>
                        <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-indigo-600">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-6 border-2 border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Announcements</p>
                            <p class="text-3xl font-bold text-purple-600 mt-1"><?php echo $archiveStats['announcements']; ?></p>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-purple-600">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-6 border-2 border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Events</p>
                            <p class="text-3xl font-bold text-pink-600 mt-1"><?php echo $archiveStats['events']; ?></p>
                        </div>
                        <div class="w-12 h-12 bg-pink-100 rounded-xl flex items-center justify-center">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-pink-600">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-6 border-2 border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Users</p>
                            <p class="text-3xl font-bold text-orange-600 mt-1"><?php echo $archiveStats['users']; ?></p>
                        </div>
                        <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-orange-600">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-6 border-2 border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Inquiries</p>
                            <p class="text-3xl font-bold text-teal-600 mt-1"><?php echo $archiveStats['inquiries']; ?></p>
                        </div>
                        <div class="w-12 h-12 bg-teal-100 rounded-xl flex items-center justify-center">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-teal-600">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Archive Section -->
        <div class="bg-white rounded-xl border-2 border-gray-200 shadow-sm mb-6">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">Bulk Archive by Academic Year</h2>
                <p class="text-sm text-gray-500 mt-1">Archive all published content from a specific academic year</p>
            </div>
            <div class="p-6">
                <form id="bulkArchiveForm" onsubmit="bulkArchive(event)" class="flex gap-4 items-end">
                    <div class="flex-1">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Academic Year</label>
                        <select id="archive-year" name="year" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select academic year...</option>
                            <?php foreach ($academicYears as $year): ?>
                            <option value="<?php echo htmlspecialchars($year['academic_year']); ?>"><?php echo htmlspecialchars($year['academic_year']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Content Type</label>
                        <select id="archive-type" name="type" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="all">All Types</option>
                            <option value="documents">Documents Only</option>
                            <option value="announcements">Announcements Only</option>
                            <option value="events">Events Only</option>
                            <option value="inquiries">Inquiries Only</option>
                            <option value="users">Users Only</option>
                        </select>
                    </div>
                    <button type="submit" class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-colors">
                        Archive Selected
                    </button>
                </form>
            </div>
        </div>

        <!-- Archive Filters -->
        <div class="bg-white rounded-xl border-2 border-gray-200 shadow-sm mb-6">
            <div class="p-6">
                <form method="GET" class="flex gap-4 items-end">
                    <div class="flex-1">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Filter by Academic Year</label>
                        <select name="year" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" onchange="this.form.submit()">
                            <option value="">All Years</option>
                            <?php foreach ($academicYears as $year): ?>
                            <option value="<?php echo htmlspecialchars($year['academic_year']); ?>" <?php echo $selectedYear === $year['academic_year'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($year['academic_year']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Content Type</label>
                        <select name="type" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" onchange="this.form.submit()">
                            <option value="all" <?php echo $selectedType === 'all' ? 'selected' : ''; ?>>All Types</option>
                            <option value="documents" <?php echo $selectedType === 'documents' ? 'selected' : ''; ?>>Documents</option>
                            <option value="announcements" <?php echo $selectedType === 'announcements' ? 'selected' : ''; ?>>Announcements</option>
                            <option value="events" <?php echo $selectedType === 'events' ? 'selected' : ''; ?>>Events</option>
                            <option value="users" <?php echo $selectedType === 'users' ? 'selected' : ''; ?>>Users</option>
                            <option value="inquiries" <?php echo $selectedType === 'inquiries' ? 'selected' : ''; ?>>Inquiries</option>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <!-- Archived Content List -->
        <div class="bg-white rounded-xl border-2 border-gray-200 shadow-sm">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">Archived Content</h2>
            </div>
            <div class="p-6">
                <?php
                $whereClause = "status = 'archived'";
                $params = [];
                
                if ($selectedYear) {
                    $whereClause .= " AND academic_year = ?";
                    $params[] = $selectedYear;
                }
                
                $archivedItems = [];
                
                if ($selectedType === 'all' || $selectedType === 'documents') {
                    $docs = dbFetchAll("SELECT *, 'document' as item_type FROM documents WHERE {$whereClause} ORDER BY created_at DESC LIMIT 50", $params);
                    $archivedItems = array_merge($archivedItems, $docs);
                }
                
                if ($selectedType === 'all' || $selectedType === 'announcements') {
                    $anns = dbFetchAll("SELECT *, 'announcement' as item_type FROM announcements WHERE {$whereClause} ORDER BY created_at DESC LIMIT 50", $params);
                    $archivedItems = array_merge($archivedItems, $anns);
                }
                
                if ($selectedType === 'all' || $selectedType === 'events') {
                    $evts = dbFetchAll("SELECT *, 'event' as item_type FROM events WHERE {$whereClause} ORDER BY created_at DESC LIMIT 50", $params);
                    $archivedItems = array_merge($archivedItems, $evts);
                }
                
                if ($selectedType === 'all' || $selectedType === 'inquiries') {
                    $inqs = dbFetchAll("SELECT *, 'inquiry' as item_type FROM contact_inquiries WHERE status = 'archived' ORDER BY created_at DESC LIMIT 50");
                    $archivedItems = array_merge($archivedItems, $inqs);
                }
                
                if ($selectedType === 'all' || $selectedType === 'users') {
                    $users = dbFetchAll("SELECT id, email, name, role, created_at, updated_at, 'user' as item_type FROM admins WHERE status = 'archived' ORDER BY created_at DESC LIMIT 50");
                    $archivedItems = array_merge($archivedItems, $users);
                }
                
                usort($archivedItems, function($a, $b) {
                    return strtotime($b['created_at']) - strtotime($a['created_at']);
                });
                ?>
                
                <?php if (!empty($archivedItems)): ?>
                <div class="space-y-4">
                    <?php foreach ($archivedItems as $item): ?>
                    <div class="border-2 border-gray-200 rounded-xl p-6 hover:border-gray-300 transition-colors">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="px-3 py-1 text-xs font-semibold bg-gray-100 text-gray-700 rounded-full">
                                        <?php echo ucfirst($item['item_type']); ?>
                                    </span>
                                    <?php if (!empty($item['academic_year'])): ?>
                                    <span class="px-3 py-1 text-xs font-semibold bg-indigo-100 text-indigo-700 rounded-full">
                                        <?php echo htmlspecialchars($item['academic_year']); ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($item['title'] ?? $item['subject'] ?? $item['name'] ?? $item['email'] ?? 'Untitled'); ?></h3>
                                <?php if ($item['item_type'] === 'user'): ?>
                                <p class="text-sm text-gray-600 mb-2">Email: <?php echo htmlspecialchars($item['email'] ?? ''); ?> | Role: <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $item['role'] ?? ''))); ?></p>
                                <?php else: ?>
                                <p class="text-sm text-gray-600 mb-2"><?php echo htmlspecialchars(substr($item['description'] ?? $item['caption'] ?? $item['message'] ?? '', 0, 150)); ?><?php echo strlen($item['description'] ?? $item['caption'] ?? $item['message'] ?? '') > 150 ? '...' : ''; ?></p>
                                <?php endif; ?>
                                <p class="text-xs text-gray-500">Archived: <?php echo date('M j, Y', strtotime($item['updated_at'] ?? $item['created_at'])); ?></p>
                            </div>
                            <div class="flex gap-2 ml-4">
                                <button onclick="restoreItem('<?php echo $item['item_type']; ?>', <?php echo $item['id']; ?>)" class="px-4 py-2 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition-colors">
                                    Restore
                                </button>
                                <button onclick="deleteItem('<?php echo $item['item_type']; ?>', <?php echo $item['id']; ?>)" class="px-4 py-2 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition-colors">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="text-center py-12">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mx-auto text-gray-400 mb-4">
                        <path d="M5 8h14M5 8a2 2 0 1 0 0-4h14a2 2 0 1 0 0 4M5 8v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8m-9 4h4"></path>
                    </svg>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">No Archived Items</h3>
                    <p class="text-gray-600">No archived content found for the selected filters.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function bulkArchive(event) {
    event.preventDefault();
    const year = document.getElementById('archive-year').value;
    const type = document.getElementById('archive-type').value;
    
    if (!confirm(`Are you sure you want to archive all ${type === 'all' ? 'content' : type} from ${year}? This action cannot be undone.`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'bulk_archive');
    formData.append('year', year);
    formData.append('type', type);
    
    // Add CSRF token
    if (window.CSRF_TOKEN) {
        formData.append('csrf_token', window.CSRF_TOKEN);
    }
    
    fetch(`${window.ADMIN_URL}/archive_handler.php`, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(`Successfully archived ${data.count} items.`);
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(err => {
        alert('Error: ' + err.message);
    });
}

function restoreItem(type, id) {
    const confirmMsg = type === 'user' 
        ? 'Are you sure you want to restore this user? They will be set back to active status.'
        : 'Are you sure you want to restore this item? It will be set back to published status.';
    
    if (!confirm(confirmMsg)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'restore');
    formData.append('type', type);
    formData.append('id', id);
    
    // Add CSRF token
    if (window.CSRF_TOKEN) {
        formData.append('csrf_token', window.CSRF_TOKEN);
    }
    
    fetch(`${window.ADMIN_URL}/archive_handler.php`, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Item restored successfully.');
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(err => {
        alert('Error: ' + err.message);
    });
}

function deleteItem(type, id) {
    const confirmMsg = type === 'user'
        ? 'Are you sure you want to permanently delete this user? This action cannot be undone.'
        : 'WARNING: This will permanently delete this item. This action cannot be undone. Are you sure?';
    
    if (!confirm(confirmMsg)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('type', type);
    formData.append('id', id);
    
    // Add CSRF token
    if (window.CSRF_TOKEN) {
        formData.append('csrf_token', window.CSRF_TOKEN);
    }
    
    fetch(`${window.ADMIN_URL}/archive_handler.php`, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Item permanently deleted.');
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(err => {
        alert('Error: ' + err.message);
    });
}
</script>

<?php include '../includes/footer.php'; ?>
