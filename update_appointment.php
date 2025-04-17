<?php
session_start();
require_once "conn.php";

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Get JSON data from request
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id']) || !isset($data['status'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$id = $data['id'];
$status = $data['status'];

// Validate status
$valid_statuses = ['scheduled', 'completed', 'cancelled'];
if (!in_array($status, $valid_statuses)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

try {
    // Prepare and execute update query
    $stmt = $conn->prepare("
        UPDATE appointments 
        SET status = ? 
        WHERE id = ?
    ");
    
    $stmt->bind_param("si", $status, $id);
    $success = $stmt->execute();
    
    if ($success) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Appointment updated successfully']);
    } else {
        throw new Exception('Failed to update appointment');
    }
    
} catch (Exception $e) {
    // Log error and return error response
    error_log("Error in update_appointment.php: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Failed to update appointment']);
}

$stmt->close();
$conn->close();
?> 