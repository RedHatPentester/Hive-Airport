<?php
// api.php - API endpoint with CORS misconfiguration vulnerability

// Allow all origins (vulnerable)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Simulated sensitive data response
$data = [
    "message" => "Sensitive data from Hive Airport API",
    "flights_available" => 100,
    "employees_count" => 50
];

echo json_encode($data);
?>
