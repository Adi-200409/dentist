<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'error.log');

require_once 'conn.php';

// Admin details
$name = "Admin User";
$phone = "9876543210"; // 10-digit phone number
$favorite_number = 42;
$password = "admin123"; // Plain password
$role = "admin";

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    // Check if admin already exists
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE phone = ? LIMIT 1");
    $check_stmt->bind_param("s", $phone);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "Admin user already exists with phone: $phone<br>";
        $user = $result->fetch_assoc();
        
        // Update the admin user
        $update_stmt = $conn->prepare("UPDATE users SET name = ?, favorite_number = ?, password = ?, role = ? WHERE id = ?");
        $update_stmt->bind_param("sissi", $name, $favorite_number, $hashed_password, $role, $user['id']);
        
        if ($update_stmt->execute()) {
            echo "Admin user updated successfully!<br>";
            echo "Phone: $phone<br>";
            echo "Password: admin123<br>";
            echo "Favorite Number: $favorite_number<br>";
        } else {
            echo "Error updating admin user: " . $conn->error . "<br>";
        }
    } else {
        // Insert new admin user
        $insert_stmt = $conn->prepare("INSERT INTO users (name, phone, favorite_number, password, role) VALUES (?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("ssiss", $name, $phone, $favorite_number, $hashed_password, $role);
        
        if ($insert_stmt->execute()) {
            echo "Admin user created successfully!<br>";
            echo "Phone: $phone<br>";
            echo "Password: admin123<br>";
            echo "Favorite Number: $favorite_number<br>";
        } else {
            echo "Error creating admin user: " . $conn->error . "<br>";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?> 