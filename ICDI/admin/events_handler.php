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
            'caption' => $_POST['caption'] ?? '',
            'description' => $_POST['description'] ?? '',
            'summary' => $_POST['summary'] ?? '',
            'category' => $_POST['category'] ?? 'other',
            'date' => $_POST['date'] ?? date('Y-m-d'),
            'end_date' => !empty($_POST['end_date']) ? $_POST['end_date'] : null,
            'location' => $_POST['location'] ?? '',
            'display_order' => intval($_POST['order'] ?? 0),
            'status' => $_POST['status'] ?? 'published',
            'created_by' => $_SESSION['admin_id'] ?? null
        ];
        
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
            $result = dbInsert('events', $data);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Event created successfully', 'id' => $result]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Error creating event']);
            }
        } else {
            $id = intval($_POST['id'] ?? 0);
            // Delete old gallery images if updating
            if ($action === 'update' && !empty($_POST['old_gallery'])) {
                $oldGallery = json_decode($_POST['old_gallery'], true);
                if (is_array($oldGallery)) {
                    foreach ($oldGallery as $oldPath) {
                        if (!filter_var($oldPath, FILTER_VALIDATE_URL)) {
                            deleteUploadedFile($oldPath);
                        }
                    }
                }
            }
            $result = dbUpdate('events', $data, 'id = :id', ['id' => $id]);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Event updated successfully']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Error updating event']);
            }
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        // Get event to delete images
        $event = dbFetchOne("SELECT image, gallery FROM events WHERE id = ?", [$id]);
        if ($event) {
            if (!empty($event['image'])) {
                deleteUploadedFile($event['image']);
            }
            if (!empty($event['gallery'])) {
                $gallery = json_decode($event['gallery'], true);
                if (is_array($gallery)) {
                    foreach ($gallery as $imgPath) {
                        if (!filter_var($imgPath, FILTER_VALIDATE_URL)) {
                            deleteUploadedFile($imgPath);
                        }
                    }
                }
            }
        }
        $result = dbDelete('events', 'id = :id', ['id' => $id]);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Event deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error deleting event']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch events
    $events = dbFetchAll("SELECT * FROM events ORDER BY date DESC, display_order ASC");
    echo json_encode(['success' => true, 'data' => $events]);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

?>

