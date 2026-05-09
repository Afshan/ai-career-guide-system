<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

include 'backend/db.php';

$user_id = $_SESSION['user_id'];
$subject_id = $_POST['subject_id'];
$subject_name = $_POST['subject_name'];
$marks = $_POST['marks'];

$stmt = $conn->prepare("UPDATE subjects SET subject_name = ?, marks = ? WHERE id = ? AND user_id = ?");
$stmt->bind_param("siii", $subject_name, $marks, $subject_id, $user_id);
$stmt->execute();

header("Location: view_subjects.php");
exit;
?>
