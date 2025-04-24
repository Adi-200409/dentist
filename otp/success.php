<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: index.php"); // Prevent access if not verified
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>OTP Verified</title>
</head>
<body>
    <h1>✅ OTP Verified Successfully!</h1>
    <p>Welcome, <?= $_SESSION['name'] ?> (<?= $_SESSION['email'] ?>)</p>
</body>
</html>
