<?php
session_start();
require_once 'conn.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Consultation ID is required']);
    exit();
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM consultations WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$consultation = $result->fetch_assoc();

if (!$consultation) {
    http_response_code(404);
    echo json_encode(['error' => 'Consultation not found']);
    exit();
}

// Return the consultation details as JSON
echo json_encode($consultation); 