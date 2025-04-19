<?php
require_once 'conn.php';

try {
    // Drop the existing table to start fresh
    $conn->query("DROP TABLE IF EXISTS emergency_requests");
    
    // Create the table with all necessary columns
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
    
    if (!$conn->query($create_table)) {
        throw new Exception("Error creating emergency_requests table: " . $conn->error);
    }
    
    echo "Emergency requests table updated successfully";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

$conn->close();
?> 