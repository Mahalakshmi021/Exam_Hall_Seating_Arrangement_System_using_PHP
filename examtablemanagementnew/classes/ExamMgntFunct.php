<?php
require_once '../config/db_connection.php';

class ExamManagementFunction {

    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

function insertIntoExam($examtype, $coursecode, $coursename, $examdate, $examtime, $examduration) {
    $sql = "INSERT INTO exams (course_code, exam_name, exam_type, exam_date, exam_time, examduration) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $this->conn->prepare($sql);

    if (!$stmt) {
        die("SQL Prepare Error: " . $this->conn->error);
    }

    // Ensure exam time is stored in HH:MM format
    $stmt->bind_param("sssssi", $coursecode, $coursename, $examtype, $examdate, $examtime, $examduration);

    if ($stmt->execute()) {
        return $this->conn->insert_id;
    } else {
        die("Execution Error: " . $stmt->error);
    }

    $stmt->close();
}

}
?>