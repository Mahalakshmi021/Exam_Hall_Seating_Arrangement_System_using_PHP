<?php
session_start();
require_once '../config/db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $examType = $_POST['exam_type'];

    // Query the database for exams based on the exam type
    $sql = "SELECT exam_id, exam_name FROM exams WHERE exam_type = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $examType);
    $stmt->execute();
    $result = $stmt->get_result();

    $exams = [];
    while ($row = $result->fetch_assoc()) {
        $exams[] = $row;
    }

    echo json_encode($exams);
}
?>