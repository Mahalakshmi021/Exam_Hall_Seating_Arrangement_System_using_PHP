<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../config/db_connection.php';
require_once '../classes/Auth.php';
require_once '../config/db_connection.php';
require_once '../classes/DeptMgntFunctions.php';
$auth = new Auth($conn);
$error = "";

// Check for session error messages
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']); // Clear error after displaying
}
$deptMgntFunctions = new DeptMgntFunctions($conn);
$departments = $deptMgntFunctions->getAllDepartments();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Debugging: Print POST data
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";

    // Sanitize user input
    $username = trim($_POST['name']);  
    $email = trim($_POST['email']);
    $registernumber = trim($_POST['register_number']);
    $semester = trim($_POST['semester']); 
    $course = trim($_POST['course']);
    $password = trim($_POST['password']); 
 
     $departmentid = $_POST['department'];


    echo "Processing Registration for: " . $username;

    // Call register function
    $registration = $auth->register($username,$email,$registernumber,$password,$course,$semester,$departmentid);

    if ($registration) {
        // Store session data
 
        header("Location: registration.php");
        exit();
    } else {
        $_SESSION['error'] = "Registration Unsuccessful";
        header("Location: registration.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
    <script>
     function goToHome() {
            window.location.replace("dashboard.php"); // Change "home.php" to your actual home page
        }
    </script>
    <link rel="stylesheet" href="css/registrationstyle.css"> 
</head>
<body>
     <div class="sidebar">
        <?php include 'sidebar.php';?>
    </div>
    <div class="title-bar">
        <h1>Registration Form</h1>
        <button onclick="goToHome()">Home</button>
    </div>
    <div class="container">
        <h1>Registration Form</h1>
        <?php if (!empty($error)) : ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        

        <div class="registration-form">
            <form id="registration-form" action="registration.php" method="POST">
                <input type="text" name="name" placeholder="Name" id="name" required>
                <div class="error-message" id="name-error"></div>

                <input type="email" name="email" placeholder="Email" id="email" required>
                <div class="error-message" id="email-error"></div>

                <input type="text" name="register_number" placeholder="Register Number" id="register-number" required>
                <div class="error-message" id="register-number-error"></div>

                <input type="password" name="password" placeholder="Password" id="password" required>
                <div class="error-message" id="password-error"></div>
              <label for="department">Department:</label>
              <select id="department" name="department">

                <option value="">Select Department</option>


                <?php foreach($departments as $department): ?> 
                  <option value="<?= htmlspecialchars($department['department_id']) ?>">
                    <?= htmlspecialchars($department['department_name']) ?>

                  </option>
                 <?php endforeach;  ?>
                <!-- Add more options as needed -->
              </select>
              <br><br>
                <select name="course" id="course" required>
                    <option value="">Select Course</option>
                    <option value="btech">B.Tech</option>
                    <option value="mtech">M.Tech</option>
                    <option value="mca">MCA</option>
                    <option value="mba">MBA</option>
                </select>
                <div class="error-message" id="course-error"></div>

                <select name="semester" id="semester" required>
                    <option value="">Select Semester</option>
                    <option value="1">1st Semester</option>
                    <option value="2">2nd Semester</option>
                    <option value="3">3rd Semester</option>
                    <option value="4">4th Semester</option>
                    <option value="5">5th Semester</option>
                    <option value="6">6th Semester</option>
                    <option value="7">7th Semester</option>
                    <option value="8">8th Semester</option>
                </select>
                <div class="error-message" id="semester-error"></div>

                <button type="submit">Register</button>
            </form>
        </div>
    </div>
</body>
</html>
