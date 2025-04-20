<?php
session_start();
require_once "conn.php";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'register_error.log');

header('Content-Type: application/json');

try {
    // Log the request method
    error_log("Request method: " . $_SERVER["REQUEST_METHOD"]);
    
    // Get the raw POST data
    $raw_data = file_get_contents('php://input');
    error_log("Raw request data: " . $raw_data);
    
    // Decode JSON data
    $data = json_decode($raw_data, true);
    
    // Check for JSON decode errors
    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON decode error: " . json_last_error_msg());
        throw new Exception("Invalid JSON data: " . json_last_error_msg());
    }
    
    error_log("Decoded data: " . print_r($data, true));
    
    // Get and sanitize form data
    $name = isset($data['name']) ? trim($data['name']) : '';
    $phone = isset($data['phone']) ? preg_replace('/\D/', '', $data['phone']) : ''; // Remove non-digits
    $favorite_number = isset($data['favorite_number']) ? intval($data['favorite_number']) : null;
    $password = isset($data['password']) ? $data['password'] : '';

    // Log registration attempt
    error_log("Registration attempt - Name: $name, Phone: $phone, Favorite number: $favorite_number");

    // Validate input
    if (empty($name) || empty($phone) || empty($password) || $favorite_number === null) {
        throw new Exception("Please fill in all fields");
    }

    if (strlen($password) < 6) {
        throw new Exception("Password must be at least 6 characters long");
    }

    // Validate favorite number
    if ($favorite_number < 1 || $favorite_number > 100) {
        throw new Exception("Favorite number must be between 1 and 100");
    }

    // Validate phone number format
    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        throw new Exception("Please enter a valid 10-digit phone number");
    }

    // Check if phone already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE phone = ?");
    if (!$stmt) {
        error_log("Database error (prepare): " . $conn->error);
        throw new Exception("Database error: " . $conn->error);
    }

    $stmt->bind_param("s", $phone);
    if (!$stmt->execute()) {
        error_log("Database error (execute): " . $stmt->error);
        throw new Exception("Database error: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        throw new Exception("Phone number already registered");
    }
    $stmt->close();

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (name, phone, password, favorite_number, role) VALUES (?, ?, ?, ?, 'user')");
    if (!$stmt) {
        error_log("Database error (prepare insert): " . $conn->error);
        throw new Exception("Database error: " . $conn->error);
    }

    // Properly bind parameters
    $stmt->bind_param("sssi", $name, $phone, $hashed_password, $favorite_number);
    
    if (!$stmt->execute()) {
        error_log("Database error (execute insert): " . $stmt->error);
        throw new Exception("Error creating user: " . $stmt->error);
    }

    // Get the new user's ID
    $user_id = $stmt->insert_id;
    $stmt->close();

    error_log("Registration successful for phone: " . $phone);

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Registration successful! Please sign in.'
    ]);

} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?> 