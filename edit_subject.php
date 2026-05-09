<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

include 'backend/db.php';

$user_id = $_SESSION['user_id'];

if (isset($_GET['id'])) {
    $subject_id = $_GET['id'];

    $stmt = $conn->prepare("SELECT subject_name, marks FROM subjects WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $subject_id, $user_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($subject_name, $marks);
        $stmt->fetch();
    } else {
        echo "Subject not found or access denied.";
        exit;
    }
} else {
    echo "No subject ID provided.";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Subject</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h3>Edit Subject</h3>
    <form action="update_subject.php" method="post">
        <input type="hidden" name="subject_id" value="<?php echo $subject_id; ?>">
        <div class="mb-3">
            <label>Subject Name:</label>
            <input type="text" name="subject_name" class="form-control" value="<?php echo htmlspecialchars($subject_name); ?>" required>
        </div>
        <div class="mb-3">
            <label>Marks:</label>
            <input type="number" name="marks" class="form-control" value="<?php echo $marks; ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="view_subjects.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>
