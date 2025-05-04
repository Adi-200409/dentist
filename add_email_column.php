<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connect to database
require_once 'conn.php';

echo "<h1>Add Email Column to Users Table</h1>";

// Check if email column already exists
$result = $conn->query("SHOW COLUMNS FROM users LIKE 'email'");
$exists = ($result->num_rows > 0);

if ($exists) {
    echo "<p>Email column already exists in users table.</p>";
} else {
    // Add email column
    $sql = "ALTER TABLE users ADD COLUMN email VARCHAR(255) AFTER phone";
    
    if ($conn->query($sql) === TRUE) {
        echo "<p>Email column added successfully.</p>";
        
        // Update existing users with dummy email
        $updateSql = "UPDATE users SET email = CONCAT(name, '_', id, '@example.com') WHERE email IS NULL";
        if ($conn->query($updateSql) === TRUE) {
            echo "<p>Default email values added to existing users.</p>";
        } else {
            echo "<p>Error updating users with default email: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>Error adding email column: " . $conn->error . "</p>";
    }
}

// Show the updated table structure
echo "<h2>Users Table Structure</h2>";
$result = $conn->query("DESCRIBE users");

if ($result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["Field"] . "</td>";
        echo "<td>" . $row["Type"] . "</td>";
        echo "<td>" . $row["Null"] . "</td>";
        echo "<td>" . $row["Key"] . "</td>";
        echo "<td>" . $row["Default"] . "</td>";
        echo "<td>" . $row["Extra"] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>No results</p>";
}

$conn->close();
?> 