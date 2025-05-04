<?php
// Database connection
include 'conn.php';

// Check if the user is authenticated as admin
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

// Get the request body
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['blocked_dates'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

$blockedDates = $data['blocked_dates'];

// First, delete all existing blocked dates
$sqlDelete = "DELETE FROM blocked_dates";
if (!$conn->query($sqlDelete)) {
    echo json_encode(['success' => false, 'message' => 'Failed to clear existing blocked dates: ' . $conn->error]);
    exit;
}

// If there are no new blocked dates, we're done
if (empty($blockedDates)) {
    echo json_encode(['success' => true, 'message' => 'All blocked dates cleared successfully']);
    exit;
}

// Insert the new blocked dates
$stmt = $conn->prepare("INSERT INTO blocked_dates (date, reason) VALUES (?, ?)");

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit;
}

$success = true;
$errors = [];

foreach ($blockedDates as $blockedDate) {
    // Validate date format
    $date = isset($blockedDate['date']) ? $blockedDate['date'] : '';
    $reason = isset($blockedDate['reason']) ? $blockedDate['reason'] : '';
    
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $errors[] = "Invalid date format: {$date}";
        continue;
    }
    
    $stmt->bind_param("ss", $date, $reason);
    
    if (!$stmt->execute()) {
        $success = false;
        $errors[] = "Failed to save blocked date: {$date}";
    }
}

$stmt->close();
$conn->close();

if ($success && empty($errors)) {
    echo json_encode(['success' => true, 'message' => 'Blocked dates saved successfully']);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to save some blocked dates', 
        'errors' => $errors
    ]);
}
?> 