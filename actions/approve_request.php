<?php
session_start();
require_once("../db/config.php");

// Security Check: Only Faculty should do this
// if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
//     header("Location: ../view/login.html");
//     exit();
// }

if (isset($_GET['id'])) {
    $request_id = intval($_GET['id']);
    
    // --- STEP 1: GET THE MISSING DATA ---
    // We need to know WHO the student is and WHICH course they want.
    // We look this up using the $request_id.
    $query = "SELECT student_id, course_id FROM course_requests WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $request_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    // If we find the request, we get the IDs
    if ($row = mysqli_fetch_assoc($result)) {
        $student_id = $row['student_id'];
        $course_id = $row['course_id'];
        
        // --- STEP 2: UPDATE STATUS ---
        $status = 'approved';
        $updateStmt = mysqli_prepare($conn, "UPDATE course_requests SET status=? WHERE id=?");
        mysqli_stmt_bind_param($updateStmt, "si", $status, $request_id);
        mysqli_stmt_execute($updateStmt);
        mysqli_stmt_close($updateStmt);

        // --- STEP 3: ENROLL THE STUDENT ---
        // Now we have the correct $student_id and $course_id variables to use here
        // We use INSERT IGNORE to prevent crashing if they are already enrolled
        $enrollStmt = mysqli_prepare($conn, "INSERT IGNORE INTO enrollments (student_id, course_id) VALUES (?, ?)");
        mysqli_stmt_bind_param($enrollStmt, "ii", $student_id, $course_id);
        
        if (!mysqli_stmt_execute($enrollStmt)) {
             // Optional: Handle error if enrollment fails
             die("Error enrolling student: " . mysqli_error($conn));
        }
        mysqli_stmt_close($enrollStmt);
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    // Redirect back to dashboard
    header("Location: ../view/faculty.php");
    exit();
}
?>