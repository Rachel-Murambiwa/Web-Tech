<?php
session_start();
require_once __DIR__ . '/../db/config.php'; // Use your config file

if (!isset($_SESSION['user_id'])) {
    header("Location: ../view/login.php"); 
    exit();
}
$student_id = $_SESSION['user_id'];

// Fetch Available Courses
$courses = [];
$sql = "SELECT * FROM courses_lms";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $courses[] = $row;
    }
}

// Fetch Enrolled Courses
$enrolled = [];
$sql = "SELECT c.course_code, c.course_title 
        FROM enrollments e
        INNER JOIN courses_lms c ON e.course_id = c.id
        WHERE e.student_id = $student_id";

$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $enrolled[] = $row;
    }
}

// Handle Course Request Logic (Keep existing logic)
if (isset($_POST['course_id'])) {
    $course_id = $_POST['course_id'];
    $check = "SELECT * FROM course_requests WHERE student_id=$student_id AND course_id=$course_id AND status='pending'";
    $res = mysqli_query($conn, $check);
    if (mysqli_num_rows($res) == 0) {
        $insert = "INSERT INTO course_requests (student_id, course_id, status) VALUES ($student_id, $course_id, 'pending')";
        mysqli_query($conn, $insert);
        $_SESSION['msg'] = "Request sent successfully!"; // Use Session
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['msg'] = "You already requested this course.";
        $_SESSION['msg_type'] = "warning";
    }
    // Refresh to show message
    header("Location: student.php");
    exit();
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
            <p>Enter the 5-digit PIN provided by your lecturer to mark yourself present.</p>
            
            <?php 
            if (isset($_SESSION['msg'])) {
                $color = ($_SESSION['msg_type'] ?? 'success') == 'success' ? 'green' : 'red';
                echo "<div style='padding:10px; margin-bottom:10px; color: $color; font-weight:bold; border: 1px solid $color; border-radius: 4px;'>";
                echo htmlspecialchars($_SESSION['msg']);
                echo "</div>";
                unset($_SESSION['msg']); // Clear message after showing
                unset($_SESSION['msg_type']);
            } 
            ?>
            
            <form action="../actions/mark_attendance.php" method="POST" style="margin-top: 15px; display: flex; gap: 10px;">
                <input type="text" name="pin" placeholder="Enter PIN (e.g. 52910)" maxlength="6" required 
                    style="padding: 10px; font-size: 1.2rem; width: 150px; letter-spacing: 2px; text-align: center;">
                <button type="submit" style="padding: 10px 20px; background-color: #27ae60; color: white; border: none; cursor: pointer;">Mark Present</button>
            </form>
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
                
                // UPDATED QUERY: Removed "AND cs.status = 'closed'" 
                // Now it counts the Active class in the total immediately
                $reportQuery = "
                    SELECT c.course_code, c.course_title,
                    (SELECT COUNT(*) FROM attendance a 
                     JOIN class_sessions cs ON a.session_id = cs.id 
                     WHERE a.student_id = $student_id AND cs.course_id = c.id) as attended,
                    (SELECT COUNT(*) FROM class_sessions cs 
                     WHERE cs.course_id = c.id) as total
                    FROM enrollments e 
                    JOIN courses_lms c ON e.course_id = c.id
                    WHERE e.student_id = $student_id
                ";
                $repResult = mysqli_query($conn, $reportQuery);
                
                if($repResult && mysqli_num_rows($repResult) > 0):
                    while($row = mysqli_fetch_assoc($repResult)): 
                        // Calculate Percentage (Prevent Division by Zero)
                        $pct = ($row['total'] > 0) ? round(($row['attended'] / $row['total']) * 100) : 0;
                ?>
                <tr>
                    <td><?= htmlspecialchars($row['course_code']) ?></td>
                    <td><?= htmlspecialchars($row['course_title']) ?></td>
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
