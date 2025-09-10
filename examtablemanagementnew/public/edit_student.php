<?php
session_start();
require_once '../config/db_connection.php';
require_once '../classes/Auth.php';

if (!isset($_SESSION['userid'])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized access.']));
}

$auth = new Auth($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = intval($_POST['student_id']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $semester = intval($_POST['semester']);

    
    // Validate inputs
    if (empty($name) || empty($email) || $semester <= 0) {
        die(json_encode(['success' => false, 'message' => 'Invalid input.']));
    }

    // Update student details
    try {
        $auth->updateStudent($studentId, $name, $email, $semester);
        echo json_encode(['success' => true, 'message' => 'Student updated successfully.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}