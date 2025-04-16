<?php
session_start();
require_once "conn.php";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'error.log');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Get JSON data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data: ' . json_last_error_msg());
    }

    if (!isset($data['otp']) || !isset($data['phone'])) {
        throw new Exception('OTP and phone number are required');
    }

    $otp = $data['otp'];
    $phone = preg_replace('/\D/', '', $data['phone']); // Remove non-digits

    // Validate phone number format
    if (!preg_match('/^[6-9]\d{9}$/', $phone)) {
        throw new Exception('Invalid phone number format');
    }

    // Log verification attempt
    error_log("Attempting to verify OTP: " . $otp . " for phone: " . $phone);

    // Check if user exists and get their current OTP details
    $stmt = $conn->prepare("SELECT reset_otp, otp_expiry FROM users WHERE phone = ?");
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }

    $stmt->bind_param("s", $phone);
    if (!$stmt->execute()) {
        throw new Exception("Database error: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        throw new Exception("User not found");
    }

    $user = $result->fetch_assoc();
    
    // Log OTP details for debugging
    error_log("Stored OTP: " . $user['reset_otp'] . ", Received OTP: " . $otp);
    error_log("OTP Expiry: " . $user['otp_expiry'] . ", Current Time: " . date('Y-m-d H:i:s'));

    // Verify OTP
    if ($user['reset_otp'] !== $otp) {
        throw new Exception("Invalid OTP");
    }

    // Check if OTP has expired
    if (strtotime($user['otp_expiry']) < time()) {
        throw new Exception("OTP has expired. Please request a new one.");
    }

    // Store phone in session for password reset
    $_SESSION['reset_phone'] = $phone;
    $_SESSION['otp_verified'] = true;

    error_log("OTP verified successfully for phone: " . $phone);

    echo json_encode([
        'status' => 'success',
        'message' => 'OTP verified successfully'
    ]);

} catch (Exception $e) {
    error_log("Error in verify_otp.php: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 