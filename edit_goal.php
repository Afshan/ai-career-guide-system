<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

include 'backend/db.php';

if (!isset($_GET['id'])) {
    header("Location: goals.php?msg=Invalid request");
    exit;
}

$goal_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch the goal
$stmt = $conn->prepare("SELECT * FROM goals WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $goal_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: goals.php?msg=Goal not found");
    exit;
}

$goal = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $category = $_POST['category'];
    $priority = $_POST['priority'];
    $target_date = $_POST['target_date'];
    $status = $_POST['status'];
    $description = $_POST['description'];

    $update_stmt = $conn->prepare("UPDATE goals SET title=?, category=?, priority=?, target_date=?, status=?, description=? WHERE id=? AND user_id=?");
    $update_stmt->bind_param("ssssssii", $title, $category, $priority, $target_date, $status, $description, $goal_id, $user_id);

    if ($update_stmt->execute()) {
        header("Location: goals.php?msg=Goal updated successfully");
        exit;
    } else {
        $error = "Error updating goal. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Goal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container mt-5">
    <h2>Edit Goal</h2>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="title" class="form-label">Goal Title</label>
            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($goal['title']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="category" class="form-label">Category</label>
            <input type="text" class="form-control" id="category" name="category" value="<?php echo htmlspecialchars($goal['category']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="priority" class="form-label">Priority</label>
            <select class="form-select" id="priority" name="priority" required>
                <option <?php if ($goal['priority'] == 'Low') echo 'selected'; ?>>Low</option>
                <option <?php if ($goal['priority'] == 'Medium') echo 'selected'; ?>>Medium</option>
                <option <?php if ($goal['priority'] == 'High') echo 'selected'; ?>>High</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="target_date" class="form-label">Target Date</label>
            <input type="date" class="form-control" id="target_date" name="target_date" value="<?php echo htmlspecialchars($goal['target_date']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" id="status" name="status" required>
                <option <?php if ($goal['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                <option <?php if ($goal['status'] == 'In Progress') echo 'selected'; ?>>In Progress</option>
                <option <?php if ($goal['status'] == 'Completed') echo 'selected'; ?>>Completed</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($goal['description']); ?></textarea>
        </div>

        <button type="submit" class="btn btn-success">Update Goal</button>
        <a href="goals.php" class="btn btn-secondary">Back</a>
    </form>
</div>

</body>
</html>
