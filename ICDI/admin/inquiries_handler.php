<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';

requireAdmin(null, true);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $inquiries = dbFetchAll("SELECT * FROM contact_inquiries ORDER BY created_at DESC");
    echo json_encode(['success' => true, 'data' => $inquiries]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'respond') {
        if (!canPublish()) {
            echo json_encode(['success' => false, 'error' => 'Only Administrators can respond to inquiries.']);
            exit;
        }
        $id = intval($_POST['id'] ?? 0);
        $responseText = trim($_POST['response_text'] ?? '');
        $status = in_array($_POST['status'] ?? '', ['open', 'closed', 'archived'], true) ? $_POST['status'] : 'closed';
        if ($id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid inquiry ID']);
            exit;
        }
        $result = dbUpdate('contact_inquiries', [
            'response_text' => $responseText,
            'status' => $status,
            'responded_at' => date('Y-m-d H:i:s'),
            'responded_by' => $_SESSION['admin_id'],
        ], 'id = :id', ['id' => $id]);
        if ($result) {
            auditLog('inquiry_respond', "Inquiry #{$id} responded / {$status}", 'inquiry', $id);
            echo json_encode(['success' => true, 'message' => 'Response saved.']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Update failed']);
        }
        exit;
    }
    if ($action === 'update_status') {
        if (!canPublish()) {
            echo json_encode(['success' => false, 'error' => 'Only Administrators can change inquiry status.']);
            exit;
        }
        $id = intval($_POST['id'] ?? 0);
        $status = in_array($_POST['status'] ?? '', ['open', 'closed', 'archived'], true) ? $_POST['status'] : null;
        if ($id <= 0 || !$status) {
            echo json_encode(['success' => false, 'error' => 'Invalid ID or status']);
            exit;
        }
        $result = dbUpdate('contact_inquiries', ['status' => $status], 'id = :id', ['id' => $id]);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Status updated.']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Update failed']);
        }
        exit;
    }
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Method not allowed']);
