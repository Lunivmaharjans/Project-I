<?php
// ---------------------------
// PHP FORM PROCESSING
// ---------------------------

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title       = $_POST["title"];
    $author      = $_POST["author"];
    $isbn        = $_POST["isbn"];
    $publisher   = $_POST["publisher"];
    $category    = $_POST["category"];
    $copies      = $_POST["copies"];
    $description = $_POST["description"];

    // Image Upload
    $coverFileName = null;

    if (!empty($_FILES["cover"]["name"])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir);

        $coverFileName = time() . "_" . basename($_FILES["cover"]["name"]);
        $targetFile = $targetDir . $coverFileName;

        move_uploaded_file($_FILES["cover"]["tmp_name"], $targetFile);
    }

    // DB connection
    $conn = new mysqli("localhost", "root", "", "library");

    if ($conn->connect_error) {
        die("Connection Failed: " . $conn->connect_error);
    }

    // SQL (NO year field)
    $sql = "INSERT INTO boooks 
    (title, author, isbn, publisher, category, copies, description, cover)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("SQL ERROR: " . $conn->error);
    }

    $stmt->bind_param(
        "sssssiss",
        $title,
        $author,
        $isbn,
        $publisher,
        $category,
        $copies,
        $description,
        $coverFileName
    );

    $stmt->execute();
    $stmt->close();
    $conn->close();

    echo "<script>alert('Book added successfully!');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Book</title>

<style>
    body {
        margin: 0;
        font-family: 'Segoe UI', sans-serif;
        background: #f5f7fb;
    }

    /* Sidebar */
    .sidebar {
        width: 230px;
        background: #fff;
        height: 100vh;
        position: fixed;
        padding: 25px 20px;
        box-shadow: 2px 0 8px rgba(0,0,0,0.08);
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

    /* Main Content */
    .main {
        margin-left: 260px;
        padding: 30px;
    }

    h2 {
        margin-bottom: 20px;
    }

    /* Form */
    .book-form {
        width: 100%;
        max-width: 800px;
        background: #fff;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 0 12px rgba(0,0,0,0.08);
    }

    .row {
        display: flex;
        gap: 20px;
        margin-bottom: 18px;
    }

    .input-group {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    label {
        margin-bottom: 6px;
        font-weight: bold;
    }

    input, select, textarea {
        padding: 10px;
        border: 1px solid #bbb;
        border-radius: 6px;
    }

    .submit-btn {
        background: #2563eb;
        color: white;
        padding: 12px 22px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        margin-top: 15px;
    }

    #preview {
        width: 120px;
        margin-top: 10px;
        display: none;
        border-radius: 6px;
    }
</style>

<script>
function previewImage(event) {
    let img = document.getElementById("preview");
    img.src = URL.createObjectURL(event.target.files[0]);
    img.style.display = "block";
}
</script>

</head>

<body>

<!-- Sidebar -->
<div class="sidebar">
    <h2>📚 Livo</h2>

    <a href="dashboard.html">Dashboard</a>
    <a href="add.php">Manage Books</a>
    <a href="#">Return Books</a>
    <a href="#">Borrowings</a>
    <a href="#">State Users</a>
    <a href="#">Overdue Books</a>

    <h4>Settings</h4>
    <a href="#">Notifications</a>
</div>

<!-- Main Content -->
<div class="main">

<h2>Add New Book</h2>

<form class="book-form" method="POST" enctype="multipart/form-data">

    <div class="row">
        <div class="input-group">
            <label>Title *</label>
            <input type="text" name="title" required>
        </div>

        <div class="input-group">
            <label>Author *</label>
            <input type="text" name="author" required>
        </div>
    </div>

    <div class="row">
        <div class="input-group">
            <label>ISBN *</label>
            <input type="text" name="isbn" required>
        </div>

        <div class="input-group">
            <label>Publisher *</label>
            <input type="text" name="publisher" required>
        </div>
    </div>

    <div class="row">
        <div class="input-group">
            <label>Category *</label>
            <select name="category" required>
                <option value="">Select Category</option>
                <option>Fiction</option>
                <option>Non-Fiction</option>
                <option>Science</option>
                <option>Technology</option>
                <option>History</option>
                <option>Biography</option>
            </select>
        </div>

        <div class="input-group">
            <label>Total Copies *</label>
            <input type="number" name="copies" required value="1">
        </div>
    </div>

    <div class="row">
        <div class="input-group">
            <label>Book Cover</label>
            <input type="file" name="cover" accept="image/*" onchange="previewImage(event)">
            <img id="preview">
        </div>
    </div>

    <div class="input-group">
        <label>Description *</label>
        <textarea name="description" rows="4" required></textarea>
    </div>

    <button class="submit-btn" type="submit">Add Book</button>

</form>

</div>

</body>
</html>
