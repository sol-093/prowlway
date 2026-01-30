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

<!-- Main Container -->
<div class="container px-4 md:px-6 lg:px-8">
    <div class="event-detail-outer-panel">
        <div class="event-detail-inner-panel px-4 md:px-8 lg:px-12 py-6 md:py-8 lg:py-10">
            
            <!-- Event Summary Section -->
            <div class="event-summary-section mb-6 md:mb-8">
                <h1 class="event-summary-title text-2xl md:text-3xl lg:text-4xl font-bold mb-4 md:mb-6">Event Summary</h1>
                
                <div class="event-summary-content flex flex-col lg:flex-row gap-6 md:gap-8">
                    <!-- Event Image -->
                    <div class="event-summary-image">
                        <img src="<?php echo getImageUrl($event['image']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" class="event-image-main">
                    </div>
                    
                    <!-- Event Details -->
                    <div class="event-summary-details">
                        <h2 class="event-summary-heading">Event Summary</h2>
                        
                        <?php if ($event['description']): ?>
                            <div class="event-description-text">
                                <?php echo htmlspecialchars($event['description']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Event Summary Text (Below) -->
                <?php if ($event['summary']): ?>
                    <div class="event-summary-text">
                        <?php echo htmlspecialchars($event['summary']); ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Photo Gallery Section -->
            <?php if (!empty($gallery)): ?>
            <div class="photo-gallery-section">
                <div class="photo-gallery-divider"></div>
                <h2 class="photo-gallery-title">Photo Gallery</h2>
                
                <div class="photo-gallery-grid">
                    <?php foreach ($gallery as $index => $galleryImage): ?>
                        <div class="photo-gallery-item">
                            <img src="<?php echo getImageUrl($galleryImage); ?>" alt="Gallery image <?php echo $index + 1; ?>" class="photo-gallery-thumbnail" onclick="openGalleryModal(<?php echo $index; ?>)">
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <button class="view-album-button" onclick="openGalleryModal(0)">
                    <span>View Album</span>
                </button>
            </div>
            <?php endif; ?>
            
        </div>
    </div>
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

