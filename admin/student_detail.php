<?php
session_start();
include '../backend/db.php';
include 'admin_header.php';
include '../backend/log_activity.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    echo "Student ID is missing.";
    exit;
}

$user_id = intval($_GET['id']);

// Fetch user info
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

if (!$user) {
    echo "User not found.";
    exit;
}

// Fetch academic info
$academic_stmt = $conn->prepare("SELECT * FROM academic_info WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
$academic_stmt->bind_param("i", $user_id);
$academic_stmt->execute();
$academic_result = $academic_stmt->get_result();
$academic = $academic_result->fetch_assoc();

// Fetch interests
$interests_stmt = $conn->prepare("SELECT interest FROM student_interests WHERE user_id = ?");
$interests_stmt->bind_param("i", $user_id);
$interests_stmt->execute();
$interests_result = $interests_stmt->get_result();

$interests = [];
while ($row = $interests_result->fetch_assoc()) {
    $interests[] = $row['interest'];
}

// Fetch goals
$goals_stmt = $conn->prepare("SELECT * FROM goals WHERE user_id = ?");
$goals_stmt->bind_param("i", $user_id);
$goals_stmt->execute();
$goals_result = $goals_stmt->get_result();
$goals = [];
while ($row = $goals_result->fetch_assoc()) {
    $goals[] = $row;
}

// Fetch suggested + selected career
$career_stmt = $conn->prepare("
    SELECT cs.suggested_career, sc.career_title AS selected_career 
    FROM career_suggestions cs 
    LEFT JOIN selected_career sc ON cs.user_id = sc.user_id 
    WHERE cs.user_id = ? 
    ORDER BY cs.created_at DESC 
    LIMIT 1
");
$career_stmt->bind_param("i", $user_id);
$career_stmt->execute();
$career_result = $career_stmt->get_result();
$career = $career_result->fetch_assoc();

// Determine which career to fetch roadmap for
$career_to_check = $career['selected_career'] ?? $career['suggested_career'] ?? null;

$roadmap_details = null;

if ($career_to_check) {
   // Fetch saved roadmap
$roadmap_stmt = $conn->prepare("SELECT roadmap_text FROM saved_roadmaps WHERE user_id = ? LIMIT 1");
$roadmap_stmt->bind_param("i", $user_id);
$roadmap_stmt->execute();
$roadmap_result = $roadmap_stmt->get_result();
$roadmap_row = $roadmap_result->fetch_assoc();
$roadmap_details = $roadmap_row['roadmap_text'] ?? null;

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Report - <?= htmlspecialchars($user['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <a href="manage_users.php" class="btn btn-secondary mb-4">&larr; Back to Student List</a>
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h3>Student Report: <?= htmlspecialchars($user['name']) ?></h3>
        </div>
        <div class="card-body">
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
            <p><strong>Signup Date:</strong> <?= date('F j, Y', strtotime($user['created_at'])) ?></p>

            <?php if ($academic): ?>
                <h5 class="mt-4">Academic Info</h5>
                <ul>
                    <li><strong>Degree:</strong> <?= htmlspecialchars($academic['degree_program']) ?></li>
                    <li><strong>Semester:</strong> <?= htmlspecialchars($academic['current_semester']) ?></li>
                    <li><strong>CGPA:</strong> <?= htmlspecialchars($academic['cgpa']) ?></li>
                </ul>
            <?php endif; ?>

            <h5 class="mt-4">Interests</h5>
            <?php if ($interests): ?>
                <ul>
                    <?php foreach ($interests as $interest): ?>
                        <li><?= htmlspecialchars($interest) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No interests listed.</p>
            <?php endif; ?>

            <h5 class="mt-4">Goals</h5>
            <?php if ($goals): ?>
                <ul>
                    <?php foreach ($goals as $goal): ?>
                        <li>
                            <strong><?= htmlspecialchars($goal['title']) ?></strong> (<?= htmlspecialchars($goal['category']) ?>) – 
                            <?= htmlspecialchars($goal['goal_text']) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No goals set.</p>
            <?php endif; ?>

            <h5 class="mt-4">Career Guidance</h5>
            <?php if ($career): ?>
                <ul>
                    <li><strong>Suggested Career:</strong> <?= htmlspecialchars($career['suggested_career']) ?></li>
                    <li><strong>Selected Career:</strong> <?= htmlspecialchars($career['selected_career']) ?></li>
                </ul>
            <?php else: ?>
                <p>No career guidance data available.</p>
            <?php endif; ?>

            <?php if ($roadmap_details): ?>
    <h5 class="mt-4">Career Roadmap</h5>
    <div style="white-space: pre-line; background-color: #f8f9fa; padding: 15px; border-radius: 5px; border: 1px solid #ddd;">
        <?= htmlspecialchars($roadmap_details) ?>
    </div>
<?php else: ?>
    <p><em>No roadmap available for this career.</em></p>
<?php endif; ?>

        </div>
    </div>
</div>
</body>
</html>
