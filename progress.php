<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

include 'backend/db.php';
include 'backend/log_activity.php';

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Student';

$subjectNames = [];
$subjectMarks = [];

$insight = '';
$tip = '';
$average = 0;
$performanceMsg = '';

$stmt = $conn->prepare("SELECT subject_name, marks FROM subjects WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$totalMarks = 0;
$count = 0;

while ($row = $result->fetch_assoc()) {
    $subject = $row['subject_name'];
    $marks = $row['marks'];

    $subjectNames[] = $subject;
    $subjectMarks[] = $marks;

    $totalMarks += $marks;
    $count++;

    // Sample insight and tip logic
    if (strtolower($subject) === 'arabic' && $marks >= 80) {
        $insight = "You're improving steadily in Arabic. Keep it up!";
    }

    if (strtolower($subject) === 'info security' && $marks < 80) {
        $tip = "Focus more on Info Security for next test – $marks% is good but can go higher!";
    }
}

if ($count > 0) {
    $average = round($totalMarks / $count, 2);

    if ($average >= 85) {
        $performanceMsg = "🌟 Excellent performance! Keep aiming high.";
    } elseif ($average >= 70) {
        $performanceMsg = "👍 Good job! A little push and you'll hit excellence.";
    } elseif ($average >= 50) {
        $performanceMsg = "🙂 Fair effort. Let's level it up!";
    } else {
        $performanceMsg = "🚀 You have potential! Start working consistently.";
    }
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Student Progress</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .fade-in { opacity: 0; transform: translateY(20px); transition: opacity 0.5s ease, transform 0.5s ease; }
        .fade-in.show { opacity: 1; transform: translateY(0); }
        .dashboard-card { border-radius: 15px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); background-color: #fff; padding: 20px; transition: all 0.3s ease; margin-bottom: 30px; }
        canvas { max-height: 400px; }
        .icon-circle { width: 60px; height: 60px; background-color: rgb(187, 236, 243); border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: background-color 0.3s, box-shadow 0.3s; }
        .icon-circle:hover { background-color: #b2ebf2; box-shadow: 0 0 12px rgba(0, 0, 0, 0.2); }
        .sidebar.active { transform: translateX(0); }
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); transition: transform 0.3s ease; position: fixed; top: 0; left: 0; height: 100%; width: 250px; background-color: #f8f9fa; z-index: 1030; overflow-y: auto; } }
        body {
            background-color: #fae2ebff;
        }
        .sidebar{
            background-color: #FFC8DD;
        }
    </style>
</head>

<body>

<div class="container-fluid">
    <div class="row">

        <!-- Sidebar -->
<div class="col-md-2 sidebar p-3 position-fixed h-100" id="sidebar">
    <div class="text-center mb-4">
        <a href="dashboard.php" class="text-decoration-none text-dark d-flex align-items-center justify-content-center">
            <div class="icon-circle me-2">
                <i class="fas fa-graduation-cap fa-2x"></i>
            </div>
            <div class="d-flex flex-column text-start">
                <span class="fw-bold">AI</span>
                <span class="fw-bold">Student</span>
                <span class="fw-bold">Guide</span>
            </div>
        </a>
    </div>

    <ul class="nav flex-column">
        <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fas fa-chart-line me-2"></i>Dashboard</a></li>
        <li class="nav-item"><a class="nav-link active" href="progress.php"><i class="fas fa-tasks me-2"></i>Progress</a></li>
        <li class="nav-item"><a class="nav-link" href="goals.php"><i class="fas fa-bullseye me-2"></i>Goals</a></li>
        <li class="nav-item"><a class="nav-link" href="career_guide.php"><i class="fas fa-route me-2"></i>Career Guide</a></li>
        <li class="nav-item"><a class="nav-link" href="view_subjects.php"><i class="fas fa-book me-2"></i>Subjects</a></li>
        <li class="nav-item"><a class="nav-link" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
    </ul>
</div>


        <!-- Overlay -->
        <div id="overlay" style="position: fixed; top: 0; left: 0; height: 100%; width: 100%; background-color: rgba(0,0,0,0.5); z-index: 1029; display: none;"></div>

        <!-- Main Content -->
        <div class="col-md-10 offset-md-2 content position-relative" id="mainContent">
            <!-- Topbar -->
<div class="d-flex justify-content-between align-items-center py-3 px-4 px-md-4 ps-2 ps-md-4 border-bottom">
    <!-- Left Section: Hamburger + Page Title -->
    <div class="d-flex align-items-center">
        <!-- Sidebar Toggle Button -->
        <button id="sidebarToggle" class="btn me-1 d-md-none">
            <i class="fas fa-bars fa-lg"></i>
        </button>
        <h4 class="mb-0">Progress Report</h4>
    </div>

    <!-- Spacer -->
    <div class="flex-grow-1"></div>

    <!-- Right Section: Bell + Profile -->
    <div class="d-flex align-items-center">
        <i class="fas fa-bell me-3"></i>
        <a href="profile.php" class="text-dark text-decoration-none">
            <i class="fas fa-user fa-lg"></i>
        </a>
    </div>
</div>


            <!-- Charts Section -->
            <div class="container mt-4 fade-in show">
                <div class="row">

                    <!-- Overall Performance Summary -->
                    <?php if ($count > 0): ?>
                    <div class="col-md-12">
                        <div class="dashboard-card bg-light">
                            <h5 class="mb-3">📚 Overall Performance</h5>
                            <p><strong>Average Score:</strong> <?php echo $average; ?>%</p>
                            <p><strong>Note:</strong> <?php echo $performanceMsg; ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Bar Chart -->
                    <div class="col-md-12">
                        <div class="dashboard-card">
                            <h5 class="mb-3">📊 Subject-wise Marks</h5>
                            <canvas id="progressChart"></canvas>
                        </div>
                    </div>

                    <!-- Line Chart -->
                    <div class="col-md-12">
                        <div class="dashboard-card">
                            <h5 class="mb-3">📈 Progress Over Time</h5>
                            <canvas id="lineChart"></canvas>
                        </div>
                    </div>

                    <!-- Radar Chart -->
                    <div class="col-md-12">
                        <div class="dashboard-card">
                            <h5 class="mb-3">🎯 Subject Strength Comparison</h5>
                            <canvas id="radarChart"></canvas>
                        </div>
                    </div>

                    <!-- Insights Section -->
                    <?php if ($insight || $tip): ?>
                    <div class="col-md-12">
                        <div class="dashboard-card bg-light">
                            <h5 class="mb-3">💡 Insights & Tips</h5>
                            <?php if ($insight): ?>
                            <p><strong>Performance Insight:</strong> <?php echo $insight; ?></p>
                            <?php endif; ?>
                            <?php if ($tip): ?>
                            <p><strong>Tip:</strong> <?php echo $tip; ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Sidebar Toggle
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    const sidebarToggle = document.getElementById('sidebarToggle');

    sidebarToggle.addEventListener('click', () => {
        sidebar.classList.toggle('active');
        overlay.style.display = sidebar.classList.contains('active') ? 'block' : 'none';
    });

    overlay.addEventListener('click', () => {
        sidebar.classList.remove('active');
        overlay.style.display = 'none';
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 768) {
            sidebar.classList.add('active');
            overlay.style.display = 'none';
        }
    });

    // Charts
    const subjectNames = <?php echo json_encode($subjectNames); ?>;
    const subjectMarks = <?php echo json_encode($subjectMarks); ?>;

    new Chart(document.getElementById('progressChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: subjectNames,
            datasets: [{
                label: 'Marks',
                data: subjectMarks,
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true, max: 100 } } }
    });

    new Chart(document.getElementById('lineChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: ['Test 1', 'Test 2', 'Test 3', 'Test 4'],
            datasets: [{
                label: 'Subject A',
                data: [65, 70, 75, 80],
                borderColor: 'rgba(153, 102, 255, 1)',
                backgroundColor: 'rgba(153, 102, 255, 0.2)',
                tension: 0.3
            }, {
                label: 'Subject B',
                data: [60, 68, 73, 78],
                borderColor: 'rgba(255, 159, 64, 1)',
                backgroundColor: 'rgba(255, 159, 64, 0.2)',
                tension: 0.3
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true, max: 100 } } }
    });

    new Chart(document.getElementById('radarChart').getContext('2d'), {
        type: 'radar',
        data: {
            labels: subjectNames,
            datasets: [{
                label: 'Your Scores',
                data: subjectMarks,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2
            }]
        },
        options: { responsive: true, scales: { r: { min: 0, max: 100, ticks: { stepSize: 20 } } } }
    });
</script>

</body>
</html>
