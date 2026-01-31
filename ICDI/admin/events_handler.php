<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/upload.php';
require_once '../includes/errors.php';
require_once '../includes/validation.php';

requireAdmin(null, true);
requireCSRFToken(true); // Validate CSRF token for POST requests

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create' || $action === 'update') {
        $requestedStatus = $_POST['status'] ?? 'draft';
        
        // Validate required fields
        $title = trim($_POST['title'] ?? '');
        $date = trim($_POST['date'] ?? '');
        $endDate = !empty($_POST['end_date']) ? trim($_POST['end_date']) : null;
        
        if (empty($title)) {
            APIError::json('Title is required', 400);
        }
        
        if (empty($date)) {
            APIError::json('Start date is required', 400);
        }
        
        // Validate date format
        if (!validateDate($date, 'Y-m-d')) {
            APIError::json('Invalid start date format', 400);
        }
        
        // Validate end_date if provided
        if ($endDate !== null) {
            if (!validateDate($endDate, 'Y-m-d')) {
                APIError::json('Invalid end date format', 400);
            }
            // Ensure end_date is not before start date
            if (strtotime($endDate) < strtotime($date)) {
                APIError::json('End date must be on or after start date', 400);
            }
        }
        
        $data = [
            'title' => sanitizeString($title, 255),
            'caption' => sanitizeString($_POST['caption'] ?? '', 500),
            'description' => sanitizeString($_POST['description'] ?? ''),
            'summary' => sanitizeString($_POST['summary'] ?? ''),
            'category' => sanitizeString($_POST['category'] ?? 'other', 50),
            'schedule_type' => sanitizeString($_POST['schedule_type'] ?? 'event', 50),
            'date' => $date,
            'end_date' => $endDate,
            'location' => sanitizeString($_POST['location'] ?? '', 255),
            'display_order' => intval($_POST['order'] ?? 0),
            'status' => normalizeStatusByRole($requestedStatus),
            'created_by' => $_SESSION['admin_id'] ?? null
        ];
        
        // Handle review/approval notes if provided
        if (!empty($_POST['review_notes'])) {
            $data['review_notes'] = $_POST['review_notes'];
        }
        if (!empty($_POST['approval_notes'])) {
            $data['approval_notes'] = $_POST['approval_notes'];
        }
        
        // Handle main image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadImage($_FILES['image'], 'images');
            if ($uploadResult['success']) {
                // Delete old image if updating
                if ($action === 'update' && !empty($_POST['old_image'])) {
                    deleteUploadedFile($_POST['old_image']);
                }
                $data['image'] = $uploadResult['path'];
            } else {
                echo json_encode(['success' => false, 'error' => 'Image upload failed: ' . $uploadResult['error']]);
                exit;
            }
        } elseif ($action === 'update' && !empty($_POST['old_image'])) {
            // Keep existing image if no new upload
            $data['image'] = $_POST['old_image'];
        } elseif ($action === 'create') {
            echo json_encode(['success' => false, 'error' => 'Event image is required']);
            exit;
        }
        
        // Handle gallery images
        $galleryPaths = [];
        
        // Keep existing gallery if updating and no new uploads
        if ($action === 'update' && !empty($_POST['old_gallery'])) {
            $oldGallery = json_decode($_POST['old_gallery'], true);
            if (is_array($oldGallery)) {
                $galleryPaths = $oldGallery;
            }
        }
        
        // Add new gallery images if uploaded
        if (isset($_FILES['gallery']) && is_array($_FILES['gallery']['name'])) {
            $uploadResults = uploadMultipleImages($_FILES['gallery'], 'images');
            foreach ($uploadResults as $result) {
                if ($result['success']) {
                    $galleryPaths[] = $result['path'];
                }
            }
        }
        
        if (!empty($galleryPaths)) {
            $data['gallery'] = json_encode($galleryPaths);
        } elseif ($action === 'update' && empty($galleryPaths)) {
            // If updating and no gallery, set to empty
            $data['gallery'] = null;
        }
        
        if ($action === 'create') {
            try {
                $result = dbInsert('events', $data);
                if ($result === false) {
                    $errorMsg = getLastDbError() ?? 'Failed to create event. Please check all required fields.';
                    error_log("Event creation failed. Error: " . $errorMsg);
                    APIError::database(new Exception($errorMsg), true);
                }
                
                if ($data['status'] === 'pending_review') {
                    auditLog('submit_review', 'Event submitted for review', 'event', $result);
                } elseif ($data['status'] === 'published') {
                    auditLog('publish', 'Event created and published', 'event', $result);
                }
                echo json_encode(['success' => true, 'message' => 'Event created successfully', 'id' => $result]);
            } catch (Exception $e) {
                error_log("Event creation exception: " . $e->getMessage());
                APIError::database($e, true);
            }
        } else {
            $id = intval($_POST['id'] ?? 0);
            if ($id <= 0) {
                APIError::json('Invalid event ID', 400);
            }
            
            // Check if event exists
            $existing = dbFetchOne("SELECT status, image FROM events WHERE id = ?", [$id]);
            if (!$existing) {
                APIError::json('Event not found', 404);
            }
            
            try {
                $result = dbUpdate('events', $data, 'id = :id', ['id' => $id]);
                if ($result === false) {
                    $errorMsg = getLastDbError() ?? 'Failed to update event. Please check all fields.';
                    error_log("Event update failed. Error: " . $errorMsg);
                    APIError::database(new Exception($errorMsg), true);
                }
                
                if ($existing && $data['status'] !== $existing['status']) {
                    if ($data['status'] === 'pending_review' && $existing['status'] === 'draft') {
                        auditLog('submit_review', 'Event submitted for review', 'event', $id);
                    } elseif ($data['status'] === 'published' && $existing['status'] !== 'published') {
                        auditLog('publish', 'Event published', 'event', $id);
                    } elseif ($data['status'] === 'archived') {
                        auditLog('archive', 'Event archived', 'event', $id);
                    }
                }
                echo json_encode(['success' => true, 'message' => 'Event updated successfully']);
            } catch (Exception $e) {
                error_log("Event update exception: " . $e->getMessage());
                APIError::database($e, true);
            }
        }
    } elseif ($action === 'approve') {
        requireAdmin(['admin', 'super_admin'], true);
        $id = intval($_POST['id'] ?? 0);
        $approvalNotes = $_POST['approval_notes'] ?? null;
        
        $updateData = [
            'status' => 'approved',
            'approved_by' => $_SESSION['admin_id'],
            'approved_at' => date('Y-m-d H:i:s'),
            'approval_notes' => $approvalNotes
        ];
        
        $existing = dbFetchOne("SELECT reviewed_by FROM events WHERE id = ?", [$id]);
        if (empty($existing['reviewed_by'])) {
            $updateData['reviewed_by'] = $_SESSION['admin_id'];
            $updateData['reviewed_at'] = date('Y-m-d H:i:s');
        }
        
        $result = dbUpdate('events', $updateData, 'id = :id', ['id' => $id]);
        if ($result) {
            auditLog('approve', 'Event approved', 'event', $id);
            echo json_encode(['success' => true, 'message' => 'Event approved successfully']);
            } else {
                $errorMsg = getLastDbError() ?? 'Error approving event';
                echo json_encode(['success' => false, 'error' => $errorMsg]);
            }
    } elseif ($action === 'publish') {
        requireAdmin(['admin', 'super_admin'], true);
        $id = intval($_POST['id'] ?? 0);
        
        $updateData = ['status' => 'published'];
        
        $existing = dbFetchOne("SELECT approved_by FROM events WHERE id = ?", [$id]);
        if (empty($existing['approved_by'])) {
            $updateData['approved_by'] = $_SESSION['admin_id'];
            $updateData['approved_at'] = date('Y-m-d H:i:s');
        }
        
        $result = dbUpdate('events', $updateData, 'id = :id', ['id' => $id]);
        if ($result) {
            auditLog('publish', 'Event published', 'event', $id);
            echo json_encode(['success' => true, 'message' => 'Event published successfully']);
            } else {
                $errorMsg = getLastDbError() ?? 'Error publishing event';
                echo json_encode(['success' => false, 'error' => $errorMsg]);
            }
    } elseif ($action === 'restore') {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            APIError::json('Invalid ID', 400);
        }
        $event = dbFetchOne("SELECT status FROM events WHERE id = ?", [$id]);
        if (!$event) {
            APIError::json('Event not found', 404);
        }
        if ($event['status'] !== 'archived') {
            APIError::json('Only archived items can be restored', 400);
        }
        try {
            $result = dbUpdate('events', ['status' => 'draft'], 'id = :id', ['id' => $id]);
            if ($result) {
                auditLog('restore', 'Event restored from archive', 'event', $id);
                echo json_encode(['success' => true, 'message' => 'Event restored successfully']);
            } else {
                $errorMsg = getLastDbError() ?? 'Failed to restore event';
                APIError::database(new Exception($errorMsg), true);
            }
        } catch (Exception $e) {
            APIError::database($e, true);
        }
    } elseif ($action === 'archive') {
        $id = intval($_POST['id'] ?? 0);
        $event = dbFetchOne("SELECT status, created_by FROM events WHERE id = ?", [$id]);
        if (!$event) {
            APIError::json('Event not found', 404);
        }
        if (!canPublish() && ($event['status'] !== 'draft' || (int)$event['created_by'] !== (int)($_SESSION['admin_id'] ?? 0))) {
            APIError::permission('Only draft events you created can be archived, or you need publish rights.', true);
        }
        try {
            $result = dbUpdate('events', ['status' => 'archived'], 'id = :id', ['id' => $id]);
            if ($result) {
                auditLog('archive', 'Event archived', 'event', $id);
                echo json_encode(['success' => true, 'message' => 'Event archived successfully']);
            } else {
                $errorMsg = getLastDbError() ?? 'Failed to archive event';
                APIError::database(new Exception($errorMsg), true);
            }
        } catch (Exception $e) {
            APIError::database($e, true);
        }
    } elseif ($action === 'bulk') {
        // Bulk operations
        requireAdmin(['admin', 'super_admin'], true);
        $bulkAction = $_POST['bulk_action'] ?? '';
        $ids = $_POST['ids'] ?? [];
        
        if (empty($ids) || !is_array($ids)) {
            APIError::json('No items selected', 400);
        }
        
        $ids = array_map('intval', $ids);
        $ids = array_filter($ids);
        
        if (empty($ids)) {
            APIError::json('Invalid item IDs', 400);
        }
        
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $successCount = 0;
        
        try {
            switch ($bulkAction) {
                case 'archive':
                    $result = dbQuery("UPDATE events SET status = 'archived' WHERE id IN ($placeholders)", $ids);
                    if ($result) {
                        $successCount = count($ids);
                        foreach ($ids as $id) {
                            auditLog('archive', 'Event archived (bulk)', 'event', $id);
                        }
                    }
                    break;
                    
                case 'publish':
                    $result = dbQuery("UPDATE events SET status = 'published' WHERE id IN ($placeholders)", $ids);
                    if ($result) {
                        $successCount = count($ids);
                        foreach ($ids as $id) {
                            auditLog('publish', 'Event published (bulk)', 'event', $id);
                        }
                    }
                    break;
                    
                case 'archive':
                    $result = dbQuery("UPDATE events SET status = 'archived' WHERE id IN ($placeholders)", $ids);
                    if ($result) {
                        $successCount = count($ids);
                        foreach ($ids as $id) {
                            auditLog('archive', 'Event archived (bulk)', 'event', $id);
                        }
                    }
                    break;
                    
                default:
                    APIError::json('Invalid bulk action', 400);
            }
            
            echo json_encode([
                'success' => true,
                'message' => "Bulk operation completed: $successCount items processed",
                'processed' => $successCount,
                'total' => count($ids)
            ]);
        } catch (Exception $e) {
            APIError::database($e, true);
        }
    } else {
        APIError::json('Invalid action', 400);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        require_once '../includes/errors.php';
        
        // Pagination parameters
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $perPage = isset($_GET['per_page']) ? min(100, max(10, intval($_GET['per_page']))) : 20;
        $offset = ($page - 1) * $perPage;
        
        // Filtering parameters
        $status = $_GET['status'] ?? '';
        $category = $_GET['category'] ?? '';
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        $search = $_GET['search'] ?? '';
        
        // Build WHERE clause
        $where = [];
        $params = [];
        
        if ($status) {
            $where[] = "status = ?";
            $params[] = $status;
        }
        if ($category) {
            $where[] = "category = ?";
            $params[] = $category;
        }
        if ($dateFrom) {
            $where[] = "date >= ?";
            $params[] = $dateFrom;
        }
        if ($dateTo) {
            $where[] = "date <= ?";
            $params[] = $dateTo;
        }
        if ($search) {
            $where[] = "(title LIKE ? OR caption LIKE ? OR description LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Get total count
        $totalQuery = "SELECT COUNT(*) as total FROM events $whereClause";
        $totalResult = dbFetchOne($totalQuery, $params);
        $total = $totalResult['total'] ?? 0;
        
        // Fetch paginated events
        $query = "SELECT * FROM events $whereClause ORDER BY date DESC, display_order ASC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        $events = dbFetchAll($query, $params);
        
        echo json_encode([
            'success' => true, 
            'data' => $events,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage)
            ]
        ]);
    } catch (Exception $e) {
        require_once '../includes/errors.php';
        APIError::database($e, true);
    }
} else {
    require_once '../includes/errors.php';
    APIError::json('Invalid request method', 405);
}

?>

