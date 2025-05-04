<?php
// Database connection
include 'conn.php';

// Check if the user is authenticated as admin
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

// Get all blocked dates from the database
$sql = "SELECT id, date, reason FROM blocked_dates ORDER BY date ASC";
$result = $conn->query($sql);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit;
}

$blockedDates = [];
while ($row = $result->fetch_assoc()) {
    $blockedDates[] = [
        'id' => $row['id'],
        'date' => $row['date'],
        'reason' => $row['reason']
    ];
}

$conn->close();

// Return the results
echo json_encode(['success' => true, 'blocked_dates' => $blockedDates]);
?> 