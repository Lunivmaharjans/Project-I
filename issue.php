<?php
session_start();

/* 🔒 ADMIN/LIBRARIAN MUST BE LOGGED IN */
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

/* 🔗 DATABASE CONNECTION */
$conn = new mysqli("localhost", "root", "", "library");
if ($conn->connect_error) die("Connection Failed: " . $conn->connect_error);

/* 📝 HANDLE FORM SUBMISSION */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST['book_id'], $_POST['username'], $_POST['borrow_days'])) {
        die("Invalid issue request");
    }

    $book_id = (int) $_POST['book_id'];
    $user_to_issue = $_POST['username'];
    $borrow_days = (int) $_POST['borrow_days'];

    if ($borrow_days < 1) $borrow_days = 1;
    if ($borrow_days > 30) $borrow_days = 30;

    /* Check book exists and copies available */
    $stmt = $conn->prepare("SELECT title, cover, copies FROM boooks WHERE id=?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) die("Book not found");
    $book = $result->fetch_assoc();
    if ($book['copies'] <= 0) {
        echo "<script>alert('No copies available.'); window.location='dashboard.html';</script>";
        exit();
    }

    /* Set borrow dates */
    $borrow_date = date("Y-m-d");
    $due_date = date("Y-m-d", strtotime("+$borrow_days days"));
    $return_date = NULL;

    /* Insert borrow request */
    $insert = $conn->prepare(
        "INSERT INTO borrow_requests 
        (username, book_id, book_title, book_cover, borrow_date, due_date, return_date, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'approved')"
    );
    $insert->bind_param(
        "sisssss",
        $user_to_issue,
        $book_id,
        $book['title'],
        $book['cover'],
        $borrow_date,
        $due_date,
        $return_date
    );

    if ($insert->execute()) {
        $update = $conn->prepare("UPDATE boooks SET copies = copies - 1 WHERE id = ?");
        $update->bind_param("i", $book_id);
        $update->execute();

        echo "<script>alert('Book issued successfully to $user_to_issue! Due date: $due_date'); window.location='dashboard.html';</script>";
        exit();
    } else {
        echo "<script>alert('Something went wrong.'); window.location='dashboard.html';</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Issue Book</title>
</head>
<body>
    <h2>Issue Book</h2>
    <form method="POST" action="">
        <label>User:</label>
        <select name="username" required>
            <option value="">Select User</option>
            <?php
            $users = $conn->query("SELECT username FROM users WHERE role='user'");
            while ($u = $users->fetch_assoc()) {
                echo "<option value='{$u['username']}'>{$u['username']}</option>";
            }
            ?>
        </select>
        <br><br>

        <label>Book:</label>
        <select name="book_id" required>
            <option value="">Select Book</option>
            <?php
            $books = $conn->query("SELECT id, title, copies FROM boooks");
            while ($b = $books->fetch_assoc()) {
                echo "<option value='{$b['id']}'>{$b['title']} (Available: {$b['copies']})</option>";
            }
            ?>
        </select>
        <br><br>

        <label>Number of Days to Borrow (1-30):</label>
        <input type="number" name="borrow_days" min="1" max="30" value="7" required>
        <br><br>

        <button type="submit">Issue Book</button>
    </form>
</body>
</html>
