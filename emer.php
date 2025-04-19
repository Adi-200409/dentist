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

    // Check if table exists, if not create it with the correct structure
    $check_table = "SHOW TABLES LIKE 'emergency_requests'";
    $table_exists = mysqli_query($conn, $check_table);
    
    if (mysqli_num_rows($table_exists) == 0) {
        // Create table with the correct structure
        $create_table = "CREATE TABLE emergency_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            phone VARCHAR(15) NOT NULL,
            location TEXT NOT NULL,
            issue TEXT NOT NULL,
            urgency VARCHAR(50) NOT NULL,
            status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        if (!mysqli_query($conn, $create_table)) {
            throw new Exception("Error creating table: " . mysqli_error($conn));
        }
    }

    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get and sanitize form data
        $name = mysqli_real_escape_string($conn, $_POST['name'] ?? '');
        $phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
        $issue = mysqli_real_escape_string($conn, $_POST['issue'] ?? '');
        $urgency = mysqli_real_escape_string($conn, $_POST['urgency'] ?? '');
        $location = mysqli_real_escape_string($conn, $_POST['location'] ?? 'Not specified');
        
        // Validate fields
        if (empty($name) || empty($phone) || empty($issue) || empty($urgency)) {
            throw new Exception("All fields are required");
        }

        // Use prepared statement for security
        $stmt = mysqli_prepare($conn, "INSERT INTO emergency_requests (name, phone, location, issue, urgency) VALUES (?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt, "sssss", $name, $phone, $location, $issue, $urgency);
        
        if (mysqli_stmt_execute($stmt)) {
            $response = [
                'status' => 'success',
                'message' => 'Emergency request submitted successfully',
                'id' => mysqli_insert_id($conn)
            ];
        } else {
            throw new Exception("Error saving data: " . mysqli_stmt_error($stmt));
        }
        
        mysqli_stmt_close($stmt);
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