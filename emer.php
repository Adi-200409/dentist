<?php

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log all POST data
error_log("Raw POST data: " . file_get_contents("php://input"));
error_log("POST array: " . print_r($_POST, true));

// Add CORS headers for local development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

try {
    // Include database connection
    require_once "conn.php";

    // Check connection
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Create database if not exists
    mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS dentist");
    mysqli_select_db($conn, "dentist");

    // Create table if not exists
    $create_table = "CREATE TABLE IF NOT EXISTS emergency_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        phone VARCHAR(15) NOT NULL,
        issue TEXT NOT NULL,
        urgency VARCHAR(50) NOT NULL,
        submission_date DATETIME NOT NULL
    )";
    mysqli_query($conn, $create_table);

    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get and sanitize form data
        $name = mysqli_real_escape_string($conn, $_POST['name'] ?? '');
        $phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
        $issue = mysqli_real_escape_string($conn, $_POST['issue'] ?? '');
        $urgency = mysqli_real_escape_string($conn, $_POST['urgency'] ?? '');
        
        // Validate fields
        if (empty($name) || empty($phone) || empty($issue) || empty($urgency)) {
            throw new Exception("All fields are required");
        }

        // Insert data
        $sql = "INSERT INTO emergency_requests (name, phone, issue, urgency, submission_date) 
                VALUES ('$name', '$phone', '$issue', '$urgency', NOW())";

        if (mysqli_query($conn, $sql)) {
            $response = [
                'status' => 'success',
                'message' => 'Emergency request submitted successfully'
            ];
        } else {
            throw new Exception("Error saving data");
        }
    } else {
        throw new Exception("Invalid request method");
    }
} catch (Exception $e) {
    error_log("Error in emer.php: " . $e->getMessage());
    $response = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
}

// Send JSON response
echo json_encode($response);
exit;
?>