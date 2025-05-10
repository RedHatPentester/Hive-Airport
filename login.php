<?php
session_start();

include_once 'includes/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Vulnerable: No password hashing, no prepared statements (simulate SQL injection vulnerability)
    $query = "SELECT * FROM customers WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($query);

    if ($result && $result->num_rows === 1) {
        $_SESSION['username'] = $username;
        $_SESSION['role'] = 'customer';
        header("Location: customer/dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Hive Airport Customer Login</title>
    <link rel="stylesheet" type="text/css" href="assets/style.css" />
</head>
<style>
p {
    color: rgb(219, 216, 14);
    font-size: 18px;
    /* text-shadow: 0 0 5px rgba(255, 248, 248, 0.5); */
    font-weight: bold;
}

h3 {
    font-size: 18px;
    margin: 0;
    color: rgb(8, 121, 17);
    text-shadow: 0 0 10px rgba(255, 248, 248, 0.5);
}
</style>

<body style="    
    background-image: url('/assets/image7.jpeg');
    background-size: cover;
    background-position: center;
    text-shadow: 0 0 5px rgba(7, 7, 7, 0.5);
   ">
    <div class="container" style=" 
    backdrop-filter: blur(10px); 
    background-color: rgba(255, 255, 255, 0.16);
    margin-left: 60% 
    ">
        <h1>Customer Login to Hive Airport</h1>
        <?php if ($error) {
            echo "<p class='error-message'>$error</p>";
        } ?>
        <form method="POST" action="login.php">
            <label>
                <h3>Username:</h3> <input type="text" name="username" />
            </label><br />
            <label>
                <h3>Password:</h3> <input type="password" name="password" />
            </label><br />
            <input type="submit" value="Login" />
        </form>
        <p>Don't have an account? <a style="color:rgb(19, 62, 153), font-weight: bolder;" href="register.php">Register
                here</a></p>
        <p>Staff? <a href="staff/login.php">Login here</a></p>
    </div>
</body>

</html>