<?php
session_start();
include '../backend/db.php';
include 'admin_header.php';


if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit;
}

$id = $_GET['id'];
$result = $conn->query("SELECT * FROM careers WHERE id = $id");
$career = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $image_url = $_POST['image_url'];

    $stmt = $conn->prepare("UPDATE careers SET title = ?, description = ?, image_url = ? WHERE id = ?");
    $stmt->bind_param("sssi", $title, $description, $image_url, $id);
    $stmt->execute();

    header("Location: manage_careers.php");
    exit;
}
?>


<div class="container mt-5">
    <h3>Edit Career</h3>
    <form method="post">
        <div class="mb-3">
            <label>Career Title</label>
            <input type="text" name="title" value="<?= htmlspecialchars($career['title']) ?>" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Description</label>
            <textarea name="description" class="form-control" rows="4" required><?= htmlspecialchars($career['description']) ?></textarea>
        </div>
        <div class="mb-3">
            <label>Image URL (optional)</label>
            <input type="text" name="image_url" value="<?= htmlspecialchars($career['image_url']) ?>" class="form-control">
        </div>
        <button class="btn btn-primary">Update Career</button>
        <a href="manage_careers.php" class="btn btn-secondary">Cancel</a>
    </form>
<?php include 'admin_footer.php'; ?>

