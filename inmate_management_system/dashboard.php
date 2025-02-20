<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

include 'config.php'; // Database connection

// Fetch monthly report counts
$stmt = $conn->query("
    SELECT DATE_FORMAT(report_date, '%Y-%m') AS month, COUNT(*) AS total_reports
    FROM reports  -- Make sure this is your actual table name
    GROUP BY month
    ORDER BY month ASC
");

$monthlyReports = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for Chart.js
$months = [];
$reportCounts = [];

foreach ($monthlyReports as $row) {
    $months[] = htmlspecialchars($row['month']); // Prevent XSS
    $reportCounts[] = (int)$row['total_reports']; // Ensure numerical data
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Chart.js Library -->
    <style>
        body {
            display: flex;
            background-color: #f8f9fa;
        }
        .sidebar {
            width: 250px;
            height: 100vh;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
            position: fixed;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 10px;
            display: block;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            width: 100%;
        }
        .chart-container {
            width: 100%;
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <h2>Welcome, <?= htmlspecialchars($_SESSION['admin_email']); ?>!</h2>
        <p>This is your admin dashboard.</p>
        <p>Use the sidebar to navigate.</p>

        <!-- Bar Chart for Monthly Reports -->
        <div class="chart-container mt-4">
            <h4 class="text-center">Monthly Reports Overview</h4>
            <canvas id="reportsChart"></canvas>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('reportsChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($months); ?>,
                datasets: [{
                    label: 'Number of Reports',
                    data: <?= json_encode($reportCounts); ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        stepSize: 1
                    }
                }
            }
        });
    </script>

</body>
</html>
