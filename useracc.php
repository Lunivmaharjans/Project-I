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

// Fetch user info from DB (FIXED COLUMN NAME)
$sql = "SELECT email, created_at, profile_pic FROM users WHERE username=?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Set defaults or DB values (FIXED VARIABLE)
$email = $user['email'] ?? 'user@example.com';
$member_since = $user['created_at'] ?? date("Y-m-d");
$profilePic = $user['profile_pic'] ?? 'default.png';

// SYNC SESSION PROFILE
if (!isset($_SESSION['profile_pic'])) {
    $_SESSION['profile_pic'] = $profilePic;
}
$profilePic = $_SESSION['profile_pic'];

// ================== PROFILE UPLOAD ==================
$uploadDir = "uploads/";
if (!is_dir($uploadDir))
    mkdir($uploadDir, 0777, true);

if (isset($_FILES['profile_pic'])) {
    $ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
    $newName = $username . "_" . time() . "." . $ext;
    $target = $uploadDir . $newName;

    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    if (in_array(strtolower($ext), $allowed)) {
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target)) {
            // Update DB
            $update = $conn->prepare("UPDATE users SET profile_pic=? WHERE username=?");
            if ($update) {
                $update->bind_param("ss", $newName, $username);
                $update->execute();
            }

            // Update session
            $_SESSION['profile_pic'] = $newName;
            $profilePic = $newName;
        }
    }
}
// =====================================================

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

<!DOCTYPE html>~
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account</title>
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
            margin-bottom: 20px;
        }

        .account-info {
            background: white;
            padding: 25px;
            border-radius: 15px;
            max-width: 500px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, .1);
        }

        .profile-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 20px;
            position: relative;
        }

        .profile-img {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #4169e1;
            cursor: pointer;
            transition: .3s;
        }

        .profile-img:hover {
            opacity: 0.85;
        }

        .profile-overlay {
            position: absolute;
            width: 130px;
            height: 130px;
            border-radius: 50%;
            top: 0;
            left: 0;
            background: rgba(0, 0, 0, .4);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: .3s;
            font-size: 14px;
            text-align: center;
            cursor: pointer;
        }

        .profile-wrapper:hover .profile-overlay {
            opacity: 1;
        }

        #profileInput {
            display: none;
        }

        .account-info p {
            font-size: 15px;
            color: #374151;
            margin-bottom: 10px;
        }

        .account-info strong {
            color: #111827;
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

    <!-- MAIN -->
    <div class="main">
        <div class="heading"><?php echo $greeting; ?>, <?php echo ucfirst($username); ?>!</div>

        <div class="account-info">
            <h3>My Account Information</h3>
            <div class="profile-wrapper">
                <img src="uploads/<?php echo $profilePic; ?>" class="profile-img" id="profileDisplay">
                <div class="profile-overlay" onclick="document.getElementById('profileInput').click();">Change Photo
                </div>
            </div>
            <p><strong>Username:</strong> <?php echo ucfirst($username); ?></p>
            <p><strong>Email:</strong> <?php echo $email; ?></p>
            <p><strong>Member Since:</strong> <?php echo $member_since; ?></p>
        </div>
    </div>

    <script>
        document.getElementById('profileInput').addEventListener('change', function () {
            document.getElementById('uploadForm').submit();
        });
    </script>

</body>

</html>