<?php
session_start();
require_once "conn.php";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in as admin or has a reset_phone in session
if ((!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') && !isset($_SESSION['reset_phone'])) {
    header('Location: login.html');
    exit;
}

// Get the phone number from session
$phone = isset($_SESSION['reset_phone']) ? $_SESSION['reset_phone'] : '';

if (empty($phone)) {
    echo "No phone number found in session. Please go back to the forgot password page.";
    exit;
}

// Get the OTP from database
$stmt = $conn->prepare("SELECT reset_otp, otp_expiry FROM users WHERE phone = ?");
$stmt->bind_param("s", $phone);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "No OTP found for this phone number.";
    exit;
}

$otp = $user['reset_otp'];
$expiry = $user['otp_expiry'];
$isExpired = strtotime($expiry) < time();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View OTP</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
            max-width: 500px;
            text-align: center;
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        .otp-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
        }
        .otp {
            font-size: 36px;
            font-weight: bold;
            color: #007bff;
            letter-spacing: 5px;
        }
        .expiry {
            color: #6c757d;
            margin-top: 10px;
        }
        .expired {
            color: #dc3545;
            font-weight: bold;
        }
        .back-btn {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
            text-decoration: none;
            display: inline-block;
        }
        .back-btn:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>OTP for Password Reset</h1>
        <div class="otp-box">
            <div class="otp"><?php echo $otp; ?></div>
            <div class="expiry <?php echo $isExpired ? 'expired' : ''; ?>">
                Expires: <?php echo date('Y-m-d H:i:s', strtotime($expiry)); ?>
                <?php if ($isExpired): ?>
                    <br>(EXPIRED)
                <?php endif; ?>
            </div>
        </div>
        <p>This OTP is for testing purposes only. In a production environment, this would be sent via SMS.</p>
        <a href="login.html" class="back-btn">Back to Login</a>
    </div>
</body>
</html> 