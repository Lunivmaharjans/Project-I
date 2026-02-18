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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Report</title>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #f9f9f9;
        }

        /* Sidebar */
        .sidebar {
            width: 240px;
            height: 100vh;
            background: #ffffff;
            position: fixed;
            padding: 25px;
            border-right: 1px solid #e5e5e5;
        }

        .sidebar h2 {
            margin-bottom: 35px;
            font-weight: 600;
        }

        .sidebar a {
            display: block;
            padding: 12px;
            margin-bottom: 8px;
            color: #333;
            text-decoration: none;
            border-radius: 8px;
            transition: 0.2s;
        }

        .sidebar a:hover {
            background: #f1f1f1;
        }

        /* Main */
        .main {
            margin-left: 260px;
            padding: 40px;
        }

        .title {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 25px;
        }

        /* Dropdown */
        select {
            padding: 10px 14px;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 15px;
            margin-bottom: 30px;
        }

        /* 2-column layout */
        .report-container {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
        }

        .chart-card,
        .analysis-card {
            background: white;
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        /* Pie chart */
        .chart-card {
            flex: 1 1 400px;
            max-width: 500px;
            text-align: center;
        }

        /* Analysis card */
        .analysis-card {
            flex: 1 1 300px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* Analysis Styling */
        .stat {
            margin: 10px 0;
            font-size: 15px;
        }

        .badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            margin-top: 15px;
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <h2>📚 Livo</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="add.php">Add Books</a>
        <a href="issue.php">Issue Book</a>
        <a href="renew.php">Renew Book</a>
        <a href="update.php">Update Books</a>
    </div>

    <div class="main">
        <div class="title">📊 Library Book Report</div>

        <select id="reportType" onchange="changeReport()">
            <option value="daily">Today</option>
            <option value="weekly" selected>Weekly</option>
            <option value="monthly">Monthly</option>
            <option value="yearly">Yearly</option>
        </select>

        <div class="report-container">
            <!-- Pie Chart -->
            <div class="chart-card">
                <canvas id="reportChart"></canvas>
            </div>

            <!-- Analysis -->
            <div class="analysis-card">
                <h3 id="analysisTitle">Weekly Analysis</h3>
                <div class="stat"><strong>Total Returns:</strong> <span id="totalReturn"></span></div>
                <div class="stat" style="color:#4CAF50;">On Time: <span id="onTimeVal"></span></div>
                <div class="stat" style="color:#FFC107;">Late: <span id="lateVal"></span></div>
                <div class="stat" style="color:#F44336;">Overdue: <span id="overdueVal"></span></div>
                <div id="performanceContainer"></div>
            </div>
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
                            text: title,
                            font: { size: 18 }
                        },
                        legend: { position: "bottom" }
                    }
                }
            });
        }

        function updateAnalysis(onTime, late, overdue, title) {

            const total = onTime + late + overdue;
            const onTimePercent = ((onTime / total) * 100).toFixed(1);

            let performanceText;
            let badgeColor;

            if (onTimePercent >= 70) {
                performanceText = "Excellent Performance";
                badgeColor = "#4CAF50";
            } else if (onTimePercent >= 50) {
                performanceText = "Average Performance";
                badgeColor = "#FFC107";
            } else {
                performanceText = "Needs Improvement";
                badgeColor = "#F44336";
            }

            document.getElementById("analysisTitle").innerText = title + " Analysis";
            document.getElementById("totalReturn").innerText = total;
            document.getElementById("onTimeVal").innerText = onTime + " (" + onTimePercent + "%)";
            document.getElementById("lateVal").innerText = late;
            document.getElementById("overdueVal").innerText = overdue;

            document.getElementById("performanceContainer").innerHTML =
                `<div class="badge" style="background:${badgeColor}; color:white;">
            ${performanceText}
         </div>`;
        }

        function changeReport() {
            const type = document.getElementById("reportType").value;

            if (type === "daily") {
                loadChart([<?= $daily_on_time ?>, <?= $daily_late ?>, <?= $daily_overdue ?>], "Today's Report");
                updateAnalysis(<?= $daily_on_time ?>, <?= $daily_late ?>, <?= $daily_overdue ?>, "Today's Report");
            }

            if (type === "weekly") {
                loadChart([<?= $weekly_on_time ?>, <?= $weekly_late ?>, <?= $weekly_overdue ?>], "Weekly Report");
                updateAnalysis(<?= $weekly_on_time ?>, <?= $weekly_late ?>, <?= $weekly_overdue ?>, "Weekly Report");
            }

            if (type === "monthly") {
                loadChart([<?= $monthly_on_time ?>, <?= $monthly_late ?>, <?= $monthly_overdue ?>], "Monthly Report");
                updateAnalysis(<?= $monthly_on_time ?>, <?= $monthly_late ?>, <?= $monthly_overdue ?>, "Monthly Report");
            }

            if (type === "yearly") {
                loadChart([<?= $yearly_on_time ?>, <?= $yearly_late ?>, <?= $yearly_overdue ?>], "Yearly Report");
                updateAnalysis(<?= $yearly_on_time ?>, <?= $yearly_late ?>, <?= $yearly_overdue ?>, "Yearly Report");
            }
        }

        changeReport();
    </script>

</body>

</html>