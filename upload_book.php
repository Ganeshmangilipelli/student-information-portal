<?php
session_start();
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $isbn = $_POST['isbn'];
    $genre = $_POST['genre'];
    $num_copies = (int)$_POST['num_copies'];
    $year = $_POST['year'];
    $shelf_location = "Shelf A"; // optional default or retrieve from user
    $availability_status = "Available";
    $phone_number = $_POST['phone_number'];  // Get the phone number from the form

    // Handle image upload
    $book_image = '';
    if (isset($_FILES['book_image']) && $_FILES['book_image']['error'] == 0) {
        $target_dir = "uploads/";
        $book_image = basename($_FILES["book_image"]["name"]);
        $target_file = $target_dir . $book_image;
        move_uploaded_file($_FILES["book_image"]["tmp_name"], $target_file);
    }

    // Insert book into database
    $stmt = $conn->prepare("INSERT INTO books (title, author, isbn, genre, picture_url, shelf_location, year_of_publication, total_copies, available_copies, availability_status, uploaded_by, phone_number) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt === false) {
        die("Error preparing SQL: " . $conn->error);
    }

    $uploaded_by = $_SESSION['roll_no']; // Assume roll_no is stored in session

    $stmt->bind_param("ssssssiissss", $title, $author, $isbn, $genre, $book_image, $shelf_location, $year, $num_copies, $num_copies, $availability_status, $uploaded_by, $phone_number);

    if ($stmt->execute()) {
        $_SESSION['book_posted'] = true; // Store flag to show success message
        header("Location: borrow.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

