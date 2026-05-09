<?php
session_start();
include '../backend/db.php';
include 'admin_header.php';


if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit;
}

$career_id = $_GET['career_id'] ?? null;
if (!$career_id) {
    header("Location: manage_roadmaps.php");
    exit;
}

$career = $conn->query("SELECT * FROM careers WHERE id = $career_id")->fetch_assoc();
$levels = ['Beginner', 'Intermediate', 'Advanced'];
?>


<div class="container mt-5">
    <h3>Edit Roadmap: <?= htmlspecialchars($career['title']) ?></h3>
    <a href="manage_roadmaps.php" class="btn btn-secondary mb-4">Back</a>

    <?php foreach ($levels as $level): ?>
        <div class="mb-4">
            <h4><?= $level ?> Level</h4>

            <?php
            $stmt = $conn->prepare("SELECT * FROM career_levels WHERE career_id = ? AND level = ?");
            $stmt->bind_param("is", $career_id, $level);
            $stmt->execute();
            $sections = $stmt->get_result();
            ?>

            <?php while ($section = $sections->fetch_assoc()): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <h5><?= htmlspecialchars($section['section_title']) ?></h5>
                        <ul>
                            <?php
                            $tasks = $conn->query("SELECT * FROM career_tasks WHERE level_id = {$section['id']}");
                            while ($task = $tasks->fetch_assoc()):
                            ?>
                                <li>
                                    <?= htmlspecialchars($task['task_text']) ?>
                                    <form action="delete_task.php" method="post" class="d-inline ms-2">
                                        <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                        <form method="post" action="add_task.php" class="d-flex mt-2">
                            <input type="hidden" name="level_id" value="<?= $section['id'] ?>">
                            <input type="text" name="task_text" class="form-control me-2" placeholder="New task" required>
                            <button class="btn btn-sm btn-success">Add Task</button>
                        </form>
                        <form method="post" action="delete_section.php" class="mt-2">
                            <input type="hidden" name="section_id" value="<?= $section['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger">Delete Section</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>

            <form method="post" action="add_section.php" class="mt-2">
                <input type="hidden" name="career_id" value="<?= $career_id ?>">
                <input type="hidden" name="level" value="<?= $level ?>">
                <div class="input-group">
                    <input type="text" name="section_title" class="form-control" placeholder="Add new section" required>
                    <button class="btn btn-primary">Add</button>
                </div>
            </form>
        </div>
    <?php endforeach; ?>
<?php include 'admin_footer.php'; ?>

