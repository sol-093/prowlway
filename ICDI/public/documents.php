<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/upload.php';

$pageTitle = 'Documents - PROWLWAY ICDISG';
$bodyClass = 'documents-page';
$selectedCategory = $_GET['category'] ?? null;

include '../includes/header.php';

// Fetch documents
$query = "SELECT * FROM documents WHERE status = 'published'";
$params = [];
if ($selectedCategory) {
    $query .= " AND category = ?";
    $params[] = $selectedCategory;
}
$query .= " ORDER BY category ASC, created_at DESC";

$documents = dbFetchAll($query, $params);

// Get document counts by category
$docCounts = [];
$allDocs = dbFetchAll("SELECT category FROM documents WHERE status = 'published'");
foreach ($allDocs as $doc) {
    $docCounts[$doc['category']] = ($docCounts[$doc['category']] ?? 0) + 1;
}

// Category names
$categoryNames = [
    '01' => 'OFFICES REPORT',
    '02' => 'EXECUTIVE ORDER',
    '03' => 'ORDINANCE',
    '04' => 'RESOLUTION',
    '05' => 'OTHER'
];
?>

<!-- Main Container -->
<div class="container">
    <div class="documents-outer-panel">
        <div class="documents-inner-panel">
    
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">DOCUMENTS</h1>
            <div class="documents-divider"></div>
        </div>

    <!-- Breadcrumb Navigation -->
    <div class="breadcrumb" id="breadcrumb">
        <div class="breadcrumb-item">
            <a href="<?php echo PUBLIC_URL; ?>/documents.php" class="breadcrumb-link">DOCUMENTS</a>
            <?php if ($selectedCategory && isset($categoryNames[$selectedCategory])): ?>
                <span class="breadcrumb-separator"> › </span>
                <span class="breadcrumb-item"><?php echo $categoryNames[$selectedCategory]; ?></span>
            <?php endif; ?>
        </div>
    </div>

        <!-- Toolbar -->
        <div class="toolbar">
            <div class="search-box">
                <span class="search-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" role="img" focusable="false">
                        <path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79L19 20.5 20.5 19zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                    </svg>
                </span>
                <input type="text" class="search-input" id="searchInput" placeholder="Search documents...">
            </div>
        </div>

        <!-- Folders Section -->
        <div id="foldersSection">
            <div class="documents-grid" id="foldersGrid">
                <?php 
                // Map category codes to icon filenames from ICONS directory
                $categoryIcons = [
                    '01' => 'OFFICE.png',
                    '02' => 'EXECUTIVE.png',
                    '03' => 'ORDINANCE.png',
                    '04' => 'RESOLUTION.png',
                    '05' => 'OTHER.png'
                ];
                foreach ($categoryNames as $catCode => $catName): 
                    $iconFile = $categoryIcons[$catCode] ?? 'OTHER.png';
                    $iconPath = ASSETS_URL . '/IMG/ICONS/' . $iconFile;
                ?>
                    <a
                        href="<?php echo PUBLIC_URL; ?>/documents.php?category=<?php echo $catCode; ?>"
                        class="folder-item <?php echo $selectedCategory === $catCode ? 'active' : ''; ?>"
                        data-skip-js-handler="true"
                    >
                        <img src="<?php echo htmlspecialchars($iconPath); ?>" alt="<?php echo htmlspecialchars($catName); ?>">
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Documents Section -->
        <div id="documentsSection">
            <h2 class="section-title"><?php echo $selectedCategory && isset($categoryNames[$selectedCategory]) ? $categoryNames[$selectedCategory] : 'Documents'; ?></h2>
            <div id="documentsContainer">
                <!-- List View -->
                <div class="documents-list" id="listView">
                    <?php if (empty($documents)): ?>
                        <div class="no-documents">
                            <div class="no-documents-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                    <line x1="12" y1="18" x2="12" y2="12"/>
                                    <line x1="9" y1="15" x2="15" y2="15"/>
                                </svg>
                            </div>
                            <h3 class="no-documents-title">No Documents Found</h3>
                            <p class="no-documents-text"><?php echo $selectedCategory ? 'There are no documents in this category yet.' : 'No documents are available at the moment.'; ?></p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($documents as $doc): 
                            $docDate = new DateTime($doc['created_at']);
                            $fileSize = !empty($doc['file_size']) ? number_format($doc['file_size'] / 1024 / 1024, 2) . ' MB' : 'N/A';
                            $fileName = !empty($doc['file_path']) ? basename($doc['file_path']) : '';
                        ?>
                            <div class="document-item">
                                <div class="doc-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" role="img" focusable="false">
                                        <path d="M6 2h7l5 5v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2zm7 1.5V9h4.5L13 3.5z"/>
                                    </svg>
                                </div>
                                <div class="doc-info">
                                    <div class="doc-title"><?php echo htmlspecialchars($doc['title']); ?></div>
                                    <div class="doc-description">
                                        <?php if ($fileName): ?>
                                            <?php echo htmlspecialchars($fileName); ?> • <?php echo $docDate->format('m/d/Y'); ?>
                                        <?php else: ?>
                                            <?php echo $docDate->format('m/d/Y'); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="doc-size-display"><?php echo $fileSize; ?></div>
                                <div class="doc-actions">
                                    <?php if ($doc['file_path']): ?>
                                        <?php $docUrl = getDocumentUrl($doc['file_path']); ?>
                                        <button 
                                            type="button" 
                                            class="doc-preview-btn" 
                                            data-preview-url="<?php echo htmlspecialchars($docUrl); ?>"
                                        >
                                            Preview
                                        </button>
                                        <a href="<?php echo $docUrl; ?>" target="_blank" class="doc-download-btn">Download</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <!-- Grid View -->
                <div class="documents-grid hidden" id="gridView">
                    <?php if (!empty($documents)): ?>
                        <?php foreach ($documents as $doc): ?>
                            <div class="document-card">
                                <div class="doc-card-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" role="img" focusable="false">
                                        <path d="M6 2h7l5 5v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2zm7 1.5V9h4.5L13 3.5z"/>
                                    </svg>
                                </div>
                                <div class="doc-card-title"><?php echo htmlspecialchars($doc['title']); ?></div>
                                <div class="doc-card-category"><?php echo $categoryNames[$doc['category']]; ?></div>
                                <?php if ($doc['file_path']): ?>
                                    <?php $docUrl = getDocumentUrl($doc['file_path']); ?>
                                    <div class="doc-card-actions">
                                        <button 
                                            type="button" 
                                            class="doc-card-preview-btn" 
                                            data-preview-url="<?php echo htmlspecialchars($docUrl); ?>"
                                        >
                                            Preview
                                        </button>
                                        <a href="<?php echo $docUrl; ?>" target="_blank" class="doc-card-download">Download</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        </div>
    </div>
</div>

<!-- Document Preview Modal -->
<div id="docPreviewModal" class="doc-preview-modal hidden" aria-hidden="true">
    <div class="doc-preview-backdrop"></div>
    <div class="doc-preview-dialog" role="dialog" aria-modal="true" aria-label="Document preview">
        <button type="button" class="doc-preview-close" id="docPreviewClose" aria-label="Close preview">&times;</button>
        <div class="doc-preview-frame-wrapper">
            <iframe id="docPreviewFrame" src="" frameborder="0"></iframe>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('docPreviewModal');
    const frame = document.getElementById('docPreviewFrame');
    const closeBtn = document.getElementById('docPreviewClose');
    const backdrop = modal ? modal.querySelector('.doc-preview-backdrop') : null;

    if (!modal || !frame || !closeBtn || !backdrop) return;

    function openPreview(url) {
        if (!url) return;
        frame.src = url;
        modal.classList.remove('hidden');
        document.body.classList.add('no-scroll');
        modal.setAttribute('aria-hidden', 'false');
    }

    function closePreview() {
        frame.src = '';
        modal.classList.add('hidden');
        document.body.classList.remove('no-scroll');
        modal.setAttribute('aria-hidden', 'true');
    }

    document.querySelectorAll('.doc-preview-btn, .doc-card-preview-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const url = this.getAttribute('data-preview-url');
            openPreview(url);
        });
    });

    closeBtn.addEventListener('click', closePreview);
    backdrop.addEventListener('click', closePreview);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closePreview();
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>

