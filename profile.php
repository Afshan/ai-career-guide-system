<?php 
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

include 'backend/db.php';

$user_id = $_SESSION['user_id'];
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Student';

// Fetch user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Upload profile picture
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_picture'])) {
    $targetDir = "images/";
    $fileName = basename($_FILES['profile_picture']['name']);
    $targetFilePath = $targetDir . time() . '_' . $fileName;

    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFilePath)) {
        $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
        $stmt->bind_param("si", $targetFilePath, $user_id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['success'] = "Profile picture updated successfully!";
        header("Location: profile.php");
        exit;
    } else {
        $_SESSION['error'] = "Failed to upload image.";
    }
}

// Update name and email
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("UPDATE users SET user_name = ?, email = ? WHERE id = ?");
    $stmt->bind_param("ssi", $name, $email, $user_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['user_name'] = $name;
    $_SESSION['success'] = "Profile updated successfully!";
    header("Location: profile.php");
    exit;
}

// Update password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_password'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];

    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($db_password);
    $stmt->fetch();
    $stmt->close();

    if (password_verify($old_password, $db_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $user_id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['success'] = "Password updated successfully!";
        header("Location: profile.php");
        exit;
    } else {
        $_SESSION['error'] = "Old password is incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Your Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .is-valid { border-color: #198754 !important; }
        .is-invalid { border-color: #dc3545 !important; }
        .validation-message { font-size: 0.9rem; }
        .success-animation {
            animation: fadeOut 2s ease forwards;
        }
        @keyframes fadeOut {
            0% { opacity: 1; }
            100% { opacity: 0; display: none; }
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
                <li class="nav-item"><a class="nav-link" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
            </ul>
        </div>

        <!-- Overlay -->
        <div id="overlay" class="overlay"></div>

        <!-- Main Content -->
        <div class="col-md-10 content position-relative">
            <div class="d-flex justify-content-between align-items-center py-3 px-4 px-md-4 ps-2 ps-md-4 border-bottom">
                <div class="d-flex align-items-center">
                    <button id="sidebarToggle" class="btn me-1 d-md-none">
                        <i class="fas fa-bars fa-lg"></i>
                    </button>
                    <h4 class="mb-0">Your Profile</h4>
                </div>

                <div class="flex-grow-1"></div>

                <div class="d-flex align-items-center">
                    <i class="fas fa-bell me-3"></i>
                    <a href="profile.php" class="text-dark text-decoration-none">
                        <i class="fas fa-user fa-lg"></i>
                    </a>
                </div>
            </div>

            <!-- Profile Content -->
            <div class="container my-4">
                <?php if (isset($_SESSION['success'])) { ?>
                    <div class="alert alert-success success-animation" id="successAlert"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php } ?>
                <?php if (isset($_SESSION['error'])) { ?>
                    <div class="alert alert-danger" id="errorAlert"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php } ?>

                <div class="text-center mb-4">
                    <?php
                    $profileImage = (!empty($user['profile_picture']) && file_exists($user['profile_picture'])) ? $user['profile_picture'] : 'images/default_avatar.png';
                    ?>
                    <img src="<?php echo $profileImage; ?>" alt="Profile Picture" class="rounded-circle" width="150" height="150" style="object-fit: cover;">
                </div>

                <h4 class="text-center mb-3">Hello, <?php echo htmlspecialchars($user_name); ?> 👋</h4>

                <!-- Profile Picture Upload -->
                <form method="POST" enctype="multipart/form-data" class="text-center mb-4">
                    <div class="mb-3">
                        <input type="file" name="profile_picture" accept="image/*" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Upload New Profile Picture</button>
                </form>

                <!-- Editable Details -->
                <h5>Edit Profile Details</h5>
                <form method="POST" class="mb-4" id="profileForm">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" id="nameInput" class="form-control" value="<?php echo htmlspecialchars($user_name); ?>" required>
                        <div id="nameFeedback" class="validation-message text-danger mt-1"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="emailInput" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        <div id="emailFeedback" class="validation-message text-danger mt-1"></div>
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-success" id="profileSubmit" disabled>Update Profile</button>
                </form>

                <!-- Password Update -->
                <h5>Change Password</h5>
                <form method="POST" id="passwordForm">
                    <div class="mb-3">
                        <label class="form-label">Old Password</label>
                        <input type="password" name="old_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" id="newPasswordInput" class="form-control" required>
                        <div id="passwordFeedback" class="validation-message text-danger mt-1"></div>
                    </div>
                    <button type="submit" name="update_password" class="btn btn-warning" id="passwordSubmit" disabled>Update Password</button>
                </form>

                <div class="text-center mt-4">
                    <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script>
// Sidebar toggle
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');

sidebarToggle.addEventListener('click', function () {
    sidebar.classList.toggle('active');
    document.body.classList.toggle('sidebar-open');
    overlay.classList.toggle('active');
});

document.addEventListener('click', function (event) {
    if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
        sidebar.classList.remove('active');
        document.body.classList.remove('sidebar-open');
        overlay.classList.remove('active');
    }
});

overlay.addEventListener('click', function () {
    sidebar.classList.remove('active');
    document.body.classList.remove('sidebar-open');
    overlay.classList.remove('active');
});

// Live Validation
$(document).ready(function () {
    function validateProfileForm() {
        const name = $('#nameInput').val().trim();
        const email = $('#emailInput').val().trim();
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        let valid = true;

        if (name.length === 0) {
            $('#nameInput').removeClass('is-valid').addClass('is-invalid');
            $('#nameFeedback').text('Name cannot be empty.');
            valid = false;
        } else {
            $('#nameInput').removeClass('is-invalid').addClass('is-valid');
            $('#nameFeedback').text('');
        }

        if (!emailPattern.test(email)) {
            $('#emailInput').removeClass('is-valid').addClass('is-invalid');
            $('#emailFeedback').text('Please enter a valid email address.');
            valid = false;
        } else {
            $('#emailInput').removeClass('is-invalid').addClass('is-valid');
            $('#emailFeedback').text('');
        }

        $('#profileSubmit').prop('disabled', !valid);
    }

    $('#nameInput, #emailInput').on('input', validateProfileForm);

    function validatePasswordForm() {
        const password = $('#newPasswordInput').val().trim();
        let valid = true;

        if (password.length < 6) {
            $('#newPasswordInput').removeClass('is-valid').addClass('is-invalid');
            $('#passwordFeedback').text('Password must be at least 6 characters.');
            valid = false;
        } else {
            $('#newPasswordInput').removeClass('is-invalid').addClass('is-valid');
            $('#passwordFeedback').text('');
        }

        $('#passwordSubmit').prop('disabled', !valid);
    }

    $('#newPasswordInput').on('input', validatePasswordForm);

    // Success animation fade out
    setTimeout(() => {
        $('#successAlert').fadeOut('slow');
    }, 2000);
});
</script>

</body>
</html>
