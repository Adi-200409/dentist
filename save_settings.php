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

if (!$data || !isset($data['form_id']) || !isset($data['data'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

$formId = $data['form_id'];
$formData = $data['data'];

// Process data based on the form ID
switch ($formId) {
    case 'general':
        // Save general settings
        saveGeneralSettings($conn, $formData);
        break;
        
    case 'email':
        // Save email settings
        saveEmailSettings($conn, $formData);
        break;
        
    case 'notifications':
        // Save notification settings
        saveNotificationSettings($conn, $formData);
        break;
        
    case 'appointments':
        // Save appointment settings
        saveAppointmentSettings($conn, $formData);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Unknown form type']);
        exit;
}

// Close the database connection
$conn->close();

// Function to save general settings
function saveGeneralSettings($conn, $data) {
    // Prepare the SQL statement for each setting
    $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) 
                           VALUES (?, ?) 
                           ON DUPLICATE KEY UPDATE setting_value = ?");
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }
    
    $success = true;
    $errors = [];
    
    // Process each general setting
    foreach ($data as $key => $value) {
        $cleanKey = 'general_' . $key; // Prefix with form name for uniqueness
        $stmt->bind_param("sss", $cleanKey, $value, $value);
        
        if (!$stmt->execute()) {
            $success = false;
            $errors[] = "Failed to save setting: {$key}";
        }
    }
    
    $stmt->close();
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'General settings saved successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save some settings', 'errors' => $errors]);
    }
}

// Function to save email settings
function saveEmailSettings($conn, $data) {
    // Prepare the SQL statement
    $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) 
                           VALUES (?, ?) 
                           ON DUPLICATE KEY UPDATE setting_value = ?");
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }
    
    $success = true;
    $errors = [];
    
    // Process each email setting
    foreach ($data as $key => $value) {
        $cleanKey = 'email_' . $key; // Prefix with form name for uniqueness
        $stmt->bind_param("sss", $cleanKey, $value, $value);
        
        if (!$stmt->execute()) {
            $success = false;
            $errors[] = "Failed to save setting: {$key}";
        }
    }
    
    $stmt->close();
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Email settings saved successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save some settings', 'errors' => $errors]);
    }
}

// Function to save notification settings
function saveNotificationSettings($conn, $data) {
    // Prepare the SQL statement
    $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) 
                           VALUES (?, ?) 
                           ON DUPLICATE KEY UPDATE setting_value = ?");
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }
    
    $success = true;
    $errors = [];
    
    // Process each notification setting
    foreach ($data as $key => $value) {
        // Convert checkboxes/booleans to 0/1
        if ($value === 'on' || $value === true) {
            $value = '1';
        } else if ($value === 'off' || $value === false) {
            $value = '0';
        }
        
        $cleanKey = 'notification_' . $key; // Prefix with form name for uniqueness
        $stmt->bind_param("sss", $cleanKey, $value, $value);
        
        if (!$stmt->execute()) {
            $success = false;
            $errors[] = "Failed to save setting: {$key}";
        }
    }
    
    $stmt->close();
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Notification settings saved successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save some settings', 'errors' => $errors]);
    }
}

// Function to save appointment settings
function saveAppointmentSettings($conn, $data) {
    // Prepare the SQL statement
    $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) 
                           VALUES (?, ?) 
                           ON DUPLICATE KEY UPDATE setting_value = ?");
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }
    
    $success = true;
    $errors = [];
    
    // Process each appointment setting
    foreach ($data as $key => $value) {
        $cleanKey = 'appointment_' . $key; // Prefix with form name for uniqueness
        $stmt->bind_param("sss", $cleanKey, $value, $value);
        
        if (!$stmt->execute()) {
            $success = false;
            $errors[] = "Failed to save setting: {$key}";
        }
    }
    
    $stmt->close();
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Appointment settings saved successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save some settings', 'errors' => $errors]);
    }
}
?> 