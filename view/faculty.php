<?php
session_start();
require_once __DIR__ . '/../db/config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_id']) || $_SESSION['role_id'] != 2) {
    header("Location: ../view/login.php");
    exit();
}

$faculty_id = (int) $_SESSION['user_id'];

$courses = [];
$requests = [];


$query = "
    SELECT c.id, c.course_code, c.course_title
    FROM courses_lms c
    JOIN course_faculty cf ON c.id = cf.course_id
    WHERE cf.faculty_id = ?
";
if ($stmt = mysqli_prepare($conn, $query)) {
    mysqli_stmt_bind_param($stmt, "i", $faculty_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $courses[] = $row;
        }
        mysqli_free_result($result);
    }
    mysqli_stmt_close($stmt);
}

$query2 = "
    SELECT cr.id AS request_id, u.name AS student_name, u.email,
           u.id AS student_id, c.course_code, c.course_title
    FROM course_requests cr
    JOIN users_lms u ON cr.student_id = u.id
    JOIN courses_lms c ON cr.course_id = c.id
    JOIN course_faculty cf ON c.id = cf.course_id
    WHERE cf.faculty_id = ? AND cr.status='pending'
";
if ($stmt2 = mysqli_prepare($conn, $query2)) {
    mysqli_stmt_bind_param($stmt2, "i", $faculty_id);
    mysqli_stmt_execute($stmt2);
    $result2 = mysqli_stmt_get_result($stmt2);
    if ($result2) {
        while ($row = mysqli_fetch_assoc($result2)) {
            $requests[] = $row;
        }
        mysqli_free_result($result2);
    }
    mysqli_stmt_close($stmt2);
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Faculty Dashboard | Ashesi Attendance</title>
  <link rel="stylesheet" href="../assets/css/faculty.css">
</head>

<body>
  <nav>
    <h2>Ashesi Faculty Dashboard</h2>
    <ul>
      <li><a href="#" class="active">Dashboard</a></li>
      <li><a href="../view/login.html">Logout</a></li>
    </ul>
  </nav>

 <main>
  <div class="dashboard-card">
  <h3>Start Class Session</h3>
  <select id="sessionCourseId" style="padding: 10px; width: 100%; margin-bottom: 10px;">
    <option value="">-- Select Course --</option>
    <?php foreach ($courses as $course): ?>
      <option value="<?= $course['id'] ?>">
        <?= htmlspecialchars($course['course_code']) ?>
      </option>
    <?php endforeach; ?>
  </select>
  
  <button onclick="generatePin()">Generate Attendance PIN</button>
  <div class="pin-display" id="generatedPin" style="font-size: 2em; margin-top: 10px; font-weight: bold; color: #333;">----</div>
</div>
    

    <div class="dashboard-card">
        <h3>Create New Course</h3>
        <form action="../actions/create_courses.php" method="POST">
            <div class="form-group">
                <label>Course Code</label>
                <input type="text" name="courseCode" placeholder="e.g., CS101" required>
            </div>
            <div class="form-group">
                <label>Course Title</label>
                <input type="text" name="courseTitle" placeholder="e.g., Intro to CS" required>
            </div>
            <button type="submit">Create Course</button>
        </form>

        <h4 style="margin-top: 20px;">Your Courses</h4>
        <ul class="course-list">
            <?php foreach ($courses as $course): ?>
                <li style="border-bottom: 1px solid #eee; padding: 10px 0;">
                    <strong><?= htmlspecialchars($course['course_code']) ?></strong>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="dashboard-card">
        <h3>Pending Requests</h3>
        <?php if (empty($requests)): ?>
            <p>No pending requests.</p>
        <?php else: ?>
            <?php foreach ($requests as $req): ?>
            <div class="request-item" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #eee;">
                <div>
                    <strong><?= htmlspecialchars($req['student_name']) ?></strong><br>
                    <small><?= htmlspecialchars($req['course_code']) ?></small>
                </div>
                <div>
                    <a href="../actions/approve_request.php?id=<?= $req['request_id'] ?>" style="color: green; margin-right: 10px;">Approve</a>
                    <a href="../actions/reject_request.php?id=<?= $req['request_id'] ?>" style="color: red;">Reject</a>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

  <script src="../assets/js/faculty.js"></script>

</body>
</html>

<?php
mysqli_close($conn);
?>
