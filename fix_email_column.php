<?php
// Simple script to add email column to users table
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Adding Email Column to Users Table</h1>";

// Connect directly to MySQL
$conn = new mysqli("localhost", "root", "", "dentist");

// Check connection
if ($conn->connect_error) {
    die("<p style='color:red'>Connection failed: " . $conn->connect_error . "</p>");
}

echo "<p>Connected to database successfully</p>";

// Check if the users table exists
$table_check = $conn->query("SHOW TABLES LIKE 'users'");
if ($table_check->num_rows == 0) {
    // Create users table if it doesn't exist
    $create_table = "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        phone VARCHAR(15) NOT NULL,
        email VARCHAR(100),
        password VARCHAR(255) NOT NULL,
        role ENUM('user', 'admin') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_phone (phone)
    )";
    
    if ($conn->query($create_table)) {
        echo "<p style='color:green'>Created users table successfully</p>";
    } else {
        echo "<p style='color:red'>Error creating users table: " . $conn->error . "</p>";
    }
} else {
    echo "<p>Users table exists</p>";
    
    // Check if email column exists
    $column_check = $conn->query("SHOW COLUMNS FROM users LIKE 'email'");
    if ($column_check->num_rows == 0) {
        // Add email column
        $add_column = "ALTER TABLE users ADD COLUMN email VARCHAR(100) AFTER phone";
        if ($conn->query($add_column)) {
            echo "<p style='color:green'>Successfully added email column</p>";
            
            // Check if index exists
            $index_check = $conn->query("SHOW INDEX FROM users WHERE Key_name = 'unique_email'");
            if ($index_check->num_rows == 0) {
                // Add unique index
                $add_index = "ALTER TABLE users ADD UNIQUE KEY unique_email (email)";
                if ($conn->query($add_index)) {
                    echo "<p style='color:green'>Successfully added unique email index</p>";
                } else {
                    echo "<p style='color:orange'>Warning: Could not add unique index: " . $conn->error . "</p>";
                    echo "<p>This is okay if you have existing NULL values for email</p>";
                }
            } else {
                echo "<p>Email index already exists</p>";
            }
        } else {
            echo "<p style='color:red'>Error adding email column: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color:green'>Email column already exists</p>";
    }
}

// Close connection
$conn->close();

echo "<p><strong>DONE!</strong> Now try registering with an email address.</p>";
echo "<p><a href='login.html'>Return to login page</a></p>";
?> 