<?php
session_start();
require_once "conn.php";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log all errors to a file
ini_set('log_errors', 1);
ini_set('error_log', 'error.log');

header('Content-Type: application/json');

try {
    error_log("Received request: " . print_r($_POST, true));

    // Get form data
    if (!isset($_POST['email'])) {
        error_log("Email not set in POST data");
        throw new Exception("Email is required");
    }

    $email = mysqli_real_escape_string($conn, $_POST['email']);
    error_log("Processing email: " . $email);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        error_log("Invalid email format: " . $email);
        throw new Exception("Invalid email format");
    }

    // Check if email exists in database
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        error_log("Prepare failed: " . mysqli_error($conn));
        throw new Exception("Database error");
    }

    mysqli_stmt_bind_param($stmt, "s", $email);
    if (!mysqli_stmt_execute($stmt)) {
        error_log("Execute failed: " . mysqli_stmt_error($stmt));
        throw new Exception("Database error");
    }

    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        error_log("Get result failed: " . mysqli_error($conn));
        throw new Exception("Database error");
    }

    if (mysqli_num_rows($result) === 0) {
        error_log("No user found with email: " . $email);
        throw new Exception("No account found with this email address");
    }

    // Generate 6-digit OTP
    $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    error_log("Generated OTP: " . $otp);
    
    // Set OTP expiry time (15 minutes from now)
    $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));

    // Update user record with OTP and expiry
    $sql = "UPDATE users SET reset_otp = ?, otp_expiry = ? WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        error_log("Prepare update failed: " . mysqli_error($conn));
        throw new Exception("Database error");
    }

    mysqli_stmt_bind_param($stmt, "sss", $otp, $expiry, $email);
    
    if (!mysqli_stmt_execute($stmt)) {
        error_log("Update failed: " . mysqli_stmt_error($stmt));
        throw new Exception("Error updating user record");
    }

    // Store email in session for OTP verification
    $_SESSION['reset_email'] = $email;
    error_log("Stored email in session: " . $email);

    // Send email with OTP
    $to = $email;
    $subject = "Password Reset OTP - JUSTSmile";
    $message = "Your OTP for password reset is: $otp\n\n";
    $message .= "This OTP will expire in 15 minutes.\n";
    $message .= "If you did not request this password reset, please ignore this email.";
    $headers = "From: noreply@justsmile.com\r\n";
    $headers .= "Reply-To: noreply@justsmile.com\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    if (!mail($to, $subject, $message, $headers)) {
        error_log("Failed to send email to: " . $email);
        throw new Exception("Error sending email. Please try again.");
    }

    error_log("Successfully sent OTP to: " . $email);

    echo json_encode([
        'status' => 'success',
        'message' => 'OTP has been sent to your email address'
    ]);

} catch (Exception $e) {
    error_log("Error in forgot_password.php: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 