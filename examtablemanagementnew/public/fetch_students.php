<?php
session_start();
require_once '../config/db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $departmentId = intval($_POST['department_id']);
    $semester = $_POST['semester'];

    // Debugging: Log received data
    error_log("Received department_id: " . $departmentId . ", semester: " . $semester);

    // Query the database for students based on department and semester
    $sql = "SELECT student_id,reg_number, name FROM students WHERE department = ? AND semester = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $departmentId, $semester);
    $stmt->execute();
    $result = $stmt->get_result();

    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }

    // Debugging: Log the response
    error_log("Response: " . json_encode($students));

    echo json_encode($students);
}
?>