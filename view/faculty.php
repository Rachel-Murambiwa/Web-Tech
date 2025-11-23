<?php
session_start();
require_once __DIR__ . '/../db/config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_id']) || $_SESSION['role_id'] != 2) {
    header("Location: ../view/login.html");
    exit();
}

$faculty_id = (int) $_SESSION['user_id'];

$courses = [];
$requests = [];


$query = "
    SELECT c.id, c.course_code, c.course_title
    FROM courses c
    JOIN courses_faculty cf ON c.id = cf.course_id
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
    JOIN users u ON cr.student_id = u.id
    JOIN courses c ON cr.course_id = c.id
    JOIN courses_faculty cf ON c.id = cf.course_id
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
      <h3>Generate Attendance PIN</h3>
      <button onclick="generatePin()">Generate PIN</button>
      <div class="pin-display" id="generatedPin">PIN will appear here</div>

      <h3 style="margin-top: 30px;">Students Present Today</h3>
      <ul class="student-list" id="studentList">
        <li>Rachel Murambiwa</li>
        <li>Aisha Chihuri</li>
        <li>Tanatswa Mhiribidi</li>
        <li>Soukuratou Zoumarou</li>
      </ul>
    </div>


    <div class="dashboard-card">
      <h3>Create New Course</h3>

      <form action="../backend/create_course.php" method="POST">
        <div class="form-group">
          <label for="courseCode">Course Code *</label>
          <input type="text" name="courseCode" placeholder="e.g., CS101" required>
        </div>

        <div class="form-group">
          <label for="courseTitle">Course Title *</label>
          <input type="text" name="courseTitle" placeholder="e.g., Intro to CS" required>
        </div>

        <button type="submit">Create Course</button>
      </form>

      <h3 style="margin-top: 30px;">My Courses</h3>
      <ul class="course-list" id="courseList">
        <?php if (empty($courses)): ?>
          <li>No courses yet. Create one above.</li>
        <?php else: ?>
          <?php foreach ($courses as $course): ?>
            <li class="course-item">
              <h4><?= htmlspecialchars($course['course_code']) ?> - <?= htmlspecialchars($course['course_title']) ?></h4>

              <p><strong>Students:</strong>
                <?php
                // count approved students for this course
                $courseId = (int) $course['id'];
                $count = 0;
                if ($stmt3 = mysqli_prepare($conn, "SELECT COUNT(*) FROM course_requests WHERE course_id=? AND status='approved'")) {
                    mysqli_stmt_bind_param($stmt3, "i", $courseId);
                    mysqli_stmt_execute($stmt3);
                    mysqli_stmt_bind_result($stmt3, $count);
                    mysqli_stmt_fetch($stmt3);
                    mysqli_stmt_close($stmt3);
                }
                echo (int)$count;
                ?>
              </p>
            </li>
          <?php endforeach; ?>
        <?php endif; ?>
      </ul>
    </div>


    <div class="dashboard-card">
      <h3>Student Course Requests</h3>
      <div id="requestsList">
        <?php if (empty($requests)): ?>
            <p>No pending requests.</p>
        <?php else: ?>
            <?php foreach ($requests as $req): ?>
            <div class="request-item">
              <h4><?= htmlspecialchars($req['student_name']) ?></h4>
              <p><strong>Course:</strong> <?= htmlspecialchars($req['course_code']) ?> - <?= htmlspecialchars($req['course_title']) ?></p>
              <p><strong>Student ID:</strong> <?= htmlspecialchars($req['student_id']) ?></p>
              <p><strong>Email:</strong> <?= htmlspecialchars($req['email']) ?></p>

              <div class="request-actions">
                <a class="secondary" href="../actions/approve_request.php?id=<?= (int)$req['request_id'] ?>">Approve</a>
                <a class="danger" href="../actions/reject_request.php?id=<?= (int)$req['request_id'] ?>">Reject</a>
              </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

  </main>

  <script src="../assets/js/faculty.js"></script>

</body>
</html>

<?php
mysqli_close($conn);
?>
