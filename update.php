<?php
$conn = new mysqli("localhost", "root", "", "library");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle update submission
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $author = $_POST['author'];
    $category = $_POST['category'];
    $copies = $_POST['copies'];

    $stmt = $conn->prepare("UPDATE boooks SET title=?, author=?, category=?, copies=? WHERE id=?");
    $stmt->bind_param("sssii", $title, $author, $category, $copies, $id);
    $stmt->execute();
    $stmt->close();
}

// Get search query if submitted
$search = "";
if (isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
}

// Query books (with search filter if provided)
$sql = $search ? "SELECT * FROM boooks WHERE title LIKE '%$search%'" : "SELECT * FROM boooks";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>

<head>
    <title>Update Books</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #0d1b2a;
            color: white;
        }

        a {
            text-decoration: none;
        }

        .navbar {
            display: flex;
            align-items: center;
            gap: 25px;
            padding: 12px 25px;
            background: #0b1623;
            flex-wrap: wrap;
        }

        .navbar-logo {
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

        .container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            padding: 30px;
        }

        .card {
            background: #1b263b;
            width: 200px;
            padding: 10px;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: 0.3s;
        }

        .card:hover {
            transform: scale(1.05);
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

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: #1b263b;
            padding: 20px;
            border-radius: 10px;
            width: 400px;
            max-width: 90%;
        }

        .modal-content h2 {
            margin-top: 0;
            color: #fff;
        }

        .modal-content input,
        .modal-content select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: none;
        }

        .modal-content button {
            padding: 10px 15px;
            background: #3b5bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .close {
            float: right;
            font-size: 20px;
            cursor: pointer;
            color: #ccc;
        }

        .close:hover {
            color: #fff;
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

    <div class="navbar">
        <a href="dashboard.php">
            <div class="navbar-logo">Livo</div>
        </a>
        <div class="search-box">
            <form method="GET" style="display:flex; width:100%;">
                <input type="text" name="search" placeholder="Search books..."
                    value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" style="display:none;">Search</button>
            </form>
        </div>
    </div>

    <h1 style="padding-left:30px;">📚 Update Books</h1>

    <div class="container">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '
        <div class="card" 
             data-id="' . $row['id'] . '" 
             data-title="' . htmlspecialchars($row['title']) . '" 
             data-author="' . htmlspecialchars($row['author']) . '" 
             data-category="' . htmlspecialchars($row['category']) . '" 
             data-copies="' . $row['copies'] . '">
            <img src="uploads/' . $row['cover'] . '" alt="Cover">
            <div class="title">' . $row['title'] . '</div>
        </div>';
            }
        } else {
            echo "<p style='color:#ccc; padding-left:30px;'>No books found.</p>";
        }
        ?>
    </div>

    <!-- Modal -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <span class="close" id="modalClose">&times;</span>
            <h2>Edit Book</h2>
            <form method="POST">
                <input type="hidden" name="id" id="bookId">
                <input type="text" name="title" id="bookTitle" placeholder="Title" required>
                <input type="text" name="author" id="bookAuthor" placeholder="Author" required>
                <select name="category" id="bookCategory" required>
                    <option value="">Select Category</option>
                    <option value="Fiction">Fiction</option>
                    <option value="Non-Fiction">Non-Fiction</option>
                    <option value="Science">Science</option>
                    <option value="Technology">Technology</option>
                    <option value="History">History</option>
                    <option value="Biography">Biography</option>
                </select>
                <input type="number" name="copies" id="bookCopies" placeholder="Copies" min="0" required>
                <button type="submit" name="update">Update</button>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('editModal');
        const modalClose = document.getElementById('modalClose');

        document.querySelectorAll('.card').forEach(card => {
            card.addEventListener('click', () => {
                document.getElementById('bookId').value = card.dataset.id;
                document.getElementById('bookTitle').value = card.dataset.title;
                document.getElementById('bookAuthor').value = card.dataset.author;
                document.getElementById('bookCategory').value = card.dataset.category;
                document.getElementById('bookCopies').value = card.dataset.copies;
                modal.style.display = 'flex';
            });
        });

        modalClose.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        window.addEventListener('click', (e) => {
            if (e.target == modal) modal.style.display = 'none';
        });
    </script>

</body>

</html>

<?php $conn->close(); ?>