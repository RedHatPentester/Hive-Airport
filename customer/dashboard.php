<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

$username = $_SESSION['username'];

include_once '../includes/config.php';

$search = $_GET['search'] ?? '';

try {
    if (!empty($search)) {
        // Check for advanced payloads (e.g., UNION SELECT with specific patterns)
        if (preg_match('/UNION\s+SELECT/i', $search)) {
            $query = "SELECT flight_number, origin, destination, departure, arrival, price, seats_available FROM flights WHERE destination LIKE '%$search%' OR origin LIKE '%$search%'";
            $result = $conn->query($query);
        } else {
            $result = false; // Simulate "not found" for non-advanced payloads
        }
    } else {
        $query = "SELECT flight_number, origin, destination, departure, arrival, price, seats_available FROM flights";
        $result = $conn->query($query);
    }
} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage()); // Log the error for debugging
    $result = false; // Gracefully handle the error
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Customer Dashboard - Hive Airport</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: url('../assets/image1.jpeg') no-repeat center center fixed;
            background-size: cover;
            color: #333;
        }
        .container {
            max-width: 1300px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        h1 {
            text-align: center;
            color: #444;
        }
        .dashboard-nav ul {
            list-style: none;
            padding: 0;
            display: flex;
            justify-content: space-around;
            background: #0073e6;
            border-radius: 5px;
        }
        .dashboard-nav ul li {
            margin: 0;
        }
        .dashboard-nav ul li a {
            text-decoration: none;
            color: white;
            padding: 10px 20px;
            display: block;
            font-weight: bold;
        }
        .dashboard-nav ul li a:hover {
            background: #005bb5;
            border-radius: 5px;
        }
        .logout-link {
            display: block;
            text-align: center;
            margin: 10px auto;
            padding: 10px 20px;
            background: #e60000;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            width: 150px;
        }
        .logout-link:hover {
            background: #b30000;
        }
        form {
            margin: 20px 0;
            text-align: center;
        }
        form label {
            font-size: 16px;
        }
        form input[type="text"] {
            padding: 5px;
            font-size: 14px;
            width: 300px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        form input[type="submit"] {
            padding: 5px 15px;
            font-size: 14px;
            background: #0073e6;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        form input[type="submit"]:hover {
            background: #005bb5;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table th, table td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }
        table th {
            background: #0073e6;
            color: white;
        }
        table tr:nth-child(even) {
            background: #f9f9f9;
        }
        table tr:hover {
            background: #f1f1f1;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Customer Dashboard</h1>
        <p style="font-size: 24px; font-weight: bold; text-align: center; color: #0073e6;">Welcome, <?php echo $username; ?>!</p>

        <?php
        // Fetch unread messages for passenger
        $msg_query = "SELECT id, message, created_at FROM messages WHERE recipient = 'passenger' AND is_read = FALSE ORDER BY created_at DESC";
        $msg_result = $conn->query($msg_query);
        if ($msg_result && $msg_result->num_rows > 0) {
            echo '<div class="notifications" style="background:#f9f9f9; border:1px solid #ccc; padding:10px; margin:10px auto; max-width:600px; border-radius:5px;">';
            echo '<h3>Notifications</h3>';
            echo '<ul>';
            while ($msg = $msg_result->fetch_assoc()) {
                echo '<li>' . htmlspecialchars($msg['message']) . ' <small>(' . htmlspecialchars($msg['created_at']) . ')</small></li>';
            }
            echo '</ul>';
            echo '</div>';
        }
        ?>

        <nav class="dashboard-nav">
            <ul>
                <li><a href="dashboard.php">Flights</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="store.php">Airport Store / Lounge Booking</a></li>
                <li><a href="messages.php">Messages / Notifications</a></li>
                <li><a href="feedback.php">Feedback / Support</a></li>
                <li><a href="uploads.php">Uploaded Documents</a></li>
            </ul>
            <a href="../logout.php" class="logout-link">Logout</a>
        </nav>
        <form method="GET" action="dashboard.php">
            <label>Search Flights: <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" /></label>
            <input type="submit" value="Search" />
        </form>
        <h2>Available Flights</h2>
        <table>
            <tr>
                <th>Flight Number</th>
                <th>Origin</th>
                <th>Destination</th>
                <th>Departure</th>
                <th>Arrival</th>
                <th>Price</th>
                <th>Seats Available</th>
            </tr>
            <?php
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['flight_number']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['origin']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['destination']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['departure']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['arrival']) . "</td>";
                    echo "<td>$" . htmlspecialchars($row['price']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['seats_available']) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No flights found or an error occurred.</td></tr>";
            }
            ?>
        </table>

        <h2>Upcoming Flights / Booking History</h2>
        <?php
        if ($booking_result && $booking_result->num_rows > 0) {
            echo "<table>";
            echo "<tr><th>Booking ID</th><th>Flight Number</th><th>Destination</th><th>Departure</th><th>Status</th><th>Notes</th><th>Actions</th></tr>";
            while ($booking = $booking_result->fetch_assoc()) {
                $booking_id_val = $booking['booking_id'];
                $flight_number = htmlspecialchars($booking['flight_number']);
                $destination = $booking['destination'];
                $departure = htmlspecialchars($booking['departure']);
                $status = $booking['status']; // No escaping for stored XSS
                $notes = $booking['notes'];   // No escaping for stored XSS

                echo "<tr>";
                echo "<td>$booking_id_val</td>";
                echo "<td>$flight_number</td>";
                echo "<td>$destination</td>";
                echo "<td>$departure</td>";
                echo "<td>$status</td>";
                echo "<td>$notes</td>";
                echo "<td>
                        <a href='boarding_pass.php?booking_id=$booking_id_val'>View Boarding Pass</a> | 
                        <a href='manage_booking.php?booking_id=$booking_id_val'>Manage Booking</a> | 
                        <a href='dashboard.php?cancel_booking=$booking_id_val'>Cancel Booking</a>
                      </td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No bookings found.</p>";
        }

        // Vulnerable: Cancel booking without CSRF protection or ownership check
        if (isset($_GET['cancel_booking'])) {
            $cancel_id = $_GET['cancel_booking'];
            // No validation or authorization check
            $cancel_query = "DELETE FROM bookings WHERE booking_id = $cancel_id";
            $conn->query($cancel_query);
            echo "<p style='color:red; font-weight:bold;'>Booking ID $cancel_id has been cancelled.</p>";
        }
        ?>
    </div>
</body>
</html>
