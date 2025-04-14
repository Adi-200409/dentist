<?php
include 'conn.php'; // make sure this file has a valid DB connection

// Set header to accept JSON requests
header('Content-Type: application/json');

try {
    // Get and sanitize POST data
    $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING));
    $phone = trim(filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING));
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    $appointmentdate = trim(filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING));
    $appointmenttime = trim(filter_input(INPUT_POST, 'time', FILTER_SANITIZE_STRING));
    $area = trim(filter_input(INPUT_POST, 'area', FILTER_SANITIZE_STRING));
    $city = trim(filter_input(INPUT_POST, 'city', FILTER_SANITIZE_STRING));
    $state = trim(filter_input(INPUT_POST, 'state', FILTER_SANITIZE_STRING));
    $postcode = trim(filter_input(INPUT_POST, 'postcode', FILTER_SANITIZE_STRING));

    // Validate required fields
    if (empty($name) || empty($phone) || empty($email) || empty($appointmentdate) || empty($appointmenttime)) {
        throw new Exception('All required fields must be filled out');
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Please enter a valid email address');
    }

    // Validate phone (Indian format)
    $phone = preg_replace('/[^0-9]/', '', $phone); // Remove non-numeric characters
    if (!preg_match('/^[6-9]\d{9}$/', $phone)) {
        throw new Exception('Please enter a valid 10-digit mobile number starting with 6-9');
    }

    // Validate date
    $date = new DateTime($appointmentdate);
    $today = new DateTime('today');
    if ($date < $today) {
        throw new Exception('Please select today\'s or a future date');
    }

    // Validate if it's a Sunday
    if ($date->format('w') === '0') {
        throw new Exception('We are closed on Sundays');
    }

    // Validate time (9 AM to 9 PM)
    $time = DateTime::createFromFormat('H:i', $appointmenttime);
    $hour = (int)$time->format('H');
    if ($hour < 9 || $hour >= 21) {
        throw new Exception('Please select a time between 9 AM and 9 PM');
    }

    // If it's today, validate time is not in the past
    if ($date->format('Y-m-d') === $today->format('Y-m-d')) {
        $currentHour = (int)(new DateTime())->format('H');
        if ($hour <= $currentHour) {
            throw new Exception('Please select a future time');
        }
    }

    // Check for duplicate appointments
    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM appointment WHERE appointmentdate = ? AND appointmenttime = ?");
    $checkStmt->bind_param("ss", $appointmentdate, $appointmenttime);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($count > 0) {
        throw new Exception('This time slot is already booked. Please select a different time.');
    }

    // Prepare and execute the insert query
    $stmt = $conn->prepare("INSERT INTO appointment (name, phone, email, appointmentdate, appointmenttime, area, city, state, postcode) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        throw new Exception('System error: Failed to prepare statement');
    }

    $stmt->bind_param("sssssssss", $name, $phone, $email, $appointmentdate, $appointmenttime, $area, $city, $state, $postcode);

    if (!$stmt->execute()) {
        throw new Exception('Failed to save appointment. Please try again.');
    }

    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'Appointment booked successfully!'
    ]);

} catch (Exception $e) {
    // Error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    // Close connections
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?>

