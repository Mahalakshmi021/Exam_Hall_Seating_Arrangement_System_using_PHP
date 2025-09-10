<?php
session_start();
require_once '../config/db_connection.php';
require_once '../classes/ExamMgntFunct.php';

$examMgnt = new ExamManagementFunction($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['examtype'], $_POST['coursecode'], $_POST['coursename'], $_POST['examdate'], $_POST['examtime'], $_POST['examduration'])) {
        $examtype = trim($_POST['examtype']);
        $coursecode = trim($_POST['coursecode']);
        $coursename = trim($_POST['coursename']);
        $examdate = trim($_POST['examdate']);
        $examtime = trim($_POST['examtime']);
        $examduration = trim($_POST['examduration']);

        // ✅ Ensure exam time is stored as HH:MM (No seconds/milliseconds)
        $examtime = date("H:i", strtotime($examtime));

        // ✅ Convert duration from minutes to Hours:Minutes
        $hours = floor($examduration / 60); // Get hours
        $minutes = $examduration % 60; // Get remaining minutes
        $examduration = sprintf("%02d:%02d", $hours, $minutes); // Format as HH:MM

        $inserted_id = $examMgnt->insertIntoExam($examtype, $coursecode, $coursename, $examdate, $examtime, $examduration);

        if ($inserted_id) {
            echo "<script>alert('Exam inserted successfully!');</script>";
        } else {
            echo "<script>alert('Insertion failed! Please try again.');</script>";
        }
    } else {
        echo "<script>alert('Missing input data!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Entry</title>
    <script>
     function goToHome() {
            window.location.replace("dashboard.php"); // Change "home.php" to your actual home page
        }
    </script>
    <link rel="stylesheet" href="css/examsstyle.css">
</head>
<body>
<div class="sidebar">
        <?php include 'sidebar.php';?>
    </div>
    <div class="title-bar">
        <h1>Add Exams</h1>
        <button onclick="goToHome()">Home</button>
    </div>
    <div class="container">
        <h1>Exam Entry</h1>

<form action="" method="POST">
    <label>Exam Type:</label>
<select name="examtype" required>
    <option value="" disabled selected>Select Exam Type</option> <!-- Placeholder -->
    <option value="FIRST SERIES EXAMINATION">First Series Examination</option>
    <option value="SECOND SERIES EXAMINATION">Second Series Examination</option>
    <option value="UNIVERSITY EXAMINATION">University Examination</option>
</select><br>

    <label>Course Code:</label>
    <input type="text" name="coursecode" required><br>

    <label>Course Name:</label>
    <input type="text" name="coursename" required><br>

    <label>Exam Date:</label>
    <input type="date" name="examdate" required><br>

    <label>Exam Time:</label>
    <input type="time" name="examtime" required><br>

    <label>Exam Duration (in minutes):</label>
    <input type="number" name="examduration" required><br>

    <button type="submit">Add Exam</button>
</form>
</div>
</body>
</html>
