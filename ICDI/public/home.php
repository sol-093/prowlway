<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/upload.php';

$pageTitle = 'PROWLWAY - ICDISG Archive Website';
$bodyClass = 'intro-active';
include '../includes/header.php';

// Fetch announcements for display
$announcements = dbFetchAll("SELECT * FROM announcements WHERE status = 'published' ORDER BY pinned DESC, created_at DESC LIMIT 10");

// Fetch events for carousel
$events = dbFetchAll("SELECT * FROM events WHERE status = 'published' ORDER BY date DESC, display_order ASC LIMIT 5");

// Fetch document counts by category
$docCounts = [];
$documents = dbFetchAll("SELECT category FROM documents WHERE status = 'published'");
foreach ($documents as $doc) {
    $docCounts[$doc['category']] = ($docCounts[$doc['category']] ?? 0) + 1;
}

// Fetch site settings (for dynamic footer, contact, social links, etc.)
$settingsRows = dbFetchAll("SELECT setting_key, setting_value, setting_type FROM site_settings");
$settings = [];
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

// Helper values with sensible fallbacks
$siteName     = $settings['site_name']     ?? 'PROWLWAY';
$siteTagline  = $settings['site_description'] ?? 'ICDISG Archive Website';
$contactEmail = $settings['contact_email'] ?? 'imacsac@kidduph';

// Dynamic hero organization label (from student_organizations)
$primaryOrg = dbFetchOne("SELECT name, acronym FROM student_organizations WHERE status = 'active' ORDER BY display_order ASC LIMIT 1");
$heroOrgLabel = $primaryOrg ? trim(($primaryOrg['acronym'] ?? '') . ' ' . ($primaryOrg['name'] ?? '')) : 'KLD-ICDI Student Government';

// Hero logo: use ICDI.png if it exists, else fallback icon (avoids broken image on mobile)
$heroLogoPath = dirname(__DIR__) . '/assets/images/ICDI.png';
$heroLogoSrc = (file_exists($heroLogoPath)) ? ASSETS_URL . '/images/ICDI.png' : ASSETS_URL . '/IMG/ICONS/OFFICE.png';

// Dynamic Origin section organizations (limited to 4)
$originOrganizations = dbFetchAll("SELECT id, name, acronym, logo FROM student_organizations WHERE status = 'active' ORDER BY display_order ASC LIMIT 4");

// Dynamic footer about text from institute_info
$footerAbout = dbFetchOne("SELECT title, content FROM institute_info WHERE section = 'about' AND status = 'published' LIMIT 1");

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
<div class="intro-screen" id="introScreen">
    <div class="intro-text" id="introText"></div>
</div>

<!-- ========================================
     MAIN CONTENT (Your Landing Page)
     ======================================== -->
<div class="main-content" id="mainContent">
    
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
                <div class="banner-pattern"></div>
                <div class="banner-content-wrapper">
                    <!-- Left Side: Text Content -->
                    <div class="banner-left">
                        <div class="icdisg-title">&lt;ICDISG&gt;</div>
                        <div class="hero-tagline">TOGETHER, WE CONNECT, INNOVATE, AND EMPOWER</div>
                        <div class="hero-meta">
                            <span class="meta-item"><?php echo htmlspecialchars($heroOrgLabel); ?></span>
                            <span class="meta-item"><?php echo htmlspecialchars($contactEmail); ?></span>
                        </div>
                    </div>
                    
                    <!-- Right Side: ICDISG Logo -->
                    <div class="banner-right">
                        <div class="icdisg-logo-circle">
                            <img src="<?php echo htmlspecialchars($heroLogoSrc); ?>" alt="ICDISG Logo" class="icdisg-logo-img">
                        </div>
                        <div class="icdisg-full-name">INSTITUTE OF COMPUTING AND DIGITAL OPERATION STUDENT GOVERNMENT</div>
                    </div>
                </div>
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
                            $annDate = new DateTime($announcement['created_at']);
                        ?>
                            <div class="announcement-card">
                                <div class="announcement-title"><?php echo htmlspecialchars($announcement['title']); ?></div>
                                <div class="announcement-date"><?php echo $annDate->format('M d, Y'); ?></div>
                                <div class="card-divider"></div>
                                <p class="announcement-desc"><?php echo htmlspecialchars($announcement['description'] ?? ''); ?></p>
                                <?php if ($announcement['content']): ?>
                                    <button class="btn-read-more" onclick="showAnnouncementModal(<?php echo htmlspecialchars(json_encode($announcement)); ?>)">READ MORE ></button>
                                <?php endif; ?>
                            </div>
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
                                <div class="event-slide <?php echo $index === 0 ? 'active' : ''; ?>">
                                    <div class="event-image">
                                        <img src="<?php echo getImageUrl($event['image']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>">
                                    </div>
                                    <p class="event-caption"><?php echo htmlspecialchars($event['caption'] ?? $event['title']); ?></p>
                                </div>
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

        <!-- ========================================
             FOOTER SECTION
             Left: About/Social Links
             Right: Tech Care Platform Links
             ======================================== -->
        <footer class="footer-section" id="contact">
            
            <!-- Footer Left: About & Social -->
            <div class="footer-about">
                <div class="footer-brand">
                    <div class="footer-logo-icon">🐾</div>
                    <div class="footer-logo-text">PROWLWAY</div>
                </div>
                
                <p class="footer-description">
                    <?php
                    if (!empty($footerAbout['content'])) {
                        echo nl2br(htmlspecialchars($footerAbout['content']));
                    } else {
                        echo 'The official archival platform for the Institute of Computing and Digital Innovation Student Government.';
                    }
                    ?>
                </p>
                
                <!-- Social Media Icons -->
                <div class="social-links">
                    <a href="<?php echo htmlspecialchars($facebookUrl); ?>" class="social-icon" aria-label="Facebook">
                        <svg viewBox="0 0 256 256" aria-hidden="true" role="img">
                            <g transform="scale(5.12,5.12)">
                                <path fill="currentColor" d="M25,3c-12.15,0 -22,9.85 -22,22c0,11.03 8.125,20.137 18.712,21.728v-15.897h-5.443v-5.783h5.443v-3.848c0,-6.371 3.104,-9.168 8.399,-9.168c2.536,0 3.877,0.188 4.512,0.274v5.048h-3.612c-2.248,0 -3.033,2.131 -3.033,4.533v3.161h6.588l-0.894,5.783h-5.694v15.944c10.738,-1.457 19.022,-10.638 19.022,-21.775c0,-12.15 -9.85,-22 -22,-22z" />
                            </g>
                        </svg>
                    </a>
                    <a href="<?php echo htmlspecialchars($instagramUrl); ?>" class="social-icon" aria-label="Instagram">
                        <svg viewBox="0 0 256 256" aria-hidden="true" role="img">
                            <g transform="scale(8.53333,8.53333)">
                                <path fill="currentColor" d="M9.99805,3c-3.859,0 -6.99805,3.14195 -6.99805,7.00195v10c0,3.859 3.14195,6.99805 7.00195,6.99805h10c3.859,0 6.99805,-3.14195 6.99805,-7.00195v-10c0,-3.859 -3.14195,-6.99805 -7.00195,-6.99805zM22,7c0.552,0 1,0.448 1,1c0,0.552 -0.448,1 -1,1c-0.552,0 -1,-0.448 -1,-1c0,-0.552 0.448,-1 1,-1zM15,9c3.309,0 6,2.691 6,6c0,3.309 -2.691,6 -6,6c-3.309,0 -6,-2.691 -6,-6c0,-3.309 2.691,-6 6,-6zM15,11c-2.20914,0 -4,1.79086 -4,4c0,2.20914 1.79086,4 4,4c2.20914,0 4,-1.79086 4,-4c0,-2.20914 -1.79086,-4 -4,-4z" />
                            </g>
                        </svg>
                    </a>
                    <a href="<?php echo htmlspecialchars($tiktokUrl); ?>" class="social-icon" aria-label="TikTok">
                        <svg viewBox="0 0 256 256" aria-hidden="true" role="img">
                            <g transform="scale(5.12,5.12)">
                                <path fill="currentColor" d="M41,4h-32c-2.757,0 -5,2.243 -5,5v32c0,2.757 2.243,5 5,5h32c2.757,0 5,-2.243 5,-5v-32c0,-2.757 -2.243,-5 -5,-5zM37.006,22.323c-0.227,0.021 -0.457,0.035 -0.69,0.035c-2.623,0 -4.928,-1.349 -6.269,-3.388c0,5.349 0,11.435 0,11.537c0,4.709 -3.818,8.527 -8.527,8.527c-4.709,0 -8.527,-3.818 -8.527,-8.527c0,-4.709 3.818,-8.527 8.527,-8.527c0.178,0 0.352,0.016 0.527,0.027v4.202c-0.175,-0.021 -0.347,-0.053 -0.527,-0.053c-2.404,0 -4.352,1.948 -4.352,4.352c0,2.404 1.948,4.352 4.352,4.352c2.404,0 4.527,-1.894 4.527,-4.298c0,-0.095 0.042,-19.594 0.042,-19.594h4.016c0.378,3.591 3.277,6.425 6.901,6.685z" />
                            </g>
                        </svg>
                    </a>
                    <a href="<?php echo htmlspecialchars($twitterUrl); ?>" class="social-icon" aria-label="X">
                        <svg viewBox="0 0 256 256" aria-hidden="true" role="img">
                            <g transform="scale(8.53333,8.53333)">
                                <path fill="currentColor" d="M26.37,26l-8.795,-12.822l0.015,0.012l7.93,-9.19h-2.65l-6.46,7.48l-5.13,-7.48h-6.95l8.211,11.971l-0.001,-0.001l-8.66,10.03h2.65l7.182,-8.322l5.708,8.322zM10.23,6l12.34,18h-2.1l-12.35,-18z" />
                            </g>
                        </svg>
                    </a>
                </div>

                <div class="footer-divider"></div>
                <p class="footer-copy"><?php echo htmlspecialchars($footerCopy); ?></p>
            </div>
            
            <!-- Footer Right: Tech Care Platform -->
            <aside class="footer-techcare">
                <h3 class="techcare-title">TECH CARE<br>PLATFORM</h3>
                <ul class="techcare-links">
                    <?php foreach ($techcareLinks as $item): 
                        $id    = isset($item['id']) ? $item['id'] : '';
                        $label = isset($item['label']) ? $item['label'] : $id;
                        if ($label === '') continue;
                    ?>
                        <li>
                            <a href="<?php echo $id !== '' ? '#'.htmlspecialchars($id) : '#'; ?>">
                                <?php echo htmlspecialchars($label); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </aside>
            
        </footer>
    </main>
 
</div>

<?php include '../includes/footer.php'; ?>

