<?php
if (!isset($_SESSION)) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Student';
?>

<!-- Sidebar -->
<div class="col-md-2 sidebar p-3" id="sidebar">
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
        <li class="nav-item"><a class="nav-link <?php if(basename($_SERVER['PHP_SELF']) == 'dashboard.php') echo 'active'; ?>" href="dashboard.php"><i class="fas fa-chart-line me-2"></i>Dashboard</a></li>
        <li class="nav-item"><a class="nav-link <?php if(basename($_SERVER['PHP_SELF']) == 'progress.php') echo 'active'; ?>" href="progress.php"><i class="fas fa-tasks me-2"></i>Progress</a></li>
        <li class="nav-item"><a class="nav-link <?php if(basename($_SERVER['PHP_SELF']) == 'goals.php') echo 'active'; ?>" href="goals.php"><i class="fas fa-bullseye me-2"></i>Goals</a></li>
        <li class="nav-item"><a class="nav-link <?php if(basename($_SERVER['PHP_SELF']) == 'career_guide.php') echo 'active'; ?>" href="career_guide.php"><i class="fas fa-route me-2"></i>Career Guide</a></li>
        <li class="nav-item"><a class="nav-link <?php if(basename($_SERVER['PHP_SELF']) == 'settings.php') echo 'active'; ?>" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
    </ul>
</div>

<!-- Overlay -->
<div id="overlay" class="overlay"></div>

<!-- Topbar -->
<div class="col-md-10 content position-relative">
    <div class="d-flex justify-content-between align-items-center py-3 px-4 px-md-4 ps-2 ps-md-4 border-bottom">
        <div class="d-flex align-items-center">
            <button id="sidebarToggle" class="btn me-2 d-md-none">
                <i class="fas fa-bars fa-lg"></i>
            </button>
            <h4 class="mb-0">Welcome, <?php echo htmlspecialchars($user_name); ?></h4>
        </div>
        <div>
            <i class="fas fa-bell me-3"></i>
            <div class="dropdown d-inline">
                <a href="#" class="text-dark text-decoration-none dropdown-toggle" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                    <li><a class="dropdown-item" href="student_subjects.php">➕ Add Subject</a></li>
                    <li><a class="dropdown-item" href="view_subjects.php">📄 View Subjects</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="logout.php">🚪 Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
