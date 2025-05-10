<?php
session_start();
include_once '../includes/config.php';

// Only allow logged in users
if (!isset($_SESSION['username'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit();
}

header('Content-Type: application/json');

$user = $_SESSION['username'];

// Get user ID from staff table
$stmt = $conn->prepare("SELECT id FROM staff WHERE first_name = ?");
$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();
if (!$result || $result->num_rows !== 1) {
    http_response_code(400);
    echo json_encode(['error' => 'User not found']);
    exit();
}
$row = $result->fetch_assoc();
$user_id = $row['id'];

// Get action and details from POST
$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';
$details = $data['details'] ?? '';
$ip_address = $_SERVER['REMOTE_ADDR'] ?? '';

if (empty($action)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing action']);
    exit();
}

$stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
$stmt->bind_param("isss", $user_id, $action, $details, $ip_address);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to log activity']);
}
?>
