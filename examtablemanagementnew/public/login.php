<?php
session_start();
require_once '../config/db_connection.php';
require_once '../classes/Auth.php';

$auth = new Auth($conn);
$error = "";

// Check if there is an error message stored in the session
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']); // Clear the error after displaying it
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['login'])) {
        // Handle login
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Call the login method from Auth.php
        $user = $auth->login($username, $password);

        if ($user) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['userid'] = $user['user_id'];
            $_SESSION['user_type'] = $user['usertype'];
            if($user['usertype'] == 'admin'){
            header("Location: dashboard.php");
            }
            else{
             header("Location: search_student_location.php");
            }
            exit();
        } else {
            $_SESSION['error'] = "Invalid username or password!";
            header("Location: login.php"); // Redirect to refresh the page
            exit();
        }
    } elseif (isset($_POST['forgot_password'])) {
        // Handle forgot password (OTP generation)
        $username = $_POST['username'];

        // Check if the username exists
        $user = $auth->getUserByUsername($username);

        if ($user) {
            // Generate a 6-digit OTP
            $otp = rand(100000, 999999);

            // Store the OTP and its expiry time in the database
            $auth->storeOTP($username, $otp);

            // Display the OTP (for testing purposes)
            $_SESSION['otp_display'] = "Your OTP is: $otp";

            // Store the username in the session for OTP verification
            $_SESSION['reset_username'] = $username;

            // Redirect to the OTP verification page
            header("Location: verify_otp.php");
            exit();
        } else {
            $_SESSION['error'] = "Username not found.";
            header("Location: login.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="css/loginstyle1.css"> <!-- Adjust path for styles -->
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h1>Login</h1>
            <form id="loginForm" action="login.php" method="POST">
                <input type="text" name="username" id="username" placeholder="Username" required>
                <input type="password" name="password" id="password" placeholder="Password" required>
                <button type="submit" name="login">Login</button>
                <p class="error"><?php echo $error; ?></p>
            </form>

            <!-- Forgot Password Section -->
            <div class="forgot-password">
                <h3>Forgot Password?</h3>
                <form id="forgotPasswordForm" action="login.php" method="POST">
                    <input type="text" name="username" id="forgot_username" placeholder="Enter your username" required>
                    <button type="submit" name="forgot_password">Get OTP</button>
                </form>
                <?php if (isset($_SESSION['otp_display'])): ?>
                    <p class="otp-display"><?php echo $_SESSION['otp_display']; unset($_SESSION['otp_display']); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="../script.js"></script> <!-- Adjust path for script -->
</body>
</html>