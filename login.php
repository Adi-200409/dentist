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
    // Check if phone and password are set
    if (!isset($_POST['phone']) || !isset($_POST['password'])) {
        throw new Exception('Phone number and password are required');
    }

    $phone = preg_replace('/\D/', '', $_POST['phone']); // Remove non-digits
    $password = $_POST['password'];

    // Log the raw input
    error_log("Login attempt - Raw phone: " . $_POST['phone'] . ", Processed phone: " . $phone);

    // Validate phone number format (Indian mobile number)
    if (!preg_match('/^[6-9]\d{9}$/', $phone)) {
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
    unset($_SESSION['reset_phone']);
    unset($_SESSION['otp_verified']);

    // Return success response with appropriate redirect
    $redirect = $user['role'] === 'admin' ? 'admin.php' : 'index.php';
    echo json_encode([
        'status' => 'success',
        'message' => 'Login successful!',
        'redirect' => $redirect
    ]);

} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 