<?php
session_start();
include '../backend/db.php';

if (!isset($_SESSION['admin_logged_in'])) exit;

$career_id = $_POST['career_id'];
$level = $_POST['level'];
$section_title = $_POST['section_title'];

$stmt = $conn->prepare("INSERT INTO career_levels (career_id, level, section_title) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $career_id, $level, $section_title);
$stmt->execute();

header("Location: edit_roadmap.php?career_id=$career_id");
exit;
