<?php
session_start();
require_once "conn.php";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
    // Check if user is verified
    if (!isset($_SESSION['otp_verified']) || !$_SESSION['otp_verified']) {
        throw new Exception("Please verify your OTP first");
    }

    // Check if reset phone exists in session
    if (!isset($_SESSION['reset_phone'])) {
        throw new Exception("Reset session expired. Please try again.");
    }

    // Get JSON data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!isset($data['password']) || !isset($data['phone'])) {
        throw new Exception("Password and phone number are required");
    }

    $password = $data['password'];
    $phone = preg_replace('/\D/', '', $data['phone']); // Remove non-digits

    // Validate phone number format
    if (!preg_match('/^[6-9]\d{9}$/', $phone)) {
        throw new Exception('Invalid phone number format');
    }

    // Verify phone matches session
    if ($phone !== $_SESSION['reset_phone']) {
        throw new Exception("Invalid reset attempt");
    }

    // Validate password
    if (strlen($password) < 8) {
        throw new Exception("Password must be at least 8 characters long");
    }

    if (!preg_match("/[A-Z]/", $password)) {
        throw new Exception("Password must contain at least one uppercase letter");
    }

    if (!preg_match("/[a-z]/", $password)) {
        throw new Exception("Password must contain at least one lowercase letter");
    }

    if (!preg_match("/[0-9]/", $password)) {
        throw new Exception("Password must contain at least one number");
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Update password in database
    $sql = "UPDATE users SET password = ?, reset_otp = NULL, otp_expiry = NULL WHERE phone = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $hashed_password, $phone);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error updating password");
    }

    // Clear session variables
    unset($_SESSION['reset_phone']);
    unset($_SESSION['otp_verified']);

    echo json_encode([
        'status' => 'success',
        'message' => 'Password reset successful!'
    ]);

} catch (Exception $e) {
    error_log("Password reset error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 