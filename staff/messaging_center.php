<?php
session_start();
include_once '../includes/config.php';

// Role-based access control
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}

// CSRF token validation
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Invalid CSRF token.");
}

$recipient = $_POST['recipient'] ?? '';
$message = $_POST['message'] ?? '';

if (empty($recipient) || empty($message)) {
    die("Recipient and message are required.");
}

// Vulnerable to SQL injection: no input sanitization or prepared statements
$query = "INSERT INTO messages (recipient, message) VALUES ('$recipient', '$message')";

if ($conn->query($query) === TRUE) {
    header("Location: dashboard.php?message=Message sent successfully");
    exit();
} else {
    die("Error sending message: " . $conn->error);
}
?>
