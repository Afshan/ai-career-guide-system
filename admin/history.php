<?php
session_start();
include '../backend/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit;
}

$result = $conn->query("SELECT * FROM activity_log ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>📜 Full Website History</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="p-4">
    <h2 class="mb-4">📜 Full Website History</h2>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>User ID</th>
                <th>Role</th>
                <th>Action</th>
                <th>Details</th>
                <th>Date & Time</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['user_id'] ?></td>
                    <td><?= ucfirst($row['role']) ?></td>
                    <td><?= htmlspecialchars($row['action']) ?></td>
                    <td><?= htmlspecialchars($row['details']) ?></td>
                    <td><?= $row['created_at'] ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
