<?php
/* =========================
   SAMPLE REPORT DATA
   (Replace with MySQL later)
   ========================= */

$weekly_on_time = 40;
$weekly_late = 15;
$weekly_overdue = 8;

$monthly_on_time = 180;
$monthly_late = 42;
$monthly_overdue = 26;

$yearly_on_time = 2100;
$yearly_late = 380;
$yearly_overdue = 120;
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Library Report</title>

  <!-- Chart.js CDN -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
    body {
      margin: 0;
      font-family: "Segoe UI", sans-serif;
      background: #f5f7fb;
    }

    /* Sidebar */
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
    }

    .sidebar a:hover {
      background: #eef2ff;
      color: #3b5bff;
    }

    /* Main */
    .main {
      margin-left: 250px;
      padding: 30px;
    }

    .title {
      font-size: 26px;
      font-weight: 600;
      margin-bottom: 30px;
    }

    /* Charts */
    .chart-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 25px;
    }

    .chart-card {
      background: #fff;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      text-align: center;
    }

    .chart-card h3 {
      margin-bottom: 15px;
      font-size: 18px;
    }
  </style>
</head>

<body>

  <!-- Sidebar -->
  <div class="sidebar">
    <h2>📚 Livo</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="add.php">Add Books</a>
    <a href="issue.php">Issue Book</a>
    <a href="renew.php">Renew Book</a>
    <a href="update.php">Update Books</a>
    <a href="report.php">📊 Reports</a>
  </div>

  <!-- Main Content -->
  <div class="main">
    <div class="title">📊 Library Book Report</div>

    <div class="chart-grid">

      <!-- Weekly Chart -->
      <div class="chart-card">
        <h3>Weekly Report</h3>
        <canvas id="weekChart"></canvas>
      </div>

      <!-- Monthly Chart -->
      <div class="chart-card">
        <h3>Monthly Report</h3>
        <canvas id="monthChart"></canvas>
      </div>

      <!-- Yearly Chart -->
      <div class="chart-card">
        <h3>Yearly Report</h3>
        <canvas id="yearChart"></canvas>
      </div>

    </div>
  </div>

  <script>
    function createPieChart(canvasId, data) {
      new Chart(document.getElementById(canvasId), {
        type: "pie",
        data: {
          labels: ["On Time", "Late", "Overdue"],
          datasets: [{
            data: data,
            backgroundColor: ["#4CAF50", "#FFC107", "#F44336"]
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              position: "bottom"
            }
          }
        }
      });
    }

    // PHP → JS Data
    createPieChart("weekChart", [
      <?= $weekly_on_time ?>,
      <?= $weekly_late ?>,
      <?= $weekly_overdue ?>
    ]);

    createPieChart("monthChart", [
      <?= $monthly_on_time ?>,
      <?= $monthly_late ?>,
      <?= $monthly_overdue ?>
    ]);

    createPieChart("yearChart", [
      <?= $yearly_on_time ?>,
      <?= $yearly_late ?>,
      <?= $yearly_overdue ?>
    ]);
  </script>

</body>
</html>
