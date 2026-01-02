<?php
session_start();
if (!isset($_SESSION['username'])) {
    die("Login required");
}

$username = $_SESSION['username'];
$conn = new mysqli("localhost", "root", "", "library");
if ($conn->connect_error) {
    die("DB Error: " . $conn->connect_error);
}

$today = date("Y-m-d");

/* ✅ FIX: Use correct table name and column names */
$sql = "
    SELECT br.*, b.title, b.cover
    FROM borrow_requests br
    JOIN boooks b ON br.book_id = b.id
    WHERE br.username = ?
    ORDER BY br.created_at DESC
";

/* Prepare statement */
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("s", $username);

if (!$stmt->execute()) {
    die("Query failed: " . $stmt->error);
}

$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>User Notifications</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #0d1b2a;
            color: white;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 220px;
            height: 100%;
            background: #1e293b;
            padding: 20px;
        }

        .sidebar h4 {
            margin-bottom: 20px;
        }

        .sidebar a {
            display: block;
            padding: 10px 0;
            color: #fff;
            text-decoration: none;
        }

        .sidebar a:hover {
            color: #4169e1;
            font-weight: bold;
        }

        .logout {
            color: red !important;
            margin-top: 30px;
        }

        .main {
            margin-left: 220px;
            padding: 25px 40px;
        }

        h2 {
            color: #a78bfa;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 10px;
            border-bottom: 1px solid #444;
            text-align: center;
        }

        th {
            color: #facc15;
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

        .late {
            color: #f87171;
        }

        @media (max-width: 768px) {
            .sidebar {
                position: relative;
                width: 100%;
            }

            .main {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <h4>NAVIGATION</h4>
        <a href="userdash.php">Dashboard</a>
        <a href="View.php">View Books</a>
        <a href="usercate.php">Categories</a>
        <a href="usernotify.php">Notifications</a>
        <a href="useracc.php">My Account</a>
        <a class="logout" href="login.php">Logout</a>
    </div>

    <div class="main">
        <h2>📚 My Borrow Requests & Notifications</h2>

        <?php if ($result->num_rows > 0): ?>
            <table>
                <tr>
                    <th>Book Name</th>
                    <th>Book Cover</th>
                    <th>Borrow Date</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Late Fee</th>
                    <th>Days Left / Overdue</th>
                </tr>

                <?php while ($row = $result->fetch_assoc()):

                    $late_fee = '-';
                    $days_info = '-';

                    $status = 'PENDING';
                    $status_class = 'pending';

                    if (!empty($row['borrow_date']) || !empty($row['due_date'])) {
                        $status = strtoupper($row['status']);
                        $status_class = $row['status'];
                    }

                    if ($row['due_date'] && $row['status'] !== 'returned') {
                        $diff = floor((strtotime($today) - strtotime($row['due_date'])) / 86400);

                        if ($diff > 0) {
                            $late_fee = "Rs " . ($diff * 2);
                            $days_info = $diff . " day(s) overdue";
                            $status = "LATE";
                            $status_class = "late";
                        } else {
                            $days_info = abs($diff) . " day(s) left";
                        }
                    }
                    ?>

                    <tr>
                        <td><?= htmlspecialchars($row['title']) ?></td>

                        <td>
                            <?php
                            if (!empty($row['cover']) && file_exists("uploads/" . $row['cover'])) {
                                echo '<img src="uploads/' . htmlspecialchars($row['cover']) . '" width="50">';
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>

                        <td><?= $row['borrow_date'] ?? '-' ?></td>
                        <td><?= $row['due_date'] ?? '-' ?></td>
                        <td class="<?= $status_class ?>"><?= $status ?></td>
                        <td><?= $late_fee ?></td>
                        <td><?= $days_info ?></td>
                    </tr>

                <?php endwhile; ?>
            </table>

        <?php else: ?>
            <p>No borrow requests or notifications found.</p>
        <?php endif; ?>
    </div>

</body>

</html>