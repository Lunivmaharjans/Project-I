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

/* ✅ HANDLE APPROVE / REJECT */
if (isset($_POST['action'], $_POST['request_id'], $_POST['book_id'])) {

    $request_id = (int) $_POST['request_id'];
    $book_id = (int) $_POST['book_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {

        // reduce book copies only if > 0
        $conn->query("UPDATE boooks SET copies = copies - 1 WHERE id = $book_id AND copies > 0");

        // update request status
        $stmt = $conn->prepare(
            "UPDATE borrow_requests SET status='approved' WHERE id=?"
        );
        $stmt->bind_param("i", $request_id);
        $stmt->execute();

    } elseif ($action === 'reject') {

        $stmt = $conn->prepare(
            "UPDATE borrow_requests SET status='rejected' WHERE id=?"
        );
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
    }
}

/* 📥 FETCH BORROW REQUESTS */
// We select username from borrow_requests table
$result = $conn->query(
    "SELECT id, book_id, book_title, book_cover, username, status 
     FROM borrow_requests 
     ORDER BY created_at DESC"
);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Library Notifications</title>
    <style>
        body {
            background: #0d1b2a;
            color: white;
            font-family: Arial;
            padding: 30px;
        }

        h2 {
            color: #a78bfa;
        }

        .request {
            background: #1b263b;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        img {
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

    <h2>📚 Borrow Requests</h2>

    <?php if ($result->num_rows === 0): ?>
        <p>No borrow requests.</p>
    <?php endif; ?>

    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="request">
            <img src="uploads/<?php echo htmlspecialchars($row['book_cover']); ?>">
            <div class="info">
                <strong><?php echo htmlspecialchars($row['book_title']); ?></strong><br>
                Requested by: <b><?php echo htmlspecialchars($row['username']); ?></b><br>
                <span class="status <?php echo $row['status']; ?>">
                    <?php echo strtoupper($row['status']); ?>
                </span>
            </div>

            <?php if ($row['status'] === 'pending'): ?>
                <form method="POST">
                    <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                    <input type="hidden" name="book_id" value="<?php echo $row['book_id']; ?>">
                    <button name="action" value="approve" class="approve">Approve</button>
                    <button name="action" value="reject" class="reject">Reject</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>

</body>

</html>