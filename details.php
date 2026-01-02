<?php
session_start(); // To access $_SESSION for username

$conn = new mysqli("localhost", "root", "", "library");
if ($conn->connect_error)
    die("Connection Failed: " . $conn->connect_error);

// -------------------- HANDLE BORROW / CANCEL REQUEST --------------------

// -------------------- HANDLE BORROW REQUEST (FIXED) --------------------
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['book_id']) && !isset($_POST['comment'])) {

    // Must be logged in
    if (!isset($_SESSION['username'])) {
        http_response_code(401);
        exit;
    }

    $book_id = (int) $_POST['book_id'];
    $username = $_SESSION['username'];

    // Prevent race condition
    $conn->query("START TRANSACTION");

    // Lock book row
    $book = $conn->query("SELECT copies FROM boooks WHERE id=$book_id FOR UPDATE")->fetch_assoc();

    if (!$book || $book['copies'] <= 0) {
        $conn->query("ROLLBACK");
        exit;
    }

    // Check duplicate borrow
    $exists = $conn->query("SELECT id FROM borrow_requests 
                            WHERE book_id=$book_id AND username='$username'");

    if ($exists->num_rows > 0) {
        $conn->query("ROLLBACK");
        exit;
    }

    // Insert borrow request
    $conn->query("INSERT INTO borrow_requests (book_id, username, status)
                  VALUES ($book_id, '$username', 'pending')");

    // Decrease copies (SAFE)
    $conn->query("
    UPDATE boooks 
    SET copies = CASE 
        WHEN copies > 0 THEN copies - 1 
        ELSE 0 
    END
    WHERE id = $book_id
");


    $conn->query("COMMIT");
    exit;
}

// Cancel Borrow Request
// -------------------- HANDLE CANCEL REQUEST (FIXED) --------------------
if (isset($_GET['cancel_id']) && isset($_SESSION['username'])) {

    $book_id = (int) $_GET['cancel_id'];
    $username = $_SESSION['username'];

    $conn->query("START TRANSACTION");

    $request = $conn->query("SELECT id FROM borrow_requests 
                             WHERE book_id=$book_id AND username='$username'")
        ->fetch_assoc();

    if ($request) {
        $conn->query("DELETE FROM borrow_requests WHERE id=" . $request['id']);

        // Restore copies safely
        $conn->query("UPDATE boooks 
                      SET copies = LEAST(copies + 1, total_copies) 
                      WHERE id=$book_id");
    }

    $conn->query("COMMIT");
    exit;
}

// -------------------- END OF BORROW / CANCEL LOGIC --------------------

// Handle comment submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['comment'])) {
    $comment = $conn->real_escape_string($_POST['comment']);
    $book_id = (int) $_POST['book_id'];
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
    $conn->query("INSERT INTO comments (book_id, comment, username) VALUES ($book_id, '$comment', '$username')");
}

// Check if user has already requested this book
$has_borrowed = false;
if (isset($_SESSION['username']) && isset($_GET['id'])) {
    $username = $_SESSION['username'];
    $book_id_check = (int) $_GET['id'];
    $check = $conn->query("SELECT * FROM borrow_requests WHERE book_id=$book_id_check AND username='$username'");
    if ($check && $check->num_rows > 0)
        $has_borrowed = true;
}

// Get book and recommended
function getBook($conn, $id)
{
    $stmt = $conn->prepare("SELECT * FROM boooks WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getRecommended($conn, $category, $exclude_id)
{
    $stmt = $conn->prepare(
        "SELECT id, title, author, cover 
         FROM boooks 
         WHERE category = ? AND id != ? 
         ORDER BY RAND() LIMIT 8"
    );
    $stmt->bind_param("si", $category, $exclude_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// AJAX loading for recommended books
if (isset($_GET['ajax_id'])) {
    $book = getBook($conn, (int) $_GET['ajax_id']);

    if (!$book) {
        echo json_encode(['error' => 'Book not found', 'copies' => 0]);
        exit;
    }

    $recommended = getRecommended($conn, $book['category'], $book['id']);

    $comments_result = $conn->query("SELECT * FROM comments WHERE book_id = " . $book['id'] . " ORDER BY id DESC");
    $comments = [];
    while ($row = $comments_result->fetch_assoc())
        $comments[] = $row;

    $has_borrowed_ajax = false;
    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];
        $check = $conn->query("SELECT * FROM borrow_requests WHERE book_id={$book['id']} AND username='$username'");
        if ($check && $check->num_rows > 0)
            $has_borrowed_ajax = true;
    }

    echo json_encode([
        'book' => $book,
        'recommended' => $recommended,
        'comments' => $comments,
        'has_borrowed' => $has_borrowed_ajax,
        'copies' => max(0, $book['copies']),
        'logged_in' => isset($_SESSION['username'])
    ]);

    exit;
}

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 1;
$book = getBook($conn, $id);

// If book deleted, provide default
if (!$book) {
    $book = [
        'id' => 0,
        'title' => 'Book not found',
        'author' => '',
        'isbn' => '',
        'category' => '',
        'publisher' => '',
        'cover' => 'default.png',
        'description' => '',
        'copies' => 0,
        'total_copies' => 0
    ];
}

$recommended = getRecommended($conn, $book['category'], $book['id']);
$comments_result = $conn->query("SELECT * FROM comments WHERE book_id = " . ($book['id'] ?? 0) . " ORDER BY id DESC");
$comments = [];
while ($row = $comments_result->fetch_assoc())
    $comments[] = $row;
?>

<!DOCTYPE html>
<html>

<head>
    <title><?php echo $book["title"]; ?></title>
    <style>
        /* Your existing CSS unchanged */
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #0d1b2a;
            color: white;
            overflow-x: hidden;
        }

        a {
            text-decoration: none;
        }

        .navbar {
            width: 100%;
            background: #0b1623;
            padding: 12px 25px;
            display: flex;
            align-items: center;
            gap: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
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

        .container {
            display: flex;
            gap: 40px;
            padding: 40px 30px;
            align-items: flex-start;
        }

        .book-left {
            flex: 1;
            display: flex;
            gap: 40px;
            flex-wrap: wrap;
        }

        .cover {
            width: 300px;
            height: 450px;
            object-fit: cover;
            border-radius: 12px;
        }

        .info {
            max-width: 772px;
        }

        .info h2 {
            font-size: 30px;
            margin-bottom: 10px;
        }

        .info p {
            margin: 6px 0;
            line-height: 1.6;
        }

        .borrow-btn {
            margin-top: 20px;
            padding: 12px 22px;
            font-size: 16px;
            border: none;
            border-radius: 8px;
            background: #22c55e;
            color: white;
            cursor: pointer;
        }

        .borrow-btn:hover {
            background: #16a34a;
        }

        .cancel-btn {
            background: #ef4444 !important;
        }

        .out-of-stock {
            background: #64748b !important;
            cursor: default;
        }

        .similar-panel {
            width: 300px;
            flex-shrink: 0;
            border-radius: 12px;
            padding: 0;
        }

        .similar-panel h3 {
            font-size: 20px;
            margin-bottom: 15px;
            color: #a78bfa;
            padding-left: 18px;
        }

        .similar-book {
            display: flex;
            gap: 12px;
            padding: 8px 18px;
            margin-bottom: 12px;
            border-radius: 8px;
            transition: 0.2s;
            cursor: pointer;
        }

        .similar-book:hover {
            background: #1e293b;
        }

        .similar-book img {
            width: 55px;
            height: 80px;
            object-fit: cover;
            border-radius: 6px;
        }

        .similar-book .text {
            font-size: 14px;
        }

        .similar-book .text span {
            display: block;
            font-size: 12px;
            color: #94a3b8;
            margin-top: 3px;
        }

        .comments-section {
            width: 100%;
            max-width: 1100px;
            margin: auto;
            margin-top: 50px;
        }

        .comments-section h3 {
            color: #a78bfa;
            margin-bottom: 15px;
        }

        .comments-section textarea {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: none;
            resize: none;
            background: #1b263b;
            color: white;
            min-height: 70px;
        }

        .comments-section button {
            margin-top: 8px;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            background: #22c55e;
            color: white;
            cursor: pointer;
        }

        .comments-section button:hover {
            background: #16a34a;
        }

        .comment-box {
            padding: 10px 15px;
            background: #1b263b;
            margin-bottom: 10px;
            border-radius: 8px;
        }

        @media screen and (max-width:900px) {
            .container {
                flex-direction: column;
            }

            .similar-panel {
                width: 100%;
            }

            .book-left {
                flex-direction: column;
            }

            .cover {
                width: 100%;
                max-width: 300px;
                margin: 0 auto;
            }
        }
    </style>
</head>

<body>

    <div class="navbar">
        <a href="View.php">
            <div class="navbar-logo">Livo</div>
        </a>
        <div class="search-box">
            <i>🔍</i>
            <form method="GET" action="View.php" style="display:flex;width:100%;">
                <input type="text" name="search" placeholder="Search books...">
                <button type="submit" style="display:none;">Search</button>
            </form>
        </div>
        <div class="nav-item">Categories ▼</div>
    </div>

    <div class="container">
        <div class="book-left">
            <img id="book-cover" src="uploads/<?php echo $book["cover"]; ?>" class="cover">
            <div class="info" id="book-info">
                <h2><?php echo $book["title"]; ?></h2>
                <p><strong>Author:</strong> <?php echo $book["author"]; ?></p>
                <p><strong>ISBN:</strong> <?php echo $book["isbn"]; ?></p>
                <p><strong>Category:</strong> <?php echo $book["category"]; ?></p>
                <p><strong>Publisher:</strong> <?php echo $book["publisher"]; ?></p>
                <p><strong>Copies Available:</strong>
                    <span id="copies-count">
                        <?php echo (int) $book['copies']; ?>
                    </span>
                </p>


                <p><strong>Description:</strong><br><?php echo $book["description"]; ?></p>

                <form id="borrow-form" method="POST">
                    <input type="hidden" name="book_id" value="<?php echo $book["id"]; ?>" id="borrow-id">
                    <?php if (!isset($_SESSION['username'])): ?>
                        <button type="button" class="borrow-btn out-of-stock" disabled>Login to Borrow</button>
                    <?php elseif ($book["copies"] <= 0): ?>
                        <button type="button" class="borrow-btn out-of-stock" disabled>Out of Stock</button>
                    <?php elseif ($has_borrowed): ?>
                        <button type="button" class="borrow-btn cancel-btn">Cancel Request</button>
                    <?php else: ?>
                        <button type="button" class="borrow-btn request-btn">Borrow Book</button>
                    <?php endif; ?>

                </form>
            </div>
        </div>

        <div class="similar-panel" id="rec-panel">
            <h3>Recommended Books</h3>
            <?php foreach ($recommended as $r): ?>
                <div class="similar-book" onclick="loadBook(<?php echo $r['id']; ?>)">
                    <img src="uploads/<?php echo $r['cover']; ?>">
                    <div class="text"><?php echo $r['title']; ?><span><?php echo $r['author']; ?></span></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="comments-section">
        <h3>Comments</h3>
        <form id="comment-form">
            <textarea name="comment" placeholder="Write your comment..." required></textarea>
            <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
            <button type="submit">Post Comment</button>
        </form>
        <div id="comments-list">
            <?php if (count($comments) === 0): ?>
                <p style="color:#ccc;">No comments yet. Be the first to comment!</p>
            <?php else: ?>
                <?php foreach ($comments as $c): ?>
                    <div class="comment-box"><strong><?php echo htmlspecialchars($c['username']); ?>:</strong>
                        <?php echo htmlspecialchars($c['comment']); ?></div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function loadBook(id) {
            fetch('?ajax_id=' + id)
                .then(res => res.json())
                .then(data => {
                    if (data.error) return alert(data.error);
                    let book = data.book;
                    let copies = data.copies; // ✅ actual copies from server
                    document.getElementById('book-cover').src = 'uploads/' + book.cover;
                    document.getElementById('book-info').innerHTML = `
            <h2>${book.title}</h2>
            <p><strong>Author:</strong> ${book.author}</p>
            <p><strong>ISBN:</strong> ${book.isbn}</p>
            <p><strong>Category:</strong> ${book.category}</p>
            <p><strong>Publisher:</strong> ${book.publisher}</p>
           <p><strong>Copies Available:</strong> 
    <span id="copies-count">${copies}</span>
</p>

            <p><strong>Description:</strong><br>${book.description}</p>
            <form id="borrow-form">
                <input type="hidden" name="book_id" value="${book.id}" id="borrow-id">
               ${!data.logged_in
                            ? '<button type="button" class="borrow-btn out-of-stock" disabled>Login to Borrow</button>'
                            : (copies <= 0
                                ? '<button type="button" class="borrow-btn out-of-stock" disabled>Out of Stock</button>'
                                : (data.has_borrowed
                                    ? '<button type="button" class="borrow-btn cancel-btn">Cancel Request</button>'
                                    : '<button type="button" class="borrow-btn request-btn">Borrow Book</button>'
                                )
                            )
                        }

            </form>
        `;
                    bindBorrowButtons();

                    // Recommended
                    let recHtml = '<h3>Recommended Books</h3>';
                    data.recommended.forEach(r => {
                        recHtml += `<div class="similar-book" onclick="loadBook(${r.id})">
                <img src="uploads/${r.cover}">
                <div class="text">${r.title}<span>${r.author}</span></div>
            </div>`;
                    });
                    document.getElementById('rec-panel').innerHTML = recHtml;

                    // Comments
                    let commentHtml = '';
                    if (data.comments.length === 0) commentHtml = '<p style="color:#ccc;">No comments yet. Be the first to comment!</p>';
                    else data.comments.forEach(c => { commentHtml += `<div class="comment-box"><strong>${c.username}:</strong> ${c.comment}</div>` });
                    document.getElementById('comments-list').innerHTML = commentHtml;
                });
        }

        document.getElementById('comment-form').addEventListener('submit', function (e) {
            e.preventDefault();
            let formData = new FormData(this);
            let currentBookId = document.querySelector('#borrow-id').value;
            formData.set('book_id', currentBookId);
            fetch('', { method: 'POST', body: formData })
                .then(() => loadBook(currentBookId))
                .catch(err => console.error(err));
            this.reset();
        });

        function bindBorrowButtons() {
            const borrowForm = document.getElementById('borrow-form');
            if (!borrowForm) return;
            const borrowBtn = borrowForm.querySelector('button');
            if (!borrowBtn || borrowBtn.classList.contains('out-of-stock')) return;

            borrowBtn.onclick = function () {
                const bookId = document.querySelector('#borrow-id').value;
                if (borrowBtn.classList.contains('request-btn')) {
                    fetch('', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'book_id=' + bookId })
                        .then(() => loadBook(bookId)) // ✅ reload book to get correct copies
                        .catch(err => console.error(err));
                } else if (borrowBtn.classList.contains('cancel-btn')) {
                    fetch('?cancel_id=' + bookId)
                        .then(() => loadBook(bookId)) // ✅ reload book after cancel
                        .catch(err => console.error(err));
                }
            }
        }

        bindBorrowButtons();
    </script>

</body>

</html>