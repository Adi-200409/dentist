<?php
session_start();
require_once "conn.php";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'error.log');

header('Content-Type: application/json');

try {
    // Get JSON data
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);
    
    // Check if the JSON was valid
    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON Error: " . json_last_error_msg());
        throw new Exception('Invalid request format');
    }
    
    // Check if phone and password are set
    if (!isset($data['phone']) || !isset($data['password'])) {
        throw new Exception('Phone number and password are required');
    }

    $phone = preg_replace('/\D/', '', $data['phone']); // Remove non-digits
    $password = $data['password'];

    // Log the raw input
    error_log("Login attempt - Raw phone: " . $data['phone'] . ", Processed phone: " . $phone);

    // Validate phone number format
    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        error_log("Invalid phone format: " . $phone);
        throw new Exception('Invalid phone number format');
    }

    // Log the login attempt
    error_log("Login attempt for phone: " . $phone);

    // Check if user exists
    $stmt = $conn->prepare("SELECT id, name, phone, password, role FROM users WHERE phone = ?");
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }

    $stmt->bind_param("s", $phone);
    if (!$stmt->execute()) {
        throw new Exception("Database error: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        error_log("User not found with phone: " . $phone);
        throw new Exception('Invalid phone number or password');
    }

    $user = $result->fetch_assoc();
    error_log("User found: " . json_encode($user));

    // Verify password
    if (!password_verify($password, $user['password'])) {
        error_log("Invalid password for phone: " . $phone);
        throw new Exception('Invalid phone number or password');
    }

    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_phone'] = $user['phone'];
    $_SESSION['role'] = $user['role'];

    // Clear any reset-related session variables
    unset($_SESSION['reset_user_id']);

    // Return success response with appropriate redirect
    $redirect = $user['role'] === 'admin' ? 'admin.php' : 'index.php';
    echo json_encode([
        'success' => true,
        'message' => 'Login successful!',
        'redirect' => $redirect
    ]);

} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 