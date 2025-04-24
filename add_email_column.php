<?php
require_once 'conn.php';

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'db_error.log');

try {
    echo "<h1>Adding Email Column to Users Table</h1>";
    
    // Check if email column exists
    $check_email_column = "SHOW COLUMNS FROM users LIKE 'email'";
    $result = $conn->query($check_email_column);
    
    if ($result && $result->num_rows === 0) {
        // Add email column if it doesn't exist
        $add_email_column = "ALTER TABLE users 
            ADD COLUMN email VARCHAR(100) AFTER phone,
            ADD UNIQUE KEY unique_email (email)";
        
        if ($conn->query($add_email_column)) {
            echo "<p style='color:green'>✅ Email column added successfully to users table</p>";
            error_log("Email column added successfully to users table");
        } else {
            throw new Exception("Error adding email column: " . $conn->error);
        }
    } else {
        echo "<p style='color:blue'>ℹ️ Email column already exists in users table</p>";
        error_log("Email column already exists in users table");
    }
    
    echo "<p>You can now <a href='login.html'>return to the login page</a>.</p>";
    
} catch (Exception $e) {
    echo "<p style='color:red'>⚠️ Error: " . $e->getMessage() . "</p>";
    error_log("Error in add_email_column.php: " . $e->getMessage());
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?> 