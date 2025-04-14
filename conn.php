<?php
// Set error logging
ini_set('log_errors', 1);
ini_set('error_log', 'db_error.log');
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    // Database connection parameters
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "dentist";

    error_log("Attempting to connect to MySQL server");

    // Test if MySQL service is running
    $socket = @fsockopen($servername, 3306, $errno, $errstr, 5);
    if (!$socket) {
        throw new Exception("MySQL server is not running. Error: $errstr ($errno)");
    }
    fclose($socket);
    error_log("MySQL server is running");

    // First connect without database
    $conn = new mysqli($servername, $username, $password);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    error_log("Connected to MySQL server successfully");

    // Create database if not exists
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    if (!$conn->query($sql)) {
        throw new Exception("Error creating database: " . $conn->error);
    }

    error_log("Database created/verified successfully");

    // Select the database
    if (!$conn->select_db($dbname)) {
        throw new Exception("Error selecting database: " . $conn->error);
    }

    error_log("Database selected successfully");

    // Create emergency_requests table if not exists
    $create_table = "CREATE TABLE IF NOT EXISTS emergency_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        emergency_type VARCHAR(50) NOT NULL,
        message TEXT NOT NULL,
        status ENUM('pending', 'contacted', 'resolved') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (!$conn->query($create_table)) {
        throw new Exception("Error creating emergency_requests table: " . $conn->error);
    }

    error_log("Emergency requests table created/verified successfully");
    
    // Set charset
    if (!$conn->set_charset("utf8mb4")) {
        throw new Exception("Error setting charset: " . $conn->error);
    }

    error_log("Database connection and setup completed successfully");

} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    error_log("Error trace: " . $e->getTraceAsString());
    
    // For API requests, return JSON error
    if (strpos($_SERVER['REQUEST_URI'], 'process_emergency.php') !== false) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Database connection error. Please try again later or call our emergency number.',
            'debug' => $e->getMessage()
        ]);
        exit;
    }
    
    // For regular pages, show a user-friendly message
    die("An error occurred while connecting to the database. Please try again later or call our emergency number.");
}
?>
