<?php
include '../backend/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subtask_id = $_POST['id'];

    // Fetch the subtask
    $stmt = $conn->prepare("SELECT * FROM subtasks WHERE id = ?");
    $stmt->bind_param("i", $subtask_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $subtask = $result->fetch_assoc();
    $stmt->close();

    if (!$subtask) {
        echo 'error: subtask not found';
        exit;
    }

    $goal_id = $subtask['goal_id'];

    // Toggle the completion status
    $newStatus = $subtask['is_completed'] ? 0 : 1;
    $stmt = $conn->prepare("UPDATE subtasks SET is_completed = ? WHERE id = ?");
    $stmt->bind_param("ii", $newStatus, $subtask_id);
    $stmt->execute();
    $stmt->close();

    // Calculate new progress
    $stmt = $conn->prepare("SELECT COUNT(*) AS total, IFNULL(SUM(is_completed), 0) AS completed FROM subtasks WHERE goal_id = ?");
    $stmt->bind_param("i", $goal_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $progressData = $result->fetch_assoc();
    $stmt->close();

    $total = $progressData['total'];
    $completed = $progressData['completed'];
    $progress = ($total > 0) ? round(($completed / $total) * 100) : 0;

    // Update the goal's progress
    $stmt = $conn->prepare("UPDATE goals SET progress = ? WHERE id = ?");
    $stmt->bind_param("ii", $progress, $goal_id);
    $stmt->execute();
    $stmt->close();

    echo $progress;
}
?>
