<?php
// ../actions/mark_attendance.php
session_start();
require_once __DIR__ . '/../db/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../view/login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$pin = isset($_POST['session_pin']) ? trim($_POST['session_pin']) : '';

if (empty($pin)) {
    $_SESSION['msg'] = "Please enter a PIN.";
    header("Location: ../view/student.php");
    exit();
}

// 1. Find active session with this PIN
$sql = "SELECT id, course_id FROM class_sessions WHERE session_pin = ? AND status = 'active' LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $pin);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    $session_id = $row['id'];
    $course_id = $row['course_id'];
    
    // 2. Check if student is enrolled in this course (Security Check)
    $check_enroll = mysqli_query($conn, "SELECT * FROM course_requests WHERE student_id = $student_id AND course_id = $course_id AND status='approved'");
    
    if (mysqli_num_rows($check_enroll) > 0) {
        // 3. Mark Attendance
        $insert = "INSERT INTO attendance (session_id, student_id) VALUES (?, ?)";
        $stmt_ins = mysqli_prepare($conn, $insert);
        mysqli_stmt_bind_param($stmt_ins, "ii", $session_id, $student_id);
        
        try {
            if (mysqli_stmt_execute($stmt_ins)) {
                $_SESSION['msg'] = "Attendance marked successfully!";
                $_SESSION['msg_type'] = "success";
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) { // Duplicate entry error code
                $_SESSION['msg'] = "You have already marked attendance for this session.";
                $_SESSION['msg_type'] = "warning";
            } else {
                $_SESSION['msg'] = "Error marking attendance.";
                $_SESSION['msg_type'] = "error";
            }
        }
    } else {
        $_SESSION['msg'] = "You are not enrolled in this course.";
        $_SESSION['msg_type'] = "error";
    }
} else {
    $_SESSION['msg'] = "Invalid or expired PIN.";
    $_SESSION['msg_type'] = "error";
}

header("Location: ../view/student.php");
exit();
?>