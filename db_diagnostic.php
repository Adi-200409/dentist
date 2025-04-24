<?php
// Set to display all errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Database Diagnostic Tool</h1>";
echo "<p>This tool will help diagnose database connection issues.</p>";

// Server information
echo "<h2>Server Information</h2>";
echo "<pre>";
echo "PHP Version: " . phpversion() . "\n";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "Operating System: " . PHP_OS . "\n";
echo "</pre>";

// XAMPP MySQL Service Check
echo "<h2>MySQL Service Check</h2>";
$servername = "localhost";
$socket = @fsockopen($servername, 3306, $errno, $errstr, 5);
if (!$socket) {
    echo "<p style='color:red'>⚠️ MySQL server is not running. Error: $errstr ($errno)</p>";
    echo "<p>Try the following:</p>";
    echo "<ol>";
    echo "<li>Check if XAMPP/MySQL service is running</li>";
    echo "<li>Start/restart XAMPP MySQL service from XAMPP Control Panel</li>";
    echo "<li>Check if another MySQL service is using port 3306</li>";
    echo "</ol>";
} else {
    echo "<p style='color:green'>✅ MySQL server is running</p>";
    fclose($socket);
}

// Database connection attempt
echo "<h2>Database Connection Test</h2>";
try {
    // Database connection parameters
    $username = "root";
    $password = "";
    $dbname = "dentist";
    
    // First try connecting without database selection
    echo "<p>Attempting basic connection without database selection...</p>";
    $conn = new mysqli($servername, $username, $password);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "<p style='color:green'>✅ Basic connection successful</p>";
    
    // Check if database exists
    echo "<p>Checking if database '$dbname' exists...</p>";
    $result = $conn->query("SHOW DATABASES LIKE '$dbname'");
    
    if ($result && $result->num_rows > 0) {
        echo "<p style='color:green'>✅ Database '$dbname' exists</p>";
    } else {
        echo "<p style='color:orange'>⚠️ Database '$dbname' does not exist. Attempting to create it...</p>";
        
        // Try to create the database
        if ($conn->query("CREATE DATABASE IF NOT EXISTS $dbname")) {
            echo "<p style='color:green'>✅ Database '$dbname' created successfully</p>";
        } else {
            throw new Exception("Error creating database: " . $conn->error);
        }
    }
    
    // Try to select the database
    echo "<p>Attempting to select database '$dbname'...</p>";
    if ($conn->select_db($dbname)) {
        echo "<p style='color:green'>✅ Database selection successful</p>";
    } else {
        throw new Exception("Error selecting database: " . $conn->error);
    }
    
    // Check if required tables exist
    echo "<h2>Table Checks</h2>";
    $requiredTables = ['users', 'emergency_requests', 'appointments'];
    
    foreach ($requiredTables as $table) {
        echo "<p>Checking if table '$table' exists...</p>";
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        
        if ($result && $result->num_rows > 0) {
            echo "<p style='color:green'>✅ Table '$table' exists</p>";
            
            // Show table structure
            $structure = $conn->query("DESCRIBE $table");
            if ($structure) {
                echo "<details>";
                echo "<summary>Table structure</summary>";
                echo "<table border='1' cellpadding='5' style='border-collapse: collapse'>";
                echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
                
                while ($row = $structure->fetch_assoc()) {
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
                echo "</details>";
            }
        } else {
            echo "<p style='color:orange'>⚠️ Table '$table' does not exist</p>";
        }
    }
    
    // Check error log
    echo "<h2>Error Log Check</h2>";
    $errorLogFile = 'db_error.log';
    if (file_exists($errorLogFile)) {
        echo "<p>Last 10 lines of error log:</p>";
        echo "<pre style='background-color: #f8f8f8; padding: 10px; max-height: 300px; overflow: auto;'>";
        $log = file($errorLogFile);
        $lastLines = array_slice($log, -10);
        foreach ($lastLines as $line) {
            echo htmlspecialchars($line);
        }
        echo "</pre>";
    } else {
        echo "<p>No error log file found at: $errorLogFile</p>";
    }
    
    echo "<h2>Conclusion</h2>";
    echo "<p style='color:green'>✅ All database tests passed successfully. If you're still experiencing issues, please check your application code for specific errors.</p>";
    
} catch (Exception $e) {
    echo "<p style='color:red'>⚠️ Error: " . $e->getMessage() . "</p>";
    echo "<h2>Troubleshooting Steps</h2>";
    echo "<ol>";
    echo "<li>Verify XAMPP MySQL service is running</li>";
    echo "<li>Check MySQL username and password in conn.php</li>";
    echo "<li>Make sure no other MySQL server is running on the same port</li>";
    echo "<li>Check permissions for the MySQL user</li>";
    echo "<li>Verify the database name is correct</li>";
    echo "</ol>";
    
    echo "<h2>Error Details</h2>";
    echo "<pre style='background-color: #fff0f0; padding: 10px;'>";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString();
    echo "</pre>";
}
?> 