<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

$username = $_SESSION['username'];

// Simulated messages
$messages = [
    ['id' => 1, 'content' => 'Your flight OAT1234 has been delayed by 30 minutes.'],
    ['id' => 2, 'content' => 'Boarding gate changed to B12 for flight OAT5678.'],
    ['id' => 3, 'content' => 'Support reply: Your complaint has been received and is being processed.'],
];

// Vulnerable: Reflected XSS via ?alert= parameter
$alert = $_GET['alert'] ?? '';

?>

<!DOCTYPE html>
<html>
<head>
    <title>Messages / Notifications - Hive Airport</title>
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
            max-width: 800px;
            margin: 50px auto;
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        h1 {
            text-align: center;
            color: #444;
        }
        .back-button {
            display: inline-block;
            margin: 10px 0;
            padding: 10px 20px;
            background: #0073e6;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .back-button:hover {
            background: #005bb5;
        }
        .messages-list {
            list-style: none;
            padding: 0;
        }
        .messages-list li {
            background: #f9f9f9;
            margin: 10px 0;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
        }
        .messages-list li:hover {
            background: #f1f1f1;
        }
        .message-icon {
            width: 40px;
            height: 40px;
            background: #0073e6;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            margin-right: 15px;
        }
        .message-content {
            flex: 1;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="javascript:history.back()" class="back-button">Back</a>
        <h1>Messages / Notifications</h1>
        <ul class="messages-list">
            <?php foreach ($messages as $msg): ?>
                <li>
                    <div class="message-icon">✈️</div>
                    <div class="message-content">
                        <?php echo htmlspecialchars($msg['content']); ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>
