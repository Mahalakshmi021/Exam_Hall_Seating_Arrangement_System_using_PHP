<?php
session_start();
require_once '../config/db_connection.php';
require_once '../classes/Auth.php';

$auth = new Auth($conn);
$error = "";

if (!isset($_SESSION['reset_username'])) {
    header("Location: forgot_password.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

   
    $otp = $_POST['otp'];
    $username = $_SESSION['reset_username'];

    // Verify the OTP
    if ($auth->verifyOTP($username, $otp)) {
        header("Location: reset_password.php");
        exit();
    } else {
        $error = "Invalid or expired OTP.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <link rel="stylesheet" href="css/loginstyle1.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h1>Verify OTP</h1>
            <form id="verifyOTPForm" action="verify_otp.php" method="POST">
                <input type="text" name="otp" id="otp" placeholder="Enter OTP" required>
                <button type="submit">Verify OTP</button>
                <p class="error"><?php echo $error; ?></p>
            </form>
        </div>
    </div>
    <script src="../script.js"></script>
</body>
</html>