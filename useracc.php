<?php
session_start();

if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION["username"];
date_default_timezone_set("Asia/Kathmandu");

/* DB */
$conn = new mysqli("localhost", "root", "", "library");
if ($conn->connect_error) {
    die("Database connection failed");
}

/* Upload library card */
if (isset($_POST['upload_card']) && isset($_FILES['library_card'])) {
    $dir = "uploads/cards/";
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    if ($_FILES['library_card']['error'] === 0) {
        $fileName = time() . "_" . basename($_FILES["library_card"]["name"]);
        move_uploaded_file($_FILES["library_card"]["tmp_name"], $dir . $fileName);

        $stmt = $conn->prepare("UPDATE users SET library_card_pic=? WHERE username=?");
        $stmt->bind_param("ss", $fileName, $username);
        $stmt->execute();
    }
}

/* Fetch user info (SAFE) */
$stmt = $conn->prepare(
    "SELECT username, email, phone, created_at, profile_pic, library_card_pic 
     FROM users WHERE username=?"
);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

/* If user not found → logout */
if ($result->num_rows === 0) {
    session_destroy();
    header("Location: login.php");
    exit();
}

$user = $result->fetch_assoc();

/* Values */
$fullname = $user['username'];
$email = $user['email'];
$phone = $user['phone'];
$member_since = $user['created_at'];
$profilePic = !empty($user['profile_pic']) ? $user['profile_pic'] : 'default.png';
$libraryCardPic = !empty($user['library_card_pic']) ? $user['library_card_pic'] : 'card_default.png';

/* Greeting */
$h = date("H");
$greeting =
    ($h < 12) ? "Good Morning" :
    (($h < 17) ? "Good Afternoon" :
        (($h < 21) ? "Good Evening" : "Good Night"));
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>My Account</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial
        }

        body {
            background: #f0f2f5
        }

        .topnav {
            background: #1e293b;
            color: #fff;
            padding: 12px 20px;
            display: flex;
            align-items: center
        }

        .logo {
            color: #2374e1;
            font-size: 20px;
            font-weight: bold
        }

        .user-menu {
            margin-left: auto;
            display: flex;
            gap: 8px;
            align-items: center
        }

        .navbar-img {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover
        }

        .sidebar {
            position: fixed;
            top: 55px;
            left: 0;
            width: 200px;
            height: 100%;
            background: #1e293b;
            padding: 20px
        }

        .sidebar a {
            display: block;
            color: #fff;
            text-decoration: none;
            margin: 12px 0
        }

        .main {
            margin-left: 220px;
            padding: 30px
        }

        .account-info {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            width: 460px
        }

        .profile-wrapper {
            text-align: center
        }

        .profile-img {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            border: 3px solid #2374e1;
            object-fit: cover
        }

        .card-img {
            width: 280px;
            height: 160px;
            margin-top: 8px;
            border-radius: 10px;
            border: 2px dashed #2374e1;
            object-fit: cover;
            cursor: pointer;
            transition: .3s
        }

        .card-img:hover {
            transform: scale(1.03);
            box-shadow: 0 6px 15px rgba(0, 0, 0, .2)
        }
    </style>
</head>

<body>

    <div class="topnav">
        <div class="logo">📚 Livo</div>
        <div class="user-menu">
            <img src="uploads/<?= $profilePic ?>?t=<?= time() ?>" class="navbar-img">
            <span><?= ucfirst($fullname) ?></span>
        </div>
    </div>

    <div class="sidebar">
        <a href="userdash.php">Dashboard</a>
        <a href="View.php">View Books</a>
        <a href="usercate.php">Categories</a>
        <a href="useracc.php">My Account</a>
    </div>

    <div class="main">
        <h2><?= $greeting ?>, <?= ucfirst($fullname) ?></h2><br>

        <div class="account-info">
            <div class="profile-wrapper">

                <img src="uploads/<?= $profilePic ?>?t=<?= time() ?>" class="profile-img">

                <p style="margin-top:12px;font-weight:bold">Library Card</p>

                <form method="POST" enctype="multipart/form-data" id="cardForm">
                    <input type="file" name="library_card" id="cardInput" hidden onchange="this.form.submit()">
                    <input type="hidden" name="upload_card">
                </form>

                <img src="uploads/cards/<?= $libraryCardPic ?>?t=<?= time() ?>" class="card-img"
                    onclick="document.getElementById('cardInput').click()"
                    title="Click to upload / replace library card">

                <p style="font-size:12px;color:#555;">Click card to upload / replace</p>

            </div><br>

            <p><b>Email:</b> <?= $email ?></p>
            <p><b>Phone:</b> <?= $phone ?></p>
            <p><b>Member Since:</b> <?= $member_since ?></p>
        </div>
    </div>

</body>

</html>