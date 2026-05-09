<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}
include 'backend/db.php';

$goal_id = $_GET['id'] ?? null;
if (!$goal_id) die("Invalid goal.");

$stmt = $conn->prepare("SELECT * FROM goals WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $goal_id, $_SESSION['user_id']);
$stmt->execute();
$goal = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $goal['title']; ?> - Checklist</title>
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
                    <li class="nav-item"><a class="nav-link" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
                    <li class="nav-item"><a class="nav-link" href="view_subjects.php"><i class="fas fa-book me-2"></i>Subjects</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 content position-relative" id="mainContent">
                <!-- Topbar -->
                <div class="d-flex justify-content-between align-items-center py-3 px-4 px-md-4 ps-2 ps-md-4 border-bottom">
                    <div class="d-flex align-items-center">
                        <button id="sidebarToggle" class="btn me-1 d-md-none">
                            <i class="fas fa-bars fa-lg"></i>
                        </button>
                        <h4 class="mb-0"><?php echo htmlspecialchars($goal['title']); ?> - Checklist</h4>
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

                <!-- Subtasks Section -->
                <div class="container mt-4">
                    <div class="mb-4">
                        <label><strong>Progress:</strong></label>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width: 0%;" id="progressBar">0%</div>
                        </div>
                    </div>

                    <form id="addForm" class="mb-4 d-flex">
                        <input type="text" class="form-control me-2" id="newSubtask" placeholder="Add new subtask..." required>
                        <button class="btn btn-primary" type="submit">Add</button>
                    </form>

                    <div id="subtasks" class="subtasks-list">
                        <!-- Subtasks will be loaded here -->
                    </div>

                    <p id="emptyState" class="text-muted mt-3" style="display: none;">No subtasks yet. Start by adding one!</p>

                    <a href="goals.php" class="btn btn-secondary mt-4">← Back to Goals</a>
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

        // Subtasks Functionality
        const goalId = <?= json_encode($goal_id); ?>;

        function loadSubtasks() {
            $.ajax({
                url: 'ajax/get_subtasks.php',
                type: 'GET',
                data: { goal_id: goalId },
                success: function (data) {
                    $('#subtasks').html(data);
                    bindSubtaskEvents();
                    updateProgress();
                    toggleEmptyState();
                },
                error: function () {
                    alert('Failed to load subtasks.');
                }
            });
        }

        function toggleEmptyState() {
            if ($('.subtask-item').length === 0) {
                $('#emptyState').show();
            } else {
                $('#emptyState').hide();
            }
        }

        function updateProgress() {
            const total = $('.subtask-item').length;
            const completed = $('.toggle-complete:checked').length;
            const percent = total > 0 ? (completed / total) * 100 : 0;
            $('#progressBar').css('width', percent + '%').text(Math.round(percent) + '%');
        }

        function bindSubtaskEvents() {
            // Toggle Complete
            $('.toggle-complete').off('change').on('change', function () {
                const parent = $(this).closest('.subtask-item');
                const id = parent.data('id');
                $.post('ajax/toggle_subtask.php', { id }, function (progress) {
                    parent.find('.subtask-text').toggleClass('completed');
                    $('#progressBar').css('width', progress + '%').text(progress + '%');
                });
            });

            // Delete Subtask
            $('.delete-btn').off('click').on('click', function () {
                const parent = $(this).closest('.subtask-item');
                const id = parent.data('id');
                $.post('ajax/delete_subtask.php', { id }, function () {
                    parent.fadeOut(300, function () {
                        parent.remove();
                        updateProgress();
                        toggleEmptyState();
                    });
                });
            });

            // Edit Subtask
            $('.subtask-text').off('dblclick').on('dblclick', function () {
                const current = $(this);
                const id = current.closest('.subtask-item').data('id');
                const input = $('<input class="form-control subtask-edit" />').val(current.text());
                current.replaceWith(input);
                input.focus();

                input.blur(function () {
                    loadSubtasks();
                });

                input.keypress(function (e) {
                    if (e.which == 13 && input.val().trim() !== '') {
                        const newText = input.val().trim();
                        $.post('ajax/update_subtask.php', { id, subtask: newText }, function (response) {
                            if (response.trim() === 'success') {
                                loadSubtasks();
                            } else {
                                alert('Failed to update subtask. Please try again.');
                                loadSubtasks(); // fallback to revert
                            }
                        });

                    }
                });
            });
        }

        // Add Subtask
        $('#addForm').submit(function (e) {
            e.preventDefault();
            const subtask = $('#newSubtask').val().trim();
            if (subtask === '') return;

            $.post('ajax/add_subtask.php', { subtask, goal_id: goalId }, function () {
                $('#newSubtask').val('');
                loadSubtasks();
            });
        });

        // Initial Load
        $(document).ready(function () {
            loadSubtasks();
        });
    </script>

</body>

</html>
