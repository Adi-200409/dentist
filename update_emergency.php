<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'update_emergency_error.log');

session_start();
require_once "conn.php";

// Set headers for AJAX response
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
    // Log incoming request
    error_log("Processing emergency update request");
    
    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
        throw new Exception('Invalid request method. Expected POST, got ' . $_SERVER['REQUEST_METHOD']);
}

// Get JSON data from request
    $json_data = file_get_contents('php://input');
    error_log("Raw input: " . $json_data);
    
    $data = json_decode($json_data, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON parsing error: " . json_last_error_msg());
        throw new Exception("Invalid JSON data: " . json_last_error_msg());
    }
    
    error_log("Parsed input: " . print_r($data, true));

if (!isset($data['id']) || !isset($data['status'])) {
        error_log("Missing required fields in request");
        throw new Exception('Missing required fields');
}

    $id = (int)$data['id'];
$status = $data['status'];
    
    error_log("Processing update for emergency ID: $id, new status: $status");

// Validate status
$valid_statuses = ['pending', 'in_progress', 'completed', 'cancelled'];
if (!in_array($status, $valid_statuses)) {
        error_log("Invalid status: $status");
        throw new Exception('Invalid status');
    }

    // Check database connection
    if (!isset($conn) || $conn->connect_error) {
        error_log("Database connection error: " . ($conn->connect_error ?? "Connection not established"));
        throw new Exception("Database connection error");
}

    // Prepare and execute update query
    $stmt = $conn->prepare("
        UPDATE emergency_requests 
        SET status = ?, 
            updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
    ");
    
    if (!$stmt) {
        error_log("Failed to prepare statement: " . $conn->error);
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }
    
    error_log("Statement prepared, binding parameters");
    $stmt->bind_param("si", $status, $id);
    
    error_log("Executing update statement");
    $success = $stmt->execute();
    
    if (!$success) {
        error_log("Failed to execute statement: " . $stmt->error);
        throw new Exception("Failed to execute statement: " . $stmt->error);
    }
    
    // Check if any rows were affected
    if ($stmt->affected_rows === 0) {
        error_log("No rows updated for ID: $id");
        // We'll still consider this a success, maybe the status was already set
        echo json_encode([
            'success' => true,
            'message' => 'Emergency request status was already updated or record not found',
            'affected_rows' => 0
        ]);
    } else {
        error_log("Emergency request updated successfully. ID: $id, New status: $status");
        echo json_encode([
            'success' => true,
            'message' => 'Emergency request updated successfully',
            'affected_rows' => $stmt->affected_rows
        ]);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    // Log error and return error response
    error_log("Error in update_emergency.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update emergency request',
        'error' => $e->getMessage(),
        'debug_info' => [
            'time' => date('Y-m-d H:i:s'),
            'php_version' => PHP_VERSION,
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'Unknown'
        ]
    ]);
}

// Close database connection
if (isset($conn)) {
$conn->close();
}
?> 