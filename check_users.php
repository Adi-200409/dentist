<?php
require_once 'conn.php';

// Output as plain text
header('Content-Type: text/plain');

echo "=== USERS TABLE CHECK ===\n\n";

try {
    // Check if table exists
    $tableExists = $conn->query("SHOW TABLES LIKE 'users'");
    if ($tableExists->num_rows === 0) {
        echo "ERROR: Users table does not exist!\n";
        exit;
    }
    
    // Get table structure
    echo "TABLE STRUCTURE:\n";
    $structure = $conn->query("DESCRIBE users");
    
    while ($col = $structure->fetch_assoc()) {
        echo "- {$col['Field']} ({$col['Type']})" . 
             ($col['Null'] === 'NO' ? ' NOT NULL' : '') . 
             ($col['Key'] === 'PRI' ? ' PRIMARY KEY' : '') . 
             ($col['Key'] === 'UNI' ? ' UNIQUE' : '') . 
             ($col['Default'] ? " DEFAULT '{$col['Default']}'" : '') . 
             "\n";
    }
    
    echo "\nTABLE CONTENT:\n";
    $result = $conn->query("SELECT * FROM users");
    
    if ($result->num_rows === 0) {
        echo "No users found in the database.\n";
    } else {
        while ($row = $result->fetch_assoc()) {
            echo "User ID: {$row['id']}\n";
            echo "  Name: {$row['name']}\n";
            echo "  Phone: {$row['phone']}\n";
            
            // Check if favorite_number column exists
            if (isset($row['favorite_number'])) {
                echo "  Favorite Number: " . ($row['favorite_number'] ? $row['favorite_number'] : 'Not set') . "\n";
            }
            
            echo "  Role: {$row['role']}\n";
            echo "  Created: {$row['created_at']}\n";
            echo "-----------------------------------\n";
        }
    }
    
    echo "\nTOTAL USERS: " . $result->num_rows . "\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}

echo "\n=== CHECK COMPLETE ===\n";
?> 