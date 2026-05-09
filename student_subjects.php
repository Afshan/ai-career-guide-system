<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

$user_name = $_SESSION['user_name'] ?? 'Student';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Add Subjects - AI Student Guide</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #e79ad7ff;
        }
        .sidebar{
                background-color: #e2c3daff;
            }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <!-- ... Your sidebar remains the same ... -->

        <!-- Main Content -->
        <div class="col-md-10 content position-relative">
            <div class="d-flex justify-content-between align-items-center py-3 px-4 px-md-4 ps-2 ps-md-4 border-bottom">
                <div class="d-flex align-items-center">
                    <button id="sidebarToggle" class="btn me-1 d-md-none">
                        <i class="fas fa-bars fa-lg"></i>
                    </button>
                    <h4 class="mb-0">Add Subjects</h4>
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

            <!-- Form Section -->
            <div class="container my-4 fade-in">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Enter Your Subjects and Marks</h5>
                    <a href="view_subjects.php" class="btn btn-secondary">Back to Subjects</a>
                </div>

                <!-- 🔄 FORM NOW POSTS TO save_subjects.php -->
                <form method="POST" action="save_subjects.php">
                    <div id="subject-fields">
                        <div class="row mb-2">
                            <div class="col">
                                <input type="text" name="subject_name[]" class="form-control" placeholder="Subject Name" required>
                            </div>
                            <div class="col">
                                <input type="number" name="marks[]" class="form-control" placeholder="Marks" required>
                            </div>
                        </div>
                    </div>
                    <button type="button" onclick="addSubjectField()" class="btn btn-secondary mb-3">+ Add Another Subject</button><br>
                    <button type="submit" class="btn btn-primary">Save All Subjects</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="overlay"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script>
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

    function addSubjectField() {
        const container = document.getElementById("subject-fields");
        const row = `
            <div class="row mb-2">
                <div class="col">
                    <input type="text" name="subject_name[]" class="form-control" placeholder="Subject Name" required>
                </div>
                <div class="col">
                    <input type="number" name="marks[]" class="form-control" placeholder="Marks" required>
                </div>
            </div>`;
        container.insertAdjacentHTML('beforeend', row);
    }

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
