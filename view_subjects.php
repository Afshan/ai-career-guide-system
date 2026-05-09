<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

include 'backend/db.php';

$showSuccessToast = false;
if (isset($_SESSION['subjects_saved_success'])) {
    $showSuccessToast = true;
    unset($_SESSION['subjects_saved_success']);
}


$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT id, subject_name, marks FROM subjects WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$user_name = $_SESSION['user_name'] ?? 'Student';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>View Subjects - AI Student Guide</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
                <li class="nav-item"><a class="nav-link active" href="view_subjects.php"><i class="fas fa-book me-2"></i>Subjects</a></li>
                <li class="nav-item"><a class="nav-link" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="col-md-10 content position-relative">
            <div class="d-flex justify-content-between align-items-center py-3 px-4 px-md-4 ps-2 ps-md-4 border-bottom">
                <div class="d-flex align-items-center">
                    <button id="sidebarToggle" class="btn me-1 d-md-none">
                        <i class="fas fa-bars fa-lg"></i>
                    </button>
                    <h4 class="mb-0">Your Entered Subjects</h4>
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

            <!-- Table Section -->
            <div class="container my-4 fade-in">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Subjects List</h5>
                    <a href="student_subjects.php" class="btn btn-primary">Add New Subject</a>
                </div>

                <table class="table table-bordered table-hover ">
                    <thead class="table-light">
                        <tr>
                            <th>Subject</th>
                            <th>Marks</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['marks']); ?></td>
                                <td>
                                    <a href="edit_subject.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="delete_subject.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
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

<?php if ($showSuccessToast): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Subjects saved!',
        text: 'Your subjects were added successfully.',
        timer: 2000,
        showConfirmButton: false
    });
</script>
<?php endif; ?>

</body>

</html>
