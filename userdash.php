<?php
session_start();

// redirect if not logged in
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION["username"];

// Database connection
$conn = new mysqli("localhost", "root", "", "library");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user info from DB
$sql = "SELECT profile_pic FROM users WHERE username=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Set default or DB profile picture
$profilePic = $user['profile_pic'] ?? 'default.png';

// SYNC SESSION PROFILE
if (!isset($_SESSION['profile_pic'])) {
    $_SESSION['profile_pic'] = $profilePic;
}
$profilePic = $_SESSION['profile_pic'];

// ================== PROFILE UPLOAD (ADDED) ==================
$uploadDir = "uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if (isset($_FILES['profile_pic'])) {
    $ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
    $newName = $username . "_" . time() . "." . $ext;
    $target = $uploadDir . $newName;

    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (in_array(strtolower($ext), $allowed)) {
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target)) {

            // Update DB
            $update = $conn->prepare("UPDATE users SET profile_pic=? WHERE username=?");
            $update->bind_param("ss", $newName, $username);
            $update->execute();

            // Sync session
            $_SESSION['profile_pic'] = $newName;
            $profilePic = $newName;
        }
    }
}
// ============================================================

// Greeting
date_default_timezone_set("Asia/Kathmandu");
$hour = (int) date("h");
$ampm = date("A");

if ($ampm === "AM") {
    $greeting = ($hour >= 5) ? "Good Morning" : "Good Night";
} else {
    if ($hour == 12 || $hour < 5)
        $greeting = "Good Afternoon";
    elseif ($hour < 9)
        $greeting = "Good Evening";
    else
        $greeting = "Good Night";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f5f7fb;
            overflow-x: hidden;
        }

        /* NAVBAR */
        .topnav {
            width: 100%;
            background: #1e293b;
            color: white;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.45);
            position: relative;
            z-index: 1000;
        }

        .logo {
            font-weight: 700;
            color: #4169e1;
            font-size: 20px;
            display: flex;
            align-items: center;
        }

        .logo span {
            margin-left: 8px;
        }

        .nav-center {
            flex: 1;
            display: flex;
            justify-content: center;
        }

        .nav-center form {
            width: 300px;
            position: relative;
        }

        .nav-center input {
            width: 100%;
            padding: 8px 36px 8px 14px;
            border-radius: 20px;
            border: 1px solid #ccc;
            outline: none;
        }

        .nav-center button {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            cursor: pointer;
        }

        .navbar-img {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #4169e1;
            cursor: pointer;
        }

        /* SIDEBAR */
        .sidebar {
            position: fixed;
            top: 55px;
            left: 0;
            width: 200px;
            height: 100%;
            background: #1e293b;
            padding: 20px;
        }

        .sidebar h4 {
            color: white;
            font-size: 13px;
            margin-bottom: 10px;
        }

        .sidebar a {
            display: block;
            padding: 10px 0;
            color: #fcf3f3ff;
            text-decoration: none;
            font-size: 15px;
        }

        .sidebar a:hover {
            color: #4169e1;
            font-weight: 600;
        }

        .logout {
            margin-top: 30px;
            color: red !important;
        }

        /* MAIN */
        .main {
            margin-left: 250px;
            padding: 25px 40px;
        }

        .heading {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stats-row {
            display: flex;
            gap: 20px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            width: 200px;
            border-radius: 12px;
            box-shadow: 0px 1px 4px rgba(0, 0, 0, 0.1);
        }

        .stat-card h3 {
            font-size: 15px;
            color: #666;
        }

        .stat-card .num {
            font-size: 28px;
            font-weight: bold;
            margin-top: 10px;
        }

        .section-title {
            margin-top: 35px;
            font-size: 18px;
            font-weight: 700;
        }

        .book-box {
            background: white;
            margin-top: 15px;
            padding: 15px;
            border-radius: 12px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
        }

        .book-item {
            display: flex;
            padding: 14px 0;
            border-bottom: 1px solid #eee;
        }

        .book-cover {
            width: 55px;
            height: 75px;
            border-radius: 6px;
            margin-right: 15px;
            background: #ddd;
        }

        .book-title {
            font-weight: 700;
        }

        .book-author {
            font-size: 13px;
            color: #777;
        }

        .days {
            font-size: 12px;
            background: #e5f0ff;
            padding: 3px 8px;
            border-radius: 5px;
            color: #4169e1;
            margin-top: 5px;
            display: inline-block;
        }
    </style>
</head>

<body>

    <!-- NAVBAR -->
    <div class="topnav">
        <div class="logo">📚 <span>Livo</span></div>
        <div class="nav-center">
            <form><input type="text" placeholder="Search books..."><button type="submit">🔍</button></form>
        </div>
        <div class="user-menu">
            <!-- CLICK PROFILE TO CHANGE -->
            <img src="uploads/<?php echo $profilePic . '?t=' . time(); ?>" class="navbar-img"
                onclick="document.getElementById('profileInput').click();">
            <span><?php echo ucfirst($username); ?></span>
        </div>
    </div>

    <!-- HIDDEN UPLOAD FORM -->
    <form method="POST" enctype="multipart/form-data" id="uploadForm" style="display:none;">
        <input type="file" name="profile_pic" id="profileInput" accept="image/*">
    </form>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <h4>NAVIGATION</h4>
        <a href="userdash.php">Dashboard</a>
        <a href="View.php">View Books</a>
        <a href="usercate.php">Categories</a>
        <a href="useracc.php">My Account</a>
        <a class="logout" href="login.php">Logout</a>
    </div>

    <!-- MAIN DASHBOARD -->
    <div class="main">
        <div class="heading"><?php echo $greeting; ?>, <?php echo ucfirst($username); ?>!</div>

        <div class="stats-row">
            <div class="stat-card">
                <h3>Books Borrowed</h3>
                <div class="num">5</div>
            </div>
            <div class="stat-card">
                <h3>Overdue</h3>
                <div class="num" style="color:red;">2</div>
            </div>
            <div class="stat-card">
                <h3>Due Soon</h3>
                <div class="num" style="color:#e6b400;">3</div>
            </div>
            <div class="stat-card">
                <h3>Account Status</h3>
                <div class="num" style="color:green;font-size:20px;">Active</div>
            </div>
        </div>

        <div class="section-title">📚 Currently Borrowed Books</div>
        <div class="book-box">
            <div class="book-item">
                <img src="assets/gatsby.jpg" class="book-cover">
                <div>
                    <div class="book-title">The Great Gatsby</div>
                    <div class="book-author">F. Scott Fitzgerald</div>
                    <div class="days">12 days left</div>
                </div>
            </div>
            <div class="book-item">
                <img src="assets/kill_mockingbird.jpg" class="book-cover">
                <div>
                    <div class="book-title">To Kill a Mockingbird</div>
                    <div class="book-author">Harper Lee</div>
                    <div class="days">17 days left</div>
                </div>
            </div>
        </div>
    </div>

    <!-- JS TO SUBMIT PROFILE UPLOAD -->
    <script>
        document.getElementById('profileInput').addEventListener('change', function () {
            document.getElementById('uploadForm').submit();
        });
    </script>

</body>

</html>