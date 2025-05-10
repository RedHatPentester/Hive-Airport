<?php
session_start();

include_once '../includes/config.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

$username = $_SESSION['username'];

$upload_message = "";

$upload_dir = __DIR__ . '/uploads/';

// Create uploads directory if not exists
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['upload_file'])) {
    $file = $_FILES['upload_file'];

    // Vulnerable: No file type or extension check, no sanitization of filename
    $filename = basename($file['name']);

    // Vulnerable: Path traversal possible by including ../ in filename
    $target_path = $upload_dir . $filename;

    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        // Vulnerable: Insecure file permissions (world readable and writable)
        chmod($target_path, 0777);
        $upload_message = "File uploaded successfully: $filename";
    } else {
        $upload_message = "Failed to upload file.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_file'])) {
    $file_to_delete = basename($_POST['delete_file']); // Prevent directory traversal
    $target_path = $upload_dir . $file_to_delete;

    if (file_exists($target_path)) {
        unlink($target_path);
        $upload_message = "File deleted successfully: $file_to_delete";
    } else {
        $upload_message = "File not found: $file_to_delete";
    }
}

// List uploaded files
$files = scandir($upload_dir);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Uploaded Documents - Hive Airport</title>
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
        .upload-message {
            text-align: center;
            font-size: 18px;
            color: green;
            margin-bottom: 20px;
        }
        form {
            text-align: center;
            margin-bottom: 30px;
        }
        form label {
            font-size: 16px;
        }
        form input[type="file"] {
            margin: 10px 0;
        }
        form input[type="submit"] {
            padding: 10px 20px;
            font-size: 16px;
            background: #0073e6;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        form input[type="submit"]:hover {
            background: #005bb5;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        ul li {
            background: #f9f9f9;
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        ul li a {
            text-decoration: none;
            color: #0073e6;
            font-weight: bold;
        }
        ul li a:hover {
            text-decoration: underline;
        }
        ul li form {
            display: inline;
        }
        ul li form input[type="submit"] {
            background: #e60000;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="javascript:history.back()" class="back-button">Back</a>
        <h1>Uploaded Documents / Identity Verification</h1>

        <?php if ($upload_message): ?>
            <p class="upload-message"> <?php echo htmlspecialchars($upload_message); ?> </p>
        <?php endif; ?>

        <form method="POST" action="uploads.php" enctype="multipart/form-data">
            <label>Upload your document (ID, boarding pass, COVID test result):<br />
                <input type="file" name="upload_file" />
            </label><br /><br />
            <input type="submit" value="Upload" />
        </form>

        <h2>Uploaded Files</h2>
        <ul>
            <?php
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') continue;
                echo '<li>';
                echo '<a href="uploads/' . $file . '" target="_blank">' . htmlspecialchars($file) . '</a> ';
                echo '<form method="POST" action="uploads.php" style="display:inline;" onsubmit="return confirm(\'Are you sure you want to delete this file?\');">';
                echo '<input type="hidden" name="delete_file" value="' . htmlspecialchars($file) . '" />';
                echo '<input type="submit" value="Delete" />';
                echo '</form>';
                echo '</li>';
            }
            ?>
        </ul>
    </div>
</body>
</html>
