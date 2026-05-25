<?php
session_start();
require_once 'db.php';

// Check if the user is logged in; if not, redirect to the login page
if (!isset($_SESSION['roll_no'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['roll_no']; // assuming roll_no is stored in the session for user identification

// Fetch borrowed books
$borrowed_books_sql = "SELECT b.title, b.author, bb.isbn, bb.borrow_date, bb.return_date, bb.status, b.phone_number as lender_phone 
                       FROM books b 
                       INNER JOIN borrowed_books bb ON b.isbn = bb.isbn 
                       WHERE bb.user_id = ? AND bb.status = 'borrowed'";

$borrowed_stmt = $conn->prepare($borrowed_books_sql);
if ($borrowed_stmt === false) {
    die('Error preparing statement: ' . $conn->error); // Error handling
}
$borrowed_stmt->bind_param("s", $user_id);
$borrowed_stmt->execute();
$borrowed_books_result = $borrowed_stmt->get_result();

// Fetch newly uploaded books
$new_books_sql = "SELECT * FROM books ORDER BY created_at DESC LIMIT 5"; // Get the latest 5 books
$new_books_result = $conn->query($new_books_sql);

// Handle book borrowing action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['isbn'])) {
    $isbn = $_POST['isbn'];
    $borrow_date = date("Y-m-d");
    $return_date = date("Y-m-d", strtotime("+2 weeks")); // Example: return after 2 weeks

    // Insert borrowed book into borrowed_books table
    $borrow_sql = "INSERT INTO borrowed_books (user_id, isbn, borrow_date, return_date, status) 
                   VALUES (?, ?, ?, ?, 'borrowed')";
    $borrow_stmt = $conn->prepare($borrow_sql);
    if ($borrow_stmt === false) {
        die('Error preparing statement: ' . $conn->error);
    }
    $borrow_stmt->bind_param("ssss", $user_id, $isbn, $borrow_date, $return_date);
    $borrow_stmt->execute();

    // Update available copies in books table
    $update_copies_sql = "UPDATE books SET available_copies = available_copies - 1 WHERE isbn = ?";
    $update_stmt = $conn->prepare($update_copies_sql);
    if ($update_stmt === false) {
        die('Error preparing statement: ' . $conn->error);
    }
    $update_stmt->bind_param("s", $isbn);
    $update_stmt->execute();

    // Redirect back to the same page after borrowing the book
    header("Location: borrow.php");
    exit;
}

// Handle book returning action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['return_isbn'])) {
    $isbn = $_POST['return_isbn'];

    // Update the status of the book to "returned"
    $return_sql = "UPDATE borrowed_books SET status = 'returned' WHERE user_id = ? AND isbn = ?";
    $return_stmt = $conn->prepare($return_sql);
    if ($return_stmt === false) {
        die('Error preparing statement: ' . $conn->error);
    }
    $return_stmt->bind_param("ss", $user_id, $isbn);
    $return_stmt->execute();

    // Update available copies in books table
    $update_copies_sql = "UPDATE books SET available_copies = available_copies + 1 WHERE isbn = ?";
    $update_stmt = $conn->prepare($update_copies_sql);
    if ($update_stmt === false) {
        die('Error preparing statement: ' . $conn->error);
    }
    $update_stmt->bind_param("s", $isbn);
    $update_stmt->execute();

    // Redirect back to the same page after returning the book
    header("Location: borrow.php");
    exit;
}

// Helper function to calculate overdue fine
function calculate_fine($return_date) {
    $current_date = date("Y-m-d");
    $return_date_timestamp = strtotime($return_date);
    $current_date_timestamp = strtotime($current_date);

    // Calculate the difference in days
    $date_diff = ($current_date_timestamp - $return_date_timestamp) / (60 * 60 * 24);

    if ($date_diff > 7) {
        // If overdue, calculate the fine
        $overdue_days = $date_diff - 7;
        $fine = $overdue_days * 3; // 3 rupees per day
        return $fine;
    } else {
        return 0; // No fine if returned within the time limit
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrow Books</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background-color: #f5f5f5; }
        .container { display: flex; height: 100vh; justify-content: space-between; }
        
        /* Left half for posting new book */
        .left-half { width: 48%; background-color: #ffffff; padding: 20px; border-right: 2px solid #ddd; }
        .left-half h2 { margin-bottom: 20px; }
        .left-half form { margin-bottom: 20px; }
        .left-half input, .left-half textarea { width: 100%; padding: 10px; margin-bottom: 10px; border-radius: 6px; border: 1px solid #ccc; }
        .left-half button { background-color: #3498db; color: white; padding: 12px; width: 100%; border-radius: 6px; font-size: 16px; cursor: pointer; }
        .left-half button:hover { background-color: #2980b9; }

        /* Right half for borrowed books and new books */
        .right-half { width: 48%; padding: 20px; background-color: #f9f9f9; }
        .right-half h3 { margin-bottom: 20px; }
        
        /* Upper part: Borrowed Books */
        .borrowed-books-section { height: 50%; overflow-y: auto; }
        .borrowed-book-box { background-color: #fff; padding: 15px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 6px; box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); }
        .borrowed-book-box p { margin: 5px 0; }
        
        /* Lower part: New Books */
        .new-books-section { height: 50%; overflow-y: auto; margin-top: 20px; }
        .new-book-box { background-color: #fff; padding: 15px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 6px; box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); }
        .new-book-box h4 { margin: 0; color: #3498db; }
        .new-book-box p { margin: 5px 0; color: #555; }
        .new-book-box img { max-width: 100px; height: auto; border-radius: 6px; margin-right: 15px; }
        
    </style>
</head>
<body>

<?php if (isset($_SESSION['book_posted'])): ?>
    <script>
        alert("Book successfully posted!");
    </script>
    <?php unset($_SESSION['book_posted']); ?>
<?php endif; ?>

<div class="container">
    <!-- Left Half (Post a New Book) -->
    <div class="left-half">
        <h2>Post a New Book</h2>
        <form method="POST" action="upload_book.php" enctype="multipart/form-data">
            <input type="text" name="title" placeholder="Book Title" required>
            <input type="text" name="author" placeholder="Author" required>
            <input type="text" name="isbn" placeholder="ISBN" required>
            <input type="text" name="genre" placeholder="Genre" required>
            <input type="number" name="num_copies" placeholder="Number of Copies" required>
            <input type="number" name="year" placeholder="Year of Publication" required>
            <input type="text" name="phone_number" placeholder="Phone Number" required>
            <input type="file" name="book_image" required>
            <button type="submit">Post Book</button>
        </form>
    </div>

    <!-- Right Half (Borrowed Books and New Books) -->
    <div class="right-half">
        <!-- Upper part: Borrowed Books -->
        <div class="borrowed-books-section">
            <h3>Your Borrowed Books</h3>
            <?php if ($borrowed_books_result->num_rows > 0): ?>
                <?php while ($row = $borrowed_books_result->fetch_assoc()): ?>
                    <div class="borrowed-book-box">
                        <h4><?php echo htmlspecialchars($row['title']); ?></h4>
                        <p><strong>Author:</strong> <?php echo htmlspecialchars($row['author']); ?></p>
                        <p><strong>Borrowed On:</strong> <?php echo date("F j, Y", strtotime($row['borrow_date'])); ?></p>
                        <p><strong>Return By:</strong> <?php echo date("F j, Y", strtotime($row['return_date'])); ?></p>
                        <p><strong>Status:</strong> <?php echo ucfirst($row['status']); ?></p>
                        
                        <?php 
                            // Calculate fine
                            $fine = calculate_fine($row['return_date']);
                            if ($fine > 0): 
                        ?>
                            <p><strong>Fine:</strong> ₹<?php echo $fine; ?> for overdue</p>
                        <?php endif; ?>

                        <p><strong>Contact the Lender:</strong> <?php echo htmlspecialchars($row['lender_phone']); ?></p>

                        <form method="POST" action="borrow.php">
                            <input type="hidden" name="return_isbn" value="<?php echo htmlspecialchars($row['isbn']); ?>">
                            <button type="submit">Return Book</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No books were added yet.</p>
            <?php endif; ?>
        </div>

        <!-- Lower part: New Books -->
        <div class="new-books-section">
            <h3>Newly Uploaded Books</h3>
            <?php if ($new_books_result->num_rows > 0): ?>
                <?php while ($row = $new_books_result->fetch_assoc()): ?>
                    <div class="new-book-box">
                        <?php if (!empty($row['picture_url'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($row['picture_url']); ?>" alt="<?php echo htmlspecialchars($row['title']); ?>">
                        <?php endif; ?>
                        <div>
                            <h4><?php echo htmlspecialchars($row['title']); ?></h4>
                            <p><strong>Author:</strong> <?php echo htmlspecialchars($row['author']); ?></p>
                            <p><strong>ISBN:</strong> <?php echo htmlspecialchars($row['isbn']); ?></p>
                            <p><strong>Genre:</strong> <?php echo htmlspecialchars($row['genre']); ?></p>
                            <p><strong>Available Copies:</strong> <?php echo htmlspecialchars($row['available_copies']); ?></p>
                            <?php if ($row['available_copies'] > 0): ?>
                                <form method="POST" action="borrow.php">
                                    <input type="hidden" name="isbn" value="<?php echo htmlspecialchars($row['isbn']); ?>">
                                    <button type="submit">Borrow Book</button>
                                </form>
                            <?php else: ?>
                                <p>No copies available.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No new books available.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
