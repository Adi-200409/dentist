<?php
session_start();
require_once 'conn.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'get_emergencies_error.log');

// Set headers
header('Content-Type: application/json');

// Skip admin check during debugging
// Comment this back in later when everything works
/*
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}
*/

try {
    // Check database connection
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection error: " . ($conn->connect_error ?? "Connection not established"));
    }
    
    error_log("Fetching emergency requests from database");
    
    // Check if emergency_requests table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'emergency_requests'");
    if ($table_check->num_rows === 0) {
        error_log("Emergency requests table does not exist");
        throw new Exception("The emergency_requests table does not exist");
    }
    
    // Prepare and execute query to get emergency requests
    $query = "
        SELECT 
            id,
            name,
            phone,
            location,
            issue,
            urgency,
            status,
            created_at,
            updated_at
        FROM emergency_requests
        ORDER BY 
            CASE 
                WHEN status = 'pending' THEN 1
                WHEN status = 'in_progress' THEN 2
                WHEN status = 'completed' THEN 3
                WHEN status = 'cancelled' THEN 4
                ELSE 5
            END,
            created_at DESC
    ";
    
    error_log("Preparing SQL query: " . $query);
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Failed to prepare statement: " . $conn->error);
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }
    
    error_log("Executing query");
    if (!$stmt->execute()) {
        error_log("Failed to execute statement: " . $stmt->error);
        throw new Exception("Failed to execute statement: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    error_log("Query executed successfully, processing results");
    
    $emergencies = [];
    while ($row = $result->fetch_assoc()) {
        $emergencies[] = $row;
    }
    
    error_log("Processed " . count($emergencies) . " emergency requests");
    
    // Return JSON response
    echo json_encode([
        'success' => true,
        'emergencies' => $emergencies
    ]);
    
} catch (Exception $e) {
    // Log error and return error response
    error_log("Error in get_emergencies.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to fetch emergency requests',
        'error' => $e->getMessage(),
        'debug_info' => [
            'time' => date('Y-m-d H:i:s'),
            'php_version' => PHP_VERSION
        ]
    ]);
}

if (isset($stmt)) {
    $stmt->close();
}

if (isset($conn)) {
$conn->close();
}
?> 