<?php
session_start();
require_once '../config/db_connection.php';
require_once '../classes/Auth.php';

$auth = new Auth($conn);
$error = "";
$otpDisplay = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];

    // Check if the username exists
    $user = $auth->getUserByUsername($username);

    if ($user) {
        // Generate a 6-digit OTP
        $otp = rand(100000, 999999);

        // Store the OTP and its expiry time in the database
        $auth->storeOTP($username, $otp);

        // Display the OTP (for testing purposes)
        $otpDisplay = "Your OTP is: $otp";

        // Store the username in the session for OTP verification
        $_SESSION['reset_username'] = $username;
    } else {
        $error = "Username not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h1>Forgot Password</h1>
            <form id="forgotPasswordForm" action="forgot_password.php" method="POST">
                <input type="text" name="username" id="username" placeholder="Username" required>
                <button type="submit">Get OTP</button>
                <p class="error"><?php echo $error; ?></p>
                <p class="otp-display"><?php echo $otpDisplay; ?></p>
            </form>
            <a href="verify_otp.php">Already have an OTP? Verify Here</a>
        </div>
    </div>
    <script src="../script.js"></script>
</body>
</html>