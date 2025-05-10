<?php
// Load all database SQL files to set up the database

include_once 'includes/config.php';

$sqlFiles = [
    'database/setup.sql',
    'database/customer.sql',
    'database/staff.sql',
    'database/bookings.sql',
    'database/flights.sql',
    'database/missing_tables.sql'
];

foreach ($sqlFiles as $file) {
    if (!file_exists($file)) {
        echo "SQL file not found: $file\n";
        continue;
    }

    $sql = file_get_contents($file);
    if ($sql === false) {
        echo "Failed to read SQL file: $file\n";
        continue;
    }

    // Split SQL statements by semicolon followed by newline to handle multiple statements
    $statements = array_filter(array_map('trim', preg_split('/;\s*\n/', $sql)));

    foreach ($statements as $statement) {
        if ($statement === '') {
            continue;
        }
        try {
            if ($conn->query($statement) === TRUE) {
                echo "Executed statement from $file successfully.<br>";
            } else {
                echo "Error executing statement from $file: " . $conn->error . "<br>";
                echo "Statement: " . htmlspecialchars($statement) . "<br>";
            }
        } catch (Exception $e) {
            echo "Exception executing statement from $file: " . htmlspecialchars($e->getMessage()) . "<br>";
            echo "Statement: " . htmlspecialchars($statement) . "<br>";
        }
    }
}

echo "Database loading complete.";
?>
