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
        $category = sanitizeString($_POST['category'] ?? '05', 10);
        $subcategory = !empty($_POST['subcategory']) ? sanitizeString($_POST['subcategory'], 100) : null;
        $seriesYear = !empty($_POST['series_year']) ? sanitizeString($_POST['series_year'], 20) : null;
        $documentType = !empty($_POST['document_type']) ? sanitizeString($_POST['document_type'], 50) : null;
        $academicYear = !empty($_POST['academic_year']) ? sanitizeString($_POST['academic_year'], 20) : null;
        
        // Validate academic year format if provided
        if ($academicYear && !validateAcademicYear($academicYear)) {
            APIError::json('Invalid academic year format. Use YYYY-YYYY (e.g., 2024-2025)', 400);
        }
        
        if (empty($title)) {
            APIError::json('Title is required', 400);
        }
        
        // Validate subcategory is required
        if (empty($subcategory)) {
            APIError::json('Subcategory is required', 400);
        }
        
        $data = [
            'title' => $title,
            'description' => $description,
            'category' => $category,
            'subcategory' => $subcategory,
            'series_year' => $seriesYear,
            'document_type' => $documentType,
            'academic_year' => $academicYear,
            'status' => normalizeStatusByRole($requestedStatus)
        ];
        
        // Only set created_by for new documents
        if ($action === 'create') {
            $data['created_by'] = $_SESSION['admin_id'] ?? null;
        }
        
        // Handle review/approval notes if provided
        if (!empty($_POST['review_notes'])) {
            $data['review_notes'] = $_POST['review_notes'];
        }
        if (!empty($_POST['approval_notes'])) {
            $data['approval_notes'] = $_POST['approval_notes'];
        }
        
        // Handle file upload (required)
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadDocument($_FILES['file']);
            if ($uploadResult['success']) {
                // Delete old file if updating
                if ($action === 'update' && !empty($_POST['old_file_path'])) {
                    deleteUploadedFile($_POST['old_file_path']);
                }
                $data['file_path'] = $uploadResult['path'];
                $data['file_size'] = $uploadResult['size'];
                $data['file_type'] = $uploadResult['type'];
                } else {
                    APIError::upload($uploadResult['error'], true);
                }
        } elseif ($action === 'update' && !empty($_POST['old_file_path'])) {
            // Keep existing file if no new upload
            $data['file_path'] = $_POST['old_file_path'];
            $oldDoc = dbFetchOne("SELECT file_size, file_type FROM documents WHERE id = ?", [intval($_POST['id'])]);
            if ($oldDoc) {
                $data['file_size'] = $oldDoc['file_size'];
                $data['file_type'] = $oldDoc['file_type'];
            }
        } else {
            if ($action === 'create') {
                APIError::json('PDF file upload is required', 400);
            }
        }
        
        if ($action === 'create') {
            $result = dbInsert('documents', $data);
            if ($result) {
                if ($data['status'] === 'pending_review') {
                    auditLog('submit_review', 'Document submitted for review', 'document', $result);
                } elseif ($data['status'] === 'published') {
                    auditLog('publish', 'Document created and published', 'document', $result);
                }
                echo json_encode(['success' => true, 'message' => 'Document created successfully', 'id' => $result]);
            } else {
                $errorMsg = getLastDbError() ?? 'Failed to insert document';
                APIError::database(new Exception($errorMsg), true);
            }
        } else {
            // UPDATE action
            $id = intval($_POST['id'] ?? 0);
            if ($id <= 0) {
                APIError::json('Invalid document ID', 400);
            }
            
            // Check if document exists and get current file path and subcategory
            $existing = dbFetchOne("SELECT id, status, file_path, subcategory FROM documents WHERE id = ?", [$id]);
            if (!$existing) {
                APIError::json('Document not found', 404);
            }
            
            // Validate subcategory - use existing if not provided in update
            if (empty($subcategory)) {
                if (!empty($existing['subcategory'])) {
                    // Keep existing subcategory if not provided in update
                    $subcategory = $existing['subcategory'];
                    $data['subcategory'] = $subcategory;
                } else {
                    APIError::json('Subcategory is required', 400);
                }
            }
            
            // Track old file path for deletion if new file is uploaded
            $oldFilePath = $existing['file_path'] ?? null;
            $newFileUploaded = isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK;
            
            // Only update file-related fields if a new file is uploaded
            if (!$newFileUploaded) {
                // Remove file-related fields from update if no new file
                unset($data['file_path'], $data['file_size'], $data['file_type']);
            }
            
            try {
                // Use proper WHERE clause with id parameter
                $result = dbUpdate('documents', $data, 'id = :id', ['id' => $id]);
                
                if ($result === false) {
                    $errorMsg = getLastDbError() ?? 'Failed to update document. Please check all fields.';
                    error_log("Document update failed. Error: " . $errorMsg);
                    APIError::database(new Exception($errorMsg), true);
                }
                
                // Only delete old file AFTER successful update and if new file was uploaded
                if ($newFileUploaded && $oldFilePath && !empty($oldFilePath)) {
                    $newFilePath = $data['file_path'] ?? null;
                    // Only delete if paths are different
                    if ($newFilePath && $oldFilePath !== $newFilePath) {
                        // Verify new file exists before deleting old one
                        $newFileFullPath = UPLOAD_BASE_PATH . '/' . $newFilePath;
                        if (file_exists($newFileFullPath)) {
                            deleteUploadedFile($oldFilePath);
                        } else {
                            error_log("Warning: New file not found after update: " . $newFileFullPath);
                        }
                    }
                }
                
                // Track status changes
                if ($existing && $data['status'] !== $existing['status']) {
                    if ($data['status'] === 'pending_review' && $existing['status'] === 'draft') {
                        auditLog('submit_review', 'Document submitted for review', 'document', $id);
                    } elseif ($data['status'] === 'published' && $existing['status'] !== 'published') {
                        auditLog('publish', 'Document published', 'document', $id);
                    } elseif ($data['status'] === 'archived') {
                        auditLog('archive', 'Document archived', 'document', $id);
                    }
                }
                echo json_encode(['success' => true, 'message' => 'Document updated successfully']);
            } catch (Exception $e) {
                error_log("Document update exception: " . $e->getMessage());
                APIError::database($e, true);
            }
        }
    } elseif ($action === 'review') {
        // Review action - set to pending_review or approved
        requireAdmin(['admin', 'super_admin'], true);
        $id = intval($_POST['id'] ?? 0);
        $reviewStatus = $_POST['review_status'] ?? 'pending_review'; // pending_review or approved
        $reviewNotes = $_POST['review_notes'] ?? null;
        
        $updateData = [
            'status' => $reviewStatus,
            'reviewed_by' => $_SESSION['admin_id'],
            'reviewed_at' => date('Y-m-d H:i:s'),
            'review_notes' => $reviewNotes
        ];
        
        if ($reviewStatus === 'approved') {
            $updateData['approved_by'] = $_SESSION['admin_id'];
            $updateData['approved_at'] = date('Y-m-d H:i:s');
            $updateData['approval_notes'] = $reviewNotes;
        }
        
        try {
            $result = dbUpdate('documents', $updateData, 'id = :id', ['id' => $id]);
            if ($result) {
                auditLog('review', "Document reviewed: {$reviewStatus}", 'document', $id);
                echo json_encode(['success' => true, 'message' => 'Document reviewed successfully']);
            } else {
                $errorMsg = getLastDbError() ?? 'Failed to update document review';
                APIError::database(new Exception($errorMsg), true);
            }
        } catch (Exception $e) {
            APIError::database($e, true);
        }
    } elseif ($action === 'approve') {
        // Approve action - set to approved
        requireAdmin(['admin', 'super_admin'], true);
        $id = intval($_POST['id'] ?? 0);
        $approvalNotes = $_POST['approval_notes'] ?? null;
        
        $updateData = [
            'status' => 'approved',
            'approved_by' => $_SESSION['admin_id'],
            'approved_at' => date('Y-m-d H:i:s'),
            'approval_notes' => $approvalNotes
        ];
        
        // If not yet reviewed, set reviewer info
        $existing = dbFetchOne("SELECT reviewed_by FROM documents WHERE id = ?", [$id]);
        if (empty($existing['reviewed_by'])) {
            $updateData['reviewed_by'] = $_SESSION['admin_id'];
            $updateData['reviewed_at'] = date('Y-m-d H:i:s');
        }
        
        try {
            $result = dbUpdate('documents', $updateData, 'id = :id', ['id' => $id]);
            if ($result) {
                auditLog('approve', 'Document approved', 'document', $id);
                echo json_encode(['success' => true, 'message' => 'Document approved successfully']);
            } else {
                $errorMsg = getLastDbError() ?? 'Failed to approve document';
                APIError::database(new Exception($errorMsg), true);
            }
        } catch (Exception $e) {
            APIError::database($e, true);
        }
    } elseif ($action === 'publish') {
        // Publish action - set to published (from approved or pending_review)
        requireAdmin(['admin', 'super_admin'], true);
        $id = intval($_POST['id'] ?? 0);
        
        $updateData = ['status' => 'published'];
        
        // Ensure approval info is set if not already
        $existing = dbFetchOne("SELECT approved_by FROM documents WHERE id = ?", [$id]);
        if (empty($existing['approved_by'])) {
            $updateData['approved_by'] = $_SESSION['admin_id'];
            $updateData['approved_at'] = date('Y-m-d H:i:s');
        }
        
        try {
            $result = dbUpdate('documents', $updateData, 'id = :id', ['id' => $id]);
            if ($result) {
                auditLog('publish', 'Document published', 'document', $id);
                echo json_encode(['success' => true, 'message' => 'Document published successfully']);
            } else {
                $errorMsg = getLastDbError() ?? 'Failed to publish document';
                APIError::database(new Exception($errorMsg), true);
            }
        } catch (Exception $e) {
            APIError::database($e, true);
        }
    } elseif ($action === 'restore') {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid ID']);
            exit;
        }
        $document = dbFetchOne("SELECT status FROM documents WHERE id = ?", [$id]);
        if (!$document) {
            echo json_encode(['success' => false, 'error' => 'Document not found']);
            exit;
        }
        if ($document['status'] !== 'archived') {
            echo json_encode(['success' => false, 'error' => 'Only archived items can be restored']);
            exit;
        }
        try {
            $result = dbUpdate('documents', ['status' => 'draft'], 'id = :id', ['id' => $id]);
            if ($result) {
                auditLog('restore', 'Document restored from archive', 'document', $id);
                echo json_encode(['success' => true, 'message' => 'Document restored successfully']);
            } else {
                $errorMsg = getLastDbError() ?? 'Failed to restore document';
                APIError::database(new Exception($errorMsg), true);
            }
        } catch (Exception $e) {
            APIError::database($e, true);
        }
    } elseif ($action === 'archive') {
        $id = intval($_POST['id'] ?? 0);
        $document = dbFetchOne("SELECT status, created_by FROM documents WHERE id = ?", [$id]);
        if (!$document) {
            echo json_encode(['success' => false, 'error' => 'Document not found']);
            exit;
        }
        if (!canPublish() && ($document['status'] !== 'draft' || (int)$document['created_by'] !== (int)($_SESSION['admin_id'] ?? 0))) {
            echo json_encode(['success' => false, 'error' => 'Only draft documents you created can be archived, or you need publish rights.']);
            exit;
        }
        try {
            $result = dbUpdate('documents', ['status' => 'archived'], 'id = :id', ['id' => $id]);
            if ($result) {
                auditLog('archive', 'Document archived', 'document', $id);
                echo json_encode(['success' => true, 'message' => 'Document archived successfully']);
            } else {
                $errorMsg = getLastDbError() ?? 'Failed to archive document';
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
        $errors = [];
        
        try {
            switch ($bulkAction) {
                case 'archive':
                    $result = dbQuery("UPDATE documents SET status = 'archived' WHERE id IN ($placeholders)", $ids);
                    if ($result) {
                        $successCount = count($ids);
                        foreach ($ids as $id) {
                            auditLog('archive', 'Document archived (bulk)', 'document', $id);
                        }
                    }
                    break;
                    
                case 'publish':
                    $result = dbQuery("UPDATE documents SET status = 'published' WHERE id IN ($placeholders)", $ids);
                    if ($result) {
                        $successCount = count($ids);
                        foreach ($ids as $id) {
                            auditLog('publish', 'Document published (bulk)', 'document', $id);
                        }
                    }
                    break;
                    
                case 'archive':
                    $result = dbQuery("UPDATE documents SET status = 'archived' WHERE id IN ($placeholders)", $ids);
                    if ($result) {
                        $successCount = count($ids);
                        foreach ($ids as $id) {
                            auditLog('archive', 'Document archived (bulk)', 'document', $id);
                        }
                    }
                    break;
                    
                case 'draft':
                    $result = dbQuery("UPDATE documents SET status = 'draft' WHERE id IN ($placeholders)", $ids);
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
        $academicYear = $_GET['academic_year'] ?? '';
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
        if ($academicYear) {
            $where[] = "academic_year = ?";
            $params[] = $academicYear;
        }
        if ($search) {
            $where[] = "(title LIKE ? OR description LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Get total count
        $totalQuery = "SELECT COUNT(*) as total FROM documents $whereClause";
        $totalResult = dbFetchOne($totalQuery, $params);
        $total = $totalResult['total'] ?? 0;
        
        // Fetch paginated documents
        $query = "SELECT * FROM documents $whereClause ORDER BY category ASC, created_at DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        $documents = dbFetchAll($query, $params);
        
        echo json_encode([
            'success' => true, 
            'data' => $documents,
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

