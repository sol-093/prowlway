<?php
session_start();
require_once '../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin(['admin', 'super_admin'], true);
requireCSRFToken(true); // Validate CSRF token for POST requests (JSON response)

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'bulk_archive') {
        $year = $_POST['year'] ?? '';
        $type = $_POST['type'] ?? 'all';
        
        if (empty($year)) {
            echo json_encode(['success' => false, 'error' => 'Academic year is required']);
            exit;
        }
        
        $count = 0;
        
        try {
            if ($type === 'all' || $type === 'documents') {
                $result = dbQuery("UPDATE documents SET status = 'archived' WHERE status = 'published' AND academic_year = ?", [$year]);
                $count += $result ? $result->rowCount() : 0;
                auditLog('bulk_archive', "Archived documents from {$year}", 'document', null);
            }
            
            if ($type === 'all' || $type === 'announcements') {
                $result = dbQuery("UPDATE announcements SET status = 'archived' WHERE status = 'published' AND academic_year = ?", [$year]);
                $count += $result ? $result->rowCount() : 0;
                auditLog('bulk_archive', "Archived announcements from {$year}", 'announcement', null);
            }
            
            if ($type === 'all' || $type === 'events') {
                $result = dbQuery("UPDATE events SET status = 'archived' WHERE status = 'published' AND academic_year = ?", [$year]);
                $count += $result ? $result->rowCount() : 0;
                auditLog('bulk_archive', "Archived events from {$year}", 'event', null);
            }
            
            echo json_encode(['success' => true, 'count' => $count, 'message' => "Successfully archived {$count} items"]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } elseif ($action === 'restore') {
        // Restore item from archive
        $itemType = $_POST['type'] ?? '';
        $id = intval($_POST['id'] ?? 0);
        
        if ($id <= 0 || empty($itemType)) {
            echo json_encode(['success' => false, 'error' => 'Invalid ID or type']);
            exit;
        }
        
        try {
            $tableName = '';
            $entityType = '';
            $restoreStatus = '';
            
            switch ($itemType) {
                case 'document':
                    $tableName = 'documents';
                    $entityType = 'document';
                    $restoreStatus = 'published';
                    break;
                case 'announcement':
                    $tableName = 'announcements';
                    $entityType = 'announcement';
                    $restoreStatus = 'published';
                    break;
                case 'event':
                    $tableName = 'events';
                    $entityType = 'event';
                    $restoreStatus = 'published';
                    break;
                case 'inquiry':
                    $tableName = 'contact_inquiries';
                    $entityType = 'inquiry';
                    $restoreStatus = 'open';
                    break;
                case 'user':
                    $tableName = 'admins';
                    $entityType = 'user';
                    $restoreStatus = 'active';
                    break;
                default:
                    echo json_encode(['success' => false, 'error' => 'Invalid item type']);
                    exit;
            }
            
            // Verify item is archived before restoring
            $item = dbFetchOne("SELECT * FROM {$tableName} WHERE id = ?", [$id]);
            if (!$item) {
                echo json_encode(['success' => false, 'error' => 'Item not found']);
                exit;
            }
            
            if ($itemType !== 'inquiry' && $itemType !== 'user' && ($item['status'] ?? '') !== 'archived') {
                echo json_encode(['success' => false, 'error' => 'Item is not archived.']);
                exit;
            }
            
            if ($itemType === 'inquiry' && ($item['status'] ?? '') !== 'archived') {
                echo json_encode(['success' => false, 'error' => 'Inquiry is not archived.']);
                exit;
            }
            
            if ($itemType === 'user' && ($item['status'] ?? '') !== 'archived') {
                echo json_encode(['success' => false, 'error' => 'User is not archived.']);
                exit;
            }
            
            // Restore the item
            $result = dbUpdate($tableName, ['status' => $restoreStatus], 'id = :id', ['id' => $id]);
            
            if ($result) {
                auditLog('restore', "Restored {$entityType} #{$id} from archive", $entityType, $id);
                echo json_encode(['success' => true, 'message' => 'Item restored successfully']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Restore failed']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } elseif ($action === 'delete') {
        // Permanent delete from archive
        $itemType = $_POST['type'] ?? '';
        $id = intval($_POST['id'] ?? 0);
        
        if ($id <= 0 || empty($itemType)) {
            echo json_encode(['success' => false, 'error' => 'Invalid ID or type']);
            exit;
        }
        
        try {
            $tableName = '';
            $entityType = '';
            
            switch ($itemType) {
                case 'document':
                    $tableName = 'documents';
                    $entityType = 'document';
                    break;
                case 'announcement':
                    $tableName = 'announcements';
                    $entityType = 'announcement';
                    break;
                case 'event':
                    $tableName = 'events';
                    $entityType = 'event';
                    break;
                case 'inquiry':
                    $tableName = 'contact_inquiries';
                    $entityType = 'inquiry';
                    break;
                case 'user':
                    $tableName = 'admins';
                    $entityType = 'user';
                    break;
                default:
                    echo json_encode(['success' => false, 'error' => 'Invalid item type']);
                    exit;
            }
            
            // Verify item is archived before deleting
            $item = dbFetchOne("SELECT * FROM {$tableName} WHERE id = ?", [$id]);
            if (!$item) {
                echo json_encode(['success' => false, 'error' => 'Item not found']);
                exit;
            }
            
            // Check if item is archived based on type
            if ($itemType === 'user') {
                if (($item['status'] ?? '') !== 'archived') {
                    echo json_encode(['success' => false, 'error' => 'User is not archived. Only archived users can be permanently deleted.']);
                    exit;
                }
            } elseif ($itemType === 'inquiry') {
                if (($item['status'] ?? '') !== 'archived') {
                    echo json_encode(['success' => false, 'error' => 'Inquiry is not archived. Only archived inquiries can be permanently deleted.']);
                    exit;
                }
            } else {
                // For documents, announcements, events
                if (($item['status'] ?? '') !== 'archived') {
                    echo json_encode(['success' => false, 'error' => 'Item is not archived. Only archived items can be permanently deleted.']);
                    exit;
                }
            }
            
            // Delete the item
            $result = dbDelete($tableName, 'id = :id', ['id' => $id]);
            
            if ($result) {
                auditLog('permanent_delete', "Permanently deleted {$entityType} #{$id} from archive", $entityType, $id);
                echo json_encode(['success' => true, 'message' => 'Item permanently deleted']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Delete failed']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}

?>
