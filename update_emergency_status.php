<?php
session_start();
require_once 'conn.php';

header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

try {
    // Get JSON data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id']) || !isset($data['status'])) {
        throw new Exception('ID and status are required');
    }
    
    $id = intval($data['id']);
    $status = $data['status'];
    
    // Validate status
    $valid_statuses = ['pending', 'in_progress', 'completed', 'cancelled'];
    if (!in_array($status, $valid_statuses)) {
        throw new Exception('Invalid status value');
    }
    
    // Update emergency status
    $stmt = $conn->prepare("UPDATE emergency_requests SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    
    if (!$stmt->execute()) {
        throw new Exception("Database error: " . $stmt->error);
    }
    
    if ($stmt->affected_rows === 0) {
        throw new Exception("Emergency not found or status not changed");
    }
    
    echo json_encode(['success' => true, 'message' => 'Emergency status updated successfully']);
    
} catch (Exception $e) {
    error_log("Error updating emergency status: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
?> 