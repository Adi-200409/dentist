<?php
// Start session
session_start();

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log details to a separate file
$log_file = 'debug_error.log';
file_put_contents($log_file, "===== Debug started at " . date('Y-m-d H:i:s') . " =====\n", FILE_APPEND);

// Log session info
file_put_contents($log_file, "SESSION:\n" . print_r($_SESSION, true) . "\n", FILE_APPEND);

// Get and log details about the users table
require_once 'conn.php';

file_put_contents($log_file, "Checking database connection...\n", FILE_APPEND);
if ($conn->connect_error) {
    file_put_contents($log_file, "Connection failed: " . $conn->connect_error . "\n", FILE_APPEND);
    echo "Connection failed: " . $conn->connect_error;
    exit;
}

file_put_contents($log_file, "Connection successful\n", FILE_APPEND);

// Get table structure
file_put_contents($log_file, "Checking users table structure...\n", FILE_APPEND);
$result = $conn->query("DESCRIBE users");
if (!$result) {
    file_put_contents($log_file, "Error getting table structure: " . $conn->error . "\n", FILE_APPEND);
    echo "Error getting table structure: " . $conn->error;
} else {
    $columns = [];
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row;
    }
    file_put_contents($log_file, "Table structure:\n" . print_r($columns, true) . "\n", FILE_APPEND);
}

// Try to get the admin user info directly
file_put_contents($log_file, "Attempting to retrieve admin user...\n", FILE_APPEND);
$result = $conn->query("SELECT * FROM users WHERE role = 'admin' LIMIT 1");
if (!$result) {
    file_put_contents($log_file, "Error retrieving admin: " . $conn->error . "\n", FILE_APPEND);
    echo "Error retrieving admin: " . $conn->error;
} else {
    $admin = $result->fetch_assoc();
    // Remove sensitive info before logging
    if (isset($admin['password'])) {
        $admin['password'] = '[REDACTED]';
    }
    file_put_contents($log_file, "Admin user:\n" . print_r($admin, true) . "\n", FILE_APPEND);
}

file_put_contents($log_file, "===== Debug finished at " . date('Y-m-d H:i:s') . " =====\n\n", FILE_APPEND);

// Output to browser
echo "<h1>Debug Information</h1>";
echo "<p>Check the debug_error.log file for detailed information.</p>";

$conn->close();
?> 