<?php
session_start();
require_once 'conn.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'])) {
        throw new Exception('User ID is required');
    }

    // Check database connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Delete user
    $query = "DELETE FROM users WHERE id = ? AND role != 'admin'";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . $conn->error);
    }
    
    $stmt->bind_param("i", $data['id']);
    
    if (!$stmt->execute()) {
        throw new Exception("Error deleting user: " . $stmt->error);
    }
    
    if ($stmt->affected_rows === 0) {
        throw new Exception("User not found or cannot be deleted");
    }
    
    // Return success response
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
    
} catch (Exception $e) {
    // Log error and return error response
    error_log("Error in delete_user.php: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to delete user', 
        'error' => $e->getMessage()
    ]);
}

if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
?> 