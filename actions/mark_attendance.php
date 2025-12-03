<?php
// actions/mark_attendance.php
session_start();
require_once __DIR__ . '/../db/config.php';

// 1. Check Login
// if (!isset($_SESSION['user_id'])) {
//     header("Location: ../view/login.html");
//     exit();
// }

$student_id = $_SESSION['user_id'];

// --- FIX 1: Check for 'pin' (matches your HTML) OR 'session_pin' ---
if (isset($_POST['pin'])) {
    $pin = trim($_POST['pin']);
} elseif (isset($_POST['session_pin'])) {
    $pin = trim($_POST['session_pin']);
} else {
    $pin = '';
}

if (empty($pin)) {
    $_SESSION['msg'] = "Error: PIN cannot be empty.";
    header("Location: ../view/student.php");
    exit();
}

// 2. Find active session
$sql = "SELECT id, course_id FROM class_sessions WHERE session_pin = ? AND status = 'active' LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $pin);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    $session_id = $row['id'];
    $course_id = $row['course_id'];
    
    // --- FIX 2: Check 'enrollments' table (The correct source of truth) ---
    $check_enroll = mysqli_prepare($conn, "SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?");
    mysqli_stmt_bind_param($check_enroll, "ii", $student_id, $course_id);
    mysqli_stmt_execute($check_enroll);
    $enroll_result = mysqli_stmt_get_result($check_enroll);
    
    if (mysqli_num_rows($enroll_result) > 0) {
        // 3. Mark Attendance
        $insert = "INSERT INTO attendance (session_id, student_id) VALUES (?, ?)";
        $stmt_ins = mysqli_prepare($conn, $insert);
        mysqli_stmt_bind_param($stmt_ins, "ii", $session_id, $student_id);
        
        try {
            if (mysqli_stmt_execute($stmt_ins)) {
                $_SESSION['msg'] = "Success: Attendance marked present!";
                $_SESSION['msg_type'] = "success"; // Green
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) { // Duplicate entry
                $_SESSION['msg'] = "You have already marked attendance for this session.";
                $_SESSION['msg_type'] = "warning"; // Orange/Yellow
            } else {
                $_SESSION['msg'] = "Database Error: " . $e->getMessage();
                $_SESSION['msg_type'] = "error";
            }
        }
        mysqli_stmt_close($stmt_ins);
    } else {
        $_SESSION['msg'] = "Error: You are not enrolled in this course.";
        $_SESSION['msg_type'] = "error";
    }
    mysqli_stmt_close($check_enroll);
} else {
    $_SESSION['msg'] = "Error: Invalid PIN or Class Session is closed.";
    $_SESSION['msg_type'] = "error";
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

header("Location: ../view/student.php");
exit();
?>