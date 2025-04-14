<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$server = "localhost";
$username = "root";
$password = ""; // Keep it empty if you're using XAMPP
$dbname = "dentist_db"; // Database name

try {
    // Create connection without database name first
    $conn = new mysqli($server, $username, $password);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    if (!$conn->query($sql)) {
        throw new Exception("Error creating database: " . $conn->error);
    }

    // Select the database
    if (!$conn->select_db($dbname)) {
        throw new Exception("Error selecting database: " . $conn->error);
    }

    // Set charset
    if (!$conn->set_charset("utf8mb4")) {
        throw new Exception("Error setting charset: " . $conn->error);
    }

    // Create users table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        reset_otp VARCHAR(6),
        otp_expiry DATETIME,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    if (!$conn->query($sql)) {
        throw new Exception("Error creating users table: " . $conn->error);
    }

    error_log("Database connection successful");
    
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("Could not connect to the database. Please try again later.");
}
?>
