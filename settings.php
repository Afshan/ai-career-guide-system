<?php 
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}
include 'backend/db.php';

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Student';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Settings - AI Student Guide</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        .icon-circle {
            width: 60px;
            height: 60px;
            background-color: rgb(187, 236, 243);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s, box-shadow 0.3s;
        }

        .icon-circle:hover {
            background-color: #b2ebf2;
            box-shadow: 0 0 12px rgba(0, 0, 0, 0.2);
        }

        #overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.3);
            display: none;
            z-index: 998;
        }

        #overlay.active {
            display: block;
        }

        .sidebar.active {
            left: 0;
        }

        .sidebar {
            transition: left 0.3s ease-in-out;
        }

        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: -250px;
                width: 250px;
                height: 100%;
                background-color: white;
                z-index: 999;
                overflow-y: auto;
            }
        }
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
        <li class="nav-item"><a class="nav-link" href="view_subjects.php"><i class="fas fa-book me-2"></i>Subjects</a></li>
         <li class="nav-item"><a class="nav-link active" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
    </ul>
</div>

        <!-- Main Content -->
        <div class="col-md-10 content position-relative">
            <!-- Topbar -->
<div class="d-flex justify-content-between align-items-center py-3 px-4 px-md-4 ps-2 ps-md-4 border-bottom">
    <!-- Left Section: Hamburger + Page Title -->
    <div class="d-flex align-items-center">
        <!-- Sidebar Toggle Button -->
        <button id="sidebarToggle" class="btn me-1 d-md-none">
            <i class="fas fa-bars fa-lg"></i>
        </button>
        <h4 class="mb-0">Settings - <?php echo htmlspecialchars($user_name); ?></h4>
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

            <!-- Circle Background Layer -->
            <div class="circle-background">
                <div class="circle circle-big"></div>
                <div class="circle circle-medium"></div>
                <div class="circle circle-small"></div>
            </div>

            <!-- Settings Content -->
            <div class="row m-4 fade-in">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Profile Information</h5>
                            <form>
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($user_name); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" placeholder="example@gmail.com">
                                </div>
                                <button type="submit" class="btn btn-primary">Update Profile</button>
                            </form>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Change Password</h5>
                            <form>
                                <div class="mb-3">
                                    <label for="currentPassword" class="form-label">Current Password</label>
                                    <input type="password" class="form-control" id="currentPassword">
                                </div>
                                <div class="mb-3">
                                    <label for="newPassword" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="newPassword">
                                </div>
                                <button type="submit" class="btn btn-warning">Change Password</button>
                            </form>
                        </div>
                    </div>

                </div>

                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Notification Preferences</h5>
                            <form>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                                    <label class="form-check-label" for="emailNotifications">Email Notifications</label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="smsNotifications">
                                    <label class="form-check-label" for="smsNotifications">SMS Notifications</label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="aiSuggestions" checked>
                                    <label class="form-check-label" for="aiSuggestions">AI Suggestions Alerts</label>
                                </div>
                                <button type="submit" class="btn btn-success mt-2">Save Preferences</button>
                            </form>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title text-danger">Account</h5>
                            <p class="text-muted">Delete your account permanently.</p>
                            <button class="btn btn-outline-danger">Delete Account</button>
                        </div>
                    </div>

                </div>
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

    // Fade-in Animation
    $(document).ready(function () {
        $('.fade-in').each(function (index) {
            $(this).delay(200 * index).queue(function (next) {
                $(this).addClass('show');
                next();
            });
        });
    });
</script>

</body>

</html>
