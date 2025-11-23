// approve_request.php
<?php
session_start();
require_once("../db/config.php");

if (isset($_GET['id'])) {
    $request_id = intval($_GET['id']);
    $status = 'approved';

    $stmt = mysqli_prepare($conn, "UPDATE course_requests SET status=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "si", $status, $request_id);
    mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    header("Location: ../view/faculty_dashboard.php");
}
?>
