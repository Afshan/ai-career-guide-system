<?php 
session_start();
include '../backend/db.php';
include 'admin_header.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit;
}

// Fetch all careers
$careers = $conn->query("SELECT * FROM careers ORDER BY id DESC");

// Fetch all saved roadmaps
$saved_roadmaps = $conn->query("
    SELECT sr.*, u.name AS student_name 
    FROM saved_roadmaps sr
    JOIN users u ON sr.user_id = u.id
    ORDER BY sr.created_at DESC
");

// Fetch all selected careers
$selected_careers = $conn->query("
    SELECT sc.*, u.name AS student_name 
    FROM selected_career sc
    JOIN users u ON sc.user_id = u.id
    ORDER BY sc.id DESC
");
?>

<div class="container mt-4">
    <h3 class="mb-3">Manage Careers</h3>

    <a href="add_career.php" class="btn btn-primary mb-3">+ Add New Career</a>

    <!-- Existing Career List -->
    <table class="table table-striped table-hover table-bordered shadow-sm table-responsive">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Description</th>
                <th>Image</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($career = $careers->fetch_assoc()): ?>
                <tr>
                    <td><?= $career['id'] ?></td>
                    <td><?= htmlspecialchars($career['title']) ?></td>
                    <td><?= htmlspecialchars(substr($career['description'], 0, 100)) ?>...</td>
                    <td>
                        <?php if (!empty($career['image_url'])): ?>
                            <img src="<?= $career['image_url'] ?>" width="80" class="rounded shadow-sm">
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="edit_career.php?id=<?= $career['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <form method="post" action="delete_career.php" class="d-inline" onsubmit="return confirm('Delete this career?');">
                            <input type="hidden" name="id" value="<?= $career['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger ms-2">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            <?php if ($careers->num_rows == 0): ?>
                <tr><td colspan="5" class="text-center">No careers added yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Saved Careers (Roadmaps) Section -->
    <h4 class="mt-5">Saved Career Roadmaps by Students</h4>
    <table class="table table-bordered table-hover">
        <thead class="table-secondary">
            <tr>
                <th>#</th>
                <th>Student Name</th>
                <th>Career Title</th>
                <th>Saved On</th>
                <th>Roadmap Text</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; while ($row = $saved_roadmaps->fetch_assoc()): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($row['student_name']) ?></td>
                    <td><?= htmlspecialchars($row['career_title']) ?></td>
                    <td><?= date('F j, Y', strtotime($row['created_at'])) ?></td>
                    <td><?= nl2br(htmlspecialchars(substr($row['roadmap_text'], 0, 150))) ?>...</td>
                    <td>
                        <a href="edit_saved_roadmap.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="delete_saved_roadmap.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this roadmap?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            <?php if ($saved_roadmaps->num_rows == 0): ?>
                <tr><td colspan="6" class="text-center">No saved roadmaps yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Selected Careers Section -->
    <h4 class="mt-5">Students’ Selected Careers</h4>
    <table class="table table-bordered table-hover">
        <thead class="table-secondary">
            <tr>
                <th>#</th>
                <th>Student Name</th>
                <th>Selected Career</th>
                <th>Selected On</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php $j = 1; while ($row = $selected_careers->fetch_assoc()): ?>
                <tr>
                    <td><?= $j++ ?></td>
                    <td><?= htmlspecialchars($row['student_name']) ?></td>
                    <td><?= htmlspecialchars($row['career_title']) ?></td>
                    <td><?= date('F j, Y', strtotime($row['created_at'])) ?></td>
                    <td>
                        <a href="edit_selected_career.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="delete_selected_career.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this selected career?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            <?php if ($selected_careers->num_rows == 0): ?>
                <tr><td colspan="5" class="text-center">No careers selected yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

</div>

<?php include 'admin_footer.php'; ?>
