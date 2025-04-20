<?php
require_once 'conn.php';
header('Content-Type: application/json');

try {
    // Get and validate appointment ID
    $appointment_id = filter_input(INPUT_POST, 'appointment_id', FILTER_VALIDATE_INT);
    if (!$appointment_id) {
        throw new Exception('Invalid appointment ID');
    }

    // Delete the appointment from the database
    $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ?");
    $stmt->bind_param("i", $appointment_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Appointment cancelled and removed successfully'
            ]);
        } else {
            throw new Exception('Appointment not found');
        }
    } else {
        throw new Exception('Failed to cancel appointment');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?> 