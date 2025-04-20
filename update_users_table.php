<?php
require_once 'conn.php';

try {
    // Add favorite_number column to users table
    $alter_table = "ALTER TABLE users 
        ADD COLUMN favorite_number VARCHAR(10) NOT NULL AFTER password";
    
    if ($conn->query($alter_table)) {
        echo "Favorite number column added successfully";
    } else {
        throw new Exception("Error adding favorite number column: " . $conn->error);
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} finally {
    $conn->close();
}
?> 