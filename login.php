<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "karthikmj6252";
$database = "project";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST['role'];
    $email = $_POST['email'];
    $password_input = $_POST['password'];

    if ($role == "student") {
        $stmt = $conn->prepare("SELECT * FROM students WHERE email = ?");
    } elseif ($role == "teacher") {
        $stmt = $conn->prepare("SELECT * FROM teachers WHERE email = ?");
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password_input, $user['password'] ?? $user['password_hash'])) {
        $_SESSION['user'] = $user['name'];
        $_SESSION['role'] = $role;
        $_SESSION['roll_no'] = $user['roll_no'];  
        header("Location: main.php");
        exit();
    } else {
        $error = "Invalid credentials!";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #ece9e6, #ffffff);
            padding: 50px;
        }

        .form-box {
            max-width: 450px;
            background: white;
            padding: 35px;
            margin: auto;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin: 10px 0 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            background-color: #2ecc71;
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #27ae60;
        }

        .error {
            color: red;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="form-box">
    <h2>Login</h2>
    <?php if ($error) echo "<p class='error'>$error</p>"; ?>

    <form method="POST">
        <select name="role" required>
            <option value="">-- Select Role --</option>
            <option value="student">Student</option>
            <option value="teacher">Teacher</option>
        </select>

        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>

        <button type="submit">Login</button>
        <p style="text-align:center; margin-top: 15px;">New here? <a href="signup.php">Sign up</a></p>
    </form>
</div>

</body>
</html>
