<?php
session_start();
require_once 'conn.php';

header('Content-Type: application/json');

try {
    // Get JSON data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['phone']) || !isset($data['favorite_number'])) {
        throw new Exception('Missing required fields');
    }

    $phone = $data['phone'];
    $favorite_number = $data['favorite_number'];

    // Validate phone number format
    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        throw new Exception('Invalid phone number format');
    }

    // Validate favorite number
    if (!is_numeric($favorite_number) || $favorite_number < 1 || $favorite_number > 100) {
        throw new Exception('Invalid favorite number');
    }

    // Check if user exists and favorite number matches
    $stmt = $conn->prepare("SELECT id FROM users WHERE phone = ? AND favorite_number = ?");
    $stmt->bind_param("si", $phone, $favorite_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Invalid phone number or favorite number');
    }

    $user = $result->fetch_assoc();
    
    // Store user ID in session for password reset
    $_SESSION['reset_user_id'] = $user['id'];
    
    echo json_encode([
        'success' => true,
        'message' => 'Identity verified successfully'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 