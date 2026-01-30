<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';

requireAdmin(null, true);

header('Content-Type: application/json');

$allowedTypes = ['regular', 'special_non_working', 'special_working', 'dasma', 'enrollment', 'wellness_break', 'christmas_break', 'year_end', 'school_end', 'start_of_school', 'school'];
$allowedRegions = ['PH', 'Dasma'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create' || $action === 'update') {
        $date = $_POST['date'] ?? date('Y-m-d');
        $endDate = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $type = $_POST['type'] ?? 'regular';
        $typeLabel = trim($_POST['type_label'] ?? '');
        $region = $_POST['region'] ?? 'PH';
        
        if (empty($name)) {
            echo json_encode(['success' => false, 'error' => 'Name is required']);
            exit;
        }
        if (!in_array($type, $allowedTypes, true)) {
            $type = 'regular';
        }
        if ($type === 'school' && $typeLabel === '') {
            echo json_encode(['success' => false, 'error' => 'Event type is required when Type is "School (enter type below)".']);
            exit;
        }
        if (!in_array($region, $allowedRegions, true)) {
            $region = 'PH';
        }
        if ($endDate !== null && $endDate < $date) {
            $endDate = $date;
        }
        
        $data = [
            'date' => $date,
            'end_date' => $endDate,
            'name' => $name,
            'description' => $description === '' ? null : $description,
            'type' => $type,
            'type_label' => $typeLabel === '' ? null : $typeLabel,
            'region' => $region,
        ];
        
        if ($action === 'create') {
            $result = dbInsert('holidays', $data);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Calendar entry added successfully', 'id' => $result]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Error adding calendar entry']);
            }
        } else {
            $id = intval($_POST['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'error' => 'Invalid calendar entry ID']);
                exit;
            }
            $result = dbUpdate('holidays', $data, 'id = :id', ['id' => $id]);
            if ($result !== false) {
                echo json_encode(['success' => true, 'message' => 'Calendar entry updated successfully']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Error updating calendar entry']);
            }
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid calendar entry ID']);
            exit;
        }
        $result = dbDelete('holidays', 'id = :id', ['id' => $id]);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Calendar entry deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error deleting calendar entry']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $holidays = dbFetchAll("SELECT * FROM holidays ORDER BY date ASC");
        echo json_encode(['success' => true, 'data' => $holidays]);
    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'error' => 'Calendar table may not exist', 'data' => []]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
