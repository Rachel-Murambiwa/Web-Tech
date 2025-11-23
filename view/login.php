<?php
session_start();
require_once("../db/config.php");

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Prepare statement to avoid SQL injection
    $stmt = mysqli_prepare($conn, "SELECT id, password, role_id, name FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) == 1) {

        // Bind the result to PHP variables
        mysqli_stmt_bind_result($stmt, $id, $passwordFromDB, $role_id, $name);
        mysqli_stmt_fetch($stmt);

        // Verify the entered password against the hashed password in the DB
        if (password_verify($password, $passwordFromDB)) {
            // Password correct → set session
            $_SESSION['user_id'] = $id;
            $_SESSION['role_id'] = $role_id;
            $_SESSION['email'] = $email;
            $_SESSION['name'] = $name;

            // Redirect to dashboard based on role
            switch ($role_id) {
                case 1: // Admin
                    header("Location: admin/admin.php");
                    break;
                case 2: // Faculty
                    header("Location: faculty.php");
                    break;
                case 3: // Students
                    header("Location: student.php");
                    break;
                default:
                    header("Location: dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid password.";
        }

    } else {
        $error = "No user found with that email.";
    }

    // Close statement and connection
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>
  <div class="form-container">
    <h2>Login</h2>

    <?php if (!empty($error)) { echo "<p style='color:red;'>$error</p>"; } ?>

    <form action="" method="POST">
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Login</button>
    </form>
  </div>
</body>
</html>
