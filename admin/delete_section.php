<?php
session_start();
include '../backend/db.php';

if (!isset($_SESSION['admin_logged_in'])) exit;

$section_id = $_POST['section_id'];
$career = $conn->query("SELECT career_id FROM career_levels WHERE id = $section_id")->fetch_assoc();

$conn->query("DELETE FROM career_levels WHERE id = $section_id");

header("Location: edit_roadmap.php?career_id=" . $career['career_id']);
exit;
