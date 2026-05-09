<?php
session_start();
include '../backend/db.php';
include 'admin_header.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit;
}

// Totals
$user_count = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
$career_count = $conn->query("SELECT COUNT(*) as total FROM selected_career")->fetch_assoc()['total'];
$goal_count = $conn->query("SELECT COUNT(*) as total FROM goals")->fetch_assoc()['total'];

// Bar Chart – Students per Degree
$degree_data = [];
$degree_query = $conn->query("SELECT degree_program, COUNT(*) as total FROM academic_info GROUP BY degree_program");
while ($row = $degree_query->fetch_assoc()) {
    $degree_data[] = $row;
}

// Pie Chart – Most Selected Careers
$career_data = [];
$career_query = $conn->query("SELECT career_title, COUNT(*) as total FROM selected_career GROUP BY career_title LIMIT 5");
while ($row = $career_query->fetch_assoc()) {
    $career_data[] = $row;
}

// Line Chart – Monthly Student Registrations
$month_data = [];
$month_query = $conn->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total 
    FROM users 
    GROUP BY month 
    ORDER BY month ASC
");
while ($row = $month_query->fetch_assoc()) {
    $month_data[] = $row;
}

// Doughnut Chart – Goal Status Distribution
$status_data = ['completed' => 0, 'in progress' => 0, 'pending' => 0];
$status_query = $conn->query("SELECT status, COUNT(*) as total FROM goals GROUP BY status");
while ($row = $status_query->fetch_assoc()) {
    $status_data[strtolower($row['status'])] = $row['total'];
}
?>

<!-- Dark Mode Styling -->
<style>
    body.dark-mode {
        background-color: #121212;
        color: #e0e0e0;
    }

    .dark-mode .dashboard-card {
        background-color: #1e1e1e;
        color: #fff;
        border: 1px solid #333;
    }

    .dark-mode .text-muted {
        color: #bbb !important;
    }

    .dark-mode .btn-outline-secondary,
    .dark-mode .btn-outline-primary {
        color: #fff;
        border-color: #ccc;
    }

    .dark-mode .btn-outline-secondary:hover,
    .dark-mode .btn-outline-primary:hover {
        background-color: #0d6efd;
        color: #fff;
    }

    .dark-mode h3,
    .dark-mode h6 {
        color: #fff !important;
    }
</style>


<div class="container mt-5">
    <h3 class="mb-4 fw-bold">📊 Admin Dashboard</h3>
    

    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h6 class="text-muted">Total Students</h6>
                    <h3 class="fw-bold"><?= $user_count ?></h3>
                </div>
            </div>
            <a href="manage_users.php" class="btn btn-outline-secondary mt-2 w-100 rounded-pill">Manage Students</a>
            
        </div>
        <div class="col-md-4">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h6 class="text-muted">Career Suggestions</h6>
                    <h3 class="fw-bold"><?= $career_count ?></h3>
                </div>
            </div>
            <div class="d-flex flex-column gap-2 mt-2">
                <a href="manage_careers.php" class="btn btn-outline-primary rounded-pill">Manage Careers</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h6 class="text-muted">Total Goals</h6>
                    <h3 class="fw-bold"><?= $goal_count ?></h3>
                    
                </div>
                
            </div>

        </div>
    </div>

    <!-- Charts -->
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h6 class="text-muted">Students per Degree</h6>
                    <div class="chart-container" style="min-height: 300px;">
                        <canvas id="barChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h6 class="text-muted">Top Selected Careers</h6>
                    <div class="chart-container" style="min-height: 300px;">
                        <canvas id="pieChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h6 class="text-muted">Monthly Registrations</h6>
                    <div class="chart-container" style="min-height: 300px;">
                        <canvas id="lineChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h6 class="text-muted">Goal Status Distribution</h6>
                    <div class="chart-container" style="min-height: 300px;">
                        <canvas id="doughnutChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Detect dark mode
const isDarkMode = localStorage.getItem('darkMode') === 'true';
if (isDarkMode) document.body.classList.add('dark-mode');

// Common options for dark mode charts
const darkChartOptions = {
    plugins: {
        legend: {
            labels: {
                color: isDarkMode ? '#fff' : '#000'
            }
        }
    },
    scales: {
        x: {
            ticks: { color: isDarkMode ? '#fff' : '#000' },
            grid: { color: isDarkMode ? '#444' : '#ccc' }
        },
        y: {
            ticks: { color: isDarkMode ? '#fff' : '#000' },
            grid: { color: isDarkMode ? '#444' : '#ccc' },
            beginAtZero: true
        }
    }
};

// Bar Chart
new Chart(document.getElementById('barChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($degree_data, 'degree_program')) ?>,
        datasets: [{
            label: 'Students',
            data: <?= json_encode(array_column($degree_data, 'total')) ?>,
            backgroundColor: '#4e73df',
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        ...darkChartOptions,
        plugins: { ...darkChartOptions.plugins, legend: { display: false } }
    }
});

// Pie Chart
new Chart(document.getElementById('pieChart'), {
    type: 'pie',
    data: {
        labels: <?= json_encode(array_column($career_data, 'career_title')) ?>,
        datasets: [{
            data: <?= json_encode(array_column($career_data, 'total')) ?>,
            backgroundColor: ['#6c757d', '#adb5bd', '#ced4da', '#dee2e6', '#e9ecef']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: { color: isDarkMode ? '#fff' : '#000' }
            }
        }
    }
});

// Line Chart
new Chart(document.getElementById('lineChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($month_data, 'month')) ?>,
        datasets: [{
            label: 'New Students',
            data: <?= json_encode(array_column($month_data, 'total')) ?>,
            borderColor: '#36b9cc',
            backgroundColor: 'rgba(54, 185, 204, 0.15)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        ...darkChartOptions
    }
});

// Doughnut Chart
new Chart(document.getElementById('doughnutChart'), {
    type: 'doughnut',
    data: {
        labels: ['Completed', 'In Progress', 'Pending'],
        datasets: [{
            data: [
                <?= $status_data['completed'] ?>,
                <?= $status_data['in progress'] ?>,
                <?= $status_data['pending'] ?>
            ],
            backgroundColor: ['#28a745', '#ffc107', '#dc3545']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: { color: isDarkMode ? '#fff' : '#000' }
            }
        }
    }
});
</script>

<?php include 'admin_footer.php'; ?>
