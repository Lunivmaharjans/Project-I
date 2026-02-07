<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Librarian Dashboard</title>

  <style>
    body {
      margin: 0;
      font-family: "Segoe UI", sans-serif;
      background: #f5f7fb;
    }

    .sidebar {
      width: 230px;
      background: #fff;
      height: 100vh;
      position: fixed;
      padding: 25px 20px;
      box-shadow: 2px 0 8px rgba(0, 0, 0, 0.08);
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

    /* Main Layout */
    .main {
      margin-left: 250px;
      padding: 30px;
    }

    .title {
      font-size: 25px;
      font-weight: 600;
    }

    .subtitle {
      color: #666;
      margin-bottom: 25px;
    }

    /* Metric Cards */
    .metrics {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 20px;
    }

    .card {
      padding: 20px;
      background: white;
      border-radius: 12px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
      opacity: 0;
      transform: translateY(20px);
      transition: 0.5s ease;
    }

    .card.show {
      opacity: 1;
      transform: translateY(0);
    }

    .card-title {
      font-size: 14px;
      color: #666;
    }

    .card-value {
      font-size: 28px;
      font-weight: 700;
      margin-top: 10px;
      color: #333;
    }

    /* Main Operations */
    .operations {
      margin-top: 40px;
    }

    .operations h3 {
      font-size: 18px;
      margin-bottom: 10px;
    }

    /* Operation Cards */
    .op-buttons-container {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
      margin-top: 20px;
    }

    .op-button {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      display: flex;
      justify-content: center;
      align-items: center;
      font-size: 28px;
      font-weight: 700;
      color: #1e3c72;
      height: 120px;
      text-decoration: none;
      transition: transform 0.3s, box-shadow 0.3s;
    }

    .op-button:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
    }


    .op-item {
      padding: 12px 0;
      border-bottom: 1px solid #eee;
      font-size: 15px;
    }

    .op-item:last-child {
      border-bottom: none;
    }
  </style>
</head>

<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <h2>📚 Livo</h2>

    <a href="dashboard.php">Dashboard</a>
    <a href="#">Manage Books</a>
    <a href="#">Return Books</a>
    <a href="#">Borrowings</a>
    <a href="view.php">View Boooks </a>
    <a href="#">Overdue Books</a>

    <h4>Settings</h4>
    <a href="libnotify.php">Notifications</a>
  </div>

  <div class="main">
    <div class="title">Librarian Dashboard</div>
    <br /><br />

    <div class="metrics">
      <div class="card">
        <div class="card-title">Total Books in Catalog</div>
        <div class="card-value">14,500</div>
      </div>

      <div class="card">
        <div class="card-title">Available Copies</div>
        <div class="card-value">9,870</div>
      </div>

      <div class="card">
        <div class="card-title">Overdue Books</div>
        <div class="card-value">28</div>
      </div>
    </div>

    <div class="operations">
      <h3>Main Operations</h3>
      <div class="op-buttons-container">
        <a href="add.php" class="op-button">➕ Add Books</a>
        <a href="issue.php" class="op-button">📤 Issue Book</a>
        <a href="renew.php" class="op-button">🔄 Renew Book</a>
        <a href="update.php" class="op-button">📕 Update Books</a>
      </div>
    </div>
  </div>

  <script>
    const cards = document.querySelectorAll(".card");
    window.addEventListener("load", () => {
      cards.forEach((card, i) => {
        setTimeout(() => {
          card.classList.add("show");
        }, i * 150);
      });
    });
  </script>
</body>

</html>