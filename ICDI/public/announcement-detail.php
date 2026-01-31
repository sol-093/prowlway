<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/upload.php';

$announcementId = intval($_GET['id'] ?? 0);

if (!$announcementId) {
    header('Location: ' . PUBLIC_URL . '/home.php');
    exit;
}

// Fetch announcement details
$announcement = dbFetchOne("SELECT * FROM announcements WHERE id = ? AND status = 'published'", [$announcementId]);

if (!$announcement) {
    header('Location: ' . PUBLIC_URL . '/home.php');
    exit;
}

// Decode social media JSON if present
$socialMedia = [];
if (!empty($announcement['social_media'])) {
    $decoded = json_decode($announcement['social_media'], true);
    if (is_array($decoded)) {
        $socialMedia = $decoded;
    }
}

// Format date
$annDate = new DateTime($announcement['created_at']);
$annDateFormatted = $annDate->format('F j, Y');

$pageTitle = htmlspecialchars($announcement['title']) . ' - Announcement - PROWLWAY';
$bodyClass = 'announcement-detail-page';
$hideHeader = true; // Hide header on announcement detail page
include '../includes/header.php';
?>

<!-- Main Container -->
<div class="announcement-detail-container">
    <!-- Header with X Button -->
    <div class="announcement-detail-header">
        <a href="<?php echo PUBLIC_URL; ?>/home.php" class="announcement-close-button" aria-label="Close">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 6L6 18M6 6l12 12"/>
            </svg>
        </a>
    </div>

    <!-- Content Wrapper -->
    <div class="announcement-detail-content-wrapper">
        <!-- Left Panel: Image Only -->
        <div class="announcement-detail-left-panel">
            <?php if (!empty($announcement['image'])): ?>
                <div class="announcement-detail-image">
                    <img src="<?php echo getImageUrl($announcement['image']); ?>" alt="<?php echo htmlspecialchars($announcement['title']); ?>">
                </div>
            <?php else: ?>
                <div class="announcement-detail-image" style="display: flex; align-items: center; justify-content: center; color: var(--color-text-muted);">
                    <p>No image available</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right Panel: Content -->
        <div class="announcement-detail-body">
            <div class="announcement-detail-header-content">
                <h1 class="announcement-detail-title"><?php echo htmlspecialchars($announcement['title']); ?></h1>
                <div class="announcement-detail-date"><?php echo $annDateFormatted; ?></div>
                
                <?php if ($announcement['is_meeting'] && ($announcement['meeting_date'] || $announcement['meeting_location'])): ?>
                    <div class="announcement-detail-meeting-info">
                        <?php if ($announcement['meeting_date']): ?>
                            <?php
                            $meetingDate = new DateTime($announcement['meeting_date']);
                            $dateStr = $meetingDate->format('F j, Y');
                            $timeStr = $meetingDate->format('g:i A');
                            ?>
                            <div class="meeting-detail-item">
                                📅 Date & Time: <?php echo $dateStr; ?> at <?php echo $timeStr; ?>
                                <?php if ($announcement['meeting_end_date']): ?>
                                    <?php
                                    $endDate = new DateTime($announcement['meeting_end_date']);
                                    $endTimeStr = $endDate->format('g:i A');
                                    ?>
                                    - <?php echo $endTimeStr; ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($announcement['meeting_location']): ?>
                            <div class="meeting-detail-item">
                                📍 Location: <?php echo htmlspecialchars($announcement['meeting_location']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($announcement['pdf_file'])): ?>
                    <div class="announcement-detail-pdf-section">
                        <div class="announcement-pdf-header">
                            <span class="announcement-pdf-label">PDF</span>
                            <a href="<?php echo BASE_URL . '/public/download.php?path=' . urlencode($announcement['pdf_file']); ?>" class="announcement-pdf-download-btn" download>
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>
                                </svg>
                                <span>Download</span>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="announcement-detail-content-full">
                <?php 
                $fullContent = $announcement['content'] ?: $announcement['description'];
                if ($fullContent): 
                    // Limit content to 50 words
                    $maxWords = 50;
                    $truncated = false;
                    
                    // Strip HTML tags for word counting
                    $textForCounting = strip_tags($fullContent);
                    $words = preg_split('/\s+/', trim($textForCounting));
                    
                    if (count($words) > $maxWords) {
                        // Get first 100 words
                        $words = array_slice($words, 0, $maxWords);
                        $truncatedText = implode(' ', $words);
                        
                        // Check if original content contains HTML tags
                        $hasHtmlTags = preg_match('/<[a-z][\s\S]*>/i', $fullContent);
                        
                        if ($hasHtmlTags) {
                            // For HTML content, try to preserve structure while truncating
                            // Simple approach: truncate and add ellipsis
                            $fullContent = $truncatedText . '...';
                        } else {
                            // For plain text, just use truncated version
                            $fullContent = $truncatedText . '...';
                        }
                        $truncated = true;
                    }
                    
                    // Check if content contains HTML tags for display
                    $hasHtmlTags = preg_match('/<[a-z][\s\S]*>/i', $fullContent);
                    if ($hasHtmlTags) {
                        echo $fullContent;
                    } else {
                        echo nl2br(htmlspecialchars($fullContent));
                    }
                    
                    if ($truncated): ?>
                        <p class="announcement-content-truncated">Content truncated for display. Full content available in PDF.</p>
                    <?php endif;
                else: 
                ?>
                    <p style="color: var(--color-text-muted); font-style: italic;">No content available.</p>
                <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>


<?php 
$hideFooter = true; // Hide footer on announcement detail page
include '../includes/footer.php'; 
?>
