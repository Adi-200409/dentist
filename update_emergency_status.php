<?php
session_start();
require_once 'conn.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get JSON data from request
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($data['id']) || !isset($data['status'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$id = $data['id'];
$status = $data['status'];

// Validate status value
$valid_statuses = ['pending', 'in_progress', 'completed', 'cancelled'];
if (!in_array($status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status value']);
    exit;
}

try {
    // Update emergency status
    $stmt = $conn->prepare("UPDATE emergency_requests SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
    } else {
        throw new Exception("Failed to update status");
    }
} catch (Exception $e) {
    error_log("Error updating emergency status: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error updating status']);
}
?> 