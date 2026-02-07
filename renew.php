<?php
session_start();

/* 🔒 ADMIN MUST BE LOGGED IN */
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

/* 🔗 DATABASE CONNECTION */
$conn = new mysqli("localhost", "root", "", "library");
if ($conn->connect_error)
    die("Connection Failed: " . $conn->connect_error);

/* 📝 HANDLE RENEW */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!isset($_POST['borrow_id'], $_POST['renew_days'])) {
        die("Invalid renew request");
    }

    $borrow_id  = (int) $_POST['borrow_id'];
    $renew_days = (int) $_POST['renew_days'];

    if ($renew_days < 1) $renew_days = 1;
    if ($renew_days > 30) $renew_days = 30;

    /* 🔍 GET CURRENT BORROW INFO */
    $stmt = $conn->prepare("
        SELECT due_date, username, book_title 
        FROM borrow_requests 
        WHERE id=? AND status='approved' AND return_date IS NULL
    ");
    $stmt->bind_param("i", $borrow_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0)
        die("Borrow record not found or already returned");

    $borrow = $result->fetch_assoc();

    /* 📅 CALCULATE NEW DUE DATE */
    $new_due_date = date("Y-m-d", strtotime($borrow['due_date'] . " + $renew_days days"));

    /* 🔄 UPDATE DUE DATE */
    $update = $conn->prepare("
        UPDATE borrow_requests 
        SET due_date=? 
        WHERE id=?
    ");
    $update->bind_param("si", $new_due_date, $borrow_id);

    if ($update->execute()) {
        echo "<script>
            alert('Book renewed successfully!\\nNew Due Date: $new_due_date');
            window.location='dashboard.php';
        </script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Renew Book</title>

<style>
body {
    margin: 0;
    font-family: 'Segoe UI', sans-serif;
    background: #f5f7fb;
}
.sidebar {
    width: 230px;
    background: #fff;
    height: 100vh;
    position: fixed;
    padding: 25px 20px;
    box-shadow: 2px 0 8px rgba(0,0,0,0.08);
}
.sidebar a {
    display: block;
    padding: 12px;
    text-decoration: none;
    color: #333;
    border-radius: 8px;
}
.sidebar a:hover {
    background: #eef2ff;
}
.main {
    margin-left: 260px;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}
.renew-box {
    background: #fff;
    width: 420px;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
}
h2 {
    text-align: center;
    margin-bottom: 20px;
}
.form-group {
    margin-bottom: 18px;
}
label {
    font-weight: 600;
    display: block;
    margin-bottom: 6px;
}
input {
    width: 100%;
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
}
button {
    width: 100%;
    padding: 12px;
    background: #2563eb;
    color: white;
    border: none;
    border-radius: 6px;
    font-weight: bold;
    cursor: pointer;
}
button:hover {
    background: #1e40af;
}
</style>
</head>

<body>

<div class="sidebar">
    <h2>📚 Livo</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="add.php">Manage Books</a>
    <a href="renew_book.php">Renew Book</a>
</div>

<div class="main"> 
    <div class="renew-box">
        <h2>Renew Book</h2>

        <form method="POST">

            <!-- SELECT BORROWED BOOK -->
            <div class="form-group">
                <label>Borrowed Book</label>
                <input list="borrowList" name="borrow_id" required placeholder="Select issued book">

                <datalist id="borrowList">
                <?php
                $borrows = $conn->query("
                    SELECT id, username, book_title, due_date 
                    FROM borrow_requests 
                    WHERE status='approved' AND return_date IS NULL
                ");
                while ($b = $borrows->fetch_assoc()) {
                    echo "<option value='{$b['id']}'>
                        {$b['book_title']} - {$b['username']} (Due: {$b['due_date']})
                    </option>";
                }
                ?>
                </datalist>
            </div>

            <!-- RENEW DAYS -->
            <div class="form-group">
                <label>Extend Days (1–30)</label>
                <input type="number" name="renew_days" min="1" max="30" value="7" required>
            </div>

            <button type="submit">Renew Book</button>
        </form>
    </div>
</div>

</body>
</html>
