<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../backend/db.php';

$subtask = $_POST['subtask'] ?? '';
$goal_id = $_POST['goal_id'] ?? 0;

if ($subtask && $goal_id) {
    $stmt = $conn->prepare("INSERT INTO subtasks (goal_id, subtask_text, is_completed) VALUES (?, ?, 0)");
    $stmt->bind_param("is", $goal_id, $subtask);
    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error: ' . $stmt->error;
    }
    $stmt->close();
} else {
    echo 'error: missing data';
}
?>
