<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/upload.php';

$pageTitle = 'Search Results - PROWLWAY';
$bodyClass = 'search-page';

// Add custom styles for search page - light theme
$customSearchStyles = '
<style>
.search-page-container {
    background: #ffffff !important;
    min-height: 100vh;
}
.search-form-input {
    background: #ffffff !important;
    border: 1px solid #d0d0d0 !important;
    color: #000000 !important;
    font-weight: bold !important;
}
.search-form-input::placeholder {
    color: #666666 !important;
}
.search-form-input:focus {
    outline: none !important;
    background: #ffffff !important;
    border-color: #000000 !important;
    box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.1) !important;
}
.search-form-input::-webkit-search-cancel-button {
    -webkit-appearance: none;
    appearance: none;
    width: 16px;
    height: 16px;
    cursor: pointer;
    background-image: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'16\' height=\'16\' viewBox=\'0 0 16 16\'%3E%3Cpath fill=\'%23000000\' d=\'M8 8.707l3.646 3.647.708-.707L8.707 8l3.647-3.646-.707-.708L8 7.293 4.354 3.646l-.707.708L7.293 8l-3.646 3.646.707.708L8 8.707z\'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: center;
    background-size: contain;
    opacity: 0.7;
    transition: opacity 0.2s ease;
}
.search-form-input::-webkit-search-cancel-button:hover {
    opacity: 1;
}
.search-form-input[type="search"]::-moz-search-clear-button {
    cursor: pointer;
    filter: brightness(0);
    opacity: 0.7;
}
.search-form-input[type="search"]::-moz-search-clear-button:hover {
    opacity: 1;
}
.search-form-select {
    background: #ffffff !important;
    border: 1px solid #d0d0d0 !important;
    color: #000000 !important;
    font-weight: bold !important;
    cursor: pointer;
}
.search-form-select:focus {
    outline: none !important;
    background: #ffffff !important;
    border-color: #000000 !important;
}
.search-form-select option {
    background: #ffffff !important;
    color: #000000 !important;
}
.search-form-button {
    background: #ffffff !important;
    color: #000000 !important;
    border: 1px solid #d0d0d0 !important;
    font-weight: bold !important;
}
.search-form-button:hover {
    background: #f5f5f5 !important;
}
.search-form-button svg {
    stroke: #000000 !important;
    fill: none !important;
}
</style>
';

$query = trim($_GET['q'] ?? '');
$type = $_GET['type'] ?? 'all'; // all, documents, announcements, events
$category = $_GET['category'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

include '../includes/header.php';
echo $customSearchStyles;

$results = [];
$totalResults = 0;
$totalPages = 0;

if (!empty($query)) {
    // Use case-insensitive LIKE search
    $searchTerm = "%" . str_replace(' ', '%', trim($query)) . "%";
    
    // Build search query based on type
    if ($type === 'all' || $type === 'documents') {
        $docQuery = "SELECT 'document' as result_type, id, title, description, category, subcategory, series_year, academic_year, created_at, file_path 
                     FROM documents 
                     WHERE status = 'published' 
                     AND (LOWER(title) LIKE LOWER(?) OR LOWER(description) LIKE LOWER(?))";
        
        if (!empty($category)) {
            $docQuery .= " AND category = ?";
        }
        $docQuery .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        
        $docParams = [$searchTerm, $searchTerm];
        if (!empty($category)) {
            $docParams[] = $category;
        }
        $docParams[] = $perPage;
        $docParams[] = $offset;
        
        try {
            $docResults = dbFetchAll($docQuery, $docParams);
            foreach ($docResults as $doc) {
                $results[] = $doc;
            }
        } catch (Exception $e) {
            error_log("Search error (documents): " . $e->getMessage());
        }
    }
    
    if ($type === 'all' || $type === 'announcements') {
        $annQuery = "SELECT 'announcement' as result_type, id, title, description, content, category, academic_year, created_at, image 
                     FROM announcements 
                     WHERE status = 'published' 
                     AND (LOWER(title) LIKE LOWER(?) OR LOWER(description) LIKE LOWER(?) OR LOWER(content) LIKE LOWER(?)) 
                     ORDER BY pinned DESC, created_at DESC LIMIT ? OFFSET ?";
        
        $annParams = [$searchTerm, $searchTerm, $searchTerm, $perPage, $offset];
        
        try {
            $annResults = dbFetchAll($annQuery, $annParams);
            foreach ($annResults as $ann) {
                $results[] = $ann;
            }
        } catch (Exception $e) {
            error_log("Search error (announcements): " . $e->getMessage());
        }
    }
    
    if ($type === 'all' || $type === 'events') {
        $evtQuery = "SELECT 'event' as result_type, id, title, caption, description, location, date, academic_year, created_at, image 
                     FROM events 
                     WHERE status = 'published' 
                     AND (LOWER(title) LIKE LOWER(?) OR LOWER(caption) LIKE LOWER(?) OR LOWER(description) LIKE LOWER(?)) 
                     ORDER BY date DESC LIMIT ? OFFSET ?";
        
        $evtParams = [$searchTerm, $searchTerm, $searchTerm, $perPage, $offset];
        
        try {
            $evtResults = dbFetchAll($evtQuery, $evtParams);
            foreach ($evtResults as $evt) {
                $results[] = $evt;
            }
        } catch (Exception $e) {
            error_log("Search error (events): " . $e->getMessage());
        }
    }
    
    // Get total count for pagination (simplified - using current results count)
    $totalResults = count($results);
    $totalPages = ceil($totalResults / $perPage);
}

$categoryNames = [
    '01' => 'OFFICES REPORT',
    '02' => 'EXECUTIVE ORDER',
    '03' => 'ORDINANCE',
    '04' => 'RESOLUTION',
    '05' => 'OTHER'
];

$subcategoryNames = [
    'OTP' => 'OTP Report',
    'OVIA' => 'OVIA Report',
    'OVPEA' => 'OVPEA Report',
    'OS' => 'OS Report',
    'OTA' => 'OTA Report',
    'OBPR' => 'OBPR Report',
    'Media Publication' => 'Media and Publication Division Report',
    'Arts Craft' => 'Arts and Craft Division Report',
    'Documentation' => 'Media Documentation Report',
    'Business' => 'Business Report'
];
?>

<div class="search-page-container px-4 md:px-6 lg:px-8 py-6 md:py-8" style="padding-top: 80px; min-height: 100vh;">
<div class="container mx-auto px-4 py-8 max-w-6xl" style="background: #ffffff; color: #000000;">
    <!-- Search Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold mb-4" style="color: #000000; font-weight: bold;">Search Results</h1>
        
        <!-- Search Form -->
        <form method="GET" action="<?php echo PUBLIC_URL; ?>/search.php" class="flex gap-4 mb-4">
            <div class="flex-1">
                <input 
                    type="search" 
                    name="q" 
                    value="<?php echo htmlspecialchars($query); ?>" 
                    placeholder="Search documents, announcements, events..." 
                    class="search-form-input"
                    required
                    style="padding: 12px 16px; border-radius: 12px; width: 100%; font-size: 14px; font-family: inherit;"
                >
            </div>
            <div>
                <select name="type" class="search-form-select" style="padding: 12px 16px; border-radius: 12px; font-size: 14px; font-family: inherit;">
                    <option value="all" <?php echo $type === 'all' ? 'selected' : ''; ?>>All</option>
                    <option value="documents" <?php echo $type === 'documents' ? 'selected' : ''; ?>>Documents</option>
                    <option value="announcements" <?php echo $type === 'announcements' ? 'selected' : ''; ?>>Announcements</option>
                    <option value="events" <?php echo $type === 'events' ? 'selected' : ''; ?>>Events</option>
                    <option value="institute" <?php echo $type === 'institute' ? 'selected' : ''; ?>>Institute</option>
                </select>
            </div>
            <button type="submit" class="search-form-button px-6 py-3 font-semibold rounded-xl transition-colors">
                Search
            </button>
        </form>
        
        <?php if (!empty($query)): ?>
            <p style="color: #000000; font-size: 15px; font-weight: bold; margin-top: 12px;">
                Found <strong style="color: #000000; font-weight: bold;"><?php echo $totalResults; ?></strong> result<?php echo $totalResults !== 1 ? 's' : ''; ?> for "<strong style="color: #000000; font-weight: bold;"><?php echo htmlspecialchars($query); ?></strong>"
            </p>
        <?php endif; ?>
    </div>
    
    <!-- Results -->
    <?php if (empty($query)): ?>
        <div class="text-center py-12">
            <p style="color: #000000; font-weight: bold; font-size: 18px;">Enter a search term to begin</p>
        </div>
    <?php elseif (empty($results)): ?>
        <div class="text-center py-12">
            <p style="color: #000000; font-size: 18px; font-weight: bold;">No results found for "<?php echo htmlspecialchars($query); ?>"</p>
            <p style="color: #000000; font-size: 14px; margin-top: 8px;">Try different keywords or check your spelling</p>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($results as $result): ?>
                <div style="background: #f5f5f5; border: 1px solid #e0e0e0; border-radius: 12px; padding: 24px; transition: all 0.3s ease;" onmouseover="this.style.background='#eeeeee'; this.style.borderColor='#d0d0d0';" onmouseout="this.style.background='#f5f5f5'; this.style.borderColor='#e0e0e0';">
                    <?php if ($result['result_type'] === 'document'): ?>
                        <div class="flex items-start gap-4">
                            <div class="text-4xl">📄</div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <span style="padding: 4px 8px; font-size: 12px; font-weight: bold; background: #ffffff; color: #000000; border: 1px solid #d0d0d0; border-radius: 4px;">Document</span>
                                    <?php if (!empty($result['category']) && isset($categoryNames[$result['category']])): ?>
                                        <span style="padding: 4px 8px; font-size: 12px; font-weight: bold; background: #ffffff; color: #000000; border: 1px solid #d0d0d0; border-radius: 4px;">
                                            <?php echo htmlspecialchars($categoryNames[$result['category']]); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($result['subcategory']) && isset($subcategoryNames[$result['subcategory']])): ?>
                                        <span style="padding: 4px 8px; font-size: 12px; font-weight: bold; background: #ffffff; color: #000000; border: 1px solid #d0d0d0; border-radius: 4px;">
                                            <?php echo htmlspecialchars($subcategoryNames[$result['subcategory']]); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($result['series_year'])): ?>
                                        <span style="padding: 4px 8px; font-size: 12px; font-weight: bold; background: #ffffff; color: #000000; border: 1px solid #d0d0d0; border-radius: 4px;">
                                            Series <?php echo htmlspecialchars($result['series_year']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <h3 style="font-size: 20px; font-weight: bold; margin-bottom: 8px; color: #000000;">
                                    <a href="<?php echo PUBLIC_URL; ?>/download.php?id=<?php echo $result['id']; ?>" style="color: #000000; font-weight: bold; text-decoration: none;" onmouseover="this.style.color='#000000';" onmouseout="this.style.color='#000000';">
                                        <?php echo htmlspecialchars($result['title']); ?>
                                    </a>
                                </h3>
                                <?php if (!empty($result['description'])): ?>
                                    <p style="color: #000000; font-weight: bold; margin-bottom: 8px; line-height: 1.6;"><?php echo htmlspecialchars(substr($result['description'], 0, 200)); ?>...</p>
                                <?php endif; ?>
                                <p style="font-size: 13px; color: #000000; font-weight: bold;">
                                    <?php echo date('F j, Y', strtotime($result['created_at'])); ?>
                                    <?php if (!empty($result['academic_year'])): ?>
                                        · Academic Year: <?php echo htmlspecialchars($result['academic_year']); ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    <?php elseif ($result['result_type'] === 'announcement'): ?>
                        <div class="flex items-start gap-4">
                            <?php if (!empty($result['image'])): ?>
                                <img src="<?php echo getImageUrl($result['image']); ?>" alt="<?php echo htmlspecialchars($result['title']); ?>" style="width: 96px; height: 96px; object-fit: cover; border-radius: 8px; border: 1px solid #d0d0d0;">
                            <?php else: ?>
                                <div class="text-4xl">📢</div>
                            <?php endif; ?>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <span style="padding: 4px 8px; font-size: 12px; font-weight: bold; background: #ffffff; color: #000000; border: 1px solid #d0d0d0; border-radius: 4px;">Announcement</span>
                                </div>
                                <h3 style="font-size: 20px; font-weight: bold; margin-bottom: 8px; color: #000000;">
                                    <a href="<?php echo PUBLIC_URL; ?>/home.php#announcement-<?php echo $result['id']; ?>" style="color: #000000; font-weight: bold; text-decoration: none;" onmouseover="this.style.color='#000000';" onmouseout="this.style.color='#000000';">
                                        <?php echo htmlspecialchars($result['title']); ?>
                                    </a>
                                </h3>
                                <?php if (!empty($result['description'])): ?>
                                    <p style="color: #000000; font-weight: bold; margin-bottom: 8px; line-height: 1.6;"><?php echo htmlspecialchars(substr($result['description'], 0, 200)); ?>...</p>
                                <?php endif; ?>
                                <p style="font-size: 13px; color: #000000; font-weight: bold;">
                                    <?php echo date('F j, Y', strtotime($result['created_at'])); ?>
                                    <?php if (!empty($result['academic_year'])): ?>
                                        · Academic Year: <?php echo htmlspecialchars($result['academic_year']); ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    <?php elseif ($result['result_type'] === 'event'): ?>
                        <div class="flex items-start gap-4">
                            <?php if (!empty($result['image'])): ?>
                                <img src="<?php echo getImageUrl($result['image']); ?>" alt="<?php echo htmlspecialchars($result['title']); ?>" style="width: 96px; height: 96px; object-fit: cover; border-radius: 8px; border: 1px solid #d0d0d0;">
                            <?php else: ?>
                                <div class="text-4xl">📅</div>
                            <?php endif; ?>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <span style="padding: 4px 8px; font-size: 12px; font-weight: bold; background: #ffffff; color: #000000; border: 1px solid #d0d0d0; border-radius: 4px;">Event</span>
                                </div>
                                <h3 style="font-size: 20px; font-weight: bold; margin-bottom: 8px; color: #000000;">
                                    <a href="<?php echo PUBLIC_URL; ?>/event-detail.php?id=<?php echo $result['id']; ?>" style="color: #000000; font-weight: bold; text-decoration: none;" onmouseover="this.style.color='#000000';" onmouseout="this.style.color='#000000';">
                                        <?php echo htmlspecialchars($result['title']); ?>
                                    </a>
                                </h3>
                                <?php if (!empty($result['caption'])): ?>
                                    <p style="color: #000000; font-weight: bold; margin-bottom: 8px; line-height: 1.6;"><?php echo htmlspecialchars(substr($result['caption'], 0, 200)); ?>...</p>
                                <?php endif; ?>
                                <p style="font-size: 13px; color: #000000; font-weight: bold;">
                                    <?php echo date('F j, Y', strtotime($result['date'])); ?>
                                    <?php if (!empty($result['location'])): ?>
                                        · <?php echo htmlspecialchars($result['location']); ?>
                                    <?php endif; ?>
                                    <?php if (!empty($result['academic_year'])): ?>
                                        · Academic Year: <?php echo htmlspecialchars($result['academic_year']); ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    <?php elseif ($result['result_type'] === 'institute_info' || $result['result_type'] === 'institute_section'): ?>
                        <div class="flex items-start gap-4">
                            <?php if (!empty($result['image'])): ?>
                                <img src="<?php echo getImageUrl($result['image']); ?>" alt="<?php echo htmlspecialchars($result['title']); ?>" style="width: 96px; height: 96px; object-fit: cover; border-radius: 8px; border: 1px solid #d0d0d0;">
                            <?php else: ?>
                                <div class="text-4xl">🏛️</div>
                            <?php endif; ?>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <span style="padding: 4px 8px; font-size: 12px; font-weight: bold; background: #ffffff; color: #000000; border: 1px solid #d0d0d0; border-radius: 4px;">Institute</span>
                                    <?php if (!empty($result['category'])): ?>
                                        <span style="padding: 4px 8px; font-size: 12px; font-weight: bold; background: #ffffff; color: #000000; border: 1px solid #d0d0d0; border-radius: 4px;">
                                            <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $result['category']))); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <h3 style="font-size: 20px; font-weight: bold; margin-bottom: 8px; color: #000000;">
                                    <a href="<?php echo PUBLIC_URL; ?>/institute.php<?php echo $result['result_type'] === 'institute_info' && !empty($result['category']) ? '?section=' . htmlspecialchars($result['category']) : ''; ?><?php echo $result['result_type'] === 'institute_section' ? '#section-' . $result['id'] : ''; ?>" style="color: #000000; font-weight: bold; text-decoration: none;" onmouseover="this.style.color='#000000';" onmouseout="this.style.color='#000000';">
                                        <?php echo htmlspecialchars($result['title']); ?>
                                    </a>
                                </h3>
                                <?php if (!empty($result['description'])): ?>
                                    <p style="color: #000000; font-weight: bold; margin-bottom: 8px; line-height: 1.6;"><?php echo htmlspecialchars(substr(strip_tags($result['description']), 0, 200)); ?>...</p>
                                <?php endif; ?>
                                <p style="font-size: 13px; color: #000000; font-weight: bold;">
                                    <?php echo date('F j, Y', strtotime($result['created_at'])); ?>
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div style="margin-top: 32px; display: flex; justify-content: center; gap: 8px;">
                <?php if ($page > 1): ?>
                    <a href="?q=<?php echo urlencode($query); ?>&type=<?php echo urlencode($type); ?>&page=<?php echo $page - 1; ?>" style="padding: 8px 16px; border: 1px solid #d0d0d0; border-radius: 8px; color: #000000; font-weight: bold; text-decoration: none; background: #ffffff;" onmouseover="this.style.background='#f5f5f5';" onmouseout="this.style.background='#ffffff';">Previous</a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?q=<?php echo urlencode($query); ?>&type=<?php echo urlencode($type); ?>&page=<?php echo $i; ?>" style="padding: 8px 16px; border: 1px solid #d0d0d0; border-radius: 8px; text-decoration: none; font-weight: bold; <?php echo $i === $page ? 'background: #000000; color: #ffffff; border-color: #000000;' : 'color: #000000; background: #ffffff;'; ?>" onmouseover="<?php echo $i === $page ? '' : "this.style.background='#f5f5f5';"; ?>" onmouseout="<?php echo $i === $page ? '' : "this.style.background='#000000';"; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="?q=<?php echo urlencode($query); ?>&type=<?php echo urlencode($type); ?>&page=<?php echo $page + 1; ?>" style="padding: 8px 16px; border: 1px solid #d0d0d0; border-radius: 8px; color: #000000; font-weight: bold; text-decoration: none; background: #ffffff;" onmouseover="this.style.background='#f5f5f5';" onmouseout="this.style.background='#ffffff';">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
</div>

<?php include '../includes/footer.php'; ?>
