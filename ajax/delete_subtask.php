<?php
include '../backend/db.php';
$id = $_POST['id'];
$conn->query("DELETE FROM subtasks WHERE id = $id");
?>