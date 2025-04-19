<?php
session_start();
require_once 'conn.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Get JSON data from request
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid data format']);
    exit();
}

// Validate required fields
$required_fields = ['working_hours_start', 'working_hours_end', 'appointment_duration', 'max_appointments_per_day'];
foreach ($required_fields as $field) {
    if (!isset($data[$field])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit();
    }
}

try {
    // Update settings in the database
    $stmt = $conn->prepare("UPDATE settings SET 
        working_hours_start = ?,
        working_hours_end = ?,
        appointment_duration = ?,
        max_appointments_per_day = ?
        WHERE id = 1");
    
    $stmt->bind_param("ssii", 
        $data['working_hours_start'],
        $data['working_hours_end'],
        $data['appointment_duration'],
        $data['max_appointments_per_day']
    );
    
    if ($stmt->execute()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Settings updated successfully']);
    } else {
        throw new Exception('Failed to update settings');
    }
    
    $stmt->close();
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?> 