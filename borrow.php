<?php
session_start();

if (!isset($_SESSION['username'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

$conn = new mysqli("localhost", "root", "", "library");
if ($conn->connect_error)
    die(json_encode(['error' => 'DB connection failed']));

$username = $_SESSION['username'];

if (!isset($_POST['book_id'])) {
    echo json_encode(['error' => 'Invalid request']);
    exit();
}

$book_id = (int) $_POST['book_id'];

/* Check if already requested */
$check = $conn->prepare("SELECT id FROM borrow_requests WHERE username=? AND book_id=? AND status='pending'");
$check->bind_param("si", $username, $book_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode(['error' => 'You have already requested this book']);
    exit();
}

/* Get book info */
$stmt = $conn->prepare("SELECT title, cover, copies FROM boooks WHERE id=?");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Book not found']);
    exit();
}

$book = $result->fetch_assoc();

/* Insert borrow request */
$borrow_days = 7;
$borrow_date = date("Y-m-d");
$due_date = date("Y-m-d", strtotime("+$borrow_days days"));
$return_date = NULL;

$insert = $conn->prepare(
    "INSERT INTO borrow_requests (username, book_id, book_title, book_cover, borrow_date, due_date, return_date)
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);
$insert->bind_param("sisssss", $username, $book_id, $book['title'], $book['cover'], $borrow_date, $due_date, $return_date);

if ($insert->execute()) {
    // Decrease copies
    $conn->query("UPDATE boooks SET copies = copies - 1 WHERE id=$book_id");

    // Return updated info for AJAX
    $copies = $conn->query("SELECT copies FROM boooks WHERE id=$book_id")->fetch_assoc()['copies'];
    echo json_encode([
        'success' => true,
        'username' => $username,
        'book_id' => $book_id,
        'book_title' => $book['title'],
        'copies' => $copies
    ]);
} else {
    echo json_encode(['error' => 'Something went wrong.']);
}
