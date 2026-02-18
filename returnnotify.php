<?php
session_start();

$conn = new mysqli("localhost", "root", "", "library");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/* ========== HANDLE ACCEPT / REJECT ========== */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action'])) {

    $username = $_POST['username'];
    $book_title = $_POST['book_title'];

    if ($_POST['action'] === "accept") {

        // Mark as returned
        $stmt = $conn->prepare("
            UPDATE borrow_requests
            SET return_status='approved',
                return_date = NOW()
            WHERE username=? AND book_title=? 
            AND return_status='pending'
        ");
        $stmt->bind_param("ss", $username, $book_title);
        $stmt->execute();
        $stmt->close();

        // ✅ Increase book copies back
        $stmt3 = $conn->prepare("
            UPDATE boooks 
            SET copies = copies + 1
            WHERE title = ?
        ");
        $stmt3->bind_param("s", $book_title);
        $stmt3->execute();
        $stmt3->close();

        // Remove notification
        $stmt2 = $conn->prepare("
            DELETE FROM return_notifications
            WHERE username=? AND book_title=?
        ");
        $stmt2->bind_param("ss", $username, $book_title);
        $stmt2->execute();
        $stmt2->close();
    }

    if ($_POST['action'] === "reject") {

        // Reset return request
        $stmt = $conn->prepare("
            UPDATE borrow_requests
            SET return_status=NULL
            WHERE username=? AND book_title=? 
            AND return_status='pending'
        ");
        $stmt->bind_param("ss", $username, $book_title);
        $stmt->execute();
        $stmt->close();

        // Remove notification
        $stmt2 = $conn->prepare("
            DELETE FROM return_notifications
            WHERE username=? AND book_title=?
        ");
        $stmt2->bind_param("ss", $username, $book_title);
        $stmt2->execute();
        $stmt2->close();
    }
}

/* ========== FETCH RETURN NOTIFICATIONS ========== */
$sql = "
    SELECT 
        rn.username,
        rn.book_title,
        b.cover AS book_cover
    FROM return_notifications rn
    JOIN boooks b 
        ON rn.book_title = b.title
    ORDER BY rn.id DESC
";

$result = $conn->query($sql);

if (!$result) {
    die("Query Error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<title>Return Requests</title>

<style>
body {
    margin: 0;
    font-family: "Segoe UI", sans-serif;
    background: #f5f7fb;
    display: flex;
}

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
}

.sidebar a:hover {
    background: #eef2ff;
    color: #3b5bff;
}

.main {
    margin-left: 250px;
    padding: 30px;
    width: calc(100% - 250px);
}

.request {
    background: #fff;
    padding: 15px;
    margin-bottom: 15px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
}

.request img {
    width: 60px;
    height: 90px;
    border-radius: 6px;
    object-fit: cover;
}

.info {
    flex: 1;
}

.actions {
    margin-left: auto;
    display: flex;
    gap: 10px;
}

button {
    padding: 6px 14px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}

.accept {
    background: #22c55e;
    color: white;
}

.reject {
    background: #ef4444;
    color: white;
}
</style>
</head>

<body>

<div class="sidebar">
    <h2>📚 Livo</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="libnotify.php">Borrow Requests</a>
    <a href="returnnotify.php">Return Requests</a>
</div>

<div class="main">
    <h2>Return Requests</h2>

    <?php if ($result->num_rows === 0): ?>
        <p>No return requests.</p>
    <?php endif; ?>

    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="request">
<?php
$coverPath = !empty($row['book_cover'])
    ? "uploads/" . $row['book_cover']
    : "uploads/default.png";

if (!file_exists($coverPath)) {
    $coverPath = "uploads/default-book.png";
}
?>

<img src="<?php echo htmlspecialchars($coverPath); ?>" alt="Book Cover">


            <div class="info">
                <strong><?php echo htmlspecialchars($row['book_title']); ?></strong><br>
                Returning by: <b><?php echo htmlspecialchars($row['username']); ?></b><br>
                <span style="color:#facc15; font-weight:bold;">RETURN PENDING</span>
            </div>

            <form method="POST" class="actions">
                <input type="hidden" name="username" value="<?php echo htmlspecialchars($row['username']); ?>">
                <input type="hidden" name="book_title" value="<?php echo htmlspecialchars($row['book_title']); ?>">
                <button name="action" value="accept" class="accept">Accept</button>
                <button name="action" value="reject" class="reject">Reject</button>
            </form>

        </div>
    <?php endwhile; ?>

</div>

</body>
</html>
