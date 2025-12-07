<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/upload.php';

// Check authentication
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create' || $action === 'update') {
        $data = [
            'title' => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? '',
            'category' => $_POST['category'] ?? '05',
            'status' => $_POST['status'] ?? 'published',
            'created_by' => $_SESSION['admin_id'] ?? null
        ];
        
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
                echo json_encode(['success' => false, 'error' => 'File upload failed: ' . $uploadResult['error']]);
                exit;
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
                echo json_encode(['success' => false, 'error' => 'PDF file upload is required']);
                exit;
            }
        }
        
        if ($action === 'create') {
            $result = dbInsert('documents', $data);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Document created successfully', 'id' => $result]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Error creating document']);
            }
        } else {
            $id = intval($_POST['id'] ?? 0);
            $result = dbUpdate('documents', $data, 'id = :id', ['id' => $id]);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Document updated successfully']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Error updating document']);
            }
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        // Get document to delete file
        $document = dbFetchOne("SELECT file_path FROM documents WHERE id = ?", [$id]);
        if ($document && !empty($document['file_path'])) {
            deleteUploadedFile($document['file_path']);
        }
        $result = dbDelete('documents', 'id = :id', ['id' => $id]);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Document deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error deleting document']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch documents
    $documents = dbFetchAll("SELECT * FROM documents ORDER BY category ASC, created_at DESC");
    echo json_encode(['success' => true, 'data' => $documents]);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

?>

