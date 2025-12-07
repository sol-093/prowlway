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
            'content' => $_POST['content'] ?? '',
            'category' => $_POST['category'] ?? 'general',
            'pinned' => isset($_POST['pinned']) ? 1 : 0,
            'status' => $_POST['status'] ?? 'published',
            'created_by' => $_SESSION['admin_id'] ?? null
        ];
        
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
                echo json_encode(['success' => false, 'error' => 'Image upload failed: ' . $uploadResult['error']]);
                exit;
            }
        } elseif ($action === 'update' && !empty($_POST['old_image'])) {
            // Keep existing image if no new upload
            $data['image'] = $_POST['old_image'];
        }
        
        if ($action === 'create') {
            $result = dbInsert('announcements', $data);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Announcement created successfully', 'id' => $result]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Error creating announcement']);
            }
        } else {
            $id = intval($_POST['id'] ?? 0);
            $result = dbUpdate('announcements', $data, 'id = :id', ['id' => $id]);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Announcement updated successfully']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Error updating announcement']);
            }
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        // Get announcement to delete image
        $announcement = dbFetchOne("SELECT image FROM announcements WHERE id = ?", [$id]);
        if ($announcement && !empty($announcement['image'])) {
            deleteUploadedFile($announcement['image']);
        }
        $result = dbDelete('announcements', 'id = :id', ['id' => $id]);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Announcement deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error deleting announcement']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch announcements
    $announcements = dbFetchAll("SELECT * FROM announcements ORDER BY pinned DESC, created_at DESC");
    echo json_encode(['success' => true, 'data' => $announcements]);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

?>

