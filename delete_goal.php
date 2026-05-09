<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

include 'backend/db.php';

if (isset($_GET['id'])) {
    $goal_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    // Ensure the goal belongs to the logged-in user
    $stmt = $conn->prepare("DELETE FROM goals WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $goal_id, $user_id);

    if ($stmt->execute()) {
        // Goal successfully deleted
        header("Location: goals.php?msg=Goal deleted successfully");
        exit;
    } else {
        // Something went wrong
        header("Location: goals.php?msg=Error deleting goal");
        exit;
    }
} else {
    // No ID provided
    header("Location: goals.php?msg=Invalid request");
    exit;
}
?>
