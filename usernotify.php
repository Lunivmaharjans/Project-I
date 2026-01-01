<?php
session_start();
if (!isset($_SESSION['username']))
    die("Login required");

$username = $_SESSION['username'];
$conn = new mysqli("localhost", "root", "", "library");
if ($conn->connect_error)
    die("DB Error");

$today = date("Y-m-d");
$result = $conn->query("SELECT * FROM borrow_requests WHERE username='$username' ORDER BY created_at DESC");

// Calculate late fees if book is approved but overdue
while ($row = $result->fetch_assoc()) {
    if ($row['status'] === 'approved' && $row['return_date'] === NULL && $row['due_date'] < $today) {
        $days_late = (strtotime($today) - strtotime($row['due_date'])) / (60 * 60 * 24);
        $late_fee = $days_late * 2; // 2 currency units per day
        $conn->query("UPDATE borrow_requests SET late_fee=$late_fee, status='late' WHERE id=" . $row['id']);
    }
}

// Fetch again after updates
$result = $conn->query("SELECT * FROM borrow_requests WHERE username='$username' ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Notifications</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #0d1b2a;
            color: white;
        }

        /* SIDEBAR */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 220px;
            height: 100%;
            background: #1e293b;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2);
        }

        .sidebar h4 {
            color: white;
            font-size: 14px;
            margin-bottom: 20px;
            letter-spacing: 0.5px;
        }

        .sidebar a {
            display: block;
            padding: 10px 0;
            color: #fcf3f3ff;
            text-decoration: none;
            font-size: 15px;
            transition: 0.2s;
        }

        .sidebar a:hover {
            color: #4169e1;
            font-weight: 600;
        }

        .sidebar .logout {
            margin-top: 30px;
            color: red !important;
        }

        /* MAIN CONTENT */
        .main {
            margin-left: 220px; /* same as sidebar width */
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

        .approved {
            color: #22c55e;
        }

        .rejected {
            color: #ef4444;
        }

        .late {
            color: #f87171;
        }

        /* Responsive */
        @media screen and (max-width: 768px) {
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
                box-shadow: none;
            }

            .main {
                margin-left: 0;
                padding: 20px;
            }
        }
    </style>
</head>

<body>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <h4>NAVIGATION</h4>
        <a href="userdash.php">Dashboard</a>
        <a href="View.php">View Books</a>
        <a href="usercate.php">Categories</a>
        <a href="usernotify.php">Notifications</a>
        <a href="useracc.php">My Account</a>
        <a class="logout" href="login.php">Logout</a>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main">
        <h2>📚 My Borrow Requests & Notifications</h2>

        <table>
            <tr>
                <th>Book</th>
                <th>Cover</th>
                <th>Borrow Date</th>
                <th>Due Date</th>
                <th>Status</th>
                <th>Late Fee</th>
                <th>Days Left / Overdue</th>
            </tr>

            <?php while ($row = $result->fetch_assoc()): 
                $days_info = '-';
                if ($row['status'] === 'approved' && $row['due_date']) {
                    $days_left = (strtotime($row['due_date']) - strtotime($today)) / (60 * 60 * 24);
                    $days_info = ($days_left >= 0) ? ceil($days_left) . " day(s) left" : abs(floor($days_left)) . " day(s) overdue";
                } elseif ($row['status'] === 'late' && $row['due_date']) {
                    $days_late = (strtotime($today) - strtotime($row['due_date'])) / (60 * 60 * 24);
                    $days_info = ceil($days_late) . " day(s) overdue";
                }
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['book_title']); ?></td>
                    <td><img src="uploads/<?php echo $row['book_cover']; ?>" width="50"></td>
                    <td><?php echo $row['borrow_date'] ?? '-'; ?></td>
                    <td><?php echo $row['due_date'] ?? '-'; ?></td>
                    <td class="<?php echo $row['status']; ?>"><?php echo strtoupper($row['status']); ?></td>
                    <td><?php echo $row['late_fee'] > 0 ? "Rs " . $row['late_fee'] : '-'; ?></td>
                    <td><?php echo $days_info; ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

</body>

</html>
