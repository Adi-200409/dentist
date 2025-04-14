<?php
session_start();
require_once "conn.php";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get and sanitize form data
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Validate input
        if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
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

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        // Check if email already exists using prepared statement
        $check_sql = "SELECT id FROM users WHERE email = ?";
        $check_stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "s", $email);
        mysqli_stmt_execute($check_stmt);
        $result = mysqli_stmt_get_result($check_stmt);
        
        if ($result && mysqli_num_rows($result) > 0) {
            throw new Exception("Email already exists");
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert user using prepared statement
        $insert_sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
        $insert_stmt = mysqli_prepare($conn, $insert_sql);
        mysqli_stmt_bind_param($insert_stmt, "sss", $name, $email, $hashed_password);
        
        if (mysqli_stmt_execute($insert_stmt)) {
            // Get the new user's ID
            $user_id = mysqli_insert_id($conn);

            // Create session
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;

            // Return success response
            echo json_encode([
                'status' => 'success',
                'message' => 'Registration successful!',
                'redirect' => 'index.php'
            ]);
        } else {
            throw new Exception("Error creating user: " . mysqli_error($conn));
        }
    }
} catch (Exception $e) {
    // Return error response
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 