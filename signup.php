<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "karthikmj6252";
$database = "project";

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Create a database connection
$conn = new mysqli($servername, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST['role'] ?? '';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $plain_password = $_POST['password'] ?? '';
    $password = password_hash($plain_password, PASSWORD_DEFAULT);

    if ($role === "student") {
        $roll_no = $_POST['student_roll_no'] ?? '';
        $branch = $_POST['branch'] ?? '';

        if (!$roll_no || !$branch) {
            $error = "Please fill all student fields.";
        } else {
            $stmt = $conn->prepare("INSERT INTO students (name, roll_no, email, password, branch) VALUES (?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("sssss", $name, $roll_no, $email, $password, $branch);
                if ($stmt->execute()) {
                    header("Location: login.php");
                    exit();
                } else {
                    $error = "Student insert error: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $error = "Student query preparation failed: " . $conn->error;
            }
        }
    } elseif ($role === "teacher") {
        $roll_no = $_POST['teacher_roll_no'] ?? '';
        $designation = $_POST['designation'] ?? '';

        if (!$roll_no || !$designation) {
            $error = "Please fill all teacher fields.";
        } else {
            $stmt = $conn->prepare("INSERT INTO teachers (name, roll_no, email, password, designation) VALUES (?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("sssss", $name, $roll_no, $email, $password, $designation);
                if ($stmt->execute()) {
                    header("Location: login.php");
                    exit();
                } else {
                    $error = "Teacher insert error: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $error = "Teacher query preparation failed: " . $conn->error;
            }
        }
    } else {
        $error = "Please select a valid role.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Signup</title>
    <style>
        body { font-family: Arial; background: #f3f3f3; padding: 40px; }
        .form-box { max-width: 500px; background: white; padding: 30px; margin: auto; border-radius: 8px; box-shadow: 0 0 10px #ccc; }
        input, select { width: 100%; padding: 10px; margin: 10px 0; }
        button { padding: 10px; width: 100%; background: blue; color: white; border: none; }
        .message { color: green; text-align: center; }
        .error { color: red; text-align: center; }
    </style>
    <script>
        function toggleFields() {
            const role = document.getElementById("role").value;
            document.getElementById("student-fields").style.display = (role === "student") ? "block" : "none";
            document.getElementById("teacher-fields").style.display = (role === "teacher") ? "block" : "none";
        }
    </script>
</head>
<body>
<div class="form-box">
    <h2>Signup</h2>
    <?php if ($success) echo "<p class='message'>$success</p>"; ?>
    <?php if ($error) echo "<p class='error'>$error</p>"; ?>

    <form method="POST" action="signup.php">
        <select name="role" id="role" onchange="toggleFields()" required>
            <option value="">-- Select Role --</option>
            <option value="student">Student</option>
            <option value="teacher">Teacher</option>
        </select>
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>

        <div id="student-fields" style="display:none;">
            <input type="text" name="student_roll_no" placeholder="Roll No">
            <input type="text" name="branch" placeholder="Branch">
        </div>

        <div id="teacher-fields" style="display:none;">
            <input type="text" name="teacher_roll_no" placeholder="Faculty ID">
            <input type="text" name="designation" placeholder="Designation">
        </div>

        <button type="submit">Sign Up</button>
        <p style="text-align:center;">Already have an account? <a href="login.php">Login</a></p>
    </form>
</div>
</body>
</html>
