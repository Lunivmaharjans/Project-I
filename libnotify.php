<?php
session_start();

/* 🔒 OPTIONAL: CHECK LIBRARIAN LOGIN */
// if (!isset($_SESSION['is_librarian'])) {
//     die("Access denied");
// }

$conn = new mysqli("localhost", "root", "", "library");
if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

/* HANDLE APPROVE / REJECT */
if (isset($_POST['action'], $_POST['request_id'], $_POST['book_id'])) {

    $request_id = (int) $_POST['request_id'];
    $book_id = (int) $_POST['book_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        // Reduce book copies if available
        $stmt = $conn->prepare("UPDATE boooks SET copies = copies - 1 WHERE id = ? AND copies > 0");
        $stmt->bind_param("i", $book_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $borrow_date = date('Y-m-d');
            $due_date = date('Y-m-d', strtotime('+7 days'));

            $stmt2 = $conn->prepare("
                UPDATE borrow_requests 
                SET status='approved', borrow_date=?, due_date=?
                WHERE id=?
            ");
            $stmt2->bind_param("ssi", $borrow_date, $due_date, $request_id);
            $stmt2->execute();
        } else {
            echo "<p style='color:red'>Cannot approve. No copies left.</p>";
        }
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("UPDATE borrow_requests SET status='rejected' WHERE id=?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
    }
}

/* FETCH BORROW REQUESTS */
$result = $conn->query("
    SELECT 
        br.id,
        br.book_id,
        b.title AS book_title,
        b.cover AS book_cover,
        br.username,
        br.status
    FROM borrow_requests br
    JOIN boooks b ON br.book_id = b.id
    ORDER BY br.id DESC
");

if (!$result) {
    die("Query Failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Library Notifications</title>

    <style>
        /* ---------------- Sidebar ---------------- */
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
            transition: 0.2s;
        }

        .sidebar a:hover {
            background: #eef2ff;
            color: #3b5bff;
        }

        .sidebar h4 {
            margin-top: 30px;
            margin-bottom: 10px;
            color: #666;
            font-size: 14px;
        }

        /* ---------------- Main Content ---------------- */
        .main {
            margin-left: 250px;
            padding: 30px;
            width: calc(100% - 250px);
        }

        h2.page-title {
            color: #1e3c72;
            margin-bottom: 20px;
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

        .status {
            font-weight: bold;
        }

        .pending {
            color: #facc15;
        }

        .approved {
            color: #22c55e;
        }

        .rejected {
            color: #ef4444;
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

        .approve {
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

    <!-- Sidebar -->
    <div class="sidebar">
        <h2>📚 Livo</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="#">Manage Books</a>
        <a href="#">Return Books</a>
        <a href="#">Borrowings</a>
        <a href="#">State Users </a>
        <a href="#">Overdue Books</a>
        <h4>Settings</h4>
        <a href="libnotify.php">Notifications</a>
    </div>

    <!-- Main Content -->
    <div class="main">
        <h2 class="page-title">📚 Borrow Requests</h2>

        <?php if ($result->num_rows === 0): ?>
            <p>No borrow requests.</p>
        <?php endif; ?>

        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="request">
                <img src="uploads/<?php echo htmlspecialchars($row['book_cover']); ?>" alt="Book Cover">
                <div class="info">
                    <strong><?php echo htmlspecialchars($row['book_title']); ?></strong><br>
                    Requested by: <b><?php echo htmlspecialchars($row['username']); ?></b><br>
                    <span class="status <?php echo $row['status']; ?>">
                        <?php echo strtoupper($row['status']); ?>
                    </span>
                </div>

                <?php if (in_array(strtolower($row['status']), ['pending', 'requested'])): ?>
                    <form method="POST" class="actions">
                        <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                        <input type="hidden" name="book_id" value="<?php echo $row['book_id']; ?>">
                        <button name="action" value="approve" class="approve">Approve</button>
                        <button name="action" value="reject" class="reject">Reject</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>

    </div>

</body>

</html>