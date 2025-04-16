<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'db_error.log');

header('Content-Type: application/json');

try {
    // Database connection parameters
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "dentist";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Check if users table exists
    $check_table = "SHOW TABLES LIKE 'users'";
    $result = $conn->query($check_table);
    
    if ($result->num_rows === 0) {
        // Create users table if it doesn't exist
        $create_table = "CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            phone VARCHAR(15) NOT NULL,
            password VARCHAR(255) NOT NULL,
            reset_otp VARCHAR(6),
            otp_expiry DATETIME,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_phone (phone)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        if (!$conn->query($create_table)) {
            throw new Exception("Error creating users table: " . $conn->error);
        }
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Users table created successfully with phone column'
        ]);
    } else {
        // Check if phone column exists
        $check_column = "SHOW COLUMNS FROM users LIKE 'phone'";
        $result = $conn->query($check_column);
        
        if ($result->num_rows === 0) {
            // First, add the phone column without the UNIQUE constraint
            $add_column = "ALTER TABLE users ADD COLUMN phone VARCHAR(15) AFTER name";
            
            if (!$conn->query($add_column)) {
                throw new Exception("Error adding phone column: " . $conn->error);
            }
            
            // Update any NULL or empty phone values with a unique placeholder
            $update_empty = "UPDATE users SET phone = CONCAT('placeholder_', id) WHERE phone IS NULL OR phone = ''";
            
            if (!$conn->query($update_empty)) {
                throw new Exception("Error updating empty phone values: " . $conn->error);
            }
            
            // Now add the NOT NULL and UNIQUE constraints
            $add_constraints = "ALTER TABLE users 
                MODIFY COLUMN phone VARCHAR(15) NOT NULL,
                ADD UNIQUE KEY unique_phone (phone)";
            
            if (!$conn->query($add_constraints)) {
                throw new Exception("Error adding constraints to phone column: " . $conn->error);
            }
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Phone column added successfully with constraints'
            ]);
        } else {
            echo json_encode([
                'status' => 'success',
                'message' => 'Phone column already exists'
            ]);
        }
    }

} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?> 