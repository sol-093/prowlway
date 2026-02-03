<?php
/**
 * Shared footer section (About, Social, Copyright). No Tech Care in footer.
 * Footer is fully static (no DB calls). Edit $footerAboutText to update the About blurb.
 */
if (!defined('ASSETS_URL')) {
    require_once __DIR__ . '/config.php';
}

// Static footer links (avoid settings-driven footer)
$facebookUrl  = 'https://www.facebook.com/profile.php?id=61569058340306';
$instagramUrl = 'https://www.instagram.com/imacssc/';
$tiktokUrl    = 'https://www.tiktok.com/@imacssc';
$twitterUrl   = 'https://twitter.com/imacssc';
$footerCopy   = 'Copyright © ' . date('Y') . '. PROWLWAY · The ICDISG Archival Website';

$footerAboutText = "The Institute of Computing and Digital Innovation (ICDI) is a technology-focused academic unit of KLD, preparing future-ready innovators through computing and digital education.";
?>
<footer class="footer-section" id="contact">
    <div class="footer-section-content">
        <div class="footer-about">
            <div class="footer-brand">
                <img src="<?php echo ASSETS_URL; ?>/IMG/ICONS/logo.png" alt="PROWLWAY Logo" class="footer-logo-icon">
                <div class="footer-logo-text">PROWLWAY</div>
            </div>
            <p class="footer-description"><?php echo htmlspecialchars($footerAboutText); ?></p>
            <div class="social-links">
                <a href="<?php echo htmlspecialchars($facebookUrl); ?>" class="social-icon" aria-label="Facebook">
                    <svg viewBox="0 0 256 256" aria-hidden="true" role="img"><g transform="scale(5.12,5.12)"><path fill="currentColor" d="M25,3c-12.15,0 -22,9.85 -22,22c0,11.03 8.125,20.137 18.712,21.728v-15.897h-5.443v-5.783h5.443v-3.848c0,-6.371 3.104,-9.168 8.399,-9.168c2.536,0 3.877,0.188 4.512,0.274v5.048h-3.612c-2.248,0 -3.033,2.131 -3.033,4.533v3.161h6.588l-0.894,5.783h-5.694v15.944c10.738,-1.457 19.022,-10.638 19.022,-21.775c0,-12.15 -9.85,-22 -22,-22z"/></g></svg>
                </a>
                <a href="<?php echo htmlspecialchars($instagramUrl); ?>" class="social-icon" aria-label="Instagram">
                    <svg viewBox="0 0 256 256" aria-hidden="true" role="img"><g transform="scale(8.53333,8.53333)"><path fill="currentColor" d="M9.99805,3c-3.859,0 -6.99805,3.14195 -6.99805,7.00195v10c0,3.859 3.14195,6.99805 7.00195,6.99805h10c3.859,0 6.99805,-3.14195 6.99805,-7.00195v-10c0,-3.859 -3.14195,-6.99805 -7.00195,-6.99805zM22,7c0.552,0 1,0.448 1,1c0,0.552 -0.448,1 -1,1c-0.552,0 -1,-0.448 -1,-1c0,-0.552 0.448,-1 1,-1zM15,9c3.309,0 6,2.691 6,6c0,3.309 -2.691,6 -6,6c-3.309,0 -6,-2.691 -6,-6c0,-3.309 2.691,-6 6,-6zM15,11c-2.20914,0 -4,1.79086 -4,4c0,2.20914 1.79086,4 4,4c2.20914,0 4,-1.79086 4,-4c0,-2.20914 -1.79086,-4 -4,-4z"/></g></svg>
                </a>
                <a href="<?php echo htmlspecialchars($tiktokUrl); ?>" class="social-icon" aria-label="TikTok">
                    <svg viewBox="0 0 256 256" aria-hidden="true" role="img"><g transform="scale(5.12,5.12)"><path fill="currentColor" d="M41,4h-32c-2.757,0 -5,2.243 -5,5v32c0,2.757 2.243,5 5,5h32c2.757,0 5,-2.243 5,-5v-32c0,-2.757 -2.243,-5 -5,-5zM37.006,22.323c-0.227,0.021 -0.457,0.035 -0.69,0.035c-2.623,0 -4.928,-1.349 -6.269,-3.388c0,5.349 0,11.435 0,11.537c0,4.709 -3.818,8.527 -8.527,8.527c-4.709,0 -8.527,-3.818 -8.527,-8.527c0,-4.709 3.818,-8.527 8.527,-8.527c0.178,0 0.352,0.016 0.527,0.027v4.202c-0.175,-0.021 -0.347,-0.053 -0.527,-0.053c-2.404,0 -4.352,1.948 -4.352,4.352c0,2.404 1.948,4.352 4.352,4.352c2.404,0 4.527,-1.894 4.527,-4.298c0,-0.095 0.042,-19.594 0.042,-19.594h4.016c0.378,3.591 3.277,6.425 6.901,6.685z"/></g></svg>
                </a>
                <a href="<?php echo htmlspecialchars($twitterUrl); ?>" class="social-icon" aria-label="X">
                    <svg viewBox="0 0 256 256" aria-hidden="true" role="img"><g transform="scale(8.53333,8.53333)"><path fill="currentColor" d="M26.37,26l-8.795,-12.822l0.015,0.012l7.93,-9.19h-2.65l-6.46,7.48l-5.13,-7.48h-6.95l8.211,11.971l-0.001,-0.001l-8.66,10.03h2.65l7.182,-8.322l5.708,8.322zM10.23,6l12.34,18h-2.1l-12.35,-18z"/></g></svg>
                </a>
            </div>
        </div>
    </div>
    <p class="footer-copy"><?php echo htmlspecialchars($footerCopy); ?></p>
</footer>
