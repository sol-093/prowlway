<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/upload.php';

$pageTitle = 'Documents - PROWLWAY ICDISG';
$bodyClass = 'documents-page';
$selectedCategory = $_GET['category'] ?? null;
$selectedSubcategory = $_GET['subcategory'] ?? null;

include '../includes/header.php';

// Fetch documents with hierarchical filtering
$query = "SELECT * FROM documents WHERE status = 'published'";
$params = [];
if ($selectedCategory) {
    $query .= " AND category = ?";
    $params[] = $selectedCategory;
    
    // If subcategory is selected, filter by it
    if ($selectedSubcategory) {
        $query .= " AND subcategory = ?";
        $params[] = $selectedSubcategory;
    }
}
$query .= " ORDER BY display_order ASC, category ASC, subcategory ASC, created_at DESC";

$documents = dbFetchAll($query, $params);

// Get document counts by category
$docCounts = [];
$allDocs = dbFetchAll("SELECT category FROM documents WHERE status = 'published'");
foreach ($allDocs as $doc) {
    $docCounts[$doc['category']] = ($docCounts[$doc['category']] ?? 0) + 1;
}

// Get subcategories for selected category from subcategories table
$subcategories = [];
$subcategoryCounts = [];
if ($selectedCategory) {
    try {
        // Fetch active subcategories from subcategories table
        $subcategoryRows = dbFetchAll(
            "SELECT id, name, display_order FROM subcategories WHERE category = ? AND status = 'active' ORDER BY display_order ASC, name ASC",
            [$selectedCategory]
        );
        
        foreach ($subcategoryRows as $subRow) {
            $subcat = $subRow['name'];
            $subcategories[] = $subcat;
            // Count documents per subcategory
            $subCount = dbFetchOne(
                "SELECT COUNT(*) as count FROM documents WHERE status = 'published' AND category = ? AND subcategory = ?",
                [$selectedCategory, $subcat]
            );
            $subcategoryCounts[$subcat] = $subCount['count'] ?? 0;
        }
    } catch (Exception $e) {
        // Fallback to old method if subcategories table doesn't exist yet
        error_log("Error fetching subcategories: " . $e->getMessage());
        $subcategoryDocs = dbFetchAll(
            "SELECT DISTINCT subcategory FROM documents WHERE status = 'published' AND category = ? AND subcategory IS NOT NULL AND subcategory != ''",
            [$selectedCategory]
        );
        foreach ($subcategoryDocs as $subDoc) {
            $subcat = $subDoc['subcategory'];
            $subcategories[] = $subcat;
            $subCount = dbFetchOne(
                "SELECT COUNT(*) as count FROM documents WHERE status = 'published' AND category = ? AND subcategory = ?",
                [$selectedCategory, $subcat]
            );
            $subcategoryCounts[$subcat] = $subCount['count'] ?? 0;
        }
    }
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
<div class="container px-4 md:px-6 lg:px-8">
    <div class="documents-outer-panel">
        <div class="documents-inner-panel px-4 md:px-8 lg:px-12 py-6 md:py-8 lg:py-10">
    
        <!-- Page Header -->
        <div class="page-header mb-6 md:mb-8">
            <h1 class="page-title text-2xl md:text-3xl lg:text-4xl font-bold mb-3 md:mb-4">DOCUMENTS</h1>
            <div class="documents-divider"></div>
        </div>

    <!-- Breadcrumb Navigation with Back Arrows (Folder Path Style) -->
    <div class="breadcrumb-path mb-4" id="breadcrumb">
        <div class="breadcrumb-container">
            <!-- DOCUMENTS (always shown) -->
            <a href="<?php echo PUBLIC_URL; ?>/documents.php" class="breadcrumb-link hover-zoom">
                DOCUMENTS
            </a>
            
            <?php if ($selectedCategory && isset($categoryNames[$selectedCategory])): ?>
                <!-- Back Arrow -->
                <span class="breadcrumb-arrow">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 18l6-6-6-6"/>
                    </svg>
                </span>
                
                <!-- Category -->
                <a href="<?php echo PUBLIC_URL; ?>/documents.php?category=<?php echo $selectedCategory; ?>" class="breadcrumb-link hover-zoom">
                    <?php echo $categoryNames[$selectedCategory]; ?>
                </a>
                
                <?php if ($selectedSubcategory): ?>
                    <!-- Back Arrow -->
                    <span class="breadcrumb-arrow">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 18l6-6-6-6"/>
                        </svg>
                    </span>
                    
                    <!-- Subcategory (current, not clickable) -->
                    <span class="breadcrumb-current">
                        <?php echo htmlspecialchars($selectedSubcategory); ?>
                    </span>
                <?php elseif (!empty($subcategories)): ?>
                    <!-- Back Arrow -->
                    <span class="breadcrumb-arrow">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 18l6-6-6-6"/>
                        </svg>
                    </span>
                    
                    <!-- SUBCATEGORY (current level) -->
                    <span class="breadcrumb-current">SUBCATEGORY</span>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

        <!-- Category Folders Section (shown when no category selected) -->
        <?php if (!$selectedCategory): ?>
        <div id="foldersSection" class="mb-6 md:mb-8">
            <div class="documents-grid grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 md:gap-6" id="foldersGrid">
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
                        class="folder-item hover-zoom"
                        data-skip-js-handler="true"
                    >
                        <img src="<?php echo htmlspecialchars($iconPath); ?>" alt="<?php echo htmlspecialchars($catName); ?>">
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Subcategories Section (shown when category is selected and no subcategory selected) -->
        <?php if ($selectedCategory && !$selectedSubcategory && !empty($subcategories)): ?>
        <div id="subcategoriesSection" class="mb-6 md:mb-8">
            <div class="documents-grid grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 md:gap-6">
                <?php 
                $folderImagePath = ASSETS_URL . '/IMG/folder.png';
                $folderIndex = 1; // Start numbering from 1
                foreach ($subcategories as $subcat): 
                    $fileCount = $subcategoryCounts[$subcat] ?? 0;
                    $subcatDisplay = htmlspecialchars($subcat);
                    $folderNumber = str_pad($folderIndex, 2, '0', STR_PAD_LEFT); // Format as 01, 02, 03, etc.
                ?>
                    <!-- Folder Container with Label Below -->
                    <div style="display: flex; flex-direction: column; align-items: center;">
                        <!-- Group 49 - Main Container (Folder Link) -->
                        <a
                            href="<?php echo PUBLIC_URL; ?>/documents.php?category=<?php echo $selectedCategory; ?>&subcategory=<?php echo urlencode($subcat); ?>"
                            class="folder-item hover-zoom <?php echo $selectedSubcategory === $subcat ? 'active' : ''; ?>"
                            style="position: relative; width: 202px; height: 141px; display: block; transition: transform 0.3s ease, box-shadow 0.3s ease; cursor: pointer; text-decoration: none; transform-origin: center center;"
                            title="<?php echo htmlspecialchars($subcat); ?> - <?php echo $fileCount; ?> file(s)"
                        >
                            <!-- Group 46 - Folder Image Container -->
                            <div style="position: absolute; width: 190px; height: 141px; left: 6px; top: 0;">
                                <!-- Vector - Folder Image (The actual folder.png) -->
                                <img 
                                    src="<?php echo htmlspecialchars($folderImagePath); ?>" 
                                    alt="<?php echo htmlspecialchars($subcat); ?> folder" 
                                    style="width: 190px; height: 141px; object-fit: contain; display: block; position: absolute; left: 0; top: 0;"
                                >
                            </div>
                            
                            <!-- Folder Number Overlay (01, 02, 03, etc.) - Centered horizontally, positioned 10% lower -->
                            <div style="position: absolute; width: 190px; height: 141px; left: 6px; top: 0; display: flex; align-items: flex-end; justify-content: center; padding-bottom: 50px; font-family: 'Sora', sans-serif; font-style: normal; font-weight: 700; font-size: 20px; line-height: 25px; text-align: center; text-transform: capitalize; color: #FFFFFF; pointer-events: none; text-shadow: 0 1px 2px rgba(0,0,0,0.5);">
                                <?php echo $folderNumber; ?>
                            </div>
                        </a>
                        <!-- Subcategory Name Label Below Folder -->
                        <div style="margin-top: 0.5rem; text-align: center; font-family: 'Sora', sans-serif; font-weight: 600; font-size: 0.875rem; color: #1f2937;">
                            <a href="<?php echo PUBLIC_URL; ?>/documents.php?category=<?php echo $selectedCategory; ?>&subcategory=<?php echo urlencode($subcat); ?>" class="hover-zoom" style="color: inherit; text-decoration: none; display: inline-block; transition: transform 0.3s ease, color 0.3s ease;">
                                <?php echo $subcatDisplay; ?>
                            </a>
                        </div>
                    </div>
                    <?php $folderIndex++; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Documents Section -->
        <?php 
        $showDocuments = false;
        if ($selectedSubcategory) {
            $showDocuments = true;
        } elseif (!$selectedCategory) {
            $showDocuments = true;
        } elseif ($selectedCategory && (empty($subcategories) || !isset($subcategories))) {
            $showDocuments = true;
        }
        if ($showDocuments): 
        ?>
        <div id="documentsSection">
            <h2 class="section-title">
                <?php 
                if ($selectedSubcategory && $selectedCategory) {
                    // Show the clicked subcategory name (the folder that was clicked)
                    echo htmlspecialchars($selectedSubcategory);
                } elseif ($selectedCategory && isset($categoryNames[$selectedCategory])) {
                    echo $categoryNames[$selectedCategory];
                } else {
                    echo 'Documents';
                }
                ?>
            </h2>
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
                            <div class="document-item hover-zoom">
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
                                <div class="doc-meta-row">
                                    <div class="doc-size-display"><?php echo $fileSize; ?></div>
                                    <div class="doc-actions">
                                        <?php if ($doc['file_path']): ?>
                                            <?php $docUrl = getDocumentUrl($doc['file_path']); ?>
                                            <button 
                                                type="button" 
                                                class="doc-preview-btn hover-zoom" 
                                                data-preview-url="<?php echo htmlspecialchars($docUrl); ?>"
                                            >
                                                Preview
                                            </button>
                                            <a href="<?php echo $docUrl; ?>" target="_blank" class="doc-download-btn hover-zoom">Download</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <!-- Grid View -->
                <div class="documents-grid hidden" id="gridView">
                    <?php if (!empty($documents)): ?>
                        <?php foreach ($documents as $doc): ?>
                            <div class="document-card hover-zoom">
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
                                            class="doc-card-preview-btn hover-zoom" 
                                            data-preview-url="<?php echo htmlspecialchars($docUrl); ?>"
                                        >
                                            Preview
                                        </button>
                                        <a href="<?php echo $docUrl; ?>" target="_blank" class="doc-card-download hover-zoom">Download</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

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

