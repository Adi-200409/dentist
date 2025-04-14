<?php
session_start();
require_once "conn.php";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Get JSON data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!isset($data['otp'])) {
        throw new Exception("No OTP provided");
    }

    if (!isset($_SESSION['reset_email'])) {
        throw new Exception("No reset email found in session");
    }

    $otp = mysqli_real_escape_string($conn, $data['otp']);
    $email = $_SESSION['reset_email'];

    // Verify OTP
    $sql = "SELECT * FROM users WHERE email = ? AND reset_otp = ? AND otp_expiry > NOW()";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $email, $otp);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        // OTP is valid
        $_SESSION['otp_verified'] = true;
        
        echo json_encode([
            'status' => 'success',
            'message' => 'OTP verified successfully!'
        ]);
    } else {
        // Check if OTP is expired
        $sql = "SELECT otp_expiry FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($result && $row = mysqli_fetch_assoc($result)) {
            if (strtotime($row['otp_expiry']) < time()) {
                throw new Exception("OTP has expired. Please request a new one.");
            }
        }
        
        throw new Exception("Invalid OTP. Please try again.");
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 