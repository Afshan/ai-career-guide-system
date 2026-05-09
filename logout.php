<?php
session_start();

if (isset($_POST['confirm'])) {
    // If user confirmed logout
    session_unset();
    session_destroy();
    header("Location: login.html");
    exit;
}

$user_name = $_SESSION['user_name'] ?? 'Student';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - AI Student Guide</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>

<body>

<div class="container-fluid">
    <div class="row">
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
                <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fas fa-chart-line me-2"></i>Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="progress.php"><i class="fas fa-tasks me-2"></i>Progress</a></li>
                <li class="nav-item"><a class="nav-link" href="goals.php"><i class="fas fa-bullseye me-2"></i>Goals</a></li>
                <li class="nav-item"><a class="nav-link" href="career_guide.php"><i class="fas fa-route me-2"></i>Career Guide</a></li>
                <li class="nav-item"><a class="nav-link" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
                <li class="nav-item"><a class="nav-link active" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="col-md-10 content position-relative">
            <!-- Topbar -->
            <div class="d-flex justify-content-between align-items-center py-3 px-4 px-md-4 ps-2 ps-md-4 border-bottom">
                <div class="d-flex align-items-center">
                    <button id="sidebarToggle" class="btn me-1 d-md-none">
                        <i class="fas fa-bars fa-lg"></i>
                    </button>
                    <h4 class="mb-0">Logout Confirmation</h4>
                </div>

                <div class="flex-grow-1"></div>

                <div class="d-flex align-items-center">
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

            <!-- Content Section -->
            <div class="container my-4">
                <h5 class="text-center">Please confirm your logout action.</h5>
            </div>
        </div>
    </div>
</div>

<!-- Logout Confirmation Modal -->
<div class="modal fade show" id="logoutModal" tabindex="-1" aria-modal="true" style="display: block;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Logout</h5>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to logout, <?php echo htmlspecialchars($user_name); ?>?</p>
            </div>
            <div class="modal-footer">
                <form method="POST">
                    <button type="submit" name="confirm" class="btn btn-danger">Yes, Logout</button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="overlay"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script>
    // Sidebar toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');

    sidebarToggle.addEventListener('click', function () {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    });

    document.addEventListener('click', function (event) {
        if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        }
    });

    overlay.addEventListener('click', function () {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
    });

    // Show Modal Immediately
    var logoutModal = new bootstrap.Modal(document.getElementById('logoutModal'), {
        backdrop: 'static',
        keyboard: false
    });
    logoutModal.show();
</script>

</body>

</html>
