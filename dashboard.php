<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

include 'config.php'; // Database connection

// Fetch total number of inmates
$totalInmatesStmt = $conn->query("SELECT COUNT(*) AS total FROM inmates");
$totalInmates = $totalInmatesStmt->fetch(PDO::FETCH_ASSOC)['total'];

// Fetch crime statistics for the current month
$crimeStmt = $conn->prepare("
    SELECT crime, COUNT(*) AS crime_count
    FROM inmates
    WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
      AND YEAR(created_at) = YEAR(CURRENT_DATE()) 
    GROUP BY crime
    ORDER BY crime_count DESC
");
$crimeStmt->execute();
$crimeData = $crimeStmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for Chart.js
$crimeLabels = [];
$crimeCounts = [];

foreach ($crimeData as $row) {
    $crimeLabels[] = htmlspecialchars($row['crime']);
    $crimeCounts[] = (int)$row['crime_count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crime & Inmate Report</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> 
    <style>
        body {
            display: flex;
            background-color: white;
            font-size: 14px;
            margin: 0;
            padding: 0;
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
            text-align: center;
        }
        .chart-container {
            width: 100%;
            max-width: 900px;
            height: 350px;
            margin: auto;
        }
        .print-button {
            margin-bottom: 10px;
        }
        @media print {
            .print-button { display: none; }
            .sidebar { display: none; }
            .main-content { margin-left: 0; width: 100%; }
            @page {
                size: A4 landscape;
                margin: 10mm;
            }
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
       

        <div class="report-container">
            <h3>Crime & Inmate Report - <?= date("F Y"); ?></h3>
            <h4 class="bg-success">Total Inmates: <?= $totalInmates; ?></h4>
            
            <div class="chart-container">
                <canvas id="crimeChart"></canvas>
            </div>
        </div>
        <button class="btn btn-success print-button float-start" onclick="window.print()" style="margin-left:25em;">Print Report</button>
    </div>

    <script>
        function getRandomColor() {
            return `rgba(${Math.random()*255}, ${Math.random()*255}, ${Math.random()*255}, 0.7)`;
        }

        const crimeLabels = <?= json_encode($crimeLabels); ?>;
        const crimeCounts = <?= json_encode($crimeCounts); ?>;
        const crimeColors = crimeLabels.map(() => getRandomColor());

        const crimeCtx = document.getElementById('crimeChart').getContext('2d');
        new Chart(crimeCtx, {
            type: 'bar',
            data: {
                labels: crimeLabels,
                datasets: [{
                    label: 'Crime Count',
                    data: crimeCounts,
                    backgroundColor: crimeColors,
                    borderColor: crimeColors.map(c => c.replace('0.7', '1')),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, stepSize: 1 } }
            }
        });
    </script>

</body>
</html>
