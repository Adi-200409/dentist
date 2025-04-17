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
    // Prepare and execute query to get emergency requests
    $stmt = $conn->prepare("
        SELECT 
            e.id,
            e.name,
            e.phone,
            e.location,
            e.status,
            e.created_at
        FROM emergency_requests e
        ORDER BY e.created_at DESC
    ");
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $emergencies = [];
    while ($row = $result->fetch_assoc()) {
        $emergencies[] = $row;
    }
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($emergencies);
    
} catch (Exception $e) {
    // Log error and return error response
    error_log("Error in get_emergencies.php: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Failed to fetch emergency requests']);
}

$stmt->close();
$conn->close();
?> 