<?php
session_start();
require_once 'conn.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

try {
    // Get all emergency requests ordered by creation date (newest first)
    $stmt = $conn->prepare("SELECT * FROM emergency_requests ORDER BY created_at DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $emergencies = [];
    while ($row = $result->fetch_assoc()) {
        $emergencies[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'phone' => $row['phone'],
            'location' => $row['location'],
            'issue' => $row['issue'],
            'urgency' => $row['urgency'],
            'status' => $row['status'],
            'created_at' => $row['created_at']
        ];
    }
    
    echo json_encode(['success' => true, 'emergencies' => $emergencies]);
} catch (Exception $e) {
    error_log("Error fetching emergencies: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error fetching emergency data']);
}

$conn->close();
?> 