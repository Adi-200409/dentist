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
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Invalid request method");
    }

    // Get and sanitize form data
    $name = trim($_POST['name']);
    $phone = preg_replace('/\D/', '', $_POST['phone']); // Remove non-digits
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Log registration attempt
    error_log("Registration attempt for phone: " . $phone);

    // Validate input
    if (empty($name) || empty($phone) || empty($password) || empty($confirm_password)) {
        throw new Exception("Please fill in all fields");
    }

    if ($password !== $confirm_password) {
        throw new Exception("Passwords do not match");
    }

    if (strlen($password) < 8) {
        throw new Exception("Password must be at least 8 characters long");
    }

    // Check password strength
    if (!preg_match("/[A-Z]/", $password)) {
        throw new Exception("Password must contain at least one uppercase letter");
    }

    if (!preg_match("/[a-z]/", $password)) {
        throw new Exception("Password must contain at least one lowercase letter");
    }

    if (!preg_match("/[0-9]/", $password)) {
        throw new Exception("Password must contain at least one number");
    }

    // Validate phone number format (Indian mobile number)
    if (!preg_match('/^[6-9]\d{9}$/', $phone)) {
        throw new Exception("Please enter a valid Indian mobile number (10 digits starting with 6-9)");
    }

    // Check if phone already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE phone = ?");
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }

    $stmt->bind_param("s", $phone);
    if (!$stmt->execute()) {
        throw new Exception("Database error: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        throw new Exception("Phone number already registered");
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (name, phone, password) VALUES (?, ?, ?)");
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }

    $stmt->bind_param("sss", $name, $phone, $hashed_password);
    if (!$stmt->execute()) {
        throw new Exception("Error creating user: " . $stmt->error);
    }

    // Get the new user's ID
    $user_id = $stmt->insert_id;

    // Create session
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_phone'] = $phone;

    error_log("Registration successful for phone: " . $phone);

    // Return success response
    echo json_encode([
        'status' => 'success',
        'message' => 'Registration successful! Redirecting to login...',
        'redirect' => 'index.php'
    ]);

} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 