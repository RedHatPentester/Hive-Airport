<?php
session_start();
include_once '../includes/config.php';

// Role-based access control
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../staff/login.php");
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

if ($user['access_level'] < 4) {
    die("Access denied: insufficient privileges.");
}

if ((($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ping') || ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'ping'))) {
    $target = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $target = $_POST['ping_target'] ?? '';
    } else {
        $target = $_GET['ping_target'] ?? '';
    }
    if (empty($target)) {
        echo json_encode(['error' => 'Ping target is required']);
        exit();
    }
    if (!preg_match('/^[A-Za-z0-9\.\-]+$/', $target)) {
        echo json_encode(['error' => 'Invalid ping target']);
        exit();
    }
    $output = [];
    $return_var = 0;
    exec("ping -c 4 " . escapeshellarg($target), $output, $return_var);
    echo json_encode(['output' => $output, 'success' => $return_var === 0]);
    exit();
}

// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrf_token = $_SESSION['csrf_token'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Dashboard - Hive Airport</title>
    <link rel="stylesheet" href="../assets/style.css" />
    <link rel="stylesheet" href="../static/old/jquery.dataTables.min.css" />
    <script src="../static/old/jquery.js"></script>
    <script src="../static/old/jquery.dataTables.min.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            /* background-image: url('../assets/image4.jpeg'); */
            margin: 0;
            padding: 0;
            color: #f9fafb;
        }
        .container {
            display: flex;
            background-image: url('../assets/image4.jpeg');
            background-size: cover;
            background-position: center;
            height: 100vh;
            overflow: hidden;
            width: 100%;
        }
        nav.sidebar {
            width: 280px;
            background-color:rgba(17, 24, 39, 0.68);
            backdrop-filter: blur(10px);
            color: #f9fafb;
            padding-top: 20px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            border-right: 2px solidrgba(55, 65, 81, 0.43);
        }
        nav.sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
            flex-grow: 1;
        }
        nav.sidebar ul li {
            padding: 15px 25px;
            cursor: pointer;
            border-bottom: 1px solid #374151;
            font-weight: 600;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        nav.sidebar ul li:hover, nav.sidebar ul li.active {
            background-color: #374151;
            color: #fbbf24;
        }
        nav.sidebar ul li svg {
            width: 20px;
            height: 20px;
            fill: #9ca3af;
        }
        nav.sidebar ul li.active svg {
            fill: #fbbf24;
        }
        main.content {
            flex-grow: 1;
            padding: 30px;
            overflow-y: auto;
            background-color:rgba(31, 41, 55, 0.47);
            box-shadow: inset 0 0 10px #374151;
            backdrop-filter: blur(10px);
        
            border-radius: 10px;
        }
        h1, h2 {
            margin-top: 0;
            color: #fbbf24;
        }
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .dashboard-header h1 {
            font-size: 28px;
            font-weight: 700;
        }
        .user-info {
            font-size: 16px;
            color: #9ca3af;
            background-color: #374151;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(251, 191, 36, 0.5);
            max-width: 300px;
            margin-top: 10px;
            line-height: 1.5;
        }
        .user-info p {
            margin: 5px 0;
            font-weight: 600;
            color: #fbbf24;
        }
        .section {
            margin-bottom: 40px;
        }
        .section h2 {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 20px;
            border-bottom: 2px solid #fbbf24;
            padding-bottom: 5px;
        }
        button, input[type="submit"] {
            background-color: #fbbf24;
            color: #1f2937;
            border: none;
            padding: 10px 20px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover, input[type="submit"]:hover {
            background-color: #f59e0b;
        }
        form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #f9fafb;
        }
        form input[type="text"], form input[type="password"], form select {
            width: 100%;
            padding: 8px 10px;
            margin-bottom: 15px;
            border: 1px solid #374151;
            border-radius: 6px;
            font-size: 14px;
            color: #f9fafb;
            background-color: #1f2937;
        }
        form input[type="text"]:focus, form input[type="password"]:focus, form select:focus {
            outline: none;
            border-color: #fbbf24;
            box-shadow: 0 0 5px #fbbf24;
        }
        .hidden {
            display: none;
        }
        table.dataTable thead th {
            background-color: #fbbf24;
            color: #1f2937;
        }
        .logout-link {
            color: #fbbf24;
            text-decoration: none;
            display: block;
            padding: 15px 25px;
            border-top: 1px solid #374151;
            margin-top: auto;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }
        .logout-link:hover {
            background-color: #374151;
        }
        .alert {
            padding: 10px;
            background-color: #ef4444;
            color: white;
            margin-bottom: 15px;
            border-radius: 6px;
        }
    </style>
</head>
<body>
    <div class="container">
        <nav class="sidebar" role="navigation" aria-label="Main navigation">
            <ul>
                <li class="active" data-section="staff-user-management" tabindex="0" aria-current="page">👥 Staff User Management</li>
                <li data-section="system-backup" tabindex="0">📂 System Backup / File Access</li>
                <li data-section="system-tools" tabindex="0">🖥️ System Tools / Terminal</li>
                <li data-section="user-activity-logs" tabindex="0">📜 User Activity Logs</li>
                <li data-section="system-settings" tabindex="0">📅 System Settings</li>
                <li><a href="../logout.php" class="logout-link" tabindex="0">Logout</a></li>
            </ul>
        </nav>
        <main class="content" role="main">
            <div class="dashboard-header">
                <h1>Admin Dashboard</h1>
                <div class="user-info">
                    <p>Welcome, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                    <p>Position: <?php echo htmlspecialchars($user['position']); ?></p>
                    <p>Department: <?php echo htmlspecialchars($user['department']); ?></p>
                    <p>Access Level: <?php echo htmlspecialchars($user['access_level']); ?></p>
                    <p>Hire Date: <?php echo htmlspecialchars($user['hire_date']); ?></p>
                </div>
            </div>

            <section id="staff-user-management" class="section">
                <h2>Staff User Management</h2>
                <div id="staff-list">
                    <!-- Staff list table will be loaded here -->
                </div>
                <button id="add-staff-btn">Add New Staff</button>
                <div id="add-staff-form" class="hidden">
                    <form method="POST" action="staff_user_management.php" autocomplete="off" id="add-staff-form-element">
                        <label for="new-username">Username:</label>
                        <input type="text" id="new-username" name="username" maxlength="30" required pattern="[A-Za-z0-9_]+" title="Alphanumeric and underscore only" />
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

            <section id="system-backup" class="section hidden">
                <h2>System Backup / File Access</h2>
                <div id="backup-files">
                    <!-- Backup files list will be loaded here -->
                </div>
                <button id="download-backup-btn">Download Latest Backup</button>
            </section>

            <section id="system-tools" class="section hidden">
                <h2>System Tools / Terminal</h2>
                <form id="ping-form" autocomplete="off">
                    <label for="ping-target">Ping Server:</label>
                    <input type="text" id="ping-target" name="ping_target" maxlength="100" pattern="[A-Za-z0-9\.\-]+" title="Alphanumeric, dots, and hyphens only" required />
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>" />
                    <input type="submit" value="Ping" />
                </form>
                <div id="ping-result" style="white-space: pre-wrap; background: #111827; color: #fbbf24; padding: 10px; border-radius: 6px; margin-top: 10px; max-height: 200px; overflow-y: auto;"></div>
                <h3>System Logs</h3>
                <div id="system-logs-list">
                    <!-- Logs will be loaded here -->
                </div>
            </section>

            <section id="user-activity-logs" class="section hidden">
                <h2>User Activity Logs</h2>
                <table id="activity-logs-table" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Details</th>
                            <th>IP Address</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Activity logs will be populated here -->
                    </tbody>
                </table>
            </section>

            <section id="system-settings" class="section hidden">
                <h2>System Settings</h2>
                <form id="settings-form" autocomplete="off">
                    <label for="maintenance-toggle">Maintenance Mode:</label>
                    <input type="checkbox" id="maintenance-toggle" name="maintenance-toggle" />
                    <label for="timezone">Timezone:</label>
                    <input type="text" id="timezone" name="timezone" maxlength="50" />
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>" />
                    <input type="submit" value="Save Settings" />
                </form>
            </section>
        </main>
    </div>

    <script>
        // Navigation sidebar tab switching
        const tabs = document.querySelectorAll('nav.sidebar ul li[data-section]');
        const sections = document.querySelectorAll('.section');

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
        // Initialize DataTable for activity logs
        $(document).ready(function() {
            var table = $('#activity-logs-table').DataTable({
                ajax: {
                    url: 'activity_logs.php',
                    dataSrc: 'logs'
                },
                columns: [
                    { data: 'id' },
                    { data: null, render: function(data, type, row) {
                        return row.first_name + ' ' + row.last_name;
                    }},
                    { data: 'action' },
                    { data: 'details' },
                    { data: 'ip_address' },
                    { data: 'created_at' }
                ],
                order: [[5, 'desc']],
                pageLength: 10,
                lengthChange: false,
                searching: false,
                info: false
            });

            // Poll for new activity logs every 10 seconds
            setInterval(function() {
                table.ajax.reload(null, false);
            }, 10000);
        });

        // Maintenance mode toggle
        $(document).ready(function() {
            // Load current maintenance mode status
            $.getJSON('maintenance_mode.php', function(data) {
                if (data.is_enabled) {
                    $('#maintenance-toggle').prop('checked', true);
                } else {
                    $('#maintenance-toggle').prop('checked', false);
                }
            });

            // Handle toggle change
            $('#maintenance-toggle').change(function() {
                var isEnabled = $(this).is(':checked');
                $.ajax({
                    url: 'maintenance_mode.php',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ is_enabled: isEnabled }),
                    success: function(response) {
                        alert('Maintenance mode ' + (response.is_enabled ? 'enabled' : 'disabled'));
                    },
                    error: function() {
                        alert('Failed to update maintenance mode');
                    }
                });
            });

            // Handle settings form submit (timezone save)
            $('#settings-form').submit(function(e) {
                e.preventDefault();
                // For now, just alert timezone saved
                alert('Settings saved (timezone: ' + $('#timezone').val() + ')');
            });
        });
    </script>
    <script>
        // Ping form AJAX submission
        $(document).ready(function() {
            $('#ping-form').submit(function(e) {
                e.preventDefault();
                var target = $('#ping-target').val();
                $('#ping-result').text('Pinging ' + target + '...\n');
                // Run ping command directly in this page using AJAX call to self
                $.ajax({
                    url: 'dashboard.php',
                    method: 'POST',
                    data: {
                        action: 'ping',
                        ping_target: target
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.error) {
                            $('#ping-result').html('<span style="color:red;">Error: ' + response.error + '</span>');
                        } else if (response.output) {
                            $('#ping-result').html('<pre>' + response.output.join("\n") + '</pre>');
                        } else {
                            $('#ping-result').html('Unknown response format.');
                        }
                    },
                    error: function() {
                        $('#ping-result').html('<span style="color:red;">Request failed.</span>');
                    }
                });
            });
        });
    </script>
</body>
</html>
