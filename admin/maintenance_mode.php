<?php
session_start();
include_once '../includes/config.php';

// Only allow admin users
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Toggle maintenance mode
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['is_enabled'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing is_enabled parameter']);
        exit();
    }
    $is_enabled = $data['is_enabled'] ? 1 : 0;

    $stmt = $conn->prepare("UPDATE maintenance_mode SET is_enabled = ?, updated_at = NOW() WHERE id = 1");
    $stmt->bind_param("i", $is_enabled);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'is_enabled' => (bool)$is_enabled]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update maintenance mode']);
    }
    exit();
} else {
    // Get current maintenance mode status
    $result = $conn->query("SELECT is_enabled FROM maintenance_mode WHERE id = 1");
    if ($result && $row = $result->fetch_assoc()) {
        echo json_encode(['is_enabled' => (bool)$row['is_enabled']]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to get maintenance mode status']);
    }
    exit();
}
?>
