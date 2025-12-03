<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../view/login.php"); 
    exit();
}
$student_id = $_SESSION['user_id'];
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
    
    <section id="attendance" class="card" style="border-left: 5px solid #27ae60;">
        <h2>Mark Attendance</h2>
        <p>Enter the 6-digit PIN provided by your lecturer to mark yourself present.</p>
        
        <form action="../actions/mark_attendance_action.php" method="POST" style="margin-top: 15px; display: flex; gap: 10px;">
            <input type="text" name="pin" placeholder="Enter PIN (e.g. 52910)" maxlength="6" required 
                   style="padding: 10px; font-size: 1.2rem; width: 150px; letter-spacing: 2px; text-align: center;">
            <button type="submit" style="padding: 10px 20px; background-color: #27ae60; color: white; border: none; cursor: pointer;">Mark Present</button>
        </form>

        <?php if(isset($_GET['msg'])): ?>
            <p style="margin-top: 10px; color: <?= strpos($_GET['msg'], 'success') !== false ? 'green' : 'red' ?>;">
                <?= htmlspecialchars($_GET['msg']) ?>
            </p>
        <?php endif; ?>
    </section>

    <section id="report" class="card">
        <h2>Attendance Report</h2>
        <table border="1" cellpadding="10" cellspacing="0" width="100%" style="border-collapse: collapse; margin-top: 15px;">
            <thead style="background-color: #f4f4f4;">
                <tr>
                    <th>Course Code</th>
                    <th>Course Title</th>
                    <th>Classes Attended</th>
                    <th>Total Classes</th>
                    <th>Percentage</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Logic to fetch report
                $student_id = $_SESSION['user_id'];
                $reportQuery = "
                    SELECT c.course_code, c.course_title,
                    (SELECT COUNT(*) FROM attendance a 
                     JOIN class_sessions cs ON a.session_id = cs.id 
                     WHERE a.student_id = $student_id AND cs.course_id = c.id) as attended,
                    (SELECT COUNT(*) FROM class_sessions cs 
                     WHERE cs.course_id = c.id AND cs.status = 'closed') as total
                    FROM enrollments e 
                    JOIN courses c ON e.course_id = c.id
                    WHERE e.student_id = $student_id
                ";
                $repResult = mysqli_query($conn, $reportQuery);
                
                if(mysqli_num_rows($repResult) > 0):
                    while($row = mysqli_fetch_assoc($repResult)): 
                        $pct = ($row['total'] > 0) ? round(($row['attended'] / $row['total']) * 100) : 0;
                ?>
                <tr>
                    <td><?= $row['course_code'] ?></td>
                    <td><?= $row['course_title'] ?></td>
                    <td style="text-align: center;"><?= $row['attended'] ?></td>
                    <td style="text-align: center;"><?= $row['total'] ?></td>
                    <td style="text-align: center; font-weight: bold; color: <?= $pct < 75 ? 'red' : 'green' ?>;">
                        <?= $pct ?>%
                    </td>
                </tr>
                <?php endwhile; endif; ?>
            </tbody>
        </table>
    </section>

    <section id="courses" class="card">
        <h2>Available Courses</h2>
        <table width="100%" border="1" cellpadding="5" style="border-collapse: collapse;">
             <tr><th>Code</th><th>Title</th><th>Action</th></tr>
             <?php foreach ($courses as $c): ?>
             <tr>
                 <td><?= htmlspecialchars($c['course_code']) ?></td>
                 <td><?= htmlspecialchars($c['course_title']) ?></td>
                 <td>
                     <form method="POST">
                         <input type="hidden" name="course_id" value="<?= $c['id'] ?>">
                         <button type="submit">Request</button>
                     </form>
                 </td>
             </tr>
             <?php endforeach; ?>
        </table>
    </section>

</section>
</main>
</body>
</html>
