<?php
session_start();
require_once "conn.php";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'error.log');

// Log request details
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Content-Type: " . (isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : 'not set'));

// Add CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

/**
 * Function to send OTP via MSG91
 */
function sendSMS($phone, $otp) {
    $authKey = "YOUR_MSG91_AUTH_KEY";  // Replace with your MSG91 Auth Key
    $templateId = "YOUR_TEMPLATE_ID";   // Replace with your MSG91 Template ID
    
    // Remove any existing +91 or 91 prefix
    $phone = preg_replace('/^\+?91/', '', $phone);
    
    $url = "https://api.msg91.com/api/v5/otp";
    
    $postData = [
        "template_id" => $templateId,
        "mobile" => "91" . $phone,
        "authkey" => $authKey,
        "otp" => $otp
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    
    if ($err) {
        error_log("MSG91 Error: " . $err);
        return false;
    }
    
    $result = json_decode($response, true);
    error_log("MSG91 Response: " . $response);
    
    return isset($result['type']) && $result['type'] === 'success';
}

try {
    // Get raw input
    $raw_input = file_get_contents('php://input');
    error_log("Raw input: " . $raw_input);

    if ($raw_input === false) {
        throw new Exception('Failed to read request body');
    }

    // Parse JSON
    $data = json_decode($raw_input, true);
    error_log("Decoded data: " . print_r($data, true));

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data: ' . json_last_error_msg());
    }

    if (!isset($data['phone'])) {
        throw new Exception('Phone number is required');
    }

    $phone = preg_replace('/[^0-9]/', '', $data['phone']); // Remove non-digits
    error_log("Processed phone number: " . $phone);

    // Validate phone number format (Indian mobile number)
    if (!preg_match('/^[6-9]\d{9}$/', $phone)) {
        throw new Exception('Invalid phone number format. Please enter a valid 10-digit Indian mobile number.');
    }

    // Log the forgot password attempt
    error_log("Forgot password attempt for phone: " . $phone);

    // Check if user exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE phone = ?");
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }

    $stmt->bind_param("s", $phone);
    if (!$stmt->execute()) {
        throw new Exception("Database error: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        throw new Exception("No account found with this phone number");
    }

    // Generate a 6-digit OTP
    $otp = sprintf("%06d", mt_rand(0, 999999));
    
    // Set OTP expiry time (10 minutes from now)
    $otp_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    // Store OTP and expiry in database
    $update_stmt = $conn->prepare("UPDATE users SET reset_otp = ?, otp_expiry = ? WHERE phone = ?");
    if (!$update_stmt) {
        throw new Exception("Database error: " . $conn->error);
    }

    $update_stmt->bind_param("sss", $otp, $otp_expiry, $phone);
    if (!$update_stmt->execute()) {
        throw new Exception("Failed to store OTP: " . $update_stmt->error);
    }

    // Try to send SMS
    $sms_sent = sendSMS($phone, $otp);
    
    // Store phone in session for OTP verification
    $_SESSION['reset_phone'] = $phone;

    // Log successful OTP generation
    error_log("OTP generated for phone: " . $phone . ", OTP: " . $otp . ", Expiry: " . $otp_expiry);
    error_log("SMS sent: " . ($sms_sent ? "Yes" : "No"));

    // Return success response
    $response = [
        'status' => 'success',
        'message' => $sms_sent ? 
            'OTP has been sent to your phone number. Please check your messages.' : 
            'OTP has been generated. Please check the OTP page.',
        'redirect' => 'view_otp.php' // Keep this for testing
    ];

    error_log("Sending response: " . json_encode($response));
    echo json_encode($response);

} catch (Exception $e) {
    error_log("Error in forgot_password.php: " . $e->getMessage());
    http_response_code(400);
    $error_response = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
    error_log("Sending error response: " . json_encode($error_response));
    echo json_encode($error_response);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($update_stmt)) {
        $update_stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?> 