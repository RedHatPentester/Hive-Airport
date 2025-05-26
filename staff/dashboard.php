<?php
session_start();
include_once '../includes/config.php';

// Role-based access control
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../login.php");
    exit();
}

$username = $_SESSION['username'];

$stmt = $conn->prepare("SELECT * FROM staff WHERE first_name = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$user_result = $stmt->get_result();

if (!$user_result || $user_result->num_rows !== 1) {
    die("Access denied: user not found.");
}

$user = $user_result->fetch_assoc();

if ($user['access_level'] < 2) {
    die("Access denied: insufficient privileges.");
}

// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrf_token = $_SESSION['csrf_token'];

?>
<!DOCTYPE html>
<html>

<head>
    <title>Staff Dashboard - Hive Airport</title>
    <link rel="stylesheet" type="text/css" href="../assets/style.css" />
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-image: url('../assets/image3.jpeg');
            background-size: cover;
            background-position: center;
            margin: 0;
            padding: 0;
        }

        .container {
            display: flex;
            height: 100vh;
            padding: 40px;
            width: 60%;
            background: rgba(233, 227, 227, 0.35);
            backdrop-filter: blur(5px);
            gap: 30px;
            box-sizing: border-box;
        }

        nav.sidebar {
            width: 280px;
            background-color: #2c3e50;
            color: white;
            padding: 30px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            border-radius: 15px;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.2);
        }

        nav.sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        nav.sidebar ul li {
            padding: 20px 25px;
            cursor: pointer;
            border-bottom: 1px solid #34495e;
            transition: background-color 0.3s ease, color 0.3s ease;
            display: flex;
            align-items: center;
        }

        nav.sidebar ul li:hover,
        nav.sidebar ul li.active {
            background-color: #1abc9c;
            color: #ffffff;
        }

        nav.sidebar ul li span {
            margin-left: 15px;
        }

        main.content {
            flex-grow: 1;
            padding: 40px;
            overflow-y: auto;
            background: rgba(224, 203, 203, 0.35);
            backdrop-filter: blur(5px);
            border-radius: 15px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
            color: #f9fafb;
            backdrop-filter: brightness(0.7);
        }

        h1,
        h2 {
            margin-top: 0;
            color: #2c3e50;
        }

        .hidden {
            display: none;
        }

        table.dataTable thead th {
            background-color: #2980b9;
            color: white;
            text-align: left;
            padding: 15px;
        }

        .logout-link {
            color: #ecf0f1;
            text-decoration: none;
            display: block;
            padding: 20px 25px;
            border-top: 1px solid #34495e;
            margin-top: auto;
            text-align: center;
            transition: background-color 0.3s ease;
        }

        .logout-link:hover {
            background-color: #c0392b;
        }

        .form-section {
            margin-bottom: 30px;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="datetime-local"],
        select,
        textarea {
            width: 100%;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            box-sizing: border-box;
        }

        input[type="submit"],
        button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 15px 20px;
            cursor: pointer;
            border-radius: 10px;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover,
        button:hover {
            background-color: #2980b9;
        }

        .alert {
            padding: 15px;
            background-color: #e74c3c;
            color: white;
            margin-bottom: 20px;
            border-radius: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <nav class="sidebar">
            <ul>
                <li class="active" data-section="flight-management">🗂️ Flight Management</li>
                <li data-section="passenger-records">👥 Passenger Records</li>
                <li data-section="security-alerts">🛡️ Security Alerts / No-Fly List</li>
                <li data-section="messaging-center">📤 Messaging Center</li>
                <li data-section="staff-account-management">🔐 Staff Account Management</li>
                <li data-section="file-upload">🗃️ File Upload / Flight Reports</li>
                <!-- <li data-section="system-logs">🔍 System Logs or Tools</li> -->
                <li><a href="../logout.php" class="logout-link">Logout</a></li>
            </ul>
        </nav>
        <main class="content">
            <h1>Staff Dashboard</h1>
            <p>Welcome, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>!</p>
            <p>Position: <?php echo htmlspecialchars($user['position']); ?></p>
            <p>Department: <?php echo htmlspecialchars($user['department']); ?></p>
            <p>Access Level: <?php echo htmlspecialchars($user['access_level']); ?></p>
            <p>Hire Date: <?php echo htmlspecialchars($user['hire_date']); ?></p>

            <?php
            // Fetch unread messages for staff
            $msg_query = "SELECT id, message, created_at FROM messages WHERE recipient = 'staff' AND is_read = FALSE ORDER BY created_at DESC";
            $msg_result = $conn->query($msg_query);
            if ($msg_result && $msg_result->num_rows > 0) {
                echo '<div class="notifications">';
                echo '<h3>Notifications</h3>';
                echo '<ul>';
                while ($msg = $msg_result->fetch_assoc()) {
                    echo '<li>' . htmlspecialchars($msg['message']) . ' <small>(' . htmlspecialchars($msg['created_at']) . ')</small></li>';
                }
                echo '</ul>';
                echo '</div>';
            }
            ?>

            <!-- Flight Management Section -->
            <section id="flight-management" class="dashboard-section">
                <h2>Flight Management</h2>
                <div class="form-section">
                    <form id="flight-search-form" method="GET" action="flight_management.php" autocomplete="off">
                        <label for="flight-search">Search Flights:</label>
                        <input type="text" id="flight-search" name="search" maxlength="50" pattern="[A-Za-z0-9\s\-]*"
                            title="Alphanumeric, spaces, and hyphens only" />
                        <input type="submit" value="Search" />
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>" />
                    </form>
                </div>
                <div id="flight-results">
                    <!-- Flight results table will be loaded here -->
                </div>
            </section>

            <!-- Passenger Records Section -->
            <section id="passenger-records" class="dashboard-section hidden">
                <h2>Passenger Records</h2>
                <div id="passenger-list">
                    <?php
<<<<<<< HEAD
                    $passenger_query = "SELECT passenger_id, full_name, passport_number, email, phone, date_of_birth, nationality, flight_code, seat_number, check_in_status FROM passengers";
=======
                    $passenger_query = "SELECT passenger_id, full_name, passport_number, email, phone, date_of_birth, nationality, id, seat_number, check_in_status FROM passengers";
>>>>>>> c495875 (Add profile_pic column migration and fix profile.php error)
                    $passenger_result = $conn->query($passenger_query);
                    if ($passenger_result && $passenger_result->num_rows > 0) {
                        echo '<table id="passenger-table" class="display" style="width:100%">';
                        echo '<thead><tr><th>Passenger ID</th><th>Full Name</th><th>Passport Number</th><th>Email</th><th>Phone</th><th>Date of Birth</th><th>Nationality</th><th>Flight Code</th><th>Seat Number</th><th>Check-in Status</th></tr></thead><tbody>';
                        while ($row = $passenger_result->fetch_assoc()) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($row['passenger_id']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['full_name']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['passport_number']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['email']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['phone']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['date_of_birth']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['nationality']) . '</td>';
<<<<<<< HEAD
                            echo '<td>' . htmlspecialchars($row['flight_code']) . '</td>';
=======
                            echo '<td>' . htmlspecialchars($row['id']) . '</td>';
>>>>>>> c495875 (Add profile_pic column migration and fix profile.php error)
                            echo '<td>' . htmlspecialchars($row['seat_number']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['check_in_status']) . '</td>';
                            echo '</tr>';
                        }
                        echo '</tbody></table>';
                    } else {
                        echo '<p>No passenger records found.</p>';
                    }
                    ?>
                </div>
            </section>

            <!-- Security Alerts / No-Fly List Section -->
            <section id="security-alerts" class="dashboard-section hidden">
                <h2>Security Alerts / No-Fly List</h2>
                <form id="no-fly-form" method="POST" action="security_alerts.php" autocomplete="off">
                    <label for="alert-name">Name:</label>
                    <input type="text" id="alert-name" name="name" maxlength="100" required pattern="[A-Za-z0-9\s\.\-]*"
                        title="Alphanumeric, spaces, dots, and hyphens only" />
                    <label for="alert-reason">Reason:</label>
                    <textarea id="alert-reason" name="reason" maxlength="500" required></textarea>
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>" />
                    <input type="submit" value="Add to No-Fly List" />
                </form>
                <div id="no-fly-list">
                    <!-- No-fly list table will be loaded here -->
                </div>
            </section>

            <!-- Messaging Center Section -->
            <section id="messaging-center" class="dashboard-section hidden">
                <h2>Messaging Center</h2>
                <form id="message-form" method="POST" action="messaging_center.php" autocomplete="off">
                    <label for="message-recipient">Recipient:</label>
                    <select id="message-recipient" name="recipient" required>
                        <option value="">Select recipient</option>
                        <option value="staff">Staff</option>
                        <option value="passenger">Passenger</option>
                    </select>
                    <label for="message-content">Message:</label>
                    <textarea id="message-content" name="message" maxlength="1000" required></textarea>
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>" />
                    <input type="submit" value="Send Message" />
                </form>
                <div id="message-log">
                    <!-- Message log will be loaded here -->
                </div>
            </section>

            <!-- Staff Account Management Section -->
            <section id="staff-account-management" class="dashboard-section hidden">
                <h2>Staff Account Management</h2>
                <div id="staff-list">
                    <!-- Staff list table will be loaded here -->
                </div>
                <button id="add-staff-btn">Add New Staff</button>
                <div id="add-staff-form" class="hidden">
                    <form method="POST" action="staff_account_management.php" autocomplete="off"
                        id="add-staff-form-element">
                        <label for="new-username">Username:</label>
                        <input type="text" id="new-username" name="username" maxlength="30" required
                            pattern="[A-Za-z0-9_]+" title="Alphanumeric and underscore only" />
                        <label for="new-password">Password:</label>
                        <input type="password" id="new-password" name="password" minlength="8" required />
                        <label for="new-role">Role:</label>
                        <select id="new-role" name="role" required>
                            <option value="staff">Staff</option>
                            <option value="admin">Admin</option>
                        </select>
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>" />
                        <input type="submit" value="Create Staff" />
                    </form>
                </div>
            </section>

            <!-- File Upload / Flight Reports Section -->
            <section id="file-upload" class="dashboard-section hidden">
                <h2>File Upload / Flight Reports</h2>
                <form id="file-upload-form" method="POST" action="file_upload.php" enctype="multipart/form-data"
                    autocomplete="off">
                    <label for="report-file">Upload Report:</label>
                    <input type="file" id="report-file" name="report_file" accept=".pdf,.txt,.log" required />
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>" />
                    <input type="submit" value="Upload" />
                </form>
                <div id="uploaded-files">
                    <!-- Uploaded files list will be loaded here -->
                </div>
            </section>

            <!-- System Logs or Tools Section -->
            <section id="system-logs" class="dashboard-section hidden">
                <!-- Removed system logs or tools section as per user request -->
        </main>
    </div>

    <script>
        // Navigation sidebar tab switching
        const tabs = document.querySelectorAll('nav.sidebar ul li[data-section]');
        const sections = document.querySelectorAll('.dashboard-section');

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                const sectionId = tab.getAttribute('data-section');
                sections.forEach(section => {
                    if (section.id === sectionId) {
                        section.classList.remove('hidden');
                    } else {
                        section.classList.add('hidden');
                    }
                });
            });
        });

        // Initialize DataTables for dynamic tables (to be loaded via AJAX or server-side)
        // Placeholder: actual data loading to be implemented in respective PHP files

        // Show/hide add staff form
        const addStaffBtn = document.getElementById('add-staff-btn');
        const addStaffForm = document.getElementById('add-staff-form');
        addStaffBtn.addEventListener('click', () => {
            if (addStaffForm.classList.contains('hidden')) {
                addStaffForm.classList.remove('hidden');
            } else {
                addStaffForm.classList.add('hidden');
            }
        });
    </script>
    <script>
        $(document).ready(function () {
            $('#passenger-table').DataTable();
        });
    </script>
</body>

</html>