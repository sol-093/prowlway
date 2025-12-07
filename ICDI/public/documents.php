<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/upload.php';

$pageTitle = 'Documents - PROWLWAY ICDISG';
$bodyClass = '';
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
    '02' => 'EXECUTIVE ORD',
    '03' => 'ORDINANCE',
    '04' => 'RESOLUTION',
    '05' => 'OTHER'
];
?>

<!-- Main Container -->
<div class="container">
    
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">📁 DOCUMENT ARCHIVE</h1>
        <p class="page-subtitle">Access official documents and resources</p>
    </div>

    <!-- Breadcrumb Navigation -->
    <div class="breadcrumb" id="breadcrumb">
        <div class="breadcrumb-item">
            <a href="#" class="breadcrumb-link" onclick="goToRoot(event)">📁 Documents</a>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="toolbar">
        <div class="search-box">
            <span class="search-icon">🔍</span>
            <input type="text" class="search-input" id="searchInput" placeholder="Search documents...">
        </div>
        <div class="view-toggle">
            <button class="view-btn active" onclick="switchView('list', event)">📄 List</button>
            <button class="view-btn" onclick="switchView('grid', event)">⊞ Grid</button>
        </div>
    </div>

    <!-- Folders Section -->
    <div id="foldersSection">
        <h2 class="section-title">📂 Folders</h2>
        <div class="folders-grid" id="foldersGrid">
            <?php foreach ($categoryNames as $catCode => $catName): ?>
                <a href="<?php echo PUBLIC_URL; ?>/documents.php?category=<?php echo $catCode; ?>" class="folder-item <?php echo $selectedCategory === $catCode ? 'active' : ''; ?>">
                    <div class="folder-title"><?php echo $catName; ?></div>
                    <div class="folder-number"><?php echo $catCode; ?></div>
                    <div class="folder-docs"><?php echo ($docCounts[$catCode] ?? 0); ?> Doc<?php echo ($docCounts[$catCode] ?? 0) !== 1 ? 's' : ''; ?></div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Documents Section -->
    <div id="documentsSection">
        <h2 class="section-title">📄 Documents<?php echo $selectedCategory ? ' - ' . $categoryNames[$selectedCategory] : ''; ?></h2>
        <div id="documentsContainer">
            <!-- List View -->
            <div class="documents-list" id="listView">
                <?php if (empty($documents)): ?>
                    <div class="no-documents">
                        <p>No documents found<?php echo $selectedCategory ? ' in this category' : ''; ?>.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($documents as $doc): 
                        $docDate = new DateTime($doc['created_at']);
                        $fileSize = !empty($doc['file_size']) ? number_format($doc['file_size'] / 1024, 2) . ' KB' : 'N/A';
                    ?>
                        <div class="document-item">
                            <div class="doc-icon">📄</div>
                            <div class="doc-info">
                                <div class="doc-title"><?php echo htmlspecialchars($doc['title']); ?></div>
                                <?php if ($doc['description']): ?>
                                    <div class="doc-description"><?php echo htmlspecialchars($doc['description']); ?></div>
                                <?php endif; ?>
                                <div class="doc-meta">
                                    <span class="doc-category"><?php echo $categoryNames[$doc['category']]; ?></span>
                                    <span class="doc-date"><?php echo $docDate->format('M d, Y'); ?></span>
                                    <span class="doc-size"><?php echo $fileSize; ?></span>
                                </div>
                            </div>
                            <div class="doc-actions">
                                <?php if ($doc['file_path']): ?>
                                    <a href="<?php echo getDocumentUrl($doc['file_path']); ?>" target="_blank" class="doc-download-btn">Download</a>
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
                            <div class="doc-card-icon">📄</div>
                            <div class="doc-card-title"><?php echo htmlspecialchars($doc['title']); ?></div>
                            <div class="doc-card-category"><?php echo $categoryNames[$doc['category']]; ?></div>
                            <?php if ($doc['file_path']): ?>
                                <a href="<?php echo getDocumentUrl($doc['file_path']); ?>" target="_blank" class="doc-card-download">Download</a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<?php include '../includes/footer.php'; ?>

