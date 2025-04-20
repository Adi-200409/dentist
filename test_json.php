<?php
header('Content-Type: application/json');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Get raw input
    $raw_data = file_get_contents('php://input');
    
    // Log what was received
    file_put_contents('test_json.log', "Received data: " . $raw_data . "\n", FILE_APPEND);
    
    // Try to decode JSON
    $data = json_decode($raw_data, true);
    
    // Check for JSON errors
    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
        file_put_contents('test_json.log', "JSON decode error: " . json_last_error_msg() . "\n", FILE_APPEND);
        throw new Exception("Invalid JSON: " . json_last_error_msg());
    }
    
    // Log decoded data
    file_put_contents('test_json.log', "Decoded data: " . print_r($data, true) . "\n", FILE_APPEND);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'JSON processed successfully',
        'received' => $data
    ]);
    
} catch (Exception $e) {
    // Log error
    file_put_contents('test_json.log', "Error: " . $e->getMessage() . "\n", FILE_APPEND);
    
    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 