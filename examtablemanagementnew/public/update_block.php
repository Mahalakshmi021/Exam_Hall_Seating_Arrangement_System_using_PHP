<?php
session_start();

require_once '../config/db_connection.php';
require_once '../classes/BlockMgntFunct.php';

// Ensure the user is logged in
if (!isset($_SESSION['userid'])) {
    die(json_encode(["success" => false, "message" => "Unauthorized access."]));
}

$userid = $_SESSION['userid'];

// Get the raw POST data
$data = json_decode(file_get_contents("php://input"), true);

error_log("Received data: " . print_r($data, true)); // Debugging

// Validate input
if (isset($data['block_id']) && isset($data['block_name'])) {
    $block_id = $data['block_id'];
    $block_name = trim($data['block_name']);

    // Pass database connection to the class
    $blockMgntFunct = new BlockMgntFunct($conn);

    // Update the block
    $result = $blockMgntFunct->updateBlock($block_id, $block_name, $userid);

    if ($result === true) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => $result]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid input."]);
}
?>