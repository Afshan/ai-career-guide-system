<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

include 'backend/db.php';

$subject_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("DELETE FROM subjects WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $subject_id, $user_id);
$stmt->execute();

header("Location: view_subjects.php");
exit;
?>
