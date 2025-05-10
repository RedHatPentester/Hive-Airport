<?php
session_start();

include_once 'includes/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $email = $_POST['email'] ?? '';

    if (empty($username) || empty($password) || empty($confirm_password) || empty($email)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Vulnerable: No input sanitization, no password hashing, no prepared statements
        $check_query = "SELECT * FROM customers WHERE username = '$username'";
        $check_result = $conn->query($check_query);

        if ($check_result && $check_result->num_rows > 0) {
            $error = "Username already taken.";
        } else {
            $insert_query = "INSERT INTO customers (username, password, email) VALUES ('$username', '$password', '$email')";
            if ($conn->query($insert_query) === TRUE) {
                $success = "<span style='font-size: 1.5em; color: #FFFF00;'>Registration successful. You can now <a href='login.php'>login</a>.</span>";
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Register - Hive Airport</title>
    <link rel="stylesheet" type="text/css" href="assets/style.css" />
</head>

<body style="
    background-image: url('/assets/image6.jpeg');
    background-size: cover;
    backdrop-filter: blur(5px);

    ">
    <div class="container"
        style="margin-top: 5px; margin-bottom: 5px; padding: 10px; padding-left: 50px; padding-right: 50px; ">
        <h1>Register for Hive Airport</h1>
        <?php if ($error) {
            echo "<p class='error-message'>$error</p>";
        } ?>
        <?php if ($success) {
            echo "<p class='success-message'>$success</p>";
        } ?>
        <form method="POST" action="register.php">
            <label>Username: <input type="text" name="username" required /></label><br />
            <label>Email: <input type="email" name="email" required /></label><br />
            <label>Password: <input type="password" name="password" required /></label><br />
            <label>Confirm Password: <input type="password" name="confirm_password" required /></label><br />
            <input type="submit" value="Register" />
        </form>
        <p style="color:rgb(3, 46, 3); font-weight: bold;">Already have an account? <a href="login.php"
                style="color: #0000a0">Login here</a></p>
    </div>
</body>

</html>