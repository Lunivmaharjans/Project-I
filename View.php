<?php
$conn = new mysqli("localhost", "root", "", "library");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get search query if submitted
$search = "";
if (isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
}

// Query books (with search filter if provided)
if ($search) {
    $sql = "SELECT * FROM boooks WHERE title LIKE '%$search%'";
} else {
    $sql = "SELECT * FROM boooks";
}

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Book Collection</title>
    <style>
        /* ----------------- GLOBAL ------------------- */
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            overflow-x: hidden; /* Remove horizontal scroll */
            font-family: Arial, sans-serif;
            background: #0d1b2a;
            color: white;
        }

        a { text-decoration: none; }

        /* ----------------- NAVBAR ------------------- */
        .navbar {
            width: 100%;
            max-width: 100%;
            background: #0b1623;
            padding: 12px 25px;
            display: flex;
            align-items: center;
            gap: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
            flex-wrap: wrap; /* Prevent overflow on small screens */
        }

        .navbar-logo {
            display: flex;
            align-items: center;
            font-size: 22px;
            font-weight: bold;
            color: white;
        }

        .search-box {
            display: flex;
            align-items: center;
            background: #1b263b;
            padding: 10px 15px;
            border-radius: 8px;
            flex-grow: 1;
            min-width: 150px;
        }

        .search-box input {
            flex: 1;
            background: transparent;
            border: none;
            outline: none;
            color: white;
            font-size: 15px;
        }

        .search-box i {
            margin-right: 10px;
            color: #9da9bb;
        }

        .nav-item {
            color: #e0e6ed;
            font-size: 15px;
            cursor: pointer;
        }

        .nav-item:hover {
            color: #fff;
        }

        .user-icon {
            width: 35px;
            height: 35px;
            background: #1b263b;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        /* ----------------- BOOK GRID ------------------- */
        .container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            padding: 30px;
            width: 100%;
            max-width: 100%;
        }

        .card {
            background: #1b263b;
            width: 200px;
            padding: 10px;
            border-radius: 10px;
            text-align: center;
            transition: 0.3s;
            flex-shrink: 0; /* Prevent shrinking that causes overflow */
        }

        .card:hover {
            transform: scale(1.05);
            cursor: pointer;
        }

        .card img {
            width: 100%;
            height: 280px;
            object-fit: cover;
            border-radius: 5px;
        }

        .title {
            margin-top: 10px;
            font-size: 16px;
            font-weight: bold;
            color: #fff;
        }

        h1 {
            padding-left: 30px;
        }

        @media screen and (max-width: 600px) {
            .card {
                width: 45%;
            }
        }

        @media screen and (max-width: 400px) {
            .card {
                width: 100%;
            }
        }
    </style>
</head>

<body>

<!-- =================== NAVBAR =================== -->
<div class="navbar">
    <a href="userdash.php"><div class="navbar-logo">Livo</div></a>

    <div class="search-box">
        <i>🔍</i>
        <form method="GET" style="display:flex; width:100%;">
            <input type="text" name="search" placeholder="Search books..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" style="display:none;">Search</button>
        </form>
    </div>

    <a href="usercate.php"><div class="nav-item">Categories</div></a>
</div>

<h1>📚 List Of Books</h1>

<div class="container">
<?php
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '
        <a href="details.php?id=' . $row["id"] . '">
            <div class="card">
                <img src="uploads/' . $row["cover"] . '" alt="Cover">
                <div class="title">' . $row["title"] . '</div>
            </div>
        </a>';
    }
} else {
    echo "<p style='color:#ccc; padding-left:30px;'>No books found.</p>";
}
?>
</div>

</body>
</html>
