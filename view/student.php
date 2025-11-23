<?php
session_start();

$student_id = 36212027;

$host = "localhost";
$user = "root";
$pass = "";
$db   = "registration";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}


$courses = [];
$sql = "SELECT * FROM courses";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $courses[] = $row;
    }
}

$enrolled = [];
$sql = "SELECT c.course_code, c.course_title 
        FROM enrollments e
        INNER JOIN courses c ON e.course_id = c.id
        WHERE e.student_id = $student_id";

$result = mysqli_query($conn, $sql);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $enrolled[] = $row;
    }
}


if (isset($_POST['course_id'])) {
    $course_id = $_POST['course_id'];

    // Check if already requested
    $check = "SELECT * FROM course_requests 
              WHERE student_id=$student_id AND course_id=$course_id AND status='pending'";
    $res = mysqli_query($conn, $check);

    if (mysqli_num_rows($res) == 0) {
        $insert = "INSERT INTO course_requests (student_id, course_id, status)
                   VALUES ($student_id, $course_id, 'pending')";
        mysqli_query($conn, $insert);
        $msg = "Request sent successfully!";
    } else {
        $msg = "You already requested this course.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Student Dashboard - Attendance</title>
  <link rel="stylesheet" href="../assets/css/student.css" />
</head>
<body>

<nav class="navbar">
    <div class="logo">Ashesi Student Portal</div>
    <ul class="nav-links">
      <li><a href="#courses">Courses</a></li>
      <li><a href="#profile">Profile</a></li>
      <li><a href="../index.html">Logout</a></li>
    </ul>
</nav>

<main class="dashboard">
    <aside class="sidebar">
      <h2>Menu</h2>
      <ul>
        <li><a href="#courses">Available Courses</a></li>
        <li><a href="#enrolled">My Courses</a></li>
        <li><a href="#profile">Profile</a></li>
      </ul>
    </aside>

    <section class="content">

      <!-- Profile Section -->
      <section id="profile" class="card">
        <h2>Profile</h2>
        <p><strong>Name:</strong> Rachel Murambiwa</p>
        <p><strong>ID:</strong> <?php echo $student_id; ?></p>
        <p><strong>Program:</strong> BSc Computer Science</p>
        <p><strong>Year:</strong> Junior</p>
      </section>

      <!-- Available Courses -->
      <section id="courses" class="card">
        <h2>Available Courses</h2>

        <?php if (!empty($msg)) echo "<p style='color:green;'>$msg</p>"; ?>

        <table border="1" cellpadding="10">
            <tr>
                <th>Course Code</th>
                <th>Title</th>
                <th>Action</th>
            </tr>

            <?php foreach ($courses as $c): ?>
            <tr>
                <td><?php echo $c['course_code']; ?></td>
                <td><?php echo $c['course_title']; ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="course_id" value="<?php echo $c['id']; ?>">
                        <button type="submit">Request to Join</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
      </section>

      <!-- Enrolled Courses -->
      <section id="enrolled" class="card">
        <h2>My Courses</h2>

        <?php if (empty($enrolled)): ?>
            <p>You are not enrolled in any courses yet.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($enrolled as $e): ?>
                    <li>
                        <strong><?php echo $e['course_code']; ?></strong> — 
                        <?php echo $e['course_title']; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
      </section>

    </section>
</main>

<footer class="footer">
    <p>© 2025 Ashesi University | Student Dashboard</p>
</footer>
</body>
</html>
