<?php
require_once '../config/db_connection.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['student_id'])) {
    $student_id = intval($_POST['student_id']);

    $query = "DELETE FROM students WHERE student_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $student_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Student deleted successfully!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to delete student.", "error" => $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
}
?>
