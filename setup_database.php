<?php
require_once "conn.php";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS dentist_db";
    if ($conn->query($sql) === TRUE) {
        echo "Database created successfully or already exists<br>";
    } else {
        throw new Exception("Error creating database: " . $conn->error);
    }

    // Select the database
    $conn->select_db("dentist_db");

    // Read and execute the SQL from create_tables.sql
    $sql = file_get_contents('create_tables.sql');
    
    // Execute multiple SQL statements
    if ($conn->multi_query($sql)) {
        do {
            // Store first result set
            if ($result = $conn->store_result()) {
                $result->free();
            }
            // Prepare next result set
        } while ($conn->more_results() && $conn->next_result());
        
        echo "Tables created successfully<br>";
    } else {
        throw new Exception("Error creating tables: " . $conn->error);
    }

    echo "Database setup completed successfully!";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

$conn->close();
?> 