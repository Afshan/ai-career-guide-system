<?php
session_start();
include '../backend/db.php';
include 'admin_header.php';
include '../backend/log_activity.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit;
}

// Get previous full week's start and end dates (Monday to Sunday)
$today = date('Y-m-d');
$last_sunday = date('Y-m-d', strtotime('last sunday', strtotime($today)));
$week_start = date('Y-m-d', strtotime($last_sunday . ' -6 days'));
$week_end = $last_sunday;

// Get new users
$new_users = $conn->query("SELECT * FROM users WHERE created_at BETWEEN '$week_start' AND '$week_end'");

// Careers added
$new_careers = $conn->query("SELECT * FROM careers WHERE created_at BETWEEN '$week_start' AND '$week_end'");

// Career selections
$selected_careers = $conn->query("
    SELECT sc.*, u.name AS student_name 
    FROM selected_career sc 
    JOIN users u ON sc.user_id = u.id 
    WHERE sc.created_at BETWEEN '$week_start' AND '$week_end'
");

// Roadmaps saved
$saved_roadmaps = $conn->query("
    SELECT sr.*, u.name AS student_name 
    FROM saved_roadmaps sr 
    JOIN users u ON sr.user_id = u.id 
    WHERE sr.created_at BETWEEN '$week_start' AND '$week_end'
");
?>

<div class="container mt-4">
    <h3 class="mb-4">📅 Weekly Report (<?= date('F j, Y', strtotime($week_start)) ?> – <?= date('F j, Y', strtotime($week_end)) ?>)</h3>

    <div class="mb-5">
        <h5> New Students Registered: <?= $new_users->num_rows ?></h5>
        <ul>
            <?php while ($user = $new_users->fetch_assoc()): ?>
                <li><?= htmlspecialchars($user['name']) ?> (<?= $user['email'] ?>)</li>
            <?php endwhile; ?>
            <?php if ($new_users->num_rows === 0): ?>
                <li>No new registrations this week.</li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="mb-5">
        <h5>📚 Careers Added: <?= $new_careers->num_rows ?></h5>
        <ul>
            <?php while ($career = $new_careers->fetch_assoc()): ?>
                <li><?= htmlspecialchars($career['title']) ?></li>
            <?php endwhile; ?>
            <?php if ($new_careers->num_rows === 0): ?>
                <li>No careers added this week.</li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="mb-5">
        <h5>🎯 Career Selections: <?= $selected_careers->num_rows ?></h5>
        <ul>
            <?php while ($row = $selected_careers->fetch_assoc()): ?>
                <li><?= htmlspecialchars($row['student_name']) ?> selected <strong><?= htmlspecialchars($row['career_title']) ?></strong></li>
            <?php endwhile; ?>
            <?php if ($selected_careers->num_rows === 0): ?>
                <li>No selections this week.</li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="mb-5">
        <h5>🗺️ Roadmaps Saved: <?= $saved_roadmaps->num_rows ?></h5>
        <ul>
            <?php while ($row = $saved_roadmaps->fetch_assoc()): ?>
                <li><?= htmlspecialchars($row['student_name']) ?> saved roadmap for <strong><?= htmlspecialchars($row['career_title']) ?></strong></li>
            <?php endwhile; ?>
            <?php if ($saved_roadmaps->num_rows === 0): ?>
                <li>No roadmaps saved this week.</li>
            <?php endif; ?>
        </ul>
    </div>
</div>

<?php include 'admin_footer.php'; ?>
