<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'error.log');

require_once 'conn.php';

try {
    // Admin user details
    $name = 'Admin';
    $phone = '9148074307';
    $password = 'admin123'; // This will be hashed before storing
    $role = 'admin';
    
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Check if user already exists
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE phone = ?");
    if (!$check_stmt) {
        throw new Exception("Failed to prepare check statement: " . $conn->error);
    }
    
    $check_stmt->bind_param("s", $phone);
    if (!$check_stmt->execute()) {
        throw new Exception("Failed to execute check statement: " . $check_stmt->error);
    }
    
    $result = $check_stmt->get_result();
    if ($result->num_rows > 0) {
        echo "User with phone " . $phone . " already exists.";
        $check_stmt->close();
        exit;
    }
    $check_stmt->close();
    
    // Insert new admin user
    $insert_stmt = $conn->prepare("INSERT INTO users (name, phone, password, role) VALUES (?, ?, ?, ?)");
    if (!$insert_stmt) {
        throw new Exception("Failed to prepare insert statement: " . $conn->error);
    }
    
    $insert_stmt->bind_param("ssss", $name, $phone, $hashed_password, $role);
    if (!$insert_stmt->execute()) {
        throw new Exception("Failed to execute insert statement: " . $insert_stmt->error);
    }
    
    if ($insert_stmt->affected_rows > 0) {
        echo "Successfully created admin user with phone: " . $phone;
    } else {
        echo "Failed to create admin user.";
    }
    
    $insert_stmt->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?> 