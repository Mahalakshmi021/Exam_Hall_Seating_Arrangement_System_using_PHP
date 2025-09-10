<?php
require_once '../config/db_connection.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['old_semester'], $_POST['new_semester'])) {
    $oldSemester = $_POST['old_semester'];
    $newSemester = $_POST['new_semester'];

    $query = "UPDATE students SET semester = ? WHERE semester = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $newSemester, $oldSemester);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Students updated successfully!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update students.", "error" => $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
}
?>
