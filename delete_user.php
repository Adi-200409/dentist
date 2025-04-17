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
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Missing user ID']);
    exit();
}

$id = $data['id'];

// Prevent deleting self
if ($id == $_SESSION['user_id']) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Cannot delete your own account']);
    exit();
}

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Delete user's appointments
    $stmt = $conn->prepare("DELETE FROM appointments WHERE user_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    // Delete user's emergency requests
    $stmt = $conn->prepare("DELETE FROM emergency_requests WHERE user_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    // Delete user
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    
    if ($success) {
        $conn->commit();
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
    } else {
        throw new Exception('Failed to delete user');
    }
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    // Log error and return error response
    error_log("Error in delete_user.php: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Failed to delete user']);
}

$stmt->close();
$conn->close();
?> 