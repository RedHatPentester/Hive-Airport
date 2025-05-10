<?php
session_start();

include_once '../includes/config.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

$username = $_SESSION['username'];

// Vulnerable: IDOR - user can specify ?id= to edit another user's profile
$user_id = isset($_GET['id']) ? intval($_GET['id']) : null;

if ($user_id === null) {
    // Get user id from username
    $stmt = $conn->prepare("SELECT id FROM customers WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($user_id);
    $stmt->fetch();
    $stmt->close();
}

if ($user_id === null) {
    die("User ID is not specified or invalid.");
}

// Fetch user profile data
$query = "SELECT id, username, email, profile_pic FROM customers WHERE id = $user_id";
$result = $conn->query($query);

if (!$result || $result->num_rows === 0) {
    die("User not found.");
}

$user = $result->fetch_assoc();

$update_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['upload_pic']) && isset($_FILES['profile_pic'])) {
        $target_dir = '../pic_uploads/';
        $target_file = $target_dir . basename($_FILES['profile_pic']['name']);
        // Vulnerable: No file type or size checks, allowing any file upload
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file)) {
            $profile_pic = basename($_FILES['profile_pic']['name']);
            $conn->query("UPDATE customers SET profile_pic = '$profile_pic' WHERE id = " . $_POST['user_id']);
            $update_message = "Profile picture updated successfully.";
        } else {
            $update_message = "Error uploading profile picture.";
        }
    }

    if (isset($_POST['email']) || isset($_POST['new_password'])) {
        $email = $_POST['email'];
        $new_password = $_POST['new_password'];

        $update_query = "UPDATE customers SET email = '$email'";
        if (!empty($new_password)) {
            // Vulnerable: Using md5 for password hashing
            $hashed_password = md5($new_password);
            $update_query .= ", password = '$hashed_password'";
        }
        $update_query .= " WHERE id = " . $_POST['user_id'];
        if ($conn->query($update_query)) {
            $update_message = "Profile updated successfully.";
        } else {
            $update_message = "Error updating profile: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profile - Hive Airport</title>
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
        .profile-pic {
            text-align: center;
            margin-bottom: 20px;
        }
        .profile-pic img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .profile-pic input[type="file"] {
            margin-top: 10px;
        }
        form {
            margin-top: 20px;
        }
        form label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }
        form input[type="text"], form input[type="email"], form input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        form input[type="submit"] {
            padding: 10px 20px;
            background: #0073e6;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        form input[type="submit"]:hover {
            background: #005bb5;
        }
        .update-message {
            text-align: center;
            font-size: 18px;
            color: green;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Profile</h1>

        <?php if ($update_message): ?>
            <p class="update-message"> <?php echo $update_message; ?> </p>
        <?php endif; ?>

        <div class="profile-pic">
            <img src="<?php echo isset($user['profile_pic']) ? '../pic_uploads/' . $user['profile_pic'] : '../assets/image2.jpeg'; ?>" alt="Profile Picture">
            <form method="POST" action="profile.php" enctype="multipart/form-data">
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                <input type="file" name="profile_pic">
                <input type="submit" name="upload_pic" value="Upload Picture">
            </form>
        </div>
        <button onclick="window.history.back()" style="margin-top: 10px; padding: 8px 16px; background-color: #0073e6; color: white; border: none; border-radius: 5px; cursor: pointer;">Back</button>

        <form method="POST" action="profile.php">
            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
            <label>Username:</label>
            <input type="text" name="username" value="<?php echo $user['username']; ?>" readonly>

            <label>Email:</label>
            <input type="email" name="email" value="<?php echo $user['email']; ?>">

            <label>New Password:</label>
            <input type="password" name="new_password">

            <input type="submit" value="Update Profile">
        </form>
    </div>
</body>
</html>
