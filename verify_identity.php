<?php
session_start();
require_once 'conn.php';

header('Content-Type: application/json');

try {
    // Get JSON data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['phone'])) {
        throw new Exception('Missing phone number');
    }

    $phone = $data['phone'];

    // Validate phone number format
    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        throw new Exception('Invalid phone number format');
    }

    // Just check if user exists with this phone number
    $stmt = $conn->prepare("SELECT id, name FROM users WHERE phone = ?");
    $stmt->bind_param("s", $phone);
    
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('User not found with this phone number');
    }

    $user = $result->fetch_assoc();
    
    // Store user ID in session for password reset
    $_SESSION['reset_user_id'] = $user['id'];
    
    echo json_encode([
        'success' => true,
        'message' => 'Identity verified successfully',
        'name' => $user['name']
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 