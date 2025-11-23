<?php
$stmt = mysqli_prepare($conn, "
    SELECT cr.id AS request_id, u.name AS student_name, u.email, u.id AS student_id, 
           c.course_code, c.course_title
    FROM course_requests cr
    JOIN users u ON cr.student_id = u.id
    JOIN courses c ON cr.course_id = c.id
    JOIN course_faculty cf ON c.id = cf.course_id
    WHERE cf.faculty_id = ? AND cr.status='pending'
");
mysqli_stmt_bind_param($stmt, "i", $faculty_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$requests = [];
while ($row = mysqli_fetch_assoc($result)) {
    $requests[] = $row;
}

mysqli_stmt_close($stmt);
?>
