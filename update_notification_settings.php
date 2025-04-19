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
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['email_notifications']) || !isset($data['sms_notifications']) || 
        !isset($data['appointment_reminders']) || !isset($data['reminder_time'])) {
        throw new Exception('All notification settings are required');
    }

    // Check database connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Update notification settings
    $settings = [
        'email_notifications' => $data['email_notifications'] ? '1' : '0',
        'sms_notifications' => $data['sms_notifications'] ? '1' : '0',
        'appointment_reminders' => $data['appointment_reminders'] ? '1' : '0',
        'reminder_time' => $data['reminder_time']
    ];
    
    foreach ($settings as $key => $value) {
        // Check if setting exists
        $stmt = $conn->prepare("SELECT id FROM settings WHERE setting_type = 'notification' AND setting_key = ?");
        $stmt->bind_param("s", $key);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update existing setting
            $stmt = $conn->prepare("UPDATE settings SET setting_value = ? WHERE setting_type = 'notification' AND setting_key = ?");
            $stmt->bind_param("ss", $value, $key);
        } else {
            // Insert new setting
            $stmt = $conn->prepare("INSERT INTO settings (setting_type, setting_key, setting_value) VALUES ('notification', ?, ?)");
            $stmt->bind_param("ss", $key, $value);
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Error updating setting {$key}: " . $stmt->error);
        }
        
        $stmt->close();
    }
    
    // Return success response
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Notification settings updated successfully']);
    
} catch (Exception $e) {
    // Log error and return error response
    error_log("Error in update_notification_settings.php: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to update notification settings', 
        'error' => $e->getMessage()
    ]);
}

$conn->close();
?> 