<?php
session_start();
require_once "conn.php";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'error.log');

header('Content-Type: application/json');

try {
    // Get JSON data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!isset($data['phone']) || !isset($data['favorite_number'])) {
        throw new Exception("Phone number and favorite number are required");
    }

    $phone = preg_replace('/\D/', '', $data['phone']); // Remove non-digits
    $favorite_number = isset($data['favorite_number']) ? intval($data['favorite_number']) : null;

    // Validate phone number format
    if (!preg_match('/^[6-9]\d{9}$/', $phone)) {
        throw new Exception('Invalid phone number format');
    }

    // Validate favorite number
    if ($favorite_number === null || $favorite_number < 0 || $favorite_number > 999999) {
        throw new Exception('Invalid favorite number');
    }

    // Check if user exists and verify favorite number
    $stmt = $conn->prepare("SELECT id FROM users WHERE phone = ? AND favorite_number = ?");
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }

    $stmt->bind_param("si", $phone, $favorite_number);
    if (!$stmt->execute()) {
        throw new Exception("Database error: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        throw new Exception("Invalid phone number or favorite number");
    }

    // Store phone in session for password reset
    $_SESSION['reset_phone'] = $phone;
    $_SESSION['reset_verified'] = true;

    echo json_encode([
        'status' => 'success',
        'message' => 'Verification successful! Please enter your new password.'
    ]);

} catch (Exception $e) {
    error_log("Verification error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 