<?php
session_start();
require_once '../includes/config.php';
$pageTitle = 'PROWLWAY Admin Panel';
$hideHeader = true;
include '../includes/header.php';

// Check if logged in
$isLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'];
?>
<script>
    // Make ADMIN_URL available to JavaScript
    window.ADMIN_URL = '<?php echo ADMIN_URL; ?>';
</script>

<div class="container">
    <div class="header">
        <div class="header-content">
            <h1>🐾 PROWLWAY Admin Panel</h1>
            <p>Manage your ICDISG Archive Website</p>
        </div>
        <div class="header-actions" id="headerActions">
            <a href="<?php echo PUBLIC_URL; ?>/home.php" class="btn-goto">Home</a>
            <a href="<?php echo PUBLIC_URL; ?>/events.php" class="btn-goto">Events</a>
            <a href="<?php echo PUBLIC_URL; ?>/documents.php" class="btn-goto" id="documentsHeaderBtn">Documents</a>
        </div>
    </div>

    <div class="content">
        <!-- Login Section -->
        <div class="login-section <?php echo (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) ? 'active' : ''; ?>" id="loginSection">
            <h2 style="margin-bottom: 20px;">Admin Login</h2>
            <div id="loginAlert">
                <?php if (isset($_SESSION['login_error'])): ?>
                    <div class="alert alert-error"><?php echo $_SESSION['login_error']; unset($_SESSION['login_error']); ?></div>
                <?php endif; ?>
            </div>
            <form id="loginForm" method="POST" action="<?php echo ADMIN_URL; ?>/login.php">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="admin@icdisg.ph" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
        </div>

        <!-- Dashboard Section -->
        <div class="dashboard-section <?php echo $isLoggedIn ? 'active' : ''; ?>" id="dashboardSection">
            <div class="user-info">
                <strong>Logged in as:</strong> <span id="userEmail"><?php echo isset($_SESSION['admin_email']) ? $_SESSION['admin_email'] : ''; ?></span>
                <a href="<?php echo ADMIN_URL; ?>/logout.php" class="btn btn-logout">Logout</a>
                <div style="clear: both;"></div>
            </div>

            <div id="dashboardAlert"></div>

            <div class="tabs">
                <button class="tab active" onclick="switchTab('announcements')">Announcements</button>
                <button class="tab" onclick="switchTab('events')">Events</button>
                <button class="tab" id="documentsTabBtn" onclick="switchTab('documents')">Documents</button>
            </div>
            
            <div style="margin: 1rem 0; padding: 1rem; background: var(--color-card-dark); border-radius: 8px;">
                <h3 style="margin-bottom: 0.5rem;">Additional Management</h3>
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <a href="<?php echo ADMIN_URL; ?>/batches.php" class="btn btn-toggle btn-small">Manage Batches</a>
                    <a href="<?php echo ADMIN_URL; ?>/institute.php" class="btn btn-toggle btn-small">Manage Institute</a>
                    <a href="<?php echo ADMIN_URL; ?>/organizations.php" class="btn btn-toggle btn-small">Manage Organizations</a>
                </div>
            </div>

            <!-- Announcements Tab -->
            <div class="tab-content active" id="announcements-tab">
                <div class="section-title">
                    <h2>Announcements</h2>
                    <button class="btn btn-toggle btn-small" onclick="toggleForm('announcement-form')">
                        ➕ Create New
                    </button>
                </div>

                <!-- Create/Edit Form -->
                <div id="announcement-form" class="form-section hidden">
                    <h3 id="announcement-form-title">Create Announcement</h3>
                    <form id="announcementForm" method="POST" action="<?php echo ADMIN_URL; ?>/announcements.php" enctype="multipart/form-data">
                        <input type="hidden" id="ann-id" name="id">
                        <div class="grid-2">
                            <div class="form-group">
                                <label for="ann-title">Title *</label>
                                <input type="text" id="ann-title" name="title" required>
                            </div>
                            <div class="form-group">
                                <label for="ann-category">Category *</label>
                                <select id="ann-category" name="category" required>
                                    <option value="general">General</option>
                                    <option value="academic">Academic</option>
                                    <option value="event">Event</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="ann-description">Short Description *</label>
                            <textarea id="ann-description" name="description" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="ann-content">Full Content *</label>
                            <textarea id="ann-content" name="content" required></textarea>
                        </div>
                        <div class="grid-2">
                            <div class="form-group">
                                <label for="ann-image">Image (optional)</label>
                                <div id="ann-image-preview" style="margin-bottom: 0.5rem; display: none;">
                                    <img id="ann-image-preview-img" src="" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px;">
                                    <input type="hidden" id="ann-old-image" name="old_image">
                                </div>
                                <input type="file" id="ann-image" name="image" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                                <small style="color: var(--color-text-muted); display: block; margin-top: 0.5rem;">Max size: 5MB. Formats: JPEG, PNG, GIF, WebP</small>
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" id="ann-pinned" name="pinned"> Pin this announcement
                                </label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary" id="ann-submit-btn">Create Announcement</button>
                        <button type="button" class="btn btn-toggle btn-small" onclick="cancelAnnouncementEdit()">Cancel</button>
                    </form>
                </div>

                <!-- Announcements List -->
                <div id="announcements-list">
                    <p>Loading announcements...</p>
                </div>
            </div>

            <!-- Events Tab -->
            <div class="tab-content" id="events-tab">
                <div class="section-title">
                    <h2>Events</h2>
                    <button class="btn btn-toggle btn-small" onclick="toggleForm('event-form')">
                        ➕ Create New
                    </button>
                </div>

                <!-- Create/Edit Form -->
                <div id="event-form" class="form-section hidden">
                    <h3 id="event-form-title">Create Event</h3>
                    <form id="eventForm" method="POST" action="<?php echo ADMIN_URL; ?>/events_handler.php" enctype="multipart/form-data">
                        <input type="hidden" id="evt-id" name="id">
                        <div class="grid-2">
                            <div class="form-group">
                                <label for="evt-title">Title *</label>
                                <input type="text" id="evt-title" name="title" required>
                            </div>
                            <div class="form-group">
                                <label for="evt-category">Category *</label>
                                <select id="evt-category" name="category" required>
                                    <option value="workshop">Workshop</option>
                                    <option value="seminar">Seminar</option>
                                    <option value="service">Service</option>
                                    <option value="celebration">Celebration</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="evt-caption">Caption *</label>
                            <input type="text" id="evt-caption" name="caption" required>
                        </div>
                        <div class="form-group">
                            <label for="evt-description">Description *</label>
                            <textarea id="evt-description" name="description" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="evt-summary">Event Summary</label>
                            <textarea id="evt-summary" name="summary" placeholder="Short highlight that appears in the event details modal"></textarea>
                            <small class="form-helper">Optional. Keep it to 1-2 concise sentences.</small>
                        </div>
                        <div class="grid-2">
                            <div class="form-group">
                                <label for="evt-image">Image *</label>
                                <div id="evt-image-preview" style="margin-bottom: 0.5rem; display: none;">
                                    <img id="evt-image-preview-img" src="" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px;">
                                    <input type="hidden" id="evt-old-image" name="old_image">
                                </div>
                                <input type="file" id="evt-image" name="image" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                                <small style="color: var(--color-text-muted); display: block; margin-top: 0.5rem;">Max size: 5MB. Formats: JPEG, PNG, GIF, WebP</small>
                            </div>
                            <div class="form-group">
                                <label for="evt-location">Location</label>
                                <input type="text" id="evt-location" name="location">
                            </div>
                        </div>
                        <div class="grid-2">
                            <div class="form-group">
                                <label for="evt-date">Event Date *</label>
                                <input type="date" id="evt-date" name="date" required>
                            </div>
                            <div class="form-group">
                                <label for="evt-order">Display Order</label>
                                <input type="number" id="evt-order" name="order" value="0">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="evt-gallery">Gallery Images</label>
                            <input type="file" id="evt-gallery" name="gallery[]" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" multiple>
                            <small class="form-helper" style="display: block; margin-top: 0.5rem;">You can select multiple images. Max size per image: 5MB.</small>
                            <div id="evt-gallery-preview" style="margin-top: 1rem; display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 0.5rem;"></div>
                            <input type="hidden" id="evt-old-gallery" name="old_gallery">
                        </div>
                        <button type="submit" class="btn btn-primary" id="evt-submit-btn">Create Event</button>
                        <button type="button" class="btn btn-toggle btn-small" onclick="cancelEventEdit()">Cancel</button>
                    </form>
                </div>

                <!-- Events List -->
                <div id="events-list">
                    <p>Loading events...</p>
                </div>
            </div>

            <!-- Documents Tab -->
            <div class="tab-content" id="documents-tab">
                <div class="section-title">
                    <h2>Documents</h2>
                    <button class="btn btn-toggle btn-small" id="createDocBtn" onclick="toggleForm('document-form')">
                        ➕ Create New
                    </button>
                </div>

                <!-- Create/Edit Form -->
                <div id="document-form" class="form-section hidden">
                    <h3 id="document-form-title">Create Document Entry</h3>
                    <form id="documentForm" method="POST" action="<?php echo ADMIN_URL; ?>/documents.php" enctype="multipart/form-data">
                        <input type="hidden" id="doc-id" name="id">
                        <div class="grid-2">
                            <div class="form-group">
                                <label for="doc-title">Title *</label>
                                <input type="text" id="doc-title" name="title" required>
                            </div>
                            <div class="form-group">
                                <label for="doc-category">Category *</label>
                                <select id="doc-category" name="category" required>
                                    <option value="01">01 - OFFICES REPORT</option>
                                    <option value="02">02 - EXECUTIVE ORD</option>
                                    <option value="03">03 - ORDINANCE</option>
                                    <option value="04">04 - RESOLUTION</option>
                                    <option value="05">05 - OTHER</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="doc-description">Description</label>
                            <textarea id="doc-description" name="description"></textarea>
                        </div>
                        
                        <!-- File Upload Section -->
                        <div class="form-group">
                            <label for="doc-file">Upload PDF File (Max 50MB) *</label>
                            <input type="file" id="doc-file" name="file" accept=".pdf" style="margin-bottom: 0.5rem;">
                            <small style="color: #9aa1a6; display: block; margin-top: 0.5rem;">
                                Choose a PDF file to upload (Maximum size: 50MB)
                            </small>
                            <div id="upload-progress" style="display: none; margin-top: 0.5rem;">
                                <div style="background: #23272a; border-radius: 4px; overflow: hidden; height: 20px;">
                                    <div id="progress-bar" style="background: #ffffff; height: 100%; width: 0%; transition: width 0.3s;"></div>
                                </div>
                                <small id="progress-text" style="color: #e6e9eb; margin-top: 0.3rem; display: block;"></small>
                            </div>
                        </div>


                        <button type="submit" class="btn btn-primary" id="doc-submit-btn">Create Document</button>
                        <button type="button" class="btn btn-toggle btn-small" onclick="cancelDocumentEdit()">Cancel</button>
                    </form>
                </div>

                <!-- Documents List -->
                <div id="documents-list">
                    <p>Loading documents...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

