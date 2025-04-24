<?php
// Set error logging
ini_set('log_errors', 1);
ini_set('error_log', 'db_error.log');
error_reporting(E_ALL);

// Only set display_errors to 0 in production
$display_errors = true;  // Set to false in production
ini_set('display_errors', $display_errors ? 1 : 0);

// Function to log with timestamp and better formatting
function logMessage($message, $level = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    error_log("[$timestamp] [$level] $message");
}

try {
    // Database connection parameters
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "dentist";

    logMessage("Attempting to connect to MySQL server");

    // Test if MySQL service is running
    $socket = @fsockopen($servername, 3306, $errno, $errstr, 5);
    if (!$socket) {
        throw new Exception("MySQL server is not running or not accessible. Error: $errstr ($errno)");
    }
    fclose($socket);
    logMessage("MySQL server is running");

    // First connect without database
    $conn = new mysqli($servername, $username, $password);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    logMessage("Connected to MySQL server successfully");

    // Check if database exists
    $result = $conn->query("SHOW DATABASES LIKE '$dbname'");
    if ($result && $result->num_rows === 0) {
        logMessage("Database '$dbname' does not exist, attempting to create it", "WARN");
    }

    // Create database if not exists
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    if (!$conn->query($sql)) {
        throw new Exception("Error creating database: " . $conn->error);
    }

    logMessage("Database created/verified successfully");

    // Select the database
    if (!$conn->select_db($dbname)) {
        throw new Exception("Error selecting database: " . $conn->error);
    }

    logMessage("Database selected successfully");

    // Create users table if not exists with all required fields
    $create_users_table = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        phone VARCHAR(15) NOT NULL,
        password VARCHAR(255) NOT NULL,
        favorite_number INT,
        role ENUM('user', 'admin') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_phone (phone)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (!$conn->query($create_users_table)) {
        throw new Exception("Error creating users table: " . $conn->error);
    }

    logMessage("Users table created/verified successfully");

    // Set charset
    if (!$conn->set_charset("utf8mb4")) {
        throw new Exception("Error setting charset: " . $conn->error);
    }

    logMessage("Database connection and setup completed successfully");

} catch (Exception $e) {
    logMessage("DATABASE ERROR: " . $e->getMessage(), "ERROR");
    logMessage("Error trace: " . $e->getTraceAsString(), "ERROR");
    
    // For API requests, return JSON error
    if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false 
        || isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false
        || strpos($_SERVER['REQUEST_URI'], '.php') !== false) {
        
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Database connection error. Please try again later.',
            'debug' => $display_errors ? $e->getMessage() : null
        ]);
        exit;
    }
    
    // For regular pages, show a user-friendly message
    if ($display_errors) {
        echo "<div style='padding: 20px; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; margin: 20px;'>";
        echo "<h2 style='color: #721c24;'>Database Error</h2>";
        echo "<p>An error occurred while connecting to the database:</p>";
        echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
        echo "<p>Check the following:</p>";
        echo "<ul>";
        echo "<li>Is the XAMPP MySQL service running?</li>";
        echo "<li>Are the database credentials correct in conn.php?</li>";
        echo "<li>Does the 'dentist' database exist?</li>";
        echo "</ul>";
        echo "<p>For more details, check the db_error.log file.</p>";
        echo "</div>";
    } else {
        echo "<div style='padding: 20px; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; margin: 20px;'>";
        echo "<h2 style='color: #721c24;'>Database Error</h2>";
        echo "<p>A database error occurred. Please try again later or contact support.</p>";
        echo "</div>";
    }
    exit;
}
?> 