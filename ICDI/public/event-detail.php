<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/upload.php';

$eventId = intval($_GET['id'] ?? 0);

if (!$eventId) {
    header('Location: ' . PUBLIC_URL . '/events.php');
    exit;
}

// Fetch event details
$event = dbFetchOne("SELECT * FROM events WHERE id = ? AND status = 'published'", [$eventId]);

if (!$event) {
    header('Location: ' . PUBLIC_URL . '/events.php');
    exit;
}

// Parse gallery images
$gallery = [];
if (!empty($event['gallery'])) {
    $galleryRaw = json_decode($event['gallery'], true);
    if (is_array($galleryRaw)) {
        $gallery = $galleryRaw;
    }
}

// Format dates
$eventDate = new DateTime($event['date']);
$eventDateFormatted = $eventDate->format('F j, Y');
$eventEndDateFormatted = '';
if (!empty($event['end_date'])) {
    $endDate = new DateTime($event['end_date']);
    $eventEndDateFormatted = $endDate->format('F j, Y');
    if ($eventDateFormatted !== $eventEndDateFormatted) {
        $eventDateFormatted = $eventDate->format('F j') . ' - ' . $eventEndDateFormatted;
    }
}

$pageTitle = $event['title'] . ' - Events - PROWLWAY';
$bodyClass = 'event-detail-page';
include '../includes/header.php';
?>

<div class="event-detail-container">
    <!-- Back Button -->
    <div class="event-detail-header">
        <a href="<?php echo PUBLIC_URL; ?>/events.php" class="back-button">← Back to Events</a>
    </div>

    <!-- Event Summary Section -->
    <div class="event-summary-section">
        <div class="event-summary-content">
            <h1 class="event-detail-title">EVENT SUMMARY</h1>
            
            <div class="event-summary-layout">
                <!-- Event Poster/Image -->
                <div class="event-poster">
                    <img src="<?php echo getImageUrl($event['image']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" class="event-poster-image">
                </div>
                
                <!-- Event Details -->
                <div class="event-details">
                    <h2 class="event-main-title"><?php echo htmlspecialchars($event['title']); ?></h2>
                    
                    <?php if ($event['caption']): ?>
                        <p class="event-subtitle"><?php echo htmlspecialchars($event['caption']); ?></p>
                    <?php endif; ?>
                    
                    <div class="event-meta-info">
                        <div class="meta-item">
                            <strong>Date:</strong> <?php echo $eventDateFormatted; ?>
                        </div>
                        <?php if ($event['location']): ?>
                            <div class="meta-item">
                                <strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?>
                            </div>
                        <?php endif; ?>
                        <div class="meta-item">
                            <strong>Category:</strong> <?php echo ucfirst($event['category']); ?>
                        </div>
                    </div>
                    
                    <?php if ($event['description']): ?>
                        <div class="event-description">
                            <?php echo nl2br(htmlspecialchars($event['description'])); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($event['summary']): ?>
                        <div class="event-summary-text">
                            <h3>CORE IMPACT</h3>
                            <?php echo nl2br(htmlspecialchars($event['summary'])); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Photo Gallery Section -->
    <?php if (!empty($gallery)): ?>
    <div class="photo-gallery-section">
        <h2 class="gallery-title">PHOTO GALLERY</h2>
        <div class="gallery-grid">
            <?php foreach ($gallery as $index => $galleryImage): ?>
                <div class="gallery-item">
                    <img src="<?php echo getImageUrl($galleryImage); ?>" alt="Gallery image <?php echo $index + 1; ?>" class="gallery-thumbnail" onclick="openGalleryModal(<?php echo $index; ?>)">
                </div>
            <?php endforeach; ?>
        </div>
        <div class="gallery-actions">
            <button class="view-album-btn" onclick="openGalleryModal(0)">VIEW ALBUM</button>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Gallery Modal -->
<?php if (!empty($gallery)): ?>
<div class="gallery-modal" id="galleryModal" onclick="closeGalleryModal(event)">
    <div class="gallery-modal-content">
        <button class="gallery-modal-close" onclick="closeGalleryModal()">×</button>
        <button class="gallery-nav gallery-nav-prev" onclick="changeGalleryImage(-1)">‹</button>
        <button class="gallery-nav gallery-nav-next" onclick="changeGalleryImage(1)">›</button>
        <img id="galleryModalImage" src="" alt="Gallery image" class="gallery-modal-image">
        <div class="gallery-counter">
            <span id="galleryCurrent">1</span> / <span id="galleryTotal"><?php echo count($gallery); ?></span>
        </div>
    </div>
</div>

<script>
const galleryImages = <?php echo json_encode(array_map(function($path) { return getImageUrl($path); }, $gallery)); ?>;
let currentGalleryIndex = 0;

function openGalleryModal(index) {
    currentGalleryIndex = index;
    updateGalleryModal();
    document.getElementById('galleryModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeGalleryModal(event) {
    if (!event || event.target === event.currentTarget || event.target.classList.contains('gallery-modal-close')) {
        document.getElementById('galleryModal').style.display = 'none';
        document.body.style.overflow = '';
    }
}

function changeGalleryImage(direction) {
    currentGalleryIndex += direction;
    if (currentGalleryIndex < 0) currentGalleryIndex = galleryImages.length - 1;
    if (currentGalleryIndex >= galleryImages.length) currentGalleryIndex = 0;
    updateGalleryModal();
}

function updateGalleryModal() {
    document.getElementById('galleryModalImage').src = galleryImages[currentGalleryIndex];
    document.getElementById('galleryCurrent').textContent = currentGalleryIndex + 1;
}

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    const modal = document.getElementById('galleryModal');
    if (modal && modal.style.display === 'flex') {
        if (e.key === 'Escape') closeGalleryModal();
        if (e.key === 'ArrowLeft') changeGalleryImage(-1);
        if (e.key === 'ArrowRight') changeGalleryImage(1);
    }
});
</script>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>

