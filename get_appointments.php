<?php
session_start();
require_once 'conn.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

try {
    // Prepare and execute query to get appointments
    $stmt = $conn->prepare("
        SELECT 
            a.id,
            a.name,
            a.phone,
            a.appointment_date,
            a.appointment_time,
            a.address,
            a.status
        FROM appointments a
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
    ");
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $appointments = [];
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($appointments);
    
} catch (Exception $e) {
    // Log error and return error response
    error_log("Error in get_appointments.php: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Failed to fetch appointments']);
}

$stmt->close();
$conn->close();
?> 