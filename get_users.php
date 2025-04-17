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
    // Prepare and execute query to get users
    $stmt = $conn->prepare("
        SELECT 
            id,
            name,
            phone,
            role,
            created_at
        FROM users
        ORDER BY created_at DESC
    ");
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($users);
    
} catch (Exception $e) {
    // Log error and return error response
    error_log("Error in get_users.php: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Failed to fetch users']);
}

$stmt->close();
$conn->close();
?> 