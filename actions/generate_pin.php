<?php
session_start();
require_once '../db/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) { 
    echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit; 
}

$course_id = $_POST['course_id'];
$faculty_id = $_SESSION['user_id'];
$pin = rand(10000, 99999);

// Close old sessions
$stmt = $conn->prepare("UPDATE class_sessions SET status='closed' WHERE course_id=? AND status='active'");
$stmt->bind_param("i", $course_id);
$stmt->execute();

// Create new session
$stmt = $conn->prepare("INSERT INTO class_sessions (course_id, faculty_id, session_pin) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $course_id, $faculty_id, $pin);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'pin' => $pin]);
} else {
    echo json_encode(['success' => false, 'message' => 'DB Error']);
}
?>