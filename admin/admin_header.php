<?php
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel - Student Career System</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* Light Mode Base */
        body {
            background-color: #e7edf7;
            color: #000;
        }

        .navbar-brand {
            font-weight: bold;
            font-size: 1.3rem;
        }

        .btn-outline-light:hover {
            color: #fff;
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .dashboard-card, .card {
            background-color: #ffffff;
            border: none;
            border-radius: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: all 0.2s ease-in-out;
            color: #000;
        }

        .dashboard-card:hover, .card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .table th, .table td {
            vertical-align: middle;
            color: #000;
        }

        .chart-container {
            min-height: 240px;
        }

        /* Dark Mode */
        body.dark-mode {
            background-color: #121212;
            color: #e0e0e0;
        }

        .dark-mode .navbar {
            background-color: #1e1e1e;
        }

        .dark-mode .navbar .navbar-brand,
        .dark-mode .navbar .btn-outline-light,
        .dark-mode .form-check-label {
            color: #fff;
        }

        .dark-mode .card,
        .dark-mode .dashboard-card {
            background-color: #1e1e1e;
            color: #e0e0e0;
            border: 1px solid #333;
        }

        .dark-mode .table th,
        .dark-mode .table td {
            color: #e0e0e0;
        }

        .dark-mode .btn-outline-dark {
            border-color: #fff;
            color: #fff;
        }

        .dark-mode .btn-outline-dark:hover {
            background-color: #fff;
            color: #000;
        }

        .dark-mode canvas {
            background-color: #1e1e1e;
        }

        .dark-mode .form-check-input:checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        
    </style>
</head>
<body>
<nav class="navbar navbar-dark bg-dark px-4 py-2 mb-4">
    <span class="navbar-brand">Admin Panel</span>
    <div class="d-flex align-items-center">

        <!-- Dark Mode Toggle -->
        <div class="form-check form-switch me-3">
            <input class="form-check-input" type="checkbox" id="darkModeToggle">
            <label class="form-check-label" for="darkModeToggle">Dark Mode</label>
        </div>
        <a href="weekly_report.php" class="btn btn-outline-light me-2"> Weekly Report</a>
        <a href="admin_dashboard.php" class="btn btn-outline-light me-2">Dashboard</a>
        <a href="logout.php" class="btn btn-outline-light">Logout</a>
    </div>
</nav>

<div class="container">
    <!-- Your admin content goes here -->
</div>

<script>
    const toggle = document.getElementById('darkModeToggle');
    const body = document.body;

    // Load saved theme on page load
    if (localStorage.getItem('darkMode') === 'true') {
        body.classList.add('dark-mode');
        toggle.checked = true;
    }

    toggle.addEventListener('change', function () {
        if (this.checked) {
            body.classList.add('dark-mode');
            localStorage.setItem('darkMode', 'true');
        } else {
            body.classList.remove('dark-mode');
            localStorage.setItem('darkMode', 'false');
        }
    });
</script>

</body>
</html>
