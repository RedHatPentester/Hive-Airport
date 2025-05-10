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

// Fetch latest 50 activity logs with user info
$query = "
    SELECT al.id, s.first_name, s.last_name, al.action, al.details, al.ip_address, al.created_at
    FROM activity_logs al
    JOIN staff s ON al.user_id = s.id
    ORDER BY al.created_at DESC
    LIMIT 50
";

$result = $conn->query($query);
$logs = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
    echo json_encode(['logs' => $logs]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch activity logs']);
}
?>
