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
        
        // Validate and sanitize input
        $title = sanitizeString($_POST['title'] ?? '', 255);
        $description = sanitizeString($_POST['description'] ?? '');
        $content = sanitizeString($_POST['content'] ?? '');
        $category = sanitizeString($_POST['category'] ?? 'general', 50);
        
        if (empty($title)) {
            APIError::json('Title is required', 400);
        }
        if (empty($description)) {
            APIError::json('Description is required', 400);
        }
        
        $data = [
            'title' => $title,
            'description' => $description,
            'content' => $content,
            'category' => $category,
            'pinned' => isset($_POST['pinned']) ? 1 : 0,
            'status' => normalizeStatusByRole($requestedStatus),
            'created_by' => $_SESSION['admin_id'] ?? null,
            'is_meeting' => isset($_POST['is_meeting']) ? 1 : 0,
            'meeting_date' => !empty($_POST['meeting_date']) ? $_POST['meeting_date'] : null,
            'meeting_end_date' => !empty($_POST['meeting_end_date']) ? $_POST['meeting_end_date'] : null,
            'meeting_location' => !empty($_POST['meeting_location']) ? sanitizeString($_POST['meeting_location'], 255) : null
        ];
        
        // Handle review/approval notes if provided
        if (!empty($_POST['review_notes'])) {
            $data['review_notes'] = $_POST['review_notes'];
        }
        if (!empty($_POST['approval_notes'])) {
            $data['approval_notes'] = $_POST['approval_notes'];
        }
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadImage($_FILES['image'], 'images');
            if ($uploadResult['success']) {
                // Delete old image if updating
                if ($action === 'update' && !empty($_POST['old_image'])) {
                    deleteUploadedFile($_POST['old_image']);
                }
                $data['image'] = $uploadResult['path'];
            } else {
                APIError::upload($uploadResult['error'], true);
            }
        } elseif ($action === 'update' && !empty($_POST['old_image'])) {
            // Keep existing image if no new upload
            $data['image'] = $_POST['old_image'];
        }
        
        if ($action === 'create') {
            $result = dbInsert('announcements', $data);
            if ($result) {
                if ($data['status'] === 'pending_review') {
                    auditLog('submit_review', 'Announcement submitted for review', 'announcement', $result);
                } elseif ($data['status'] === 'published') {
                    auditLog('publish', 'Announcement created and published', 'announcement', $result);
                }
                echo json_encode(['success' => true, 'message' => 'Announcement created successfully', 'id' => $result]);
            } else {
                $errorMsg = getLastDbError();
                if (empty($errorMsg)) {
                    $errorMsg = 'Failed to insert announcement. Database operation returned false.';
                }
                error_log("Announcement insert failed. Error: " . $errorMsg);
                APIError::database(new Exception($errorMsg), true);
            }
        } else {
            $id = intval($_POST['id'] ?? 0);
            $existing = dbFetchOne("SELECT status FROM announcements WHERE id = ?", [$id]);
            $result = dbUpdate('announcements', $data, 'id = :id', ['id' => $id]);
            if ($result) {
                if ($existing && $data['status'] !== $existing['status']) {
                    if ($data['status'] === 'pending_review' && $existing['status'] === 'draft') {
                        auditLog('submit_review', 'Announcement submitted for review', 'announcement', $id);
                    } elseif ($data['status'] === 'published' && $existing['status'] !== 'published') {
                        auditLog('publish', 'Announcement published', 'announcement', $id);
                    } elseif ($data['status'] === 'archived') {
                        auditLog('archive', 'Announcement archived', 'announcement', $id);
                    }
                }
                echo json_encode(['success' => true, 'message' => 'Announcement updated successfully']);
            } else {
                $errorMsg = getLastDbError() ?? 'Failed to update announcement';
                APIError::database(new Exception($errorMsg), true);
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
        
        $existing = dbFetchOne("SELECT reviewed_by FROM announcements WHERE id = ?", [$id]);
        if (empty($existing['reviewed_by'])) {
            $updateData['reviewed_by'] = $_SESSION['admin_id'];
            $updateData['reviewed_at'] = date('Y-m-d H:i:s');
        }
        
        try {
            $result = dbUpdate('announcements', $updateData, 'id = :id', ['id' => $id]);
            if ($result) {
                auditLog('approve', 'Announcement approved', 'announcement', $id);
                echo json_encode(['success' => true, 'message' => 'Announcement approved successfully']);
            } else {
                $errorMsg = getLastDbError() ?? 'Failed to approve announcement';
                APIError::database(new Exception($errorMsg), true);
            }
        } catch (Exception $e) {
            APIError::database($e, true);
        }
    } elseif ($action === 'publish') {
        requireAdmin(['admin', 'super_admin'], true);
        $id = intval($_POST['id'] ?? 0);
        
        $updateData = ['status' => 'published'];
        
        $existing = dbFetchOne("SELECT approved_by FROM announcements WHERE id = ?", [$id]);
        if (empty($existing['approved_by'])) {
            $updateData['approved_by'] = $_SESSION['admin_id'];
            $updateData['approved_at'] = date('Y-m-d H:i:s');
        }
        
        try {
            $result = dbUpdate('announcements', $updateData, 'id = :id', ['id' => $id]);
            if ($result) {
                auditLog('publish', 'Announcement published', 'announcement', $id);
                echo json_encode(['success' => true, 'message' => 'Announcement published successfully']);
            } else {
                $errorMsg = getLastDbError() ?? 'Failed to publish announcement';
                APIError::database(new Exception($errorMsg), true);
            }
        } catch (Exception $e) {
            APIError::database($e, true);
        }
    } elseif ($action === 'restore') {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            APIError::json('Invalid ID', 400);
        }
        $announcement = dbFetchOne("SELECT status FROM announcements WHERE id = ?", [$id]);
        if (!$announcement) {
            APIError::json('Announcement not found', 404);
        }
        if ($announcement['status'] !== 'archived') {
            APIError::json('Only archived items can be restored', 400);
        }
        try {
            $result = dbUpdate('announcements', ['status' => 'draft'], 'id = :id', ['id' => $id]);
            if ($result) {
                auditLog('restore', 'Announcement restored from archive', 'announcement', $id);
                echo json_encode(['success' => true, 'message' => 'Announcement restored successfully']);
            } else {
                $errorMsg = getLastDbError() ?? 'Failed to restore announcement';
                APIError::database(new Exception($errorMsg), true);
            }
        } catch (Exception $e) {
            APIError::database($e, true);
        }
    } elseif ($action === 'archive') {
        $id = intval($_POST['id'] ?? 0);
        $announcement = dbFetchOne("SELECT status, created_by FROM announcements WHERE id = ?", [$id]);
        if (!$announcement) {
            APIError::json('Announcement not found', 404);
        }
        if (!canPublish() && ($announcement['status'] !== 'draft' || (int)$announcement['created_by'] !== (int)($_SESSION['admin_id'] ?? 0))) {
            APIError::permission('Only draft items you created can be archived, or you need publish rights.', true);
        }
        try {
            $result = dbUpdate('announcements', ['status' => 'archived'], 'id = :id', ['id' => $id]);
            if ($result) {
                auditLog('archive', 'Announcement archived', 'announcement', $id);
                echo json_encode(['success' => true, 'message' => 'Announcement archived successfully']);
            } else {
                $errorMsg = getLastDbError() ?? 'Failed to archive announcement';
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
                    $result = dbQuery("UPDATE announcements SET status = 'archived' WHERE id IN ($placeholders)", $ids);
                    if ($result) {
                        $successCount = count($ids);
                        foreach ($ids as $id) {
                            auditLog('archive', 'Announcement archived (bulk)', 'announcement', $id);
                        }
                    }
                    break;
                    
                case 'publish':
                    $result = dbQuery("UPDATE announcements SET status = 'published' WHERE id IN ($placeholders)", $ids);
                    if ($result) {
                        $successCount = count($ids);
                        foreach ($ids as $id) {
                            auditLog('publish', 'Announcement published (bulk)', 'announcement', $id);
                        }
                    }
                    break;
                    
                case 'archive':
                    $result = dbQuery("UPDATE announcements SET status = 'archived' WHERE id IN ($placeholders)", $ids);
                    if ($result) {
                        $successCount = count($ids);
                        foreach ($ids as $id) {
                            auditLog('archive', 'Announcement archived (bulk)', 'announcement', $id);
                        }
                    }
                    break;
                    
                case 'pin':
                    $result = dbQuery("UPDATE announcements SET pinned = 1 WHERE id IN ($placeholders)", $ids);
                    if ($result) {
                        $successCount = count($ids);
                    }
                    break;
                    
                case 'unpin':
                    $result = dbQuery("UPDATE announcements SET pinned = 0 WHERE id IN ($placeholders)", $ids);
                    if ($result) {
                        $successCount = count($ids);
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
        // Pagination parameters
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $perPage = isset($_GET['per_page']) ? min(100, max(10, intval($_GET['per_page']))) : 20;
        $offset = ($page - 1) * $perPage;
        
        // Filtering parameters
        $status = $_GET['status'] ?? '';
        $category = $_GET['category'] ?? '';
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
        if ($search) {
            $where[] = "(title LIKE ? OR description LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Get total count
        $totalQuery = "SELECT COUNT(*) as total FROM announcements $whereClause";
        $totalResult = dbFetchOne($totalQuery, $params);
        $total = $totalResult['total'] ?? 0;
        
        // Fetch paginated announcements
        $query = "SELECT * FROM announcements $whereClause ORDER BY pinned DESC, created_at DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        $announcements = dbFetchAll($query, $params);
        
        echo json_encode([
            'success' => true, 
            'data' => $announcements,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage)
            ]
        ]);
    } catch (Exception $e) {
        APIError::database($e, true);
    }
} else {
    APIError::json('Invalid request method', 405);
}

?>

