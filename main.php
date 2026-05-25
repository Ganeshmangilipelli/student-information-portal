<?php
session_start();

// If the user is not logged in, redirect them to the login page
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "karthikmj6252";
$database = "project";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Information Portal</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f2f4f7;
        }

        header {
            background-color: #2c3e50;
            color: white;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            font-size: 24px;
        }

        .auth a {
            color: white;
            margin-left: 20px;
            text-decoration: none;
            font-weight: bold;
        }

        .auth a:hover {
            text-decoration: underline;
        }

        .container {
            max-width: 800px;
            margin: 50px auto;
            display: flex;
            flex-direction: column;
            gap: 40px;
            padding: 0 20px;
        }

        .module {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .module:hover {
            transform: translateY(-5px);
        }

        .module h2 {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .module p {
            color: #666;
            font-size: 16px;
            margin-bottom: 20px;
        }

        .module a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
        }

        .module a:hover {
            background-color: #2980b9;
        }

        .no-access {
            text-align: center;
            padding: 50px 0;
            font-size: 18px;
            color: #e74c3c;
        }
    </style>
</head>
<body>

<header>
    <h1>Student Information Portal</h1>
    <div class="auth">
        <?php if (isset($_SESSION['user'])): ?>
            Welcome, <?php echo htmlspecialchars($_SESSION['user']); ?> |
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="signup.php">Signup</a>
        <?php endif; ?>
    </div>
</header>

<?php
// If the user is logged in, show the modules
if (isset($_SESSION['user'])): ?>
    <div class="container">
        <div class="module">
            <h2>📚 Book Borrowing</h2>
            <p>Borrow and manage your books with ease.</p>
            <a href="borrow.php">Go to Book Borrowing</a>
        </div>

        <div class="module">
            <h2>💬 Teacher-Student Interaction</h2>
            <p>Communicate with teachers and classmates directly.</p>
            <a href="message.php">Open Interaction Module</a>
        </div>

        <div class="module">
            <h2>🎉 Campus Events</h2>
            <p>View and register for campus events and activities.</p>
            <a href="campus_events.php">Check Campus Events</a>
        </div>
    </div>

<?php else: ?>
    <!-- Display access denied message for users not logged in -->
    <div class="no-access">
        You need to log in to access the modules. Please <a href="login.php">login</a> first.
    </div>
<?php endif; ?>

</body>
</html>
