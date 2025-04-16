<?php
include 'conn.php'; // make sure this file has a valid DB connection

// Set header to accept JSON requests
header('Content-Type: application/json');

// Get and sanitize input data
$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$phone = preg_replace('/\D/', '', $_POST['phone']); // Remove all non-digits
$date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING);
$time = filter_input(INPUT_POST, 'time', FILTER_SANITIZE_STRING);
$area = filter_input(INPUT_POST, 'area', FILTER_SANITIZE_STRING);
$city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_STRING);
$state = filter_input(INPUT_POST, 'state', FILTER_SANITIZE_STRING);
$postcode = filter_input(INPUT_POST, 'postcode', FILTER_SANITIZE_STRING);

// Validate phone number (10 digits starting with 6-9)
if (!preg_match('/^[6-9]\d{9}$/', $phone)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid phone number format']);
    exit;
}

// Combine address components
$address = "$area, $city, $state - $postcode";

try {
    // Prepare SQL statement
    $stmt = $conn->prepare("INSERT INTO appointments (name, email, phone, appointment_date, appointment_time, address, status) VALUES (?, ?, ?, ?, ?, ?, 'scheduled')");
    
    // Bind parameters
    $stmt->bind_param("ssssss", $name, $email, $phone, $date, $time, $address);
    
    // Execute the statement
    if ($stmt->execute()) {
        $appointment_id = $stmt->insert_id;
        echo json_encode([
            'success' => true,
            'message' => 'Appointment scheduled successfully',
            'appointment_id' => $appointment_id,
            'appointment' => [
                'id' => $appointment_id,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'date' => $date,
                'time' => $time,
                'address' => $address
            ]
        ]);
    } else {
        throw new Exception("Error scheduling appointment");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to schedule appointment: ' . $e->getMessage()
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

