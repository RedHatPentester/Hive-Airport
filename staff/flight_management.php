<?php
session_start();
include_once '../includes/config.php';

// Role-based access control
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../login.php");
    exit();
}

$username = $_SESSION['username'];

// CSRF token check (optional, but included for form consistency)
if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Invalid CSRF token.");
}

$search = $_GET['search'] ?? '';

$error = '';
$flights = [];

if ($search !== '') {
    // Vulnerable to SQL injection: directly embedding user input into query
    $query = "SELECT * FROM flights WHERE flight_number LIKE '%$search%' OR origin LIKE '%$search%' OR destination LIKE '%$search%'";

    $result = $conn->query($query);

    if ($result === false) {
        $error = "Database error: " . $conn->error;
    } else {
        while ($row = $result->fetch_assoc()) {
            $flights[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Flight Management - Hive Airport</title>
    <link rel="stylesheet" type="text/css" href="../assets/style.css" />
    <link rel="stylesheet" type="text/css" href="../static/old/jquery.dataTables.min.css" />
    <script src="../static/old/jquery.js"></script>
    <script src="../static/old/jquery.dataTables.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 40px 20px;
            display: flex;
            justify-content: center;
        }
        .container {
            width: 100%;
            max-width: 960px;
            background: white;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 25px;
            text-align: center;
        }
        .error {
            color: red;
            margin-bottom: 15px;
            text-align: center;
        }
        form {
            margin-bottom: 25px;
            text-align: center;
        }
        input[type="text"] {
            padding: 10px 12px;
            width: 350px;
            max-width: 100%;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
            box-sizing: border-box;
            margin-right: 10px;
        }
        input[type="submit"] {
            padding: 10px 20px;
            background-color: #2980b9;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        input[type="submit"]:hover {
            background-color: #1c5980;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 14px 18px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #2980b9;
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Flight Management</h1>

        <form method="GET" action="flight_management.php" autocomplete="off">
            <input type="text" name="search" maxlength="50" pattern="[A-Za-z0-9\s\-]*" title="Alphanumeric, spaces, and hyphens only" value="<?php echo htmlspecialchars($search); ?>" />
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_GET['csrf_token'] ?? ''); ?>" />
            <input type="submit" value="Search" />
        </form>

        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (count($flights) > 0): ?>
            <table id="flights-table">
                <thead>
                    <tr>
                        <th>Flight Number</th>
                        <th>Origin</th>
                        <th>Destination</th>
                        <th>Departure Time</th>
                        <th>Arrival Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($flights as $flight): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($flight['flight_number']); ?></td>
                            <td><?php echo htmlspecialchars($flight['origin']); ?></td>
                            <td><?php echo htmlspecialchars($flight['destination']); ?></td>
                            <td><?php echo htmlspecialchars($flight['departure_time']); ?></td>
                            <td><?php echo htmlspecialchars($flight['arrival_time']); ?></td>
                            <td><?php echo htmlspecialchars($flight['status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($search !== ''): ?>
            <p style="text-align:center;">No flights found matching your search.</p>
        <?php endif; ?>
    </div>

    <script>
        $(document).ready(function() {
            $('#flights-table').DataTable();
        });
    </script>
</body>
</html>
