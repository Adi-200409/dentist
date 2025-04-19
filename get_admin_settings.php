<?php
session_start();
require_once 'conn.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

try {
    // Get admin settings from database
    $settings = [
        'appointment_settings' => [
            'working_hours_start' => '09:00',
            'working_hours_end' => '21:00',
            'appointment_duration' => '30',
            'max_appointments_per_day' => '20'
        ],
        'notification_settings' => [
            'email_notifications' => true,
            'sms_notifications' => true,
            'appointment_reminders' => true,
            'reminder_time' => '24'
        ]
    ];

    echo json_encode([
        'success' => true,
        'data' => $settings
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch settings: ' . $e->getMessage()
    ]);
}
?> 