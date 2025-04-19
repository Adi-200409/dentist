<?php
require_once 'conn.php';

// Create settings table
$sql = "CREATE TABLE IF NOT EXISTS settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    working_hours_start TIME NOT NULL DEFAULT '09:00:00',
    working_hours_end TIME NOT NULL DEFAULT '21:00:00',
    appointment_duration INT NOT NULL DEFAULT 30,
    max_appointments_per_day INT NOT NULL DEFAULT 20,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    // Check if we need to insert default settings
    $result = $conn->query("SELECT id FROM settings WHERE id = 1");
    if ($result->num_rows == 0) {
        // Insert default settings
        $sql = "INSERT INTO settings (id, working_hours_start, working_hours_end, appointment_duration, max_appointments_per_day) 
                VALUES (1, '09:00:00', '21:00:00', 30, 20)";
        if ($conn->query($sql) === TRUE) {
            echo "Settings table created and default settings inserted successfully";
        } else {
            echo "Error inserting default settings: " . $conn->error;
        }
    } else {
        echo "Settings table already exists with default settings";
    }
} else {
    echo "Error creating settings table: " . $conn->error;
}

$conn->close();
?> 