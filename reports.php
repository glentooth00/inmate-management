<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

include 'config.php'; // Database connection

// Function to generate CSV report
function generateReport($type, $conn) {
    $filename = ($type === 'monthly') ? 'Monthly_Report_' . date('F_Y') . '.csv' : 'Yearly_Report_' . date('Y') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Crime', 'Total Cases']); // CSV Header

    if ($type === 'monthly') {
        $stmt = $conn->prepare("
            SELECT crime, COUNT(*) AS total_cases
            FROM inmates
            WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
              AND YEAR(created_at) = YEAR(CURRENT_DATE()) 
            GROUP BY crime
            ORDER BY total_cases DESC
        ");
    } else { // Yearly report
        $stmt = $conn->prepare("
            SELECT crime, COUNT(*) AS total_cases
            FROM inmates
            WHERE YEAR(created_at) = YEAR(CURRENT_DATE()) 
            GROUP BY crime
            ORDER BY total_cases DESC
        ");
    }

    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $row) {
        fputcsv($output, [$row['crime'], $row['total_cases']]);
    }

    fclose($output);
    exit;
}

// Handle report generation
if (isset($_GET['generate'])) {
    $type = $_GET['generate'];
    generateReport($type, $conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Reports</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
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
        .report-container {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <h2>Generate Reports</h2>
        <p>Click the buttons below to download reports.</p>

        <div class="report-container">
            <h4>Crime Reports</h4>
            <p>Download crime statistics for the current month or year.</p>
            <a href="reports.php?generate=monthly" class="btn btn-primary m-2">Generate Monthly Report</a>
            <a href="reports.php?generate=yearly" class="btn btn-success m-2">Generate Yearly Report</a>
        </div>
    </div>

</body>
</html>
