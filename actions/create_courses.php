<?php
session_start();
require_once("../db/config.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_code = trim($_POST['courseCode']);
    $course_title = trim($_POST['courseTitle']);
    $faculty_id = $_SESSION['user_id'];
    $stmt = mysqli_prepare($conn, "INSERT INTO courses_lms (course_code, course_title) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, "ss", $course_code, $course_title);
    mysqli_stmt_execute($stmt);
    $course_id = mysqli_insert_id($conn); 
    mysqli_stmt_close($stmt);
    $stmt2 = mysqli_prepare($conn, "INSERT INTO course_faculty (course_id, faculty_id) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt2, "ii", $course_id, $faculty_id);
    mysqli_stmt_execute($stmt2);
    mysqli_stmt_close($stmt2);

    mysqli_close($conn);
    header("Location: ../view/faculty.php");
    exit();
}
?>
