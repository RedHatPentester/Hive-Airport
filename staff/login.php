<?php
session_start();

include_once '../includes/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Vulnerable: No password hashing, no prepared statements (simulate SQL injection vulnerability)
    $query = "SELECT * FROM staff WHERE first_name = '$username' AND password = '$password'";
    $result = $conn->query($query);

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION['username'] = $username;
        if (strtolower($user['position']) === 'admin') {
            $_SESSION['role'] = 'admin';
            header("Location: ../admin/dashboard.php");
        } else {
            $_SESSION['role'] = 'staff';
            header("Location: ../staff/dashboard.php");
        }
        exit();
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Hive Airport Staff Login</title>
    <link rel="stylesheet" type="text/css" href="../assets/style.css" />
</head>
<body style="    
    background-image: url('/assets/image5.jpeg');
    background-size: cover;
    background-position: center;
    ">
    <div class="container">
        <h1>Staff Login to Hive Airport</h1>
        <?php if ($error) { echo "<p class='error-message'>$error</p>"; } ?>
        <form method="POST" action="login.php">
            <label>Username: <input type="text" name="username" /></label><br />
            <label>Password: <input type="password" name="password" /></label><br />
            <input type="submit" value="Login" />
        </form>
        <p>Customer? <a href="../login.php">Login here</a></p>
    </div>
</body>
</html>
