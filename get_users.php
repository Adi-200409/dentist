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
    // Check database connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Query to get all users
    $query = "SELECT id, name, phone, role, created_at FROM users ORDER BY created_at DESC";
    
    $result = $conn->query($query);
    if (!$result) {
        throw new Exception("Error executing query: " . $conn->error);
    }
    
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'data' => $users]);
    
} catch (Exception $e) {
    // Log error and return error response
    error_log("Error in get_users.php: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to fetch users', 
        'error' => $e->getMessage()
    ]);
}

$conn->close();
?> 