<?php
session_start();

include_once '../includes/config.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

$username = $_SESSION['username'];

$feedback_message = "";
$redirect_url = "feedback.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment = $_POST['comment'] ?? '';

    // Vulnerable: No input sanitization, stored XSS possible
    $stmt = $conn->prepare("INSERT INTO feedback (username, comment) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $comment);
    $stmt->execute();
    $stmt->close();

    // Vulnerable: Open redirect via ?redirect= parameter
    if (isset($_GET['redirect'])) {
        $redirect_url = $_GET['redirect'];
    }

    header("Location: $redirect_url?submitted=1");
    exit();
}

$submitted = isset($_GET['submitted']) ? true : false;

// Fetch all feedback comments
$result = $conn->query("SELECT username, comment FROM feedback ORDER BY id DESC");

?>

<!DOCTYPE html>
<html>
<head>
    <title>Feedback / Support - Hive Airport</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(to right, #74ebd5, #acb6e5);
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
            color: #2c3e50;
        }
        nav.dashboard-nav {
            margin-bottom: 20px;
            background: transparent;
            padding: 10px;
            display: flex;
            justify-content: flex-end;
        }
        nav.dashboard-nav a {
            color: white;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 5px;
            background-color:rgb(179, 24, 24);
            font-size: 14px;
            transition: background-color 0.3s ease;
        }
        nav.dashboard-nav a:hover {
            background-color: #1abc9c;
        }
        form {
            margin-bottom: 30px;
        }
        form label {
            font-size: 16px;
            font-weight: bold;
            display: block;
            margin-bottom: 10px;
        }
        form textarea {
            width: 95%;
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 5px;
            resize: none;
            margin-bottom: 20px
        }
        form input[type="submit"] {
            padding: 10px 20px;
            font-size: 16px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        form input[type="submit"]:hover {
            background: #2980b9;
        }
        .feedback-list {
            margin-top: 20px;
        }
        .feedback-list p {
            background: #f9f9f9;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .feedback-list p strong {
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Feedback / Support</h1>
        <nav class="dashboard-nav">
            <a href="dashboard.php">Back</a>
        </nav>

        <?php if ($submitted): ?>
            <p class="success-message">Thank you for your feedback!</p>
        <?php endif; ?>

        <form method="POST" action="feedback.php<?php echo isset($_GET['redirect']) ? '?redirect=' . htmlspecialchars($_GET['redirect']) : ''; ?>">
            <label>Submit your feedback or complaints:</label>
            <textarea name="comment" rows="5" placeholder="Write your feedback here..."></textarea>
            <input type="submit" value="Submit Feedback" />
        </form>

        <h2>Previous Feedback</h2>
        <div class="feedback-list">
            <?php
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<p><strong>" . htmlspecialchars($row['username']) . ":</strong> " . htmlspecialchars($row['comment']) . "</p>";
                }
            } else {
                echo "<p>No feedback yet.</p>";
            }
            ?>
        </div>
    </div>
</body>
</html>
