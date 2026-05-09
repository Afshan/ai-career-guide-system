<?php
session_start();
include '../backend/db.php';

if (!isset($_SESSION['admin_logged_in'])) exit;

$task_id = $_POST['task_id'];
$career = $conn->query("
    SELECT cl.career_id 
    FROM career_tasks ct
    JOIN career_levels cl ON ct.level_id = cl.id 
    WHERE ct.id = $task_id
")->fetch_assoc();

$conn->query("DELETE FROM career_tasks WHERE id = $task_id");

header("Location: edit_roadmap.php?career_id=" . $career['career_id']);
exit;
