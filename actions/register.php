<?php
require_once("../db/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name     = $_POST['name'];
    $email    = $_POST['email'];
    $password = $_POST['password'];
    
    if (isset($_POST['status'])) {
        $selected_role = $_POST['status'];
    } else {
        $selected_role = 'student'; // Fallback default
    }

    // 2. Map the text selection to your Database IDs
    switch ($selected_role) {
        case 'admin':
            $role_id = 1;
            break;
        case 'faculty':
            $role_id = 2;
            break;
        case 'student':
            $role_id = 3;
            break;
        default:
            $role_id = 3; // Default to student if something goes wrong
            break;
    }
    
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
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
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
