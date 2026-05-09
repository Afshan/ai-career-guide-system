<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['progress' => 0]);
    exit;
}
include 'backend/db.php';

$user_id = $_SESSION['user_id'];

$total_subtasks = 0;
$completed_subtasks = 0;

$goals_stmt = $conn->prepare("SELECT id FROM goals WHERE user_id = ?");
$goals_stmt->bind_param("i", $user_id);
$goals_stmt->execute();
$goals_result = $goals_stmt->get_result();

while ($goal = $goals_result->fetch_assoc()) {
    $goal_id = $goal['id'];

    $subtasks_stmt = $conn->prepare("SELECT COUNT(*) as total FROM subtasks WHERE goal_id = ?");
    $subtasks_stmt->bind_param("i", $goal_id);
    $subtasks_stmt->execute();
    $total = $subtasks_stmt->get_result()->fetch_assoc()['total'];

    $completed_stmt = $conn->prepare("SELECT COUNT(*) as completed FROM subtasks WHERE goal_id = ? AND is_completed = 1");
    $completed_stmt->bind_param("i", $goal_id);
    $completed_stmt->execute();
    $completed = $completed_stmt->get_result()->fetch_assoc()['completed'];

    $total_subtasks += $total;
    $completed_subtasks += $completed;

    $subtasks_stmt->close();
    $completed_stmt->close();
}

$goals_stmt->close();

$average_progress = ($total_subtasks > 0) ? ($completed_subtasks / $total_subtasks) * 100 : 0;
$average_progress = round($average_progress);

echo json_encode(['progress' => $average_progress]);
?>
