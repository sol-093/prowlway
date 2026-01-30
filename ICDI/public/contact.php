<?php
require_once '../includes/config.php';
require_once '../includes/database.php';

$pageTitle = 'Get in Touch - PROWLWAY';
$bodyClass = 'contact-page';

// Fetch site settings for contact email
$settingsRows = dbFetchAll("SELECT setting_key, setting_value, setting_type FROM site_settings");
$settings = [];
foreach ($settingsRows as $row) {
    $key = $row['setting_key'];
    $value = $row['setting_value'];
    switch ($row['setting_type']) {
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
$contactEmail = $settings['contact_email'] ?? '';
$siteName = $settings['site_name'] ?? 'PROWLWAY';

$success = isset($_GET['sent']) && $_GET['sent'] === '1';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if (empty($name) || empty($email) || empty($message)) {
        $error = 'Please fill in name, email, and message.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $domain = strtolower(substr(strrchr($email, '@'), 1) ?: '');
        if ($domain !== 'kld.edu.ph') {
            $error = 'Only KLD student email addresses (@kld.edu.ph) are accepted.';
        }
    }
    if (empty($error)) {
        $inserted = dbInsert('contact_inquiries', [
            'name' => $name,
            'email' => $email,
            'subject' => $subject ?: null,
            'message' => $message,
            'status' => 'open',
        ]);
        // Redirect to avoid resubmit (even if insert failed, e.g. table not yet migrated)
        header('Location: ' . PUBLIC_URL . '/contact.php?sent=1');
        exit;
    }
}

include '../includes/header.php';
?>

<div class="main-container contact-page-container">
    <main class="contact-main">
        <section class="contact-section">
            <h1 class="contact-page-title">Get in Touch</h1>
            <p class="contact-page-intro">Have a question or feedback? Reach out to us and we’ll get back to you as soon as we can.</p>

            <div class="contact-layout">
                <div class="contact-info">
                    <h2 class="contact-info-title">Contact information</h2>
                    <?php if ($contactEmail): ?>
                        <p class="contact-email">
                            <span class="contact-label">Email</span>
                            <a href="mailto:<?php echo htmlspecialchars($contactEmail); ?>"><?php echo htmlspecialchars($contactEmail); ?></a>
                        </p>
                    <?php endif; ?>
                    <p class="contact-org-room">
                        <span class="contact-label">Org room</span>
                        CB1 2052
                    </p>
                    <p class="contact-note"><?php echo htmlspecialchars($siteName); ?> – ICDISG Archive Website</p>
                </div>

                <div class="contact-form-wrap">
                    <?php if ($success): ?>
                        <div class="contact-success" role="alert">
                            <p>Thank you for your message. We’ll get back to you soon.</p>
                        </div>
                    <?php else: ?>
                        <?php if ($error): ?>
                            <p class="contact-error" role="alert"><?php echo htmlspecialchars($error); ?></p>
                        <?php endif; ?>
                        <form method="post" action="<?php echo PUBLIC_URL; ?>/contact.php" class="contact-form">
                            <div class="contact-field">
                                <label for="contact-name">Name <span class="required">*</span></label>
                                <input type="text" id="contact-name" name="name" required maxlength="255" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" placeholder="Your name">
                            </div>
                            <div class="contact-field">
                                <label for="contact-email">Email (@kld.edu.ph only) <span class="required">*</span></label>
                                <input type="email" id="contact-email" name="email" required maxlength="255" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" placeholder="your.name@kld.edu.ph">
                            </div>
                            <div class="contact-field">
                                <label for="contact-subject">Subject</label>
                                <input type="text" id="contact-subject" name="subject" maxlength="255" value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>" placeholder="Subject (optional)">
                            </div>
                            <div class="contact-field">
                                <label for="contact-message">Message <span class="required">*</span></label>
                                <textarea id="contact-message" name="message" required rows="5" placeholder="Your message"><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                            </div>
                            <button type="submit" class="contact-submit">Send message</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>
</div>

<?php include '../includes/footer.php'; ?>
