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

$name = $_POST['name'] ?? '';
$reason = $_POST['reason'] ?? '';

if (empty($name) || empty($reason)) {
    die("Name and reason are required.");
}

// Vulnerable to SQL injection: no input sanitization or prepared statements
$query = "INSERT INTO no_fly_list (name, reason) VALUES ('$name', '$reason')";

if ($conn->query($query) === TRUE) {
    header("Location: dashboard.php?message=No-fly entry added successfully");
    exit();
} else {
    die("Error adding no-fly entry: " . $conn->error);
}
?>
