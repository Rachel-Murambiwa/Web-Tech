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

      <form action="../actions/create_courses.php" method="POST">
        <div class="form-group">
          <label for="courseCode">Course Code *</label>
          <input type="text" name="courseCode" placeholder="e.g., CS101" required>
        </div>

        <div class="form-group">
          <label for="courseTitle">Course Title *</label>
          <input type="text" name="courseTitle" placeholder="e.g., Introduction to Computer Science" required>
        </div>

        <button type="submit">Create Course</button>
      </form>

      <h3 style="margin-top: 30px;">My Courses</h3>
      <ul class="course-list" id="courseList">

        <?php foreach ($courses as $course): ?>
          <li class="course-item">
            <h4><?= $course['course_code'] ?> - <?= $course['course_title'] ?></h4>

            <p><strong>Students:</strong>
              <?php
                $stmt3 = mysqli_prepare($conn, "
                    SELECT COUNT(*) FROM course_requests
                    WHERE course_id=? AND status='approved'
                ");
                mysqli_stmt_bind_param($stmt3, "i", $course['id']);
                mysqli_stmt_execute($stmt3);
                mysqli_stmt_bind_result($stmt3, $count);
                mysqli_stmt_fetch($stmt3);
                echo $count;
                mysqli_stmt_close($stmt3);
              ?>
            </p>
          </li>
        <?php endforeach; ?>

      </ul>
    </div>

    <div class="dashboard-card">
      <h3>Student Course Requests</h3>
      <div id="requestsList">

        <?php if (empty($requests)): ?>
            <p>No pending requests.</p>
        <?php endif; ?>

        <?php foreach ($requests as $req): ?>
        <div class="request-item">
          <h4><?= $req['student_name'] ?></h4>

          <p><strong>Course:</strong> 
            <?= $req['course_code'] ?> - <?= $req['course_title'] ?></p>

          <p><strong>Student ID:</strong> <?= $req['student_id'] ?></p>
          <p><strong>Email:</strong> <?= $req['email'] ?></p>

          <div class="request-actions">
            <a class="secondary" 
               href="../actions/approve_requests.php?id=<?= $req['request_id'] ?>">
              Approve
            </a>

            <a class="danger"
               href="../actions/reject_request.php?id=<?= $req['request_id'] ?>">
              Reject
            </a>
          </div>
        </div>
        <?php endforeach; ?>

      </div>
    </div>

  </main>

  <script src="../assets/js/faculty.js"></script>

</body>
</html>