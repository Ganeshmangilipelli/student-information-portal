<?php
session_start();
require_once 'db.php';

// If the user is not logged in, redirect them to the login page
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user_role = $_SESSION['role'];
$user_id = $_SESSION['roll_no'];  // roll_no for students, teacher_id for teachers

// Handle form submission to post an event
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $event_time = $_POST['event_time'];
    $location = $_POST['location'];
    $guest_of_honour = $_POST['guest_of_honour'];

    if ($title && $description && $event_time && $location) {
        // Insert event into database
        $stmt = $conn->prepare("INSERT INTO events (title, description, event_time, location, guest_of_honour, created_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $title, $description, $event_time, $location, $guest_of_honour, $user_id);
        if ($stmt->execute()) {
            echo "<script>alert('Event posted successfully!');</script>";
        } else {
            echo "<script>alert('Error posting event!');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Please fill all fields!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Campus Events</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; display: flex; height: 100vh; background-color: #f5f5f5; }
        .container { display: flex; width: 100%; justify-content: space-between; }
        .left-half, .right-half { width: 48%; padding: 20px; box-sizing: border-box; }
        .left-half { background-color: #ffffff; border-right: 2px solid #ddd; }
        .right-half { background-color: #f9f9f9; overflow-y: auto; height: 80vh; }
        .event-box { width: 100%; padding: 15px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 6px; background-color: #f1f1f1; }
        textarea, input { width: 100%; padding: 10px; margin-bottom: 10px; }
        button { background-color: #3498db; color: white; padding: 12px; width: 100%; border-radius: 6px; font-size: 16px; cursor: pointer; }
        button:hover { background-color: #2980b9; }
        .exit-button { background-color:rgb(52, 219, 144); color: white; padding: 12px; border-radius: 6px; cursor: pointer; width: 100%; text-align: center; }
        .exit-button:hover { background-color: #e74c3c; }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Function to fetch events dynamically
        function loadEvents() {
            $.ajax({
                url: 'fetch_events.php',
                method: 'GET',
                success: function(data) {
                    $('#event-container').html(data);
                }
            });
        }

        // Call loadEvents function every 3 seconds to update the events
        setInterval(loadEvents, 3000);
    </script>
</head>
<body>

<div class="container">
    <!-- Left Half (Post Event Section) -->
    <div class="left-half">
        <h2>Post a New Event</h2>
        <form method="POST">
            <input type="text" name="title" placeholder="Event Title" required>
            <textarea name="description" placeholder="Event Description" required></textarea>
            <input type="datetime-local" name="event_time" required>
            <input type="text" name="location" placeholder="Event Location" required>
            <input type="text" name="guest_of_honour" placeholder="Guest of Honour (Optional)">
            <button type="submit">Post Event</button>
        </form>
        <br>
        <button class="exit-button" onclick="window.location.href='main.php'">Exit</button>
    </div>

    <!-- Right Half (Event Display Section) -->
    <div class="right-half" id="event-container">
        <!-- Events will be loaded here dynamically using AJAX -->
    </div>
</div>

</body>
</html>
