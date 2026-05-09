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

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'] ?? '';
    $category = $_POST['category'] ?? '';
    $priority = $_POST['priority'] ?? '';
    $targetDate = $_POST['target_date'] ?? '';
    $status = $_POST['status'] ?? '';
    $description = $_POST['description'] ?? '';
    $created_at = date('Y-m-d H:i:s');

    $insertGoal = "INSERT INTO goals (user_id, title, category, priority, target_date, status, description, created_at)
                   VALUES ('$user_id', '$title', '$category', '$priority', '$targetDate', '$status', '$description', '$created_at')";

    if (mysqli_query($conn, $insertGoal)) {
        header("Location: goals.php");
        exit();
    } else {
        echo "Error adding goal.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Add Goal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .dashboard-card {
            border-radius: 15px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            background-color: #fff;
            padding: 20px;
            margin-bottom: 30px;
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
                <i class="fas fa-graduation-cap fa-2x"></i>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fas fa-chart-line me-2"></i>Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="progress.php"><i class="fas fa-tasks me-2"></i>Progress</a></li>
                <li class="nav-item"><a class="nav-link" href="goals.php"><i class="fas fa-bullseye me-2"></i>Goals</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-route me-2"></i>Career Guide</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-cog me-2"></i>Settings</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="col-md-10 content position-relative">
            <div class="d-flex justify-content-between align-items-center py-3 px-4 border-bottom">
                <h4>Add New Goal - <?php echo htmlspecialchars($user_name); ?></h4>
                <div>
                    <button id="sidebarToggle" class="btn me-2">
                        <i class="fas fa-bars fa-lg"></i>
                    </button>
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

            <!-- Add Goal Form -->
            <div class="container mt-4">
                <div class="row">
                    <div class="col-md-12">
                        <div class="dashboard-card bg-light">
                            <h5 class="mb-3">🎯 Add New Goal</h5>
                            <form method="POST" action="add_goal.php">
                                <div class="mb-3">
                                    <label class="form-label">Title</label>
                                    <input type="text" class="form-control" name="title" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Category</label>
                                    <select class="form-control" name="category" required>
                                        <option value="" disabled selected>Select Category</option>
                                        <option value="Academic">Academic</option>
                                        <option value="Personal">Personal</option>
                                        <option value="Skill">Skill</option>
                                        <option value="Career">Career</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Priority</label>
                                    <select class="form-select" name="priority" required>
                                        <option value="High">High</option>
                                        <option value="Medium">Medium</option>
                                        <option value="Low">Low</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Target Date</label>
                                    <input type="date" class="form-control" name="target_date" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="status" required>
                                        <option value="Pending">Pending</option>
                                        <option value="In Progress">In Progress</option>
                                        <option value="Completed">Completed</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="description" rows="3" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Add Goal</button>
                                <a href="goals.php" class="btn btn-secondary ms-2">Back to Goals</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
