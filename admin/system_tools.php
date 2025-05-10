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

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'ping':
        $target = $_POST['ping_target'] ?? '';
        if (empty($target)) {
            echo json_encode(['error' => 'Ping target is required']);
            exit();
        }
        // Sanitize target to allow only valid hostname or IP characters
        if (!preg_match('/^[A-Za-z0-9\.\-]+$/', $target)) {
            echo json_encode(['error' => 'Invalid ping target']);
            exit();
        }
        // Execute ping command (Linux)
        $output = [];
        $return_var = 0;
        exec("ping -c 4 " . escapeshellarg($target), $output, $return_var);
        echo json_encode(['output' => $output, 'success' => $return_var === 0]);
        break;

    case 'disk_usage':
        // Get disk usage info
        $output = [];
        exec("df -h", $output);
        echo json_encode(['output' => $output]);
        break;

    case 'system_uptime':
        $uptime = shell_exec("uptime -p");
        echo json_encode(['uptime' => trim($uptime)]);
        break;

    default:
        echo json_encode(['error' => 'Unknown action']);
        break;
}
?>
