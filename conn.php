<?php

$server = "localhost";
$username = "root";
$password = ""; // Keep it empty if you're using XAMPP
$dbname = "dentist";

try {
    $conn = new mysqli($server, $username, $password, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Set charset to ensure proper handling of special characters
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    // Log error but don't expose details to user
    error_log("Database connection error: " . $e->getMessage());
    die("Could not connect to the database. Please try again later.");
}

?>
