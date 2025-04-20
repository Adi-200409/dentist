<?php
session_start();
require_once 'conn.php';

header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

try {
    // Get JSON data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'])) {
        throw new Exception('User ID is required');
    }

    $user_id = intval($data['id']);
    
    // Prevent admin from deleting themselves
    if ($user_id == $_SESSION['user_id']) {
        throw new Exception('Cannot delete your own account');
    }

    // Check if user exists and is not an admin
    $check_stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('User not found');
    }
    
    $user = $result->fetch_assoc();
    if ($user['role'] === 'admin') {
        throw new Exception('Cannot delete an admin account');
    }
    
    // Delete user
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Database error: " . $stmt->error);
    }
    
    if ($stmt->affected_rows === 0) {
        throw new Exception("Failed to delete user");
    }
    
    echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
    
} catch (Exception $e) {
    error_log("Error in delete_user.php: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}

if (isset($check_stmt)) {
    $check_stmt->close();
}

if (isset($stmt)) {
    $stmt->close();
}

$conn->close();
?> 