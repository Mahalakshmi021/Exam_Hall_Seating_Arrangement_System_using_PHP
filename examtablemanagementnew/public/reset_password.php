<?php
session_start();
require_once '../config/db_connection.php';
require_once '../classes/Auth.php';

$auth = new Auth($conn);
$error = "";
$success = "";

if (!isset($_SESSION['reset_username'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($password === $confirmPassword) {
        // Update the password
        $auth->updatePassword($_SESSION['reset_username'], $password);
        $success = "Your password has been reset successfully.";
        unset($_SESSION['reset_username']); // Clear the session
    } else {
        $error = "Passwords do not match.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="css/loginstyle1.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h1>Reset Password</h1>
            <?php if (empty($success)): ?>
                <!-- Show the reset password form if the password hasn't been reset yet -->
                <form id="resetPasswordForm" action="reset_password.php" method="POST">
                    <input type="password" name="password" id="password" placeholder="New Password" required>
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
                    <button type="submit">Reset Password</button>
                    <p class="error"><?php echo $error; ?></p>
                </form>
            <?php else: ?>
                <!-- Show success message and a login button after successful reset -->
                <p class="success"><?php echo $success; ?></p>
                <a href="login.php" class="login-button">Login</a>
            <?php endif; ?>
        </div>
    </div>
    <script src="../script.js"></script>
</body>
</html>