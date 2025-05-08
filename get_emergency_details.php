<?php
session_start();
require_once 'conn.php';

// Set response header to JSON
header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Emergency ID is required']);
    exit;
}

$emergency_id = intval($_GET['id']);

try {
    // Prepare and execute query
    $stmt = $conn->prepare("SELECT * FROM emergency_requests WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $emergency_id);
    if (!$stmt->execute()) {
        throw new Exception("Execute statement failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Emergency not found']);
        exit;
    }
    
    $emergency = $result->fetch_assoc();
    
    // Return emergency data
    echo json_encode([
        'success' => true,
        'emergency' => [
            'id' => $emergency['id'],
            'name' => $emergency['name'] ?? 'Unknown',
            'phone' => $emergency['phone'] ?? 'N/A',
            'issue' => nl2br(htmlspecialchars($emergency['issue'] ?? 'No description provided')),
            'status' => $emergency['status'] ?? 'pending'
        ]
    ]);
    
} catch (Exception $e) {
    // Log error and return error message
    error_log("Error fetching emergency details: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while fetching emergency details']);
}

// Close database connection
if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
?> 