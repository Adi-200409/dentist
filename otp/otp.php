<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';

$otp_sent = false;
$message = "";

// Handle form submission to send OTP
if (isset($_POST['send'])) {
    $name  = $_POST['name'];
    $email = $_POST['email'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
    } else {
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['email'] = $email;
        $_SESSION['name'] = $name;

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'adityap4til@gmail.com';
            $mail->Password = 'zbybehuioslzgize';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;

            $mail->setFrom('election25@gmail.com', 'Election Team');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = "Your OTP for Verification";
            $mail->Body    = "
                <h2>Hello $name,</h2>
                <p>Your OTP for verification is:</p>
                <h3 style='color: blue;'>$otp</h3>
                <p>This OTP is valid for 10 minutes.</p>
            ";

            if($mail->send()){
            $message = "OTP sent to <strong>$email</strong>";
            $otp_sent = true;
            }
        } catch (Exception $e) {
            $message = "Error: {$mail->ErrorInfo}";
        }
    }
}

// Handle OTP verification
if (isset($_POST['verify'])) {
    $user_otp = $_POST['otp'];
    if ($user_otp == $_SESSION['otp']) {
        header("Location: success.php"); // Redirect on success
        exit;
    } else {
        $message = "Invalid OTP. Please try again.";
    }
}
?>

<!-- HTML FORM -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>OTP Email Verification</title>
</head>
<body>
    <h2>Send OTP</h2>
    <form method="post">
        <p>
            <label>Full Name:</label>
            <input type="text" name="name" required value="<?= $_SESSION['name'] ?? '' ?>">
        </p>
        <p>
            <label>Email:</label>
            <input type="email" name="email" required value="<?= $_SESSION['email'] ?? '' ?>">
        </p>
        <p><button type="submit" name="send">Send OTP</button></p>
    </form>

    <?php if ($otp_sent || isset($_SESSION['otp'])): ?>
        <h3>Enter OTP</h3>
        <form method="post">
            <p>
                <label>OTP:</label>
                <input type="text" name="otp" required>
            </p>
            <p><button type="submit" name="verify">Verify OTP</button></p>
        </form>
    <?php endif; ?>

    <p style="color: green"><?= $message ?></p>
</body>
</html>
