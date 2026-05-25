<?php
session_start();
require_once 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['role']) || !isset($_SESSION['roll_no'])) {
    header("Location: login.php");
    exit();
}

$user_role = $_SESSION['role'];
$user_id = $_SESSION['roll_no']; // roll_no or teacher_id

// Handle form submission to send a message
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message = $_POST['message'] ?? '';
    $recipient_type = $_POST['recipient_type'] ?? '';
    $recipient_id = $_POST['recipient_id'] ?? '';

    if (!empty($message) && !empty($recipient_type)) {
        if ($recipient_type == 'all') {
            // When sending to all students
            // Remove the recipient_id field for sending to all
            $stmt = $conn->prepare("SELECT roll_no FROM students ORDER BY id ASC");
            $stmt->execute();
            $result = $stmt->get_result();

            // Send the message to all students
            while ($row = $result->fetch_assoc()) {
                $individual_recipient_id = $row['roll_no'];
                $actual_recipient_type = 'student'; // Important: set as 'student'

                $stmt_insert = $conn->prepare("INSERT INTO messages (sender_type, sender_id, recipient_type, recipient_id, message) VALUES (?, ?, ?, ?, ?)");
                $stmt_insert->bind_param("sssss", $user_role, $user_id, $actual_recipient_type, $individual_recipient_id, $message);
                $stmt_insert->execute();
            }
            echo "<script>alert('Message sent successfully to all students!'); window.location.href='message.php';</script>";
        } elseif ($recipient_type == 'branch') {
            // Sending to a specific branch of students
            $branch = $recipient_id; // The branch entered by the teacher

            // Validate the branch entered by the teacher
            $stmt_check_branch = $conn->prepare("SELECT DISTINCT branch FROM students WHERE branch = ?");
            $stmt_check_branch->bind_param("s", $branch);
            $stmt_check_branch->execute();
            $result_branch_check = $stmt_check_branch->get_result();

            if ($result_branch_check->num_rows > 0) {
                // Branch exists, send message to students in that branch
                $stmt = $conn->prepare("SELECT roll_no FROM students WHERE branch = ? ORDER BY id ASC");
                $stmt->bind_param("s", $branch);
                $stmt->execute();
                $result = $stmt->get_result();

                while ($row = $result->fetch_assoc()) {
                    $individual_recipient_id = $row['roll_no'];
                    $actual_recipient_type = 'student'; // Important: set as 'student'

                    $stmt_insert = $conn->prepare("INSERT INTO messages (sender_type, sender_id, recipient_type, recipient_id, message) VALUES (?, ?, ?, ?, ?)");
                    $stmt_insert->bind_param("sssss", $user_role, $user_id, $actual_recipient_type, $individual_recipient_id, $message);
                    $stmt_insert->execute();
                }
                echo "<script>alert('Message sent successfully to all students in the branch!'); window.location.href='message.php';</script>";
            } else {
                echo "<script>alert('No students found in the entered branch! Please check the branch name.'); window.location.href='message.php';</script>";
            }
        } else {
            // Sending to an individual student
            $stmt = $conn->prepare("INSERT INTO messages (sender_type, sender_id, recipient_type, recipient_id, message) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $user_role, $user_id, $recipient_type, $recipient_id, $message);
            $stmt->execute();
            echo "<script>alert('Message sent successfully!'); window.location.href='message.php';</script>";
        }
    } else {
        echo "<script>alert('Please fill all the fields!'); window.location.href='message.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher-Student Interaction</title>
    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Body Styling */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #333;
        }

        /* Container for the left and right sections */
        .container {
            display: flex;
            width: 90%;
            max-width: 1200px;
            height: 80vh;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        /* Left Half - Compose Message */
        .left {
            width: 50%;
            padding: 20px;
            border-right: 2px solid #f0f0f0;
            background-color: #fafafa;
        }

        .left h2 {
            font-size: 24px;
            margin-bottom: 15px;
            color: #1a73e8;
        }

        textarea {
            width: 100%;
            height: 150px;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 8px;
            resize: none;
            font-size: 16px;
            outline: none;
            margin-bottom: 15px;
        }

        textarea:focus {
            border-color: #1a73e8;
        }

        #recipientOptions {
            margin-bottom: 15px;
        }

        input[type="text"],
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }

        input[type="text"]:focus,
        select:focus {
            border-color: #1a73e8;
        }

        button[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #1a73e8;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button[type="submit"]:hover {
            background-color: #1558b0;
        }

        button#exitButton {
            margin-top: 20px;
            width: 100%;
            padding: 12px;
            background-color: #e5e5e5;
            color: #333;
            font-size: 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button#exitButton:hover {
            background-color: #ccc;
        }

        /* Right Half - Messages Display */
        .right {
            width: 50%;
            padding: 20px;
            background-color: #fff;
            overflow-y: auto;
            font-size: 16px;
        }

        .right h2 {
            font-size: 24px;
            margin-bottom: 15px;
            color: #1a73e8;
        }

        .message {
            padding: 15px;
            margin-bottom: 15px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
            transition: background-color 0.3s;
        }

        .message:hover {
            background-color: #f1f1f1;
        }

        .message strong {
            font-size: 16px;
            color: #1a73e8;
        }

        .message em {
            font-size: 12px;
            color: #777;
            display: block;
            margin-top: 5px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                height: auto;
            }

            .left,
            .right {
                width: 100%;
                padding: 15px;
            }

            button[type="submit"], 
            button#exitButton {
                width: 100%;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Left Half: Compose Message -->
    <div class="left">
        <h2>Compose Message</h2>

        <!-- Form to send message -->
        <form method="POST" action="message.php">
            <textarea name="message" placeholder="Type your message..." required></textarea>

            <?php if ($user_role == 'student') { ?>
                <select name="recipient_id" required>
                    <option value="">Select a Teacher</option>
                    <?php
                    // Fetch teachers' roll_no and name for the dropdown
                    $stmt = $conn->prepare("SELECT roll_no, name FROM teachers ORDER BY name ASC");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while ($teacher = $result->fetch_assoc()) {
                        echo "<option value='" . $teacher['roll_no'] . "'>" . $teacher['roll_no'] . " - " . $teacher['name'] . "</option>";
                    }
                    ?>
                </select>
                <input type="hidden" name="recipient_type" value="teacher">
            <?php } elseif ($user_role == 'teacher') { ?>
                <select name="recipient_type" id="recipientOptions" required onchange="toggleRecipientField()">
                    <option value="student">Send to Student</option>
                    <option value="all">Send to All Students</option>
                    <option value="branch">Send to Branch</option>
                </select>
                <div id="branchWarning" style="color: red; margin-bottom: 10px; display: none;">
                    <strong>You can only enter these branch values: </strong>
                    <?php
                    // Fetch all distinct branches from the students table
                    $stmt = $conn->prepare("SELECT DISTINCT branch FROM students");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $branches = [];
                    while ($row = $result->fetch_assoc()) {
                        $branches[] = $row['branch'];
                    }
                    echo implode(', ', $branches);
                    ?>
                </div>
                <input type="text" id="recipientInput" name="recipient_id" placeholder="Enter Branch (e.g., cybersecurity, machinelearning)" required>
            <?php } ?>

            <button type="submit">Send Message</button>
        </form>

        <!-- Exit Button -->
        <button id="exitButton" onclick="window.location.href='main.php'">Exit</button>
    </div>

    <!-- Right Half: Messages Display -->
    <div class="right">
        <h2>Your Messages</h2>

        <?php
        // Fetch messages for the logged-in user
        if ($user_role == 'student') {
            $stmt = $conn->prepare("SELECT * FROM messages WHERE recipient_type = 'student' AND recipient_id = ? ORDER BY timestamp DESC");
            $stmt->bind_param("s", $user_id);
        } elseif ($user_role == 'teacher') {
            $stmt = $conn->prepare("
                SELECT * FROM messages 
                WHERE (recipient_type = 'teacher' AND recipient_id = ?) 
                   OR (sender_type = 'student' AND recipient_type = 'teacher' AND recipient_id = ?)
                ORDER BY timestamp DESC
            ");
            $stmt->bind_param("ss", $user_id, $user_id);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $messages = $result->fetch_all(MYSQLI_ASSOC);

        foreach ($messages as $message) {
            // Get sender's name based on the sender type and ID
            if ($message['sender_type'] == 'student') {
                $sender_query = $conn->prepare("SELECT name, roll_no FROM students WHERE roll_no = ?");
                $sender_query->bind_param("s", $message['sender_id']);
                $sender_query->execute();
                $sender_result = $sender_query->get_result();
                $sender = $sender_result->fetch_assoc();
                $sender_name = $sender['name'];
                $sender_roll_no = $sender['roll_no'];
            } else {
                $sender_query = $conn->prepare("SELECT name, roll_no FROM teachers WHERE roll_no = ?");
                $sender_query->bind_param("s", $message['sender_id']);
                $sender_query->execute();
                $sender_result = $sender_query->get_result();
                $sender = $sender_result->fetch_assoc();
                $sender_name = $sender['name'];
                $sender_roll_no = $sender['roll_no'];
            }

            echo "<div class='message'>";
            echo "<strong>From: {$sender_name} [{$sender_roll_no}]</strong><br>";
            echo htmlspecialchars($message['message']);
            echo "<br><em>{$message['timestamp']}</em>";
            echo "</div><hr>";
        }
        ?>
    </div>
</div>

<script>
function toggleRecipientField() {
    var select = document.getElementById('recipientOptions');
    var input = document.getElementById('recipientInput');
    var branchWarning = document.getElementById('branchWarning');
    if (select.value === 'all') {
        input.style.display = 'none'; // Hide input field when "Send to All"
        input.required = false; // Don't require input
        branchWarning.style.display = 'none'; // Hide branch warning
    } else if (select.value === 'branch') {
        input.style.display = 'block'; // Show input field for branch
        input.required = true; // Make input required
        branchWarning.style.display = 'block'; // Show branch warning
    } else {
        input.style.display = 'block'; // Show input field for student
        input.required = true; // Make input required
        branchWarning.style.display = 'none'; // Hide branch warning
    }
}
</script>

</body>
</html>
