<style>
        body {
            display: flex;
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
    </style>
        <div class="sidebar">
        <h4 class="text-center">Admin Panel</h4>
        <hr>
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_inmates.php">Manage Inmates</a>
        <!-- <a href="manage_users.php">Manage Users</a> -->
        <a href="reports.php">Reports</a>
        <a href="logout.php" class="text-danger">Logout</a>
    </div>