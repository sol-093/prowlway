<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/errors.php';
require_once '../includes/validation.php';

requireAdmin(null, true);
requireCSRFToken(true); // Validate CSRF token for POST requests

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create' || $action === 'update') {
        $category = sanitizeString($_POST['category'] ?? '', 10);
        $name = trim($_POST['name'] ?? '');
        $status = sanitizeString($_POST['status'] ?? 'active', 20);
        $displayOrder = intval($_POST['display_order'] ?? 0);
        
        // Validate required fields
        if (empty($category) || !in_array($category, ['01', '02', '03', '04', '05'])) {
            APIError::json('Valid category is required', 400);
        }
        
        if (empty($name)) {
            APIError::json('Subcategory name is required', 400);
        }
        
        if (strlen($name) > 100) {
            APIError::json('Subcategory name must be 100 characters or less', 400);
        }
        
        if (!in_array($status, ['active', 'inactive'])) {
            $status = 'active';
        }
        
        $data = [
            'category' => $category,
            'name' => sanitizeString($name, 100),
            'status' => $status,
            'display_order' => $displayOrder
        ];
        
        if ($action === 'create') {
            // Check for duplicate subcategory name under same category
            $existing = dbFetchOne(
                "SELECT id FROM subcategories WHERE category = ? AND name = ?",
                [$category, $name]
            );
            
            if ($existing) {
                APIError::json('A subcategory with this name already exists under this category', 400);
            }
            
            try {
                $result = dbInsert('subcategories', $data);
                if ($result === false) {
                    $errorMsg = getLastDbError() ?? 'Failed to create subcategory';
                    error_log("Subcategory creation failed. Error: " . $errorMsg);
                    APIError::database(new Exception($errorMsg), true);
                }
                
                auditLog('create', "Subcategory created: {$name} under category {$category}", 'subcategory', $result);
                echo json_encode(['success' => true, 'message' => 'Subcategory created successfully', 'id' => $result]);
            } catch (Exception $e) {
                error_log("Subcategory creation exception: " . $e->getMessage());
                APIError::database($e, true);
            }
        } else {
            // Update action
            $id = intval($_POST['id'] ?? 0);
            if ($id <= 0) {
                APIError::json('Invalid subcategory ID', 400);
            }
            
            // Check if subcategory exists
            $existing = dbFetchOne("SELECT category, name FROM subcategories WHERE id = ?", [$id]);
            if (!$existing) {
                APIError::json('Subcategory not found', 404);
            }
            
            // Check for duplicate name (excluding current record)
            $duplicate = dbFetchOne(
                "SELECT id FROM subcategories WHERE category = ? AND name = ? AND id != ?",
                [$category, $name, $id]
            );
            
            if ($duplicate) {
                APIError::json('A subcategory with this name already exists under this category', 400);
            }
            
            try {
                $result = dbUpdate('subcategories', $data, 'id = :id', ['id' => $id]);
                if ($result === false) {
                    $errorMsg = getLastDbError() ?? 'Failed to update subcategory';
                    error_log("Subcategory update failed. Error: " . $errorMsg);
                    APIError::database(new Exception($errorMsg), true);
                }
                
                auditLog('update', "Subcategory updated: {$name} under category {$category}", 'subcategory', $id);
                echo json_encode(['success' => true, 'message' => 'Subcategory updated successfully']);
            } catch (Exception $e) {
                error_log("Subcategory update exception: " . $e->getMessage());
                APIError::database($e, true);
            }
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            APIError::json('Invalid subcategory ID', 400);
        }
        
        // Check if subcategory exists
        $existing = dbFetchOne("SELECT category, name FROM subcategories WHERE id = ?", [$id]);
        if (!$existing) {
            APIError::json('Subcategory not found', 404);
        }
        
        // Check if any documents are using this subcategory
        $documentsUsing = dbFetchOne(
            "SELECT COUNT(*) as count FROM documents WHERE category = ? AND subcategory = ?",
            [$existing['category'], $existing['name']]
        );
        
        if ($documentsUsing && $documentsUsing['count'] > 0) {
            APIError::json('Cannot delete subcategory: ' . $documentsUsing['count'] . ' document(s) are using it. Please reassign or delete those documents first.', 400);
        }
        
        try {
            $result = dbDelete('subcategories', 'id = :id', ['id' => $id]);
            if ($result === false) {
                $errorMsg = getLastDbError() ?? 'Failed to delete subcategory';
                error_log("Subcategory deletion failed. Error: " . $errorMsg);
                APIError::database(new Exception($errorMsg), true);
            }
            
            auditLog('delete', "Subcategory deleted: {$existing['name']} under category {$existing['category']}", 'subcategory', $id);
            echo json_encode(['success' => true, 'message' => 'Subcategory deleted successfully']);
        } catch (Exception $e) {
            error_log("Subcategory deletion exception: " . $e->getMessage());
            APIError::database($e, true);
        }
    } elseif ($action === 'toggle_status') {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            APIError::json('Invalid subcategory ID', 400);
        }
        
        $existing = dbFetchOne("SELECT status FROM subcategories WHERE id = ?", [$id]);
        if (!$existing) {
            APIError::json('Subcategory not found', 404);
        }
        
        $newStatus = $existing['status'] === 'active' ? 'inactive' : 'active';
        
        try {
            $result = dbUpdate('subcategories', ['status' => $newStatus], 'id = :id', ['id' => $id]);
            if ($result === false) {
                $errorMsg = getLastDbError() ?? 'Failed to toggle subcategory status';
                APIError::database(new Exception($errorMsg), true);
            }
            
            auditLog('update', "Subcategory status changed to {$newStatus}", 'subcategory', $id);
            echo json_encode(['success' => true, 'message' => 'Subcategory status updated', 'status' => $newStatus]);
        } catch (Exception $e) {
            APIError::database($e, true);
        }
    } else {
        APIError::json('Invalid action', 400);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $category = $_GET['category'] ?? '';
        
        $where = [];
        $params = [];
        
        if ($category && in_array($category, ['01', '02', '03', '04', '05'])) {
            $where[] = "category = ?";
            $params[] = $category;
        }
        
        // Only show active subcategories by default, unless admin requests all
        $showAll = isset($_GET['show_all']) && $_GET['show_all'] === '1';
        if (!$showAll) {
            $where[] = "status = 'active'";
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $query = "SELECT * FROM subcategories $whereClause ORDER BY display_order ASC, name ASC";
        $subcategories = dbFetchAll($query, $params);
        
        echo json_encode([
            'success' => true,
            'data' => $subcategories
        ]);
    } catch (Exception $e) {
        APIError::database($e, true);
    }
} else {
    APIError::json('Invalid request method', 405);
}

?>
