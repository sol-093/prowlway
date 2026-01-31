<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/upload.php';
require_once '../includes/errors.php';
require_once '../includes/validation.php';

requireAdmin(null, true);
requireCSRFToken(true);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Get current sponsors from settings
    $sponsorsSetting = dbFetchOne("SELECT setting_value FROM site_settings WHERE setting_key = 'sponsors'");
    $sponsors = [];
    if ($sponsorsSetting && !empty($sponsorsSetting['setting_value'])) {
        $decoded = json_decode($sponsorsSetting['setting_value'], true);
        if (is_array($decoded)) {
            $sponsors = $decoded;
        }
    }
    
    if ($action === 'create' || $action === 'update') {
        $title = sanitizeString($_POST['title'] ?? '', 255);
        $linkUrl = sanitizeString($_POST['link_url'] ?? '', 500);
        $displayOrder = intval($_POST['display_order'] ?? 0);
        $active = isset($_POST['active']) && $_POST['active'] === '1' ? 1 : 0;
        
        if (empty($title)) {
            APIError::json('Title is required', 400);
        }
        
        $sponsorData = [
            'title' => $title,
            'link_url' => !empty($linkUrl) ? $linkUrl : null,
            'display_order' => $displayOrder,
            'active' => $active
        ];
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadImage($_FILES['image'], 'images');
            if ($uploadResult['success']) {
                // Delete old image if updating
                if ($action === 'update' && !empty($_POST['old_image'])) {
                    deleteUploadedFile($_POST['old_image']);
                }
                $sponsorData['image'] = $uploadResult['path'];
            } else {
                APIError::upload($uploadResult['error'], true);
            }
        } elseif ($action === 'update' && !empty($_POST['old_image'])) {
            // Keep existing image if no new upload
            $sponsorData['image'] = $_POST['old_image'];
        } else {
            if ($action === 'create') {
                APIError::json('Image is required', 400);
            }
        }
        
        if ($action === 'create') {
            // Generate unique ID
            $sponsorData['id'] = uniqid('sponsor_', true);
            $sponsorData['created_at'] = date('Y-m-d H:i:s');
            $sponsors[] = $sponsorData;
        } else {
            $id = $_POST['id'] ?? '';
            $found = false;
            foreach ($sponsors as $key => $sponsor) {
                if ($sponsor['id'] === $id) {
                    $sponsorData['id'] = $id;
                    $sponsorData['created_at'] = $sponsor['created_at'] ?? date('Y-m-d H:i:s');
                    $sponsors[$key] = $sponsorData;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                APIError::json('Sponsor not found', 404);
            }
        }
        
        // Sort by display_order
        usort($sponsors, function($a, $b) {
            return ($a['display_order'] ?? 0) - ($b['display_order'] ?? 0);
        });
        
        // Save to settings
        $jsonValue = json_encode($sponsors);
        $existing = dbFetchOne("SELECT id FROM site_settings WHERE setting_key = 'sponsors'");
        if ($existing) {
            dbUpdate('site_settings', [
                'setting_value' => $jsonValue,
                'setting_type' => 'json',
                'updated_by' => $_SESSION['admin_id']
            ], 'setting_key = :k', ['k' => 'sponsors']);
        } else {
            dbInsert('site_settings', [
                'setting_key' => 'sponsors',
                'setting_value' => $jsonValue,
                'setting_type' => 'json',
                'description' => 'Active sponsors/ads for popup display',
                'updated_by' => $_SESSION['admin_id']
            ]);
        }
        
        auditLog('sponsor_' . $action, 'Sponsor ' . $action . 'd', 'sponsor', null);
        echo json_encode(['success' => true, 'message' => 'Sponsor ' . ($action === 'create' ? 'created' : 'updated') . ' successfully']);
        
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        if (empty($id)) {
            APIError::json('Sponsor ID is required', 400);
        }
        
        $found = false;
        foreach ($sponsors as $key => $sponsor) {
            if ($sponsor['id'] === $id) {
                // Delete image file
                if (!empty($sponsor['image'])) {
                    deleteUploadedFile($sponsor['image']);
                }
                unset($sponsors[$key]);
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            APIError::json('Sponsor not found', 404);
        }
        
        // Re-index array
        $sponsors = array_values($sponsors);
        
        // Save to settings
        $jsonValue = json_encode($sponsors);
        dbUpdate('site_settings', [
            'setting_value' => $jsonValue,
            'updated_by' => $_SESSION['admin_id']
        ], 'setting_key = :k', ['k' => 'sponsors']);
        
        auditLog('sponsor_delete', 'Sponsor deleted', 'sponsor', null);
        echo json_encode(['success' => true, 'message' => 'Sponsor deleted successfully']);
        
    } elseif ($action === 'toggle_active') {
        $id = $_POST['id'] ?? '';
        if (empty($id)) {
            APIError::json('Sponsor ID is required', 400);
        }
        
        $found = false;
        foreach ($sponsors as $key => $sponsor) {
            if ($sponsor['id'] === $id) {
                $sponsors[$key]['active'] = ($sponsor['active'] ?? 0) ? 0 : 1;
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            APIError::json('Sponsor not found', 404);
        }
        
        // Save to settings
        $jsonValue = json_encode($sponsors);
        dbUpdate('site_settings', [
            'setting_value' => $jsonValue,
            'updated_by' => $_SESSION['admin_id']
        ], 'setting_key = :k', ['k' => 'sponsors']);
        
        auditLog('sponsor_toggle', 'Sponsor active status toggled', 'sponsor', null);
        echo json_encode(['success' => true, 'message' => 'Sponsor status updated']);
        
    } else {
        APIError::json('Invalid action', 400);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get all sponsors
    $sponsorsSetting = dbFetchOne("SELECT setting_value FROM site_settings WHERE setting_key = 'sponsors'");
    $sponsors = [];
    if ($sponsorsSetting && !empty($sponsorsSetting['setting_value'])) {
        $decoded = json_decode($sponsorsSetting['setting_value'], true);
        if (is_array($decoded)) {
            $sponsors = $decoded;
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => $sponsors
    ]);
} else {
    APIError::json('Invalid request method', 405);
}

?>
