<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'emergency_error.log');

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Log request data
    error_log("Emergency request received");
    error_log("POST data: " . print_r($_POST, true));
    error_log("Raw input: " . file_get_contents('php://input'));

    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method. Expected POST, got ' . $_SERVER['REQUEST_METHOD']);
    }

    // Include database connection
    require_once 'conn.php';

    // Validate and sanitize input
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $location = isset($_POST['location']) ? trim($_POST['location']) : '';
    $issue = isset($_POST['issue']) ? trim($_POST['issue']) : '';
    $urgency = isset($_POST['urgency']) ? trim($_POST['urgency']) : '';

    // Log sanitized data
    error_log("Sanitized input - Name: $name, Phone: $phone, Location: $location, Issue: $issue, Urgency: $urgency");

    // Validation
    $errors = [];
    
    if (empty($name) || strlen($name) < 2) {
        $errors[] = "Please enter a valid name (minimum 2 characters)";
    }

    if (empty($phone)) {
        $errors[] = "Please enter a valid phone number";
    }

    // Remove any non-digit characters from phone
    $phone_digits = preg_replace('/\D/', '', $phone);
    if (strlen($phone_digits) < 10) {
        $errors[] = "Phone number must have at least 10 digits";
    }

    if (empty($location)) {
        $errors[] = "Please provide your location";
    }

    if (empty($issue) || strlen($issue) < 10) {
        $errors[] = "Please provide a detailed description of your emergency (minimum 10 characters)";
    }

    if (empty($urgency)) {
        $errors[] = "Please select the urgency level";
    }

    // If there are validation errors, return them
    if (!empty($errors)) {
        error_log("Validation errors: " . print_r($errors, true));
        echo json_encode([
            'status' => 'error',
            'errors' => $errors
        ]);
        exit;
    }

    // Verify database connection
    if (!isset($conn) || !($conn instanceof mysqli)) {
        throw new Exception("Database connection not properly initialized");
    }

    if ($conn->connect_error) {
        throw new Exception("Database connection error: " . $conn->connect_error);
    }

    error_log("Database connection verified");

    // Prepare SQL statement
    $stmt = $conn->prepare("INSERT INTO emergency_requests (name, phone, location, issue, urgency) VALUES (?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }

    // Bind parameters and execute
    error_log("Binding parameters to prepared statement");
    $stmt->bind_param("sssss", $name, $phone, $location, $issue, $urgency);
    
    error_log("Executing prepared statement");
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute statement: " . $stmt->error);
    }

    // Log success
    error_log("Emergency request successfully inserted. ID: " . $stmt->insert_id);

    // Return success response
    echo json_encode([
        'status' => 'success',
        'message' => 'Your emergency request has been received. We will contact you shortly.',
        'request_id' => $stmt->insert_id
    ]);

    $stmt->close();

} catch (Exception $e) {
    // Log the error
    error_log("Emergency request error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Return error response
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while processing your request. Please try again or call our emergency number.',
        'debug' => $e->getMessage()
    ]);
}
?> 