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

// Get status filter from URL
$status_filter = $_GET['status'] ?? 'All';

// Fetch Goals based on status filter
$goals = [];
if ($status_filter === 'All') {
    $stmt = $conn->prepare("SELECT * FROM goals WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
} else {
    $stmt = $conn->prepare("SELECT * FROM goals WHERE user_id = ? AND status = ?");
    $stmt->bind_param("is", $user_id, $status_filter);
}

$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $goals[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Goals</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .dashboard-card { border-radius: 15px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); background-color: #fff; padding: 20px; margin-bottom: 30px; }
        .goal-card { border-radius: 15px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); background-color: #fff; padding: 20px; margin-bottom: 20px; transition: transform 0.3s, box-shadow 0.3s, border 0.3s; position: relative; }
        .goal-card:hover { transform: translateY(-4px); box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1); border: 2px solid #007bff; }
        .badge-status { font-size: 0.9rem; padding: 5px 10px; border-radius: 20px; }
        .badge-pending { background-color: #ffc107; color: #000; }
        .badge-inprogress { background-color: #0dcaf0; color: #000; }
        .badge-completed { background-color: #198754; color: #fff; }
        .filter-btn { margin-right: 10px; }
        .filter-btn.active { background-color: #007bff; color: white; }
        .action-btn { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; border: none; background-color: #f0f0f0; color: #333; transition: background-color 0.3s, transform 0.2s; margin-left: 5px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1); }
        .action-btn:hover { background-color: #007bff; color: white; transform: scale(1.1); }
        .action-btn i { font-size: 14px; }
        .icon-circle { width: 60px; height: 60px; background-color: rgb(187, 236, 243); border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: background-color 0.3s, box-shadow 0.3s; }
        .icon-circle:hover { background-color: #b2ebf2; box-shadow: 0 0 12px rgba(0, 0, 0, 0.2); }
        /* Overlay for mobile sidebar */
        #overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.3); display: none; z-index: 998; }
        #overlay.active { display: block; }
        .sidebar.active { left: 0; }
        .sidebar { transition: left 0.3s ease-in-out; }
        @media (max-width: 768px) {
            .sidebar { position: fixed; top: 0; left: -250px; width: 250px; height: 100%; background-color: white; z-index: 999; overflow-y: auto; }
        }
        body {
            background-color: #fae2ebff;
        }
        .sidebar{
            background-color: #FFC8DD;
        }
        .container-goals{
                background-color: #fcdcf3ff;
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
        <li class="nav-item"><a class="nav-link active" href="goals.php"><i class="fas fa-bullseye me-2"></i>Goals</a></li>
        <li class="nav-item"><a class="nav-link" href="career_guide.php"><i class="fas fa-route me-2"></i>Career Guide</a></li>
        <li class="nav-item"><a class="nav-link" href="view_subjects.php"><i class="fas fa-book me-2"></i>Subjects</a></li>
        <li class="nav-item"><a class="nav-link" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
    </ul>
</div>
        <!-- Main Content -->
        <div class="col-md-10 content position-relative">
            <!-- Topbar -->
<div class="d-flex justify-content-between align-items-center py-3 px-4 px-md-4 ps-2 ps-md-4 border-bottom">
    <!-- Left Section: Hamburger + Welcome -->
    <div class="d-flex align-items-center">
        <!-- Sidebar Toggle Button -->
        <button id="sidebarToggle" class="btn me-1 d-md-none">
            <i class="fas fa-bars fa-lg"></i>
        </button>
        <h4 class="mb-0">Goals - <?php echo htmlspecialchars($user_name); ?></h4>
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

            <!-- Goals Section -->
            <div class="container mt-4">
                <div class="row">

                    <!-- Add New Goal Button -->
                    <div class="col-md-12 mb-4 d-flex justify-content-between">
                        <a href="add_goal.php" class="btn btn-primary">➕ Add New Goal</a>

                        <!-- Filter Buttons -->
                        <div>
                            <?php
                            $filters = ['All', 'Pending', 'In Progress', 'Completed'];
                            foreach ($filters as $filter) {
                                $activeClass = ($status_filter == $filter) ? 'active' : '';
                                echo '<a href="goals.php?status=' . urlencode($filter) . '" class="btn filter-btn ' . $activeClass . '">' . $filter . '</a>';
                            }
                            ?>
                        </div>
                    </div>

                    <!-- Display Goals -->
                    <div class="col-md-12">
                        <div class="dashboard-card container-goals">
                            <h5 class="mb-4 ">📋 Your Goals - <?php echo htmlspecialchars($status_filter); ?></h5>
                            <div class="row container-goals">
                                <?php if (count($goals) > 0): ?>
                                    <?php foreach ($goals as $goal): ?>
                                        <div class="col-md-4">
                                            <div class="goal-card">
                                                <!-- Edit and Delete Buttons -->
                                                <div class="position-absolute top-0 end-0 m-2">
                                                    <a href="edit_goal.php?id=<?php echo $goal['id']; ?>" class="action-btn" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button class="action-btn delete-btn" data-id="<?php echo $goal['id']; ?>" title="Delete">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </div>

                                                <h6 class="mb-2"><?php echo htmlspecialchars($goal['title']); ?></h6>
                                                <p><strong>Category:</strong> <?php echo htmlspecialchars($goal['category']); ?></p>
                                                <p><strong>Priority:</strong> <?php echo htmlspecialchars($goal['priority']); ?></p>
                                                <p><strong>Target Date:</strong> <?php echo htmlspecialchars($goal['target_date']); ?></p>
                                                <p><strong>Status:</strong>
                                                    <?php
                                                    $statusClass = '';
                                                    if ($goal['status'] == 'Pending') {
                                                        $statusClass = 'badge-pending';
                                                    } elseif ($goal['status'] == 'In Progress') {
                                                        $statusClass = 'badge-inprogress';
                                                    } elseif ($goal['status'] == 'Completed') {
                                                        $statusClass = 'badge-completed';
                                                    }
                                                    ?>
                                                    <span class="badge-status <?php echo $statusClass; ?>"><?php echo htmlspecialchars($goal['status']); ?></span>
                                                </p>
                                                <p><strong>Description:</strong><br><?php echo nl2br(htmlspecialchars($goal['description'])); ?></p>

                                                <!-- Progress Bar -->
                                                <div class="mt-3">
                                                    <p class="mb-1"><strong>Progress:</strong> <?php echo $goal['progress']; ?>%</p>
                                                    <div class="progress">
                                                        <div class="progress-bar <?php echo ($goal['progress'] == 100) ? 'bg-success' : 'bg-info'; ?>" role="progressbar" style="width: <?php echo $goal['progress']; ?>%;" aria-valuenow="<?php echo $goal['progress']; ?>" aria-valuemin="0" aria-valuemax="100">
                                                            <?php echo $goal['progress']; ?>%
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- View Checklist Button -->
                                                <a href="goal_details.php?id=<?php echo $goal['id']; ?>" class="btn btn-sm btn-outline-primary mt-3">View Checklist</a>

                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p>No goals found for this status.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<!-- Mobile Sidebar Overlay -->
<div id="overlay"></div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this goal?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function () {
            const goalId = this.getAttribute('data-id');
            const deleteLink = `delete_goal.php?id=${goalId}`;
            document.getElementById('confirmDeleteBtn').setAttribute('href', deleteLink);
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        });
    });

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
</script>

</body>
</html>
