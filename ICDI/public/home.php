<?php
// Error reporting for debugging (remove in production or set to 0)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to users, but log them
ini_set('log_errors', 1);

// Disable output buffering if active to prevent blocking
while (ob_get_level() > 0) {
    ob_end_flush();
}

try {
    require_once '../includes/config.php';
    require_once '../includes/database.php';
    require_once '../includes/upload.php';
} catch (Exception $e) {
    error_log("Homepage initialization error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    // Show a user-friendly error page instead of dying
    http_response_code(500);
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Error - PROWLWAY</title>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #0f1112; color: #fff; }
            h1 { color: #e74c3c; }
            a { color: #4a9eff; }
            pre { text-align: left; background: #1a1a1a; padding: 15px; border-radius: 5px; overflow: auto; }
        </style>
    </head>
    <body>
        <h1>Error Loading Page</h1>
        <p>We're experiencing technical difficulties. Please try again later.</p>
        <?php if (defined('DEBUG_MODE') && DEBUG_MODE): ?>
        <pre>Error: <?php echo htmlspecialchars($e->getMessage()); ?>\n\n<?php echo htmlspecialchars($e->getTraceAsString()); ?></pre>
        <?php endif; ?>
        <p><a href="<?php echo isset($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : '/'; ?>">Go Back</a></p>
        <p><a href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/debug_homepage.php">Run Diagnostics</a></p>
    </body>
    </html>
    <?php
    exit;
}

$pageTitle = 'PROWLWAY - ICDISG Archive Website';
// Enable intro animation only if not shown before (check cookie)
// Can be skipped with ?skip_intro=1
$introShownCookie = $_COOKIE['prowlway_intro_shown'] ?? null;
$showIntro = !isset($_GET['skip_intro']) && empty($introShownCookie);
$bodyClass = $showIntro ? 'intro-active' : '';
include '../includes/header.php';

// Fetch announcements for display (with error handling)
try {
    $announcements = dbFetchAll("SELECT *, is_meeting, meeting_date, meeting_end_date, meeting_location FROM announcements WHERE status = 'published' ORDER BY pinned DESC, created_at DESC LIMIT 10");
    if (!is_array($announcements)) {
        $announcements = [];
    }
} catch (Exception $e) {
    error_log("Error fetching announcements: " . $e->getMessage());
    $announcements = [];
}

// Upcoming meetings removed - all announcements go to regular announcements panel
$upcomingMeetings = [];

// Fetch events for carousel (with error handling)
try {
    $events = dbFetchAll("SELECT * FROM events WHERE status = 'published' ORDER BY display_order ASC, created_at DESC LIMIT 5");
    if (!is_array($events)) {
        $events = [];
    }
} catch (Exception $e) {
    error_log("Error fetching events: " . $e->getMessage());
    $events = [];
}

// Fetch document counts by category (with error handling)
$docCounts = [];
try {
    $documents = dbFetchAll("SELECT category FROM documents WHERE status = 'published'");
    if (is_array($documents)) {
        foreach ($documents as $doc) {
            $docCounts[$doc['category']] = ($docCounts[$doc['category']] ?? 0) + 1;
        }
    }
} catch (Exception $e) {
    error_log("Error fetching document counts: " . $e->getMessage());
}

// Fetch site settings (for dynamic footer, contact, social links, etc.) (with error handling)
$settings = [];
try {
    $settingsRows = dbFetchAll("SELECT setting_key, setting_value, setting_type FROM site_settings");
    if (is_array($settingsRows)) {
        foreach ($settingsRows as $row) {
            $key = $row['setting_key'];
            $value = $row['setting_value'];
            switch ($row['setting_type']) {
                case 'json':
                    $decoded = json_decode($value, true);
                    $settings[$key] = $decoded !== null ? $decoded : $value;
                    break;
                case 'boolean':
                    $settings[$key] = $value === '1' || $value === 'true';
                    break;
                case 'number':
                    $settings[$key] = is_numeric($value) ? (float)$value : $value;
                    break;
                default:
                    $settings[$key] = $value;
            }
        }
    }
} catch (Exception $e) {
    error_log("Error fetching site settings: " . $e->getMessage());
}

// Helper values with sensible fallbacks
$siteName     = $settings['site_name']     ?? 'PROWLWAY';
$siteTagline  = $settings['site_description'] ?? 'ICDISG Archive Website';
$contactEmail = $settings['contact_email'] ?? 'imacsac@kidduph';

// Dynamic hero organization label (from student_organizations) (with error handling)
try {
    $primaryOrg = dbFetchOne("SELECT name, acronym FROM student_organizations WHERE status = 'active' ORDER BY display_order ASC LIMIT 1");
    $heroOrgLabel = $primaryOrg ? trim(($primaryOrg['acronym'] ?? '') . ' ' . ($primaryOrg['name'] ?? '')) : 'KLD-ICDI Student Government';
} catch (Exception $e) {
    error_log("Error fetching primary organization: " . $e->getMessage());
    $heroOrgLabel = 'KLD-ICDI Student Government';
}

// Hero logo: use ICDI.png if it exists, else fallback icon (avoids broken image on mobile)
try {
    $heroLogoPath = (defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__)) . '/assets/images/ICDI.png';
    $heroLogoSrc = (file_exists($heroLogoPath)) ? ASSETS_URL . '/images/ICDI.png' : ASSETS_URL . '/IMG/ICONS/OFFICE.png';
} catch (Exception $e) {
    error_log("Error setting hero logo: " . $e->getMessage());
    $heroLogoSrc = ASSETS_URL . '/IMG/ICONS/OFFICE.png';
}

// Dynamic Origin section organizations (limited to 4) (with error handling)
try {
    $originOrganizations = dbFetchAll("SELECT id, name, acronym, logo FROM student_organizations WHERE status = 'active' ORDER BY display_order ASC LIMIT 4");
    if (!is_array($originOrganizations)) {
        $originOrganizations = [];
    }
} catch (Exception $e) {
    error_log("Error fetching origin organizations: " . $e->getMessage());
    $originOrganizations = [];
}

// Dynamic footer about text from institute_info (with error handling)
try {
    $footerAbout = dbFetchOne("SELECT title, content FROM institute_info WHERE section = 'about' AND status = 'published' LIMIT 1");
    if (!is_array($footerAbout)) {
        $footerAbout = [];
    }
} catch (Exception $e) {
    error_log("Error fetching footer about: " . $e->getMessage());
    $footerAbout = [];
}

// Dynamic Tech Care links from settings (JSON), with fallback list
$defaultTechcareLinks = [
    ['id' => 'concern',  'label' => 'Concern Form'],
    ['id' => 'printing', 'label' => 'Diy Printing Station'],
    ['id' => 'wiring',   'label' => 'Lab Wiring'],
    ['id' => 'outreach', 'label' => 'Outreach'],
];
$techcareLinks = [];
if (!empty($settings['techcare_links']) && is_array($settings['techcare_links'])) {
    $techcareLinks = $settings['techcare_links'];
} else {
    $techcareLinks = $defaultTechcareLinks;
}

// Social links from settings with fallbacks
$facebookUrl  = $settings['social_facebook']  ?? 'https://www.facebook.com/profile.php?id=61569058340306';
$instagramUrl = $settings['social_instagram'] ?? 'https://www.instagram.com/imacssc/';
$tiktokUrl    = $settings['social_tiktok']    ?? 'https://www.tiktok.com/@imacssc';
$twitterUrl   = $settings['social_twitter']   ?? 'https://twitter.com/imacssc';

// Footer copyright text (can be overridden via settings)
$footerCopy = $settings['footer_copy'] ?? 'Copyright © ' . date('Y') . '. ' . $siteName . ' · The ICDISG Archival Website';
?>

<!-- ========================================
     INTRO ANIMATION SCREEN
     ======================================== -->
<?php if ($showIntro): ?>
<div class="intro-screen" id="introScreen">
    <div class="intro-text" id="introText"></div>
</div>
<?php endif; ?>

<!-- ========================================
     MAIN CONTENT (Your Landing Page)
     ======================================== -->
<div class="main-content" id="mainContent" <?php echo $showIntro ? '' : 'style="opacity: 1 !important; visibility: visible !important; display: block !important;"'; ?>>
    
    <!-- ========================================
         MAIN CONTENT AREA
         ======================================== -->
    <main class="main-container" id="home">
        
        <!-- ========================================
             HERO SECTION
             Left: Main banner with ICDISG badge
             Right: Announcements panel
             ======================================== -->
        <section class="hero-section">
            <!-- Hero Banner -->
            <div class="hero-banner" aria-label="ICDISG banner">
                <picture class="hero-banner-picture">
                    <source media="(max-width: 768px)" srcset="<?php echo ASSETS_URL; ?>/IMG/mobile.png">
                    <source media="(min-width: 769px)" srcset="<?php echo ASSETS_URL; ?>/IMG/banner.png">
                    <img src="<?php echo ASSETS_URL; ?>/IMG/banner.png" alt="ICDISG Banner" class="hero-banner-image">
                </picture>
            </div>
            
            
            <!-- Announcements Panel -->
            <aside class="announcements-panel">
                <h2 class="panel-title">ANNOUNCEMENTS</h2>
                
                <div class="scroll-wrapper">
                    <?php if (empty($announcements)): ?>
                        <div class="announcement-card">
                            <div class="announcement-title">No announcements yet</div>
                            <p class="announcement-desc">Check back later for updates.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($announcements as $announcement): 
                            try {
                                $annDate = new DateTime($announcement['created_at'] ?? 'now');
                            } catch (Exception $e) {
                                $annDate = new DateTime('now');
                            }
                            // Escape JSON for data attribute (double encode to prevent issues)
                            $announcementJson = htmlspecialchars(json_encode($announcement, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8');
                        ?>
                            <a href="<?php echo PUBLIC_URL; ?>/announcement-detail.php?id=<?php echo $announcement['id']; ?>" class="announcement-card-link">
                                <div class="announcement-card">
                                    <?php if (!empty($announcement['image'])): ?>
                                        <div class="announcement-image-wrapper">
                                            <img src="<?php echo getImageUrl($announcement['image']); ?>" alt="<?php echo htmlspecialchars($announcement['title'] ?? ''); ?>" class="announcement-image" onerror="this.style.display='none';">
                                        </div>
                                    <?php endif; ?>
                                    <div class="announcement-title"><?php echo htmlspecialchars($announcement['title'] ?? ''); ?></div>
                                    <div class="announcement-date"><?php echo $annDate->format('M d, Y'); ?></div>
                                    <div class="card-divider"></div>
                                    <p class="announcement-desc"><?php echo htmlspecialchars($announcement['description'] ?? ''); ?></p>
                                    <div class="btn-read-more hover-zoom">READ MORE ></div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <div class="fade-overlay"></div>
            </aside>
        </section>

        <!-- ========================================
             THREE CARD GRID SECTION
             Cards: Origin | Documents | Events
             ======================================== -->
        <section class="cards-grid" id="institute">
            
            <!-- ORIGIN CARD -->
            <div class="card origin-card">
                <h3 class="card-title">ORIGIN</h3>
                <div class="origin-logos">
                    <!-- Logo/Badge Grid (2x2) driven by student_organizations -->
                    <?php
                    // Define default logo paths used in the original static design
                    $originLogoFallbacks = [
                        0 => ASSETS_URL . '/images/ICDI.png',
                        1 => ASSETS_URL . '/images/ISSOC.png',
                        2 => ASSETS_URL . '/images/BITCUB.png',
                        3 => ASSETS_URL . '/images/GDSC.png',
                    ];
                    for ($i = 0; $i < 4; $i++):
                        $org = $originOrganizations[$i] ?? null;
                        $logoSrc = $org && !empty($org['logo'])
                            ? getImageUrl($org['logo'])
                            : ($originLogoFallbacks[$i] ?? $originLogoFallbacks[0]);
                        $logoAlt = $org
                            ? trim(($org['acronym'] ?? '') . ' ' . ($org['name'] ?? 'Organization logo'))
                            : 'Organization logo';
                        $orgUrl = $org ? PUBLIC_URL . '/organization.php?id=' . $org['id'] : null;
                        // Transparent-friendly: no bg/border when image is PNG or uploaded (often transparent)
                        $isTransparentImg = preg_match('/\.png$/i', $logoSrc) || ($org && !empty($org['logo']));
                    ?>
                    <div class="logo-item<?php echo $isTransparentImg ? ' logo-transparent' : ''; ?>">
                        <?php if ($orgUrl): ?>
                            <a href="<?php echo htmlspecialchars($orgUrl); ?>" class="logo-badge-link">
                                <div class="logo-badge">
                                    <img src="<?php echo htmlspecialchars($logoSrc); ?>" alt="<?php echo htmlspecialchars($logoAlt); ?>" class="logo-image">
                                </div>
                            </a>
                        <?php else: ?>
                            <div class="logo-badge">
                                <img src="<?php echo htmlspecialchars($logoSrc); ?>" alt="<?php echo htmlspecialchars($logoAlt); ?>" class="logo-image">
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
            
            <!-- DOCUMENTS CARD -->
            <div class="card documents-card">
                <h3 class="card-title">DOCUMENTS</h3>
                <div class="documents-grid">
                    <!-- Document Folder Items (2x2 Grid) -->
                    <?php 
                    // Map category codes to icon filenames from ICONS directory
                    $categoryIcons = [
                        '01' => 'OFFICE.png',
                        '02' => 'EXECUTIVE.png',
                        '03' => 'ORDINANCE.png',
                        '04' => 'RESOLUTION.png',
                        '05' => 'OTHER.png'
                    ];
                    $categoryNames = [
                        '01' => 'OFFICES REPORT',
                        '02' => 'EXECUTIVE ORDER',
                        '03' => 'ORDINANCE',
                        '04' => 'RESOLUTION',
                        '05' => 'OTHER'
                    ];
                    ?>
                    <a href="<?php echo PUBLIC_URL; ?>/documents.php?category=01" class="folder-item folder-item-01">
                        <img src="<?php echo ASSETS_URL; ?>/IMG/ICONS/<?php echo $categoryIcons['01']; ?>" alt="<?php echo $categoryNames['01']; ?>">
                    </a>
                    <a href="<?php echo PUBLIC_URL; ?>/documents.php?category=02" class="folder-item folder-item-02">
                        <img src="<?php echo ASSETS_URL; ?>/IMG/ICONS/<?php echo $categoryIcons['02']; ?>" alt="<?php echo $categoryNames['02']; ?>">
                    </a>
                    <a href="<?php echo PUBLIC_URL; ?>/documents.php?category=03" class="folder-item folder-item-03">
                        <img src="<?php echo ASSETS_URL; ?>/IMG/ICONS/<?php echo $categoryIcons['03']; ?>" alt="<?php echo $categoryNames['03']; ?>">
                    </a>
                    <a href="<?php echo PUBLIC_URL; ?>/documents.php?category=04" class="folder-item folder-item-04">
                        <img src="<?php echo ASSETS_URL; ?>/IMG/ICONS/<?php echo $categoryIcons['04']; ?>" alt="<?php echo $categoryNames['04']; ?>">
                    </a>
                </div>
            </div>
            
            <!-- EVENTS CARD -->
            <div class="card events-card">
                <h3 class="card-title">EVENTS</h3>
                
                <!-- Events Carousel/Slider -->
                <div class="events-carousel">
                    <div class="carousel-track">
                        <?php if (empty($events)): ?>
                            <div class="event-slide active">
                                <div class="event-image">
                                    <div style="width: 100%; height: 200px; background: var(--color-card-dark); display: flex; align-items: center; justify-content: center; color: var(--color-text-muted);">
                                        No events available
                                    </div>
                                </div>
                                <p class="event-caption">Check back later for upcoming events</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($events as $index => $event): ?>
                                <a href="<?php echo PUBLIC_URL; ?>/event-detail.php?id=<?php echo htmlspecialchars($event['id'] ?? ''); ?>" class="event-slide-link" style="text-decoration: none; color: inherit;">
                                    <div class="event-slide <?php echo $index === 0 ? 'active' : ''; ?>">
                                        <div class="event-image">
                                            <?php if (!empty($event['image'])): ?>
                                                <img src="<?php echo htmlspecialchars(getImageUrl($event['image'])); ?>" alt="<?php echo htmlspecialchars($event['title'] ?? 'Event'); ?>" onerror="this.style.display='none'; this.parentElement.innerHTML='<div style=\'width:100%;height:200px;background:var(--color-card-dark);display:flex;align-items:center;justify-content:center;color:var(--color-text-muted);\'>No image</div>';">
                                            <?php else: ?>
                                                <div style="width: 100%; height: 200px; background: var(--color-card-dark); display: flex; align-items: center; justify-content: center; color: var(--color-text-muted);">No image</div>
                                            <?php endif; ?>
                                        </div>
                                        <p class="event-caption"><?php echo htmlspecialchars($event['caption'] ?? $event['title'] ?? 'Event'); ?></p>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Carousel Navigation Dots -->
                    <div class="carousel-dots" aria-label="Event carousel navigation">
                        <?php if (!empty($events)): ?>
                            <?php foreach ($events as $index => $event): ?>
                                <span class="dot <?php echo $index === 0 ? 'active' : ''; ?>" data-slide="<?php echo $index; ?>"></span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
        </section>

    </main>
 
</div>

<!-- Sponsor Popup -->
<?php
// Fetch active sponsors
$sponsorsSetting = dbFetchOne("SELECT setting_value FROM site_settings WHERE setting_key = 'sponsors'");
$activeSponsors = [];
if ($sponsorsSetting && !empty($sponsorsSetting['setting_value'])) {
    $decoded = json_decode($sponsorsSetting['setting_value'], true);
    if (is_array($decoded)) {
        // Filter only active sponsors
        $activeSponsors = array_filter($decoded, function($sponsor) {
            return !empty($sponsor['active']) && $sponsor['active'] == 1;
        });
        // Sort by display_order
        usort($activeSponsors, function($a, $b) {
            return ($a['display_order'] ?? 0) - ($b['display_order'] ?? 0);
        });
    }
}
?>
<?php if (!empty($activeSponsors)): ?>
<div id="sponsor-popup" class="sponsor-popup" style="display: none;">
    <div class="sponsor-popup-backdrop" onclick="closeSponsorPopup()"></div>
    <div class="sponsor-popup-content">
        <button class="sponsor-popup-close" onclick="closeSponsorPopup()" aria-label="Close">&times;</button>
        <div class="sponsor-popup-header">
            <h3>Our Sponsors</h3>
        </div>
        <div class="sponsor-popup-body">
            <?php foreach ($activeSponsors as $sponsor): ?>
                <div class="sponsor-item">
                    <?php if (!empty($sponsor['image'])): ?>
                        <?php if (!empty($sponsor['link_url'])): ?>
                            <a href="<?php echo htmlspecialchars($sponsor['link_url']); ?>" target="_blank" rel="noopener noreferrer">
                                <img src="<?php echo getImageUrl($sponsor['image']); ?>" alt="<?php echo htmlspecialchars($sponsor['title']); ?>" class="sponsor-image">
                            </a>
                        <?php else: ?>
                            <img src="<?php echo getImageUrl($sponsor['image']); ?>" alt="<?php echo htmlspecialchars($sponsor['title']); ?>" class="sponsor-image">
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if (!empty($sponsor['title'])): ?>
                        <p class="sponsor-title"><?php echo htmlspecialchars($sponsor['title']); ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
.sponsor-popup {
    position: fixed;
    inset: 0;
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
}
.sponsor-popup-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(4px);
}
.sponsor-popup-content {
    position: relative;
    background: white;
    border-radius: 1rem;
    max-width: 600px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    z-index: 10001;
}
.sponsor-popup-close {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: transparent;
    border: none;
    font-size: 2rem;
    cursor: pointer;
    color: #6b7280;
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.5rem;
    transition: all 0.2s;
}
.sponsor-popup-close:hover {
    background: #f3f4f6;
    color: #1f2937;
}
.sponsor-popup-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}
.sponsor-popup-header h3 {
    font-size: 1.5rem;
    font-weight: bold;
    color: #1f2937;
    margin: 0;
}
.sponsor-popup-body {
    padding: 1.5rem;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
}
.sponsor-item {
    text-align: center;
}
.sponsor-image {
    max-width: 100%;
    height: auto;
    border-radius: 0.5rem;
    margin-bottom: 0.5rem;
}
.sponsor-title {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0;
}
</style>

<script>
(function() {
    // Check if popup was already shown in this session
    if (sessionStorage.getItem('sponsor_popup_shown') === '1') {
        return;
    }
    
    // Show popup after a short delay
    setTimeout(function() {
        const popup = document.getElementById('sponsor-popup');
        if (popup) {
            popup.style.display = 'flex';
            // Mark as shown in session
            sessionStorage.setItem('sponsor_popup_shown', '1');
        }
    }, 2000); // Show after 2 seconds
    
    window.closeSponsorPopup = function() {
        const popup = document.getElementById('sponsor-popup');
        if (popup) {
            popup.style.display = 'none';
        }
    };
})();
</script>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>

