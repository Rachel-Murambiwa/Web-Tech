<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="../assets/css/register.css">
</head>
<body>
    <form action="../actions/register.php" method="POST">
        <h2>
            Register
        </h2>
    <input type="text" for="name" name="name" placeholder="First Name" required>
    <input type="email" name="email" placeholder="Email" required>
    <select name="status" id="registerStatus" required>
        <option value="" disabled selected>
            Status
        </option>
        <option value="student">
            Student
        </option>
        <option value="faculty">
            Faculty
        </option>
        <option value="admin">
            Admin
        </option>
    </select>
  <input type="password" name="password" placeholder="Password" required>
  <input type="password" name="confirm_password" placeholder="Confirm Password" required>
  <button type="submit">
    Register
</button>
</form>
<script src="../assets/js/register.js"></script>
</body>
</html>