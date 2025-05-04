<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'test_emergency_error.log');

require_once 'conn.php';

try {
    echo "<h2>Creating Test Emergency Data</h2>";
    
    // Check database connection
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection error: " . ($conn->connect_error ?? "Connection not established"));
    }
    
    // Check if the table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'emergency_requests'");
    if ($tableCheck->num_rows == 0) {
        echo "<p>Table 'emergency_requests' does not exist. Creating it...</p>";
        
        // Create the table
        $createTable = "CREATE TABLE IF NOT EXISTS emergency_requests (
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
        
        if (!$conn->query($createTable)) {
            throw new Exception("Failed to create table: " . $conn->error);
        }
        
        echo "<p>Table created successfully!</p>";
    } else {
        echo "<p>Table 'emergency_requests' already exists.</p>";
    }
    
    // Sample emergency requests
    $emergencies = [
        [
            'name' => 'Ezekiel Rodgers',
            'phone' => '+1 (717) 424-46',
            'location' => 'Nulla obcaecati quib, Asperiores a sit dol',
            'issue' => 'Severe toothache and swelling in the left side of mouth. Patient reports pain level 9/10.',
            'urgency' => 'Severe Pain',
            'status' => 'pending'
        ],
        [
            'name' => 'Sarah Johnson',
            'phone' => '+1 (555) 123-4567',
            'location' => '123 Main St, Apartment 4B',
            'issue' => 'Broken front tooth after falling on stairs. Visible chip and bleeding gums.',
            'urgency' => 'Broken Tooth',
            'status' => 'in_progress'
        ],
        [
            'name' => 'Michael Davis',
            'phone' => '+1 (555) 987-6543',
            'location' => '456 Oak Avenue',
            'issue' => 'Lost filling and experiencing sharp pain when drinking cold liquids.',
            'urgency' => 'Other',
            'status' => 'completed'
        ],
        [
            'name' => 'Jennifer Wilson',
            'phone' => '+1 (555) 345-6789',
            'location' => '789 Pine Street',
            'issue' => 'Wisdom tooth pain and swelling for 2 days. Difficulty opening mouth fully.',
            'urgency' => 'Swelling',
            'status' => 'cancelled'
        ]
    ];
    
    // Prepare insert statement
    $stmt = $conn->prepare("
        INSERT INTO emergency_requests 
            (name, phone, location, issue, urgency, status)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }
    
    // Insert each emergency
    $insertCount = 0;
    foreach ($emergencies as $emergency) {
        $stmt->bind_param(
            "ssssss", 
            $emergency['name'], 
            $emergency['phone'], 
            $emergency['location'], 
            $emergency['issue'], 
            $emergency['urgency'], 
            $emergency['status']
        );
        
        if ($stmt->execute()) {
            $insertCount++;
            echo "<p>Inserted emergency request for {$emergency['name']} (ID: {$stmt->insert_id})</p>";
        } else {
            echo "<p>Failed to insert emergency for {$emergency['name']}: {$stmt->error}</p>";
        }
    }
    
    echo "<h3>Inserted $insertCount emergency requests</h3>";
    echo "<p><a href='emergency-details.php?id=1'>View first emergency</a></p>";
    echo "<p><a href='get_emergencies.php'>View all emergencies (JSON)</a></p>";
    
    $stmt->close();
    
} catch (Exception $e) {
    echo "<div style='color:red; padding:10px; background-color:#ffe0e0; border:1px solid #900;'>";
    echo "<h3>Error:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
    
    error_log("Error in create_test_emergency.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
}

// Close connection
if (isset($conn)) {
    $conn->close();
}
?> 