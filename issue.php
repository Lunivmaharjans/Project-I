<?php
session_start();

/* 🔒 ADMIN/LIBRARIAN MUST BE LOGGED IN */
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

/* 🔗 DATABASE CONNECTION */
$conn = new mysqli("localhost", "root", "", "library");
if ($conn->connect_error)
    die("Connection Failed: " . $conn->connect_error);

/* 📝 HANDLE FORM SUBMISSION */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!isset($_POST['book_id'], $_POST['username'], $_POST['borrow_days'])) {
        die("Invalid issue request");
    }

    $book_id = (int) $_POST['book_id'];
    $user_to_issue = trim($_POST['username']);
    $borrow_days = (int) $_POST['borrow_days'];

    if ($borrow_days < 1)
        $borrow_days = 1;
    if ($borrow_days > 30)
        $borrow_days = 30;

    /* Check book */
    $stmt = $conn->prepare("SELECT title, cover, copies FROM boooks WHERE id=?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0)
        die("Book not found");
    $book = $result->fetch_assoc();

    if ($book['copies'] <= 0) {
        echo "<script>alert('No copies available'); window.location='dashboard.php';</script>";
        exit();
    }

    $borrow_date = date("Y-m-d");
    $due_date = date("Y-m-d", strtotime("+$borrow_days days"));
    $return_date = NULL;

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
        $update = $conn->prepare("UPDATE boooks SET copies = copies - 1 WHERE id=?");
        $update->bind_param("i", $book_id);
        $update->execute();

        echo "<script>alert('Book issued to $user_to_issue. Due: $due_date'); window.location='dashboard.php';</script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Issue Book</title>

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #f5f7fb;
        }

        /* SIDEBAR */
        .sidebar {
            width: 230px;
            background: #fff;
            height: 100vh;
            position: fixed;
            padding: 25px 20px;
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.08);
        }

        .sidebar h2 {
            font-size: 20px;
            margin-bottom: 30px;
        }

        .sidebar a {
            display: block;
            padding: 12px 15px;
            margin: 6px 0;
            color: #333;
            text-decoration: none;
            border-radius: 8px;
            font-size: 15px;
            transition: 0.2s;
        }

        .sidebar a:hover {
            background: #eef2ff;
            color: #3b5bff;
        }

        /* MAIN */
        .main {
            margin-left: 260px;
            padding: 40px;

            display: flex;
            justify-content: center;
            /* horizontal center */
            align-items: center;
            /* vertical center */
            min-height: 100vh;
        }


        /* ISSUE FORM */
        .issue-container {
            background: #ffffff;
            width: 420px;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .issue-container h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #1e3c72;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-size: 14px;
            font-weight: 600;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        .issue-btn {
            width: 100%;
            padding: 12px;
            background: #2563eb;
            border: none;
            border-radius: 6px;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }

        .issue-btn:hover {
            background: #1e40af;
        }
    </style>
</head>

<body>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <h2>📚 Livo</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="add.php">Manage Books</a>
        <a href="#">Return Books</a>
        <a href="#">Borrowings</a>
        <a href="#">State Users</a>
        <a href="#">Overdue Books</a>

        <h4>Settings</h4>
        <a href="#">Notifications</a>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main">

        <div class="issue-container">
            <h2>Issue Book</h2>

            <form method="POST">

                <!-- SEARCH USER -->
                <div class="form-group">
                    <label>User</label>
                    <input list="userList" name="username" placeholder="Search username..." required>

                    <datalist id="userList">
                        <?php
                        $users = $conn->query("SELECT username FROM users WHERE role='user'");
                        while ($u = $users->fetch_assoc()) {
                            echo "<option value='{$u['username']}'>";
                        }
                        ?>
                    </datalist>
                </div>

                <!-- SELECT BOOK -->
                <div class="form-group">
                    <label>Book</label>
                    <input list="bookList" name="book_id" placeholder="Search book by title..." required>

                    <datalist id="bookList">
                        <?php
                        $books = $conn->query("SELECT id, title, copies FROM boooks WHERE copies > 0");
                        while ($b = $books->fetch_assoc()) {
                            echo "<option value='{$b['id']}'>{$b['title']} (Available: {$b['copies']})</option>";
                        }
                        ?>
                    </datalist>
                </div>

                <!-- BORROW DAYS -->
                <div class="form-group">
                    <label>Borrow Days (1–30)</label>
                    <input type="number" name="borrow_days" min="1" max="30" value="7" required>
                </div>

                <button type="submit" class="issue-btn">Issue Book</button>
            </form>
        </div>

    </div>

</body>

</html>