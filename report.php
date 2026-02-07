<?php
/* =========================
   SAMPLE REPORT DATA
   ========================= */

$daily_on_time = 6;
$daily_late = 2;
$daily_overdue = 1;

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

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
        }

        .sidebar a:hover {
            background: #eef2ff;
            color: #3b5bff;
        }

        .main {
            margin-left: 250px;
            padding: 30px;
        }

        .title {
            font-size: 26px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        select {
            padding: 8px 12px;
            font-size: 15px;
            border-radius: 6px;
            margin-bottom: 25px;
        }

        .chart-card {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            text-align: center;
            max-width: 450px;
        }

        canvas {
            max-width: 100%;
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
        <a href="report.php">Reports</a>
    </div>

    <!-- Main -->
    <div class="main">
        <div class="title">📊 Library Book Report</div>

        <!-- SELECT OPTION -->
        <select id="reportType" onchange="changeReport()">
            <option value="daily">Today</option>
            <option value="weekly" selected>Weekly</option>
            <option value="monthly">Monthly</option>
            <option value="yearly">Yearly</option>
        </select>

        <!-- Chart -->
        <div class="chart-card">
            <canvas id="reportChart"></canvas>
        </div>
    </div>

    <script>
        let chart;

        function loadChart(data, title) {
            if (chart) chart.destroy();

            chart = new Chart(document.getElementById("reportChart"), {
                type: "pie",
                data: {
                    labels: ["On Time", "Late", "Overdue"],
                    datasets: [{
                        data: data,
                        backgroundColor: ["#4CAF50", "#FFC107", "#F44336"]
                    }]
                },
                options: {
                    plugins: {
                        title: {
                            display: true,
                            text: title
                        },
                        legend: {
                            position: "bottom"
                        }
                    }
                }
            });
        }

        function changeReport() {
            const type = document.getElementById("reportType").value;

            if (type === "daily") {
                loadChart(
                    [<?= $daily_on_time ?>, <?= $daily_late ?>, <?= $daily_overdue ?>],
                    "Today's Report"
                );
            }

            if (type === "weekly") {
                loadChart(
                    [<?= $weekly_on_time ?>, <?= $weekly_late ?>, <?= $weekly_overdue ?>],
                    "Weekly Report"
                );
            }

            if (type === "monthly") {
                loadChart(
                    [<?= $monthly_on_time ?>, <?= $monthly_late ?>, <?= $monthly_overdue ?>],
                    "Monthly Report"
                );
            }

            if (type === "yearly") {
                loadChart(
                    [<?= $yearly_on_time ?>, <?= $yearly_late ?>, <?= $yearly_overdue ?>],
                    "Yearly Report"
                );
            }
        }

        // Load default (Weekly)
        changeReport();
    </script>

</body>

</html>