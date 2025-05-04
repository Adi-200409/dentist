<?php
session_start();
require_once 'conn.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['name']) || !isset($data['phone'])) {
        throw new Exception('Name and phone are required');
    }

    // Check database connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    $user_id = $_SESSION['user_id'];
    $name = $data['name'];
    $phone = $data['phone'];
    
    // Check if email field exists in the users table
    $result = $conn->query("DESCRIBE users");
    $hasEmailField = false;
    while ($row = $result->fetch_assoc()) {
        if ($row['Field'] === 'email') {
            $hasEmailField = true;
            break;
        }
    }
    
    // Update admin profile
    if (isset($data['password']) && !empty($data['password'])) {
        // Update with password
        $password = password_hash($data['password'], PASSWORD_DEFAULT);
        
        if ($hasEmailField && isset($data['email'])) {
            $email = $data['email'];
            $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, email = ?, password = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $name, $phone, $email, $password, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, password = ? WHERE id = ?");
            $stmt->bind_param("sssi", $name, $phone, $password, $user_id);
        }
    } else {
        // Update without password
        if ($hasEmailField && isset($data['email'])) {
            $email = $data['email'];
            $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, email = ? WHERE id = ?");
            $stmt->bind_param("sssi", $name, $phone, $email, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
            $stmt->bind_param("ssi", $name, $phone, $user_id);
        }
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Error updating profile: " . $stmt->error);
    }
    
    // Return success response
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    
} catch (Exception $e) {
    // Log error and return error response
    error_log("Error in update_admin_profile.php: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to update profile', 
        'error' => $e->getMessage()
    ]);
}

if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
?> 