<?php
session_start();
require_once '../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin(['admin', 'super_admin']);

$pageTitle = 'Review Queue - PROWLWAY Admin';
$hideHeader = true;
$bodyClass = 'admin-panel-page';
include '../includes/header.php';

$canPublish = canPublish();
$isSuperAdmin = isSuperAdmin();

// Fetch pending review items
$pendingDocuments = dbFetchAll("
    SELECT d.*, a.name as created_by_name 
    FROM documents d 
    LEFT JOIN admins a ON d.created_by = a.id 
    WHERE d.status = 'pending_review' 
    ORDER BY d.created_at ASC
");

$pendingAnnouncements = dbFetchAll("
    SELECT a.*, ad.name as created_by_name 
    FROM announcements a 
    LEFT JOIN admins ad ON a.created_by = ad.id 
    WHERE a.status = 'pending_review' 
    ORDER BY a.created_at ASC
");

$pendingEvents = dbFetchAll("
    SELECT e.*, a.name as created_by_name 
    FROM events e 
    LEFT JOIN admins a ON e.created_by = a.id 
    WHERE e.status = 'pending_review' 
    ORDER BY e.created_at ASC
");

$totalPending = count($pendingDocuments) + count($pendingAnnouncements) + count($pendingEvents);
?>

<script>
    window.ADMIN_URL = '<?php echo ADMIN_URL; ?>';
    window.ADMIN_CAN_PUBLISH = <?php echo $canPublish ? 'true' : 'false'; ?>;
</script>

<div class="admin-panel-wrapper min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Review Queue</h1>
                    <p class="text-gray-600 mt-2">Review and approve content submitted by editors</p>
                </div>
                <a href="<?php echo ADMIN_URL; ?>/index.php" class="px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors">
                    ← Back to Dashboard
                </a>
            </div>
            
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl p-6 border-2 border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Total Pending</p>
                            <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo $totalPending; ?></p>
                        </div>
                        <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-yellow-600">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-6 border-2 border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Documents</p>
                            <p class="text-3xl font-bold text-indigo-600 mt-1"><?php echo count($pendingDocuments); ?></p>
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
                            <p class="text-3xl font-bold text-purple-600 mt-1"><?php echo count($pendingAnnouncements); ?></p>
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
                            <p class="text-3xl font-bold text-pink-600 mt-1"><?php echo count($pendingEvents); ?></p>
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
            </div>
        </div>

        <!-- Pending Documents -->
        <?php if (!empty($pendingDocuments)): ?>
        <div class="bg-white rounded-xl border-2 border-gray-200 shadow-sm mb-6">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">Documents Pending Review</h2>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <?php foreach ($pendingDocuments as $doc): ?>
                    <div class="border-2 border-gray-200 rounded-xl p-6 hover:border-indigo-300 transition-colors">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($doc['title']); ?></h3>
                                <p class="text-sm text-gray-600 mb-2"><?php echo htmlspecialchars($doc['description'] ?? 'No description'); ?></p>
                                <div class="flex items-center gap-4 text-xs text-gray-500">
                                    <span>Category: <?php echo htmlspecialchars($doc['category']); ?></span>
                                    <span>Created by: <?php echo htmlspecialchars($doc['created_by_name'] ?? 'Unknown'); ?></span>
                                    <span>Submitted: <?php echo date('M j, Y g:i A', strtotime($doc['created_at'])); ?></span>
                                </div>
                            </div>
                            <div class="flex gap-2 ml-4">
                                <button onclick="reviewItem('document', <?php echo $doc['id']; ?>, 'approve')" class="px-4 py-2 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition-colors">
                                    Approve
                                </button>
                                <button onclick="reviewItem('document', <?php echo $doc['id']; ?>, 'reject')" class="px-4 py-2 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition-colors">
                                    Reject
                                </button>
                                <button onclick="showReviewModal('document', <?php echo $doc['id']; ?>)" class="px-4 py-2 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition-colors">
                                    Review
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Pending Announcements -->
        <?php if (!empty($pendingAnnouncements)): ?>
        <div class="bg-white rounded-xl border-2 border-gray-200 shadow-sm mb-6">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">Announcements Pending Review</h2>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <?php foreach ($pendingAnnouncements as $ann): ?>
                    <div class="border-2 border-gray-200 rounded-xl p-6 hover:border-purple-300 transition-colors">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($ann['title']); ?></h3>
                                <p class="text-sm text-gray-600 mb-2"><?php echo htmlspecialchars(substr($ann['description'] ?? '', 0, 150)); ?>...</p>
                                <div class="flex items-center gap-4 text-xs text-gray-500">
                                    <span>Created by: <?php echo htmlspecialchars($ann['created_by_name'] ?? 'Unknown'); ?></span>
                                    <span>Submitted: <?php echo date('M j, Y g:i A', strtotime($ann['created_at'])); ?></span>
                                </div>
                            </div>
                            <div class="flex gap-2 ml-4">
                                <button onclick="reviewItem('announcement', <?php echo $ann['id']; ?>, 'approve')" class="px-4 py-2 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition-colors">
                                    Approve
                                </button>
                                <button onclick="reviewItem('announcement', <?php echo $ann['id']; ?>, 'reject')" class="px-4 py-2 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition-colors">
                                    Reject
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Pending Events -->
        <?php if (!empty($pendingEvents)): ?>
        <div class="bg-white rounded-xl border-2 border-gray-200 shadow-sm mb-6">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">Events Pending Review</h2>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <?php foreach ($pendingEvents as $evt): ?>
                    <div class="border-2 border-gray-200 rounded-xl p-6 hover:border-pink-300 transition-colors">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($evt['title']); ?></h3>
                                <p class="text-sm text-gray-600 mb-2"><?php echo htmlspecialchars($evt['caption'] ?? ''); ?></p>
                                <div class="flex items-center gap-4 text-xs text-gray-500">
                                    <span>Date: <?php echo date('M j, Y', strtotime($evt['date'])); ?></span>
                                    <span>Created by: <?php echo htmlspecialchars($evt['created_by_name'] ?? 'Unknown'); ?></span>
                                </div>
                            </div>
                            <div class="flex gap-2 ml-4">
                                <button onclick="reviewItem('event', <?php echo $evt['id']; ?>, 'approve')" class="px-4 py-2 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition-colors">
                                    Approve
                                </button>
                                <button onclick="reviewItem('event', <?php echo $evt['id']; ?>, 'reject')" class="px-4 py-2 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition-colors">
                                    Reject
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (empty($pendingDocuments) && empty($pendingAnnouncements) && empty($pendingEvents)): ?>
        <div class="bg-white rounded-xl border-2 border-gray-200 shadow-sm p-12 text-center">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mx-auto text-gray-400 mb-4">
                <path d="M9 11l3 3L22 4"></path>
                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
            </svg>
            <h3 class="text-xl font-bold text-gray-900 mb-2">No Items Pending Review</h3>
            <p class="text-gray-600">All content has been reviewed. Check back later for new submissions.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Review Modal -->
<div id="reviewModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl p-8 max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <h3 class="text-2xl font-bold text-gray-900 mb-4">Review Item</h3>
        <form id="reviewForm" onsubmit="submitReview(event)">
            <input type="hidden" id="review-type" name="type">
            <input type="hidden" id="review-id" name="id">
            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-2">Review Notes</label>
                <textarea id="review-notes" name="notes" rows="4" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Add your review comments..."></textarea>
            </div>
            <div class="flex gap-3">
                <button type="submit" name="action" value="approve" class="flex-1 px-6 py-3 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition-colors">
                    Approve
                </button>
                <button type="submit" name="action" value="reject" class="flex-1 px-6 py-3 bg-red-600 text-white font-semibold rounded-xl hover:bg-red-700 transition-colors">
                    Reject
                </button>
                <button type="button" onclick="closeReviewModal()" class="px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function reviewItem(type, id, action) {
    if (action === 'approve') {
        submitReviewAction(type, id, 'approve', '');
    } else if (action === 'reject') {
        submitReviewAction(type, id, 'reject', '');
    }
}

function showReviewModal(type, id) {
    document.getElementById('review-type').value = type;
    document.getElementById('review-id').value = id;
    document.getElementById('reviewModal').classList.remove('hidden');
}

function closeReviewModal() {
    document.getElementById('reviewModal').classList.add('hidden');
    document.getElementById('review-notes').value = '';
}

function submitReview(event) {
    event.preventDefault();
    const form = event.target;
    const type = document.getElementById('review-type').value;
    const id = document.getElementById('review-id').value;
    const action = event.submitter.value;
    const notes = document.getElementById('review-notes').value;
    
    submitReviewAction(type, id, action, notes);
    closeReviewModal();
}

function submitReviewAction(type, id, action, notes) {
    const endpoint = type === 'document' ? 'documents.php' : (type === 'announcement' ? 'announcements.php' : 'events_handler.php');
    
    const formData = new FormData();
    if (action === 'approve') {
        formData.append('action', 'approve');
        formData.append('id', id);
        if (notes) {
            formData.append('approval_notes', notes);
        }
    } else if (action === 'reject') {
        formData.append('action', 'update');
        formData.append('id', id);
        formData.append('status', 'draft');
        if (notes) {
            formData.append('review_notes', notes);
        }
    }
    
    fetch(`${window.ADMIN_URL}/${endpoint}`, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
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
