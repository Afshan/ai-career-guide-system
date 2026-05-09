<?php
session_start();
include '../backend/db.php';

if (!isset($_SESSION['admin_logged_in'])) exit;

$level_id = $_POST['level_id'];
$task_text = $_POST['task_text'];

$stmt = $conn->prepare("INSERT INTO career_tasks (level_id, task_text) VALUES (?, ?)");
$stmt->bind_param("is", $level_id, $task_text);
$stmt->execute();

// Get career_id for redirection
$career = $conn->query("SELECT career_id FROM career_levels WHERE id = $level_id")->fetch_assoc();
header("Location: edit_roadmap.php?career_id=" . $career['career_id']);
exit;
