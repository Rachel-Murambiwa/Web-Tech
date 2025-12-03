<?php
// ../actions/create_session.php
session_start();
require_once __DIR__ . '/../db/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = intval($_POST['course_id']);
    $faculty_id = $_SESSION['user_id'];
    
    // Generate a random 5-digit PIN
    $pin = rand(10000, 99999);
    
    // Close any previous active sessions for this course to avoid confusion
    $updateStmt = mysqli_prepare($conn, "UPDATE class_sessions SET status = 'closed' WHERE course_id = ? AND status = 'active'");
    mysqli_stmt_bind_param($updateStmt, "i", $course_id);
    mysqli_stmt_execute($updateStmt);
    
    // Create new session
    $stmt = mysqli_prepare($conn, "INSERT INTO class_sessions (course_id, faculty_id, session_pin) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iis", $course_id, $faculty_id, $pin);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'pin' => $pin]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}
?>