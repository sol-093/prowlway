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
                            <span class="meta-item">KLD-ICDI Student Government</span>
                            <span class="meta-item">imacsac@kidduph</span>
                        </div>
                    </div>
                    
                    <!-- Right Side: ICDISG Logo -->
                    <div class="banner-right">
                        <div class="icdisg-logo-circle">
                            <img src="<?php echo ASSETS_URL; ?>/images/ICDI.png" alt="ICDISG Logo" class="icdisg-logo-img">
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
                    <!-- Logo/Badge Grid (2x2) -->
                    <div class="logo-item">
                        <div class="logo-badge">
                            <img src="<?php echo ASSETS_URL; ?>/images/ICDI.png" alt="ICDISG logo" class="logo-image">
                        </div>
                    </div>
                    <div class="logo-item">
                        <div class="logo-badge">
                            <img src="<?php echo ASSETS_URL; ?>/images/ISSOC.png" alt="ISSOC logo" class="logo-image">
                        </div>
                    </div>
                    <div class="logo-item">
                        <div class="logo-badge">
                            <img src="<?php echo ASSETS_URL; ?>/images/BITCUB.png" alt="BITCUB logo" class="logo-image">
                        </div>
                    </div>
                    <div class="logo-item">
                        <div class="logo-badge">
                            <img src="<?php echo ASSETS_URL; ?>/images/GDSC.png" alt="GDSC logo" class="logo-image">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- DOCUMENTS CARD -->
            <div class="card documents-card">
                <h3 class="card-title">DOCUMENTS</h3>
                <div class="documents-grid">
                    <!-- Document Folder Items (2x2 Grid) -->
                    <a href="<?php echo PUBLIC_URL; ?>/documents.php?category=01" class="folder-item">
                        <div class="folder-title">OFFICES REPORT</div>
                        <div class="folder-number">01</div>
                        <div class="folder-docs"><?php echo ($docCounts['01'] ?? 0); ?> Doc<?php echo ($docCounts['01'] ?? 0) !== 1 ? 's' : ''; ?></div>
                    </a>
                    <a href="<?php echo PUBLIC_URL; ?>/documents.php?category=02" class="folder-item">
                        <div class="folder-title">EXECUTIVE ORD</div>
                        <div class="folder-number">02</div>
                        <div class="folder-docs"><?php echo ($docCounts['02'] ?? 0); ?> Doc<?php echo ($docCounts['02'] ?? 0) !== 1 ? 's' : ''; ?></div>
                    </a>
                    <a href="<?php echo PUBLIC_URL; ?>/documents.php?category=03" class="folder-item">
                        <div class="folder-title">ORDINANCE</div>
                        <div class="folder-number">03</div>
                        <div class="folder-docs"><?php echo ($docCounts['03'] ?? 0); ?> Doc<?php echo ($docCounts['03'] ?? 0) !== 1 ? 's' : ''; ?></div>
                    </a>
                    <a href="<?php echo PUBLIC_URL; ?>/documents.php?category=04" class="folder-item">
                        <div class="folder-title">RESOLUTION</div>
                        <div class="folder-number">04</div>
                        <div class="folder-docs"><?php echo ($docCounts['04'] ?? 0); ?> Doc<?php echo ($docCounts['04'] ?? 0) !== 1 ? 's' : ''; ?></div>
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
                    Lorem Ipsum Dolor Sit Amet, Consectetur Adipiscing Elit, Sed Do Eiusmod Tempor Incididunt 
                    Ut Labore Et Dolore Magna Aliqua. Ut Enim Ad Minim Veniam, Quis Nostrud Exercitation 
                    Ullamco Laboris Nisi Ut Aliquip Ex Ea Commodo Consequat Duis Aute Irure Dolor In 
                    Reprehenderit In Voluptate Velit Esse Cillum Dolore Eu Fugiat Nulla Pariatur. 
                    Excepteur Sint Occaecat Cupidatat Non Proident.
                </p>
                
                <!-- Social Media Icons -->
                <div class="social-links">
                    <a href="https://www.facebook.com/profile.php?id=61569058340306" class="social-icon" aria-label="Facebook">
                        <svg viewBox="0 0 256 256" aria-hidden="true" role="img">
                            <g transform="scale(5.12,5.12)">
                                <path fill="currentColor" d="M25,3c-12.15,0 -22,9.85 -22,22c0,11.03 8.125,20.137 18.712,21.728v-15.897h-5.443v-5.783h5.443v-3.848c0,-6.371 3.104,-9.168 8.399,-9.168c2.536,0 3.877,0.188 4.512,0.274v5.048h-3.612c-2.248,0 -3.033,2.131 -3.033,4.533v3.161h6.588l-0.894,5.783h-5.694v15.944c10.738,-1.457 19.022,-10.638 19.022,-21.775c0,-12.15 -9.85,-22 -22,-22z" />
                            </g>
                        </svg>
                    </a>
                    <a href="https://www.instagram.com/imacssc/" class="social-icon" aria-label="Instagram">
                        <svg viewBox="0 0 256 256" aria-hidden="true" role="img">
                            <g transform="scale(8.53333,8.53333)">
                                <path fill="currentColor" d="M9.99805,3c-3.859,0 -6.99805,3.14195 -6.99805,7.00195v10c0,3.859 3.14195,6.99805 7.00195,6.99805h10c3.859,0 6.99805,-3.14195 6.99805,-7.00195v-10c0,-3.859 -3.14195,-6.99805 -7.00195,-6.99805zM22,7c0.552,0 1,0.448 1,1c0,0.552 -0.448,1 -1,1c-0.552,0 -1,-0.448 -1,-1c0,-0.552 0.448,-1 1,-1zM15,9c3.309,0 6,2.691 6,6c0,3.309 -2.691,6 -6,6c-3.309,0 -6,-2.691 -6,-6c0,-3.309 2.691,-6 6,-6zM15,11c-2.20914,0 -4,1.79086 -4,4c0,2.20914 1.79086,4 4,4c2.20914,0 4,-1.79086 4,-4c0,-2.20914 -1.79086,-4 -4,-4z" />
                            </g>
                        </svg>
                    </a>
                    <a href="https://www.tiktok.com/@imacssc" class="social-icon" aria-label="TikTok">
                        <svg viewBox="0 0 256 256" aria-hidden="true" role="img">
                            <g transform="scale(5.12,5.12)">
                                <path fill="currentColor" d="M41,4h-32c-2.757,0 -5,2.243 -5,5v32c0,2.757 2.243,5 5,5h32c2.757,0 5,-2.243 5,-5v-32c0,-2.757 -2.243,-5 -5,-5zM37.006,22.323c-0.227,0.021 -0.457,0.035 -0.69,0.035c-2.623,0 -4.928,-1.349 -6.269,-3.388c0,5.349 0,11.435 0,11.537c0,4.709 -3.818,8.527 -8.527,8.527c-4.709,0 -8.527,-3.818 -8.527,-8.527c0,-4.709 3.818,-8.527 8.527,-8.527c0.178,0 0.352,0.016 0.527,0.027v4.202c-0.175,-0.021 -0.347,-0.053 -0.527,-0.053c-2.404,0 -4.352,1.948 -4.352,4.352c0,2.404 1.948,4.352 4.352,4.352c2.404,0 4.527,-1.894 4.527,-4.298c0,-0.095 0.042,-19.594 0.042,-19.594h4.016c0.378,3.591 3.277,6.425 6.901,6.685z" />
                            </g>
                        </svg>
                    </a>
                    <a href="https://twitter.com/imacssc" class="social-icon" aria-label="X">
                        <svg viewBox="0 0 256 256" aria-hidden="true" role="img">
                            <g transform="scale(8.53333,8.53333)">
                                <path fill="currentColor" d="M26.37,26l-8.795,-12.822l0.015,0.012l7.93,-9.19h-2.65l-6.46,7.48l-5.13,-7.48h-6.95l8.211,11.971l-0.001,-0.001l-8.66,10.03h2.65l7.182,-8.322l5.708,8.322zM10.23,6l12.34,18h-2.1l-12.35,-18z" />
                            </g>
                        </svg>
                    </a>
                </div>

                <div class="footer-divider"></div>
                <p class="footer-copy">Copyright © 2025. PROWLWAY · The ICDISG Archival Website</p>
            </div>
            
            <!-- Footer Right: Tech Care Platform -->
            <aside class="footer-techcare">
                <h3 class="techcare-title">TECH CARE<br>PLATFORM</h3>
                <ul class="techcare-links">
                    <li><a href="#concern">Concern Form</a></li>
                    <li><a href="#printing">Diy Printing Station</a></li>
                    <li><a href="#wiring">Lab Wiring</a></li>
                    <li><a href="#outreach">Outreach</a></li>
                </ul>
            </aside>
            
        </footer>
    </main>
 
</div>

<?php include '../includes/footer.php'; ?>

