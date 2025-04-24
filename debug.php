<?php
// Debug script to help identify database and functionality issues
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Debug Information</h1>";

// PHP Information
echo "<h2>PHP & Server Information</h2>";
echo "<ul>";
echo "<li>PHP Version: " . phpversion() . "</li>";
echo "<li>Server: " . $_SERVER['SERVER_SOFTWARE'] . "</li>";
echo "<li>Database Extension: " . (extension_loaded('mysqli') ? 'mysqli is loaded' : 'mysqli NOT loaded') . "</li>";
echo "</ul>";

// Database Connection Test
echo "<h2>Database Connection Test</h2>";
try {
    // Database connection parameters
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "dentist";
    
    // Test MySQL Server Connection
    echo "<h3>Step 1: Testing Basic MySQL Server Connection</h3>";
    $connection_test = @mysqli_connect($servername, $username, $password);
    
    if (!$connection_test) {
        echo "<p style='color:red'>❌ Cannot connect to MySQL server. Error: " . mysqli_connect_error() . "</p>";
        echo "<ul>";
        echo "<li>Check if MySQL service is running in XAMPP</li>";
        echo "<li>Verify username and password are correct</li>";
        echo "</ul>";
    } else {
        echo "<p style='color:green'>✅ Successfully connected to MySQL server</p>";
        
        // Test Database Selection
        echo "<h3>Step 2: Testing Database Selection</h3>";
        
        // Check if database exists
        $db_exists = mysqli_select_db($connection_test, $dbname);
        
        if (!$db_exists) {
            echo "<p style='color:orange'>⚠️ Database '$dbname' does not exist. Attempting to create it...</p>";
            
            // Try to create the database
            if (mysqli_query($connection_test, "CREATE DATABASE IF NOT EXISTS $dbname")) {
                echo "<p style='color:green'>✅ Database '$dbname' created successfully</p>";
                $db_exists = true;
            } else {
                echo "<p style='color:red'>❌ Failed to create database. Error: " . mysqli_error($connection_test) . "</p>";
            }
        } else {
            echo "<p style='color:green'>✅ Database '$dbname' exists</p>";
        }
        
        // If database exists or was created, check tables
        if ($db_exists) {
            echo "<h3>Step 3: Testing Tables</h3>";
            
            // Select the database
            mysqli_select_db($connection_test, $dbname);
            
            // Check if users table exists
            $table_result = mysqli_query($connection_test, "SHOW TABLES LIKE 'users'");
            
            if (mysqli_num_rows($table_result) === 0) {
                echo "<p style='color:orange'>⚠️ Table 'users' does not exist. Attempting to create it...</p>";
                
                // Create users table
                $create_table_sql = "CREATE TABLE users (
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
                
                if (mysqli_query($connection_test, $create_table_sql)) {
                    echo "<p style='color:green'>✅ Table 'users' created successfully</p>";
                } else {
                    echo "<p style='color:red'>❌ Failed to create table. Error: " . mysqli_error($connection_test) . "</p>";
                }
            } else {
                echo "<p style='color:green'>✅ Table 'users' exists</p>";
                
                // Check fields in users table
                echo "<h4>Users Table Structure:</h4>";
                
                $fields_result = mysqli_query($connection_test, "DESCRIBE users");
                
                echo "<table border='1' cellpadding='5' style='border-collapse: collapse'>";
                echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
                
                while ($row = mysqli_fetch_assoc($fields_result)) {
                    echo "<tr>";
                    echo "<td>{$row['Field']}</td>";
                    echo "<td>{$row['Type']}</td>";
                    echo "<td>{$row['Null']}</td>";
                    echo "<td>{$row['Key']}</td>";
                    echo "<td>{$row['Default']}</td>";
                    echo "<td>{$row['Extra']}</td>";
                    echo "</tr>";
                }
                
                echo "</table>";
                
                // Check if email field exists
                $email_check = mysqli_query($connection_test, "SHOW COLUMNS FROM users LIKE 'email'");
                
                if (mysqli_num_rows($email_check) === 0) {
                    echo "<p style='color:orange'>⚠️ Email field missing in users table. Attempting to add it...</p>";
                    
                    // Add email field
                    if (mysqli_query($connection_test, "ALTER TABLE users ADD COLUMN email VARCHAR(100) AFTER phone")) {
                        echo "<p style='color:green'>✅ Added email field to users table</p>";
                    } else {
                        echo "<p style='color:red'>❌ Failed to add email field. Error: " . mysqli_error($connection_test) . "</p>";
                    }
                }
            }
        }
        
        mysqli_close($connection_test);
    }
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Error: " . $e->getMessage() . "</p>";
}

// Check if conn.php exists and content
echo "<h2>Connection File Check</h2>";
if (file_exists('conn.php')) {
    echo "<p style='color:green'>✅ conn.php file exists</p>";
    
    // Check content safely (without executing)
    $conn_content = htmlspecialchars(file_get_contents('conn.php'));
    echo "<details>";
    echo "<summary>View conn.php content</summary>";
    echo "<pre style='background-color: #f0f0f0; padding: 10px; max-height: 300px; overflow: auto;'>";
    echo $conn_content;
    echo "</pre>";
    echo "</details>";
} else {
    echo "<p style='color:red'>❌ conn.php file does not exist</p>";
}

// Error logs check
echo "<h2>Error Log Check</h2>";
$error_logs = [
    'db_error.log', 
    'error.log', 
    'register_error.log'
];

foreach ($error_logs as $log_file) {
    if (file_exists($log_file)) {
        echo "<h3>$log_file</h3>";
        echo "<p>File size: " . round(filesize($log_file) / 1024, 2) . " KB</p>";
        
        $log_content = file_get_contents($log_file);
        $last_log_entries = array_slice(explode("\n", $log_content), -15);
        
        echo "<details>";
        echo "<summary>View last 15 log entries</summary>";
        echo "<pre style='background-color: #f0f0f0; padding: 10px; max-height: 300px; overflow: auto;'>";
        echo htmlspecialchars(implode("\n", $last_log_entries));
        echo "</pre>";
        echo "</details>";
    } else {
        echo "<h3>$log_file</h3>";
        echo "<p>File does not exist or is empty</p>";
    }
}

// Fix Button
echo "<h2>Fix Database Connection</h2>";
echo "<p>Click the button below to use simplified connection file:</p>";

echo "<form method='post'>";
echo "<input type='submit' name='fix_connection' value='Use Simplified Connection File' style='padding: 10px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;'>";
echo "</form>";

// If fix button clicked, replace conn.php with conn_simple.php
if (isset($_POST['fix_connection']) && file_exists('conn_simple.php')) {
    if (copy('conn_simple.php', 'conn.php')) {
        echo "<p style='color:green'>✅ Successfully replaced conn.php with simplified version</p>";
        echo "<p>Try your login/registration again now!</p>";
    } else {
        echo "<p style='color:red'>❌ Failed to replace conn.php file</p>";
    }
}

echo "<p><a href='login.html' style='padding: 10px; background-color: #28a745; color: white; text-decoration: none; border-radius: 4px; display: inline-block;'>Return to Login Page</a></p>";
?> 