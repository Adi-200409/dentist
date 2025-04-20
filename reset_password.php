<?php
session_start();
require_once "conn.php";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
    // Check if user is verified
    if (!isset($_SESSION['reset_user_id'])) {
        throw new Exception('Please verify your identity first');
    }

    // Get JSON data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['password'])) {
        throw new Exception('Missing password');
    }

    $password = $data['password'];
    $user_id = $_SESSION['reset_user_id'];

    // Validate password
    if (strlen($password) < 6) {
        throw new Exception('Password must be at least 6 characters long');
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Update password in database
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed_password, $user_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update password');
    }

    // Clear the reset session
    unset($_SESSION['reset_user_id']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Password reset successfully'
    ]);

} catch (Exception $e) {
    error_log("Password reset error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 