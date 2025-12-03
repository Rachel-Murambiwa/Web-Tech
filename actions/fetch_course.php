<?php
$stmt = mysqli_prepare($conn, "
    SELECT c.id, c.course_code, c.course_title
    FROM courses_lms c
    JOIN course_faculty cf ON c.id = cf.course_id
    WHERE cf.faculty_id = ?
");
mysqli_stmt_bind_param($stmt, "i", $faculty_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$courses = [];
while ($row = mysqli_fetch_assoc($result)) {
    $courses[] = $row;
}

mysqli_stmt_close($stmt);
?>
