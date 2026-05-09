<?php
session_start();
include '../backend/db.php';
include 'admin_header.php';


if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit;
}

$careers = $conn->query("SELECT * FROM careers");
?>


<div class="container mt-5">
    <h3 class="mb-4">Manage Career Roadmaps</h3>
    <div class="row">
        <div class="col-md-6">
            <form action="edit_roadmap.php" method="get">
                <div class="mb-3">
                    <label>Select Career</label>
                    <select name="career_id" class="form-select" required>
                        <option value="">-- Choose Career --</option>
                        <?php while ($career = $careers->fetch_assoc()): ?>
                            <option value="<?= $career['id'] ?>"><?= htmlspecialchars($career['title']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button class="btn btn-primary">Edit Roadmap</button>
            </form>
        </div>
    </div>
<?php include 'admin_footer.php'; ?>

