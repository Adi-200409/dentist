<?php
require_once 'conn.php';

try {
    // Drop existing table if needed (comment this out if you don't want to drop the table)
    // $conn->query("DROP TABLE IF EXISTS emergency_requests");
    
    // Create emergency_requests table if not exists
    $sql = "CREATE TABLE IF NOT EXISTS emergency_requests (
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
    
    if ($conn->query($sql)) {
        echo "Emergency requests table created or already exists<br>";
        
        // Add sample emergency request for testing if the table is empty
        $check = $conn->query("SELECT COUNT(*) as count FROM emergency_requests");
        $row = $check->fetch_assoc();
        
        if ($row['count'] == 0) {
            $insert = $conn->prepare("INSERT INTO emergency_requests (name, phone, location, issue, urgency) VALUES (?, ?, ?, ?, ?)");
            $name = "Test Patient";
            $phone = "9876543210";
            $location = "123 Test Street, Test City";
            $issue = "Severe tooth pain";
            $urgency = "High";
            
            $insert->bind_param("sssss", $name, $phone, $location, $issue, $urgency);
            
            if ($insert->execute()) {
                echo "Sample emergency request added for testing<br>";
            } else {
                echo "Error adding sample data: " . $insert->error . "<br>";
            }
            
            $insert->close();
        } else {
            echo "Table already has " . $row['count'] . " records<br>";
        }
    } else {
        echo "Error creating table: " . $conn->error . "<br>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

$conn->close();
echo "Done!";
?> 