<?php
require_once("../db/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name     = $_POST['name'];
    $email    = $_POST['email'];
    $password = $_POST['password'];

    // Default role = student
    $role_id = 3; 

    // 1. Check if email exists
    $check_sql = "SELECT id FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        echo "Email already exists!";
        exit;
    }

    mysqli_stmt_close($stmt);

    // 2. Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // 3. Insert user
    $insert_sql = "INSERT INTO users (name, email, password, role_id) VALUES (?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $insert_sql);
    mysqli_stmt_bind_param($stmt, "sssi", $name, $email, $hashedPassword, $role_id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: ../view/login.php");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}
?>
