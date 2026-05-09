<?php
session_start();
include '../backend/db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);

    // Delete from users (you can do soft delete if you prefer)
    $conn->query("DELETE FROM users WHERE id = $user_id");

    // Optional: Also delete linked records (for simplicity)
    $conn->query("DELETE FROM academic_info WHERE user_id = $user_id");
    $conn->query("DELETE FROM student_interests WHERE user_id = $user_id");
    $conn->query("DELETE FROM goals WHERE user_id = $user_id");
    $conn->query("DELETE FROM subjects WHERE user_id = $user_id");
    $conn->query("DELETE FROM selected_career WHERE user_id = $user_id");

    header("Location: manage_users.php");
    exit;
}
?>
