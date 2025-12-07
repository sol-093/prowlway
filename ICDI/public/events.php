<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/upload.php';

$pageTitle = 'Events - PROWLWAY ICDISG';
$bodyClass = '';
include '../includes/header.php';

// Fetch events from database
$events = dbFetchAll("SELECT * FROM events WHERE status = 'published' ORDER BY date DESC, display_order ASC");
?>

<!-- Events Gallery Container -->
<div class="events-gallery-container">
    <!-- Page Header -->
    <div class="events-gallery-header">
        <h1 class="events-gallery-title">EVENTS</h1>
    </div>

    <!-- Events Grid -->
    <div class="events-grid-gallery">
        <?php if (empty($events)): ?>
            <div class="no-events" style="grid-column: 1 / -1; text-align: center; padding: var(--spacing-xl); color: var(--color-text-muted);">
                <p>No events available yet.</p>
            </div>
        <?php else: ?>
            <?php foreach ($events as $event): 
                $eventDate = new DateTime($event['date']);
                $gallery = !empty($event['gallery']) ? json_decode($event['gallery'], true) : [];
            ?>
                <a href="<?php echo PUBLIC_URL; ?>/event-detail.php?id=<?php echo $event['id']; ?>" class="event-card-gallery-link">
                <div class="event-card-gallery">
                    <div class="event-card-image">
                        <img src="<?php echo getImageUrl($event['image']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>">
                    </div>
                    <div class="event-card-content">
                        <h3 class="event-card-title"><?php echo htmlspecialchars($event['title']); ?></h3>
                        <?php if ($event['caption']): ?>
                            <p class="event-card-description"><?php echo htmlspecialchars($event['caption']); ?></p>
                        <?php endif; ?>
                        <?php if ($event['description']): ?>
                            <div class="event-card-name"><?php echo htmlspecialchars(substr($event['description'], 0, 100)) . (strlen($event['description']) > 100 ? '...' : ''); ?></div>
                        <?php endif; ?>
                        <div class="event-card-meta">
                            <span><?php echo $eventDate->format('F j, Y'); ?></span>
                            <?php if ($event['location']): ?>
                                <span><?php echo htmlspecialchars($event['location']); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="event-card-footer">
                            <span class="event-card-arrow">→</span>
                        </div>
                    </div>
                </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Event Detail Modal -->
<div class="modal" id="eventModal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal()">×</button>
        <div class="modal-layout">
            <div class="modal-image-panel">
                <img id="modalImage" class="modal-image" src="" alt="Event visual">
                <div class="image-caption" id="modalCaption">&nbsp;</div>
            </div>
            <div class="modal-sheet-card">
                <div class="sheet-header">
                    <div>
                        <p class="sheet-label">PROWLWAY EVENT LOG</p>
                        <h2 class="sheet-title" id="modalTitle"></h2>
                    </div>
                    <div class="sheet-meta">
                        <span id="modalCategory" class="sheet-badge"></span>
                        <span id="modalDateShort" class="sheet-date"></span>
                    </div>
                </div>
                <table class="modal-sheet" role="table">
                    <thead>
                        <tr>
                            <th scope="col">DETAIL</th>
                            <th scope="col">INFORMATION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th scope="row">Event Name</th>
                            <td id="modalTitleDetail"></td>
                        </tr>
                        <tr>
                            <th scope="row">Category</th>
                            <td id="modalCategoryDetail"></td>
                        </tr>
                        <tr>
                            <th scope="row">Schedule</th>
                            <td id="modalDate"></td>
                        </tr>
                        <tr>
                            <th scope="row">Location</th>
                            <td id="modalLocation"></td>
                        </tr>
                    </tbody>
                </table>
                <div class="sheet-notes">
                    <div class="sheet-notes-heading">Briefing Notes</div>
                    <p id="modalDescription" class="modal-description"></p>
                </div>
                <div class="sheet-summary">
                    <div class="sheet-section-title">Event Summary</div>
                    <p id="modalSummary" class="sheet-summary-text">&nbsp;</p>
                </div>
                <div class="sheet-gallery">
                    <div class="sheet-section-title">Event Gallery</div>
                    <div class="gallery-grid" id="modalGallery" aria-label="Event gallery previews"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

