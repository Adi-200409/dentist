<?php
session_start();
require_once "conn.php";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Create database if not exists
    mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS dentist");
    mysqli_select_db($conn, "dentist");

    // Create users table if not exists
    $create_table = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!mysqli_query($conn, $create_table)) {
        throw new Exception("Error creating table: " . mysqli_error($conn));
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = $_POST['password'];

        // Validate input
        if (empty($email) || empty($password)) {
            throw new Exception("Please fill in all fields");
        }

        // Check if user exists
        $sql = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Password is correct, create session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];

                // Return success response
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Login successful!',
                    'redirect' => 'index.php'
                ]);
                exit;
            } else {
                throw new Exception("Invalid email or password");
            }
        } else {
            throw new Exception("Invalid email or password");
        }
    }
} catch (Exception $e) {
    // Return error response
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
    exit;
}
?> 