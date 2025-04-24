<?php
// Simple connection file with better error handling
// Set error logging
ini_set('log_errors', 1);
ini_set('error_log', 'db_error.log');
error_reporting(E_ALL);

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dentist";

// Create connection
try {
    // First connect to MySQL server without database
    $conn = new mysqli($servername, $username, $password);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    if (!$conn->query($sql)) {
        throw new Exception("Error creating database: " . $conn->error);
    }
    
    // Close the connection and reconnect to the specific database
    $conn->close();
    
    // Connect to the database
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Check connection again
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    // Ensure the users table exists with all required columns
    $create_users_table = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        phone VARCHAR(15) NOT NULL,
        email VARCHAR(100),
        password VARCHAR(255) NOT NULL,
        role ENUM('user', 'admin') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_phone (phone)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (!$conn->query($create_users_table)) {
        throw new Exception("Error creating users table: " . $conn->error);
    }
    
    // Check if email column exists and add unique key if needed
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'email'");
    if ($result && $result->num_rows === 0) {
        // Add email column if it doesn't exist
        $conn->query("ALTER TABLE users ADD COLUMN email VARCHAR(100) AFTER phone");
    }
    
    // Check if unique_email index exists
    $result = $conn->query("SHOW INDEX FROM users WHERE Key_name = 'unique_email'");
    if ($result && $result->num_rows === 0) {
        // Add unique email index if it doesn't exist
        $conn->query("ALTER TABLE users ADD UNIQUE KEY unique_email (email)");
    }
    
    // Set charset
    if (!$conn->set_charset("utf8mb4")) {
        throw new Exception("Error setting charset: " . $conn->error);
    }
    
    // Log successful connection
    error_log("Database connection established successfully");
    
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    
    // For API requests, return JSON error
    if (strpos($_SERVER['REQUEST_URI'], '.php') !== false) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Database connection error. Please try again later.',
            'debug' => $e->getMessage()
        ]);
        exit;
    }
    
    // For regular pages, show a user-friendly message
    echo "<div style='padding: 20px; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; margin: 20px;'>";
    echo "<h2 style='color: #721c24;'>Database Error</h2>";
    echo "<p>There was a problem connecting to the database. Please try the following:</p>";
    echo "<ol>";
    echo "<li>Make sure XAMPP/MySQL service is running</li>";
    echo "<li>Check your database credentials in conn.php</li>";
    echo "<li>Make sure the dentist database exists or can be created</li>";
    echo "</ol>";
    echo "<p>Technical details: " . $e->getMessage() . "</p>";
    echo "</div>";
    exit;
}
?> 