<?php
session_start();
require_once '../config/db_connection.php';
require_once '../classes/DeptMgntFunctions.php';

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['department_id'], $data['department_name'], $data['block_id'])) {
        echo json_encode(["success" => false, "message" => "Invalid data"]);
        exit();
    }

    $deptMgntFunctions = new DeptMgntFunctions($conn);
    $department_id = intval($data['department_id']);
    $department_name = htmlspecialchars(trim($data['department_name']));
    $block_id = intval($data['block_id']);

    if ($deptMgntFunctions->updateDepartment($department_id, $department_name, $block_id)) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update department"]);
    }
}
?>
