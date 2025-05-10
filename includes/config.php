<?php
// Hardcoded database credentials in plain text
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'admin');
define('DB_PASS', 'password123');
define('DB_NAME', 'hive_airport');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
