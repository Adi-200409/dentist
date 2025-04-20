<?php
require_once 'conn.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Users Table Verification</h2>";

try {
    // Check if users table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'users'");
    
    if ($tableCheck->num_rows == 0) {
        echo "<p>Users table doesn't exist. Creating it now...</p>";
        
        // Create users table
        $createTable = "CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            phone VARCHAR(15) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            favorite_number INT,
            role ENUM('user', 'admin') DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        if ($conn->query($createTable)) {
            echo "<p>Users table created successfully!</p>";
        } else {
            throw new Exception("Error creating users table: " . $conn->error);
        }
    } else {
        echo "<p>Users table exists. Checking structure...</p>";
        
        // Check if all required columns exist
        $requiredColumns = [
            'name' => 'VARCHAR(100)',
            'phone' => 'VARCHAR(15)',
            'password' => 'VARCHAR(255)',
            'favorite_number' => 'INT',
            'role' => 'ENUM'
        ];
        
        $missingColumns = [];
        $columnsResult = $conn->query("SHOW COLUMNS FROM users");
        $existingColumns = [];
        
        while ($column = $columnsResult->fetch_assoc()) {
            $existingColumns[$column['Field']] = $column['Type'];
        }
        
        echo "<p><strong>Current columns:</strong></p>";
        echo "<ul>";
        foreach ($existingColumns as $name => $type) {
            echo "<li>$name ($type)</li>";
        }
        echo "</ul>";
        
        foreach ($requiredColumns as $column => $type) {
            if (!isset($existingColumns[$column])) {
                $missingColumns[] = $column;
            }
        }
        
        if (!empty($missingColumns)) {
            echo "<p><strong>Missing columns:</strong> " . implode(', ', $missingColumns) . "</p>";
            
            // Add missing columns
            foreach ($missingColumns as $column) {
                $alterQuery = "";
                
                switch ($column) {
                    case 'favorite_number':
                        $alterQuery = "ALTER TABLE users ADD COLUMN favorite_number INT AFTER password";
                        break;
                    case 'phone':
                        $alterQuery = "ALTER TABLE users ADD COLUMN phone VARCHAR(15) NOT NULL AFTER name, ADD UNIQUE (phone)";
                        break;
                    case 'role':
                        $alterQuery = "ALTER TABLE users ADD COLUMN role ENUM('user', 'admin') DEFAULT 'user' AFTER favorite_number";
                        break;
                    case 'name':
                        $alterQuery = "ALTER TABLE users ADD COLUMN name VARCHAR(100) NOT NULL FIRST";
                        break;
                    case 'password':
                        $alterQuery = "ALTER TABLE users ADD COLUMN password VARCHAR(255) NOT NULL AFTER phone";
                        break;
                }
                
                if (!empty($alterQuery)) {
                    if ($conn->query($alterQuery)) {
                        echo "<p>Added missing column: $column</p>";
                    } else {
                        echo "<p>Error adding column $column: " . $conn->error . "</p>";
                    }
                }
            }
        } else {
            echo "<p>All required columns exist!</p>";
        }
    }
    
    // Check for admin users
    $adminCheck = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
    $adminCount = $adminCheck->fetch_assoc()['count'];
    
    echo "<p>Number of admin users: $adminCount</p>";
    
    if ($adminCount == 0) {
        echo "<p>No admin users found. You should run create_admin.php to create one.</p>";
    }
    
    // Show total number of users
    $userCount = $conn->query("SELECT COUNT(*) as count FROM users");
    $totalUsers = $userCount->fetch_assoc()['count'];
    
    echo "<p>Total users in database: $totalUsers</p>";
    
} catch (Exception $e) {
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
}

$conn->close();
echo "<p>Done!</p>";
?> 