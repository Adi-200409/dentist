<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once 'conn.php';

// Check database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the emergency_requests table exists
$check_table = "SHOW TABLES LIKE 'emergency_requests'";
$table_exists = $conn->query($check_table);

if ($table_exists->num_rows == 0) {
    echo "Emergency requests table does not exist. Creating it now...<br>";
    
    // Create the table
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
    )";
    
    if (!$conn->query($create_table)) {
        die("Error creating table: " . $conn->error);
    }
    echo "Table created successfully.<br>";
}

// Insert a test emergency request
$test_name = "Test Patient";
$test_phone = "1234567890";
$test_location = "123 Test Street, Test City";
$test_issue = "This is a test emergency request";
$test_urgency = "Severe Pain";

$stmt = $conn->prepare("INSERT INTO emergency_requests (name, phone, location, issue, urgency) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $test_name, $test_phone, $test_location, $test_issue, $test_urgency);

if ($stmt->execute()) {
    echo "Test emergency request inserted successfully. ID: " . $stmt->insert_id . "<br>";
} else {
    echo "Error inserting test emergency request: " . $stmt->error . "<br>";
}

$stmt->close();

// Display all emergency requests
echo "<h2>All Emergency Requests</h2>";
$result = $conn->query("SELECT * FROM emergency_requests ORDER BY created_at DESC");

if ($result->num_rows > 0) {
    echo "<table border='1'>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Phone</th>
        <th>Location</th>
        <th>Issue</th>
        <th>Urgency</th>
        <th>Status</th>
        <th>Created At</th>
    </tr>";
    
    while($row = $result->fetch_assoc()) {
        echo "<tr>
            <td>" . $row["id"] . "</td>
            <td>" . $row["name"] . "</td>
            <td>" . $row["phone"] . "</td>
            <td>" . $row["location"] . "</td>
            <td>" . $row["issue"] . "</td>
            <td>" . $row["urgency"] . "</td>
            <td>" . $row["status"] . "</td>
            <td>" . $row["created_at"] . "</td>
        </tr>";
    }
    echo "</table>";
} else {
    echo "No emergency requests found.";
}

$conn->close();
?> 