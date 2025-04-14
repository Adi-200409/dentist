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
    // Log request data
    error_log("Received request data: " . file_get_contents('php://input'));
    error_log("Session data: " . print_r($_SESSION, true));

    // Get JSON data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data: ' . json_last_error_msg());
    }

    if (!isset($data['otp'])) {
        throw new Exception('OTP is required');
    }

    if (!isset($_SESSION['reset_email'])) {
        throw new Exception('Reset session expired. Please try again.');
    }

    $otp = $data['otp'];
    $email = $_SESSION['reset_email'];

    // Log values for debugging
    error_log("Attempting to verify OTP: " . $otp . " for email: " . $email);

    // First check if user exists and get their current OTP details
    $stmt = $conn->prepare("SELECT reset_otp, otp_expiry FROM users WHERE email = ?");
    if (!$stmt) {
        throw new Exception("Database prepare error: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        throw new Exception("Database execute error: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        throw new Exception("User not found");
    }

    $row = $result->fetch_assoc();
    $stored_otp = $row['reset_otp'];
    $otp_expiry = strtotime($row['otp_expiry']);

    // Log the comparison
    error_log("Stored OTP: " . $stored_otp . ", Received OTP: " . $otp);
    error_log("OTP Expiry: " . date('Y-m-d H:i:s', $otp_expiry) . ", Current Time: " . date('Y-m-d H:i:s'));

    // Check if OTP matches
    if ($stored_otp !== $otp) {
        throw new Exception("Invalid OTP");
    }

    // Check if OTP is expired
    if ($otp_expiry < time()) {
        throw new Exception("OTP has expired. Please request a new one.");
    }

    // Set session variable to indicate OTP is verified
    $_SESSION['otp_verified'] = true;
    error_log("OTP verified successfully for email: " . $email);

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