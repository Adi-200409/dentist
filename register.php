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
    $email = isset($data['email']) ? trim($data['email']) : '';
    $password = isset($data['password']) ? $data['password'] : '';

    // Log registration attempt
    error_log("Registration attempt - Name: $name, Phone: $phone, Email: $email");

    // Validate input
    if (empty($name) || empty($phone) || empty($email) || empty($password)) {
        throw new Exception("Please fill in all fields");
    }

    if (strlen($password) < 6) {
        throw new Exception("Password must be at least 6 characters long");
    }

    // Validate phone number format
    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        throw new Exception("Please enter a valid 10-digit phone number");
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Please enter a valid email address");
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

    // First check if email column exists
    $email_column_exists = false;
    $check_email_column = $conn->query("SHOW COLUMNS FROM users LIKE 'email'");
    if ($check_email_column && $check_email_column->num_rows > 0) {
        $email_column_exists = true;
        
        // Only check for duplicate email if the column exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        if (!$stmt) {
            error_log("Database error (prepare): " . $conn->error);
            throw new Exception("Database error: " . $conn->error);
        }

        $stmt->bind_param("s", $email);
        if (!$stmt->execute()) {
            error_log("Database error (execute): " . $stmt->error);
            throw new Exception("Database error: " . $stmt->error);
        }

        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            throw new Exception("Email already registered");
        }
        $stmt->close();
    } else {
        // Add email column if it doesn't exist
        error_log("Email column does not exist, attempting to add it");
        if (!$conn->query("ALTER TABLE users ADD COLUMN email VARCHAR(100) AFTER phone")) {
            error_log("Error adding email column: " . $conn->error);
            // Continue even if we can't add the column
        } else {
            error_log("Email column added successfully");
            $email_column_exists = true;
        }
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare the insert statement based on whether email column exists
    if ($email_column_exists) {
        // Insert user with email
        $stmt = $conn->prepare("INSERT INTO users (name, phone, email, password, role) VALUES (?, ?, ?, ?, 'user')");
        if (!$stmt) {
            error_log("Database error (prepare insert): " . $conn->error);
            throw new Exception("Database error: " . $conn->error);
        }

        // Properly bind parameters
        $stmt->bind_param("ssss", $name, $phone, $email, $hashed_password);
    } else {
        // Insert user without email
        $stmt = $conn->prepare("INSERT INTO users (name, phone, password, role) VALUES (?, ?, ?, 'user')");
        if (!$stmt) {
            error_log("Database error (prepare insert): " . $conn->error);
            throw new Exception("Database error: " . $conn->error);
        }

        // Properly bind parameters
        $stmt->bind_param("sss", $name, $phone, $hashed_password);
    }
    
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