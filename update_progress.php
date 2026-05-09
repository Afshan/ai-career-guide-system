<?php
session_start();
include 'backend/db.php';

if (isset($_SESSION['user_id']) && isset($_POST['goal_id']) && isset($_POST['progress'])) {
    $user_id = $_SESSION['user_id'];
    $goal_id = $_POST['goal_id'];
    $progress = $_POST['progress'];

    $stmt = $conn->prepare("UPDATE goals SET progress = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("iii", $progress, $goal_id, $user_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }

    $stmt->close();
} else {
    echo json_encode(['status' => 'unauthorized']);
}
?>
