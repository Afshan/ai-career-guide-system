<?php
session_start();
include '../backend/db.php';
include 'admin_header.php';


if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $image_url = $_POST['image_url'];

    $stmt = $conn->prepare("INSERT INTO careers (title, description, image_url) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $title, $description, $image_url);
    $stmt->execute();

    header("Location: manage_careers.php");
    exit;
}
?>


<div class="container mt-5">
    <h3>Add New Career</h3>
    <form method="post">
        <div class="mb-3">
            <label>Career Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Description</label>
            <textarea name="description" class="form-control" rows="4" required></textarea>
        </div>
        <div class="mb-3">
            <label>Image URL (optional)</label>
            <input type="text" name="image_url" class="form-control">
        </div>
        <button class="btn btn-success">Add Career</button>
        <a href="manage_careers.php" class="btn btn-secondary">Cancel</a>
    </form>
<?php include 'admin_footer.php'; ?>

