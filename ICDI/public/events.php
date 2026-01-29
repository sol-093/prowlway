<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/upload.php';

$pageTitle = 'Events - PROWLWAY ICDISG';
$bodyClass = 'events-page';
include '../includes/header.php';

// Fetch events from database
$events = dbFetchAll("SELECT * FROM events WHERE status = 'published' ORDER BY date DESC, display_order ASC");
?>

<!-- Main Container -->
<div class="container">
    <div class="events-outer-panel">
        <div class="events-inner-panel">
            
            <!-- Page Title -->
            <h1 class="events-page-title">Events</h1>
            
            <!-- Events Rows -->
            <div class="events-rows-container">
                <?php if (empty($events)): ?>
                    <div class="no-events">
                        <p>No events available yet.</p>
                    </div>
                <?php else: 
                    // Group events into rows of 3
                    $eventRows = array_chunk($events, 3);
                    foreach ($eventRows as $rowIndex => $rowEvents):
                ?>
                    <div class="events-row">
                        <?php foreach ($rowEvents as $event): 
                            $eventDate = new DateTime($event['date']);
                            $endDate = !empty($event['end_date']) ? new DateTime($event['end_date']) : null;
                            
                            // Format date range or single date
                            if ($endDate && $endDate != $eventDate) {
                                $dateDisplay = $eventDate->format('F j') . ' - ' . $endDate->format('j, Y');
                            } else {
                                $dateDisplay = $eventDate->format('F j, Y');
                            }
                        ?>
                            <a href="<?php echo PUBLIC_URL; ?>/event-detail.php?id=<?php echo $event['id']; ?>" class="event-card-link">
                                <div class="event-card">
                                    <div class="event-card-image-wrapper">
                                        <img src="<?php echo getImageUrl($event['image']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" class="event-card-image">
                                    </div>
                                    <div class="event-card-overlay">
                                        <h3 class="event-card-title"><?php echo htmlspecialchars($event['title']); ?></h3>
                                        <div class="event-card-date"><?php echo $dateDisplay; ?></div>
                                        <?php if ($event['location']): ?>
                                            <div class="event-card-location"><?php echo htmlspecialchars($event['location']); ?></div>
                                        <?php endif; ?>
                                        <div class="event-card-arrow-button">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M5 12h14M12 5l7 7-7 7"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
        </div>
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

