<?php
include '../backend/db.php';

$id = $_POST['id'] ?? 0;
$newText = trim($_POST['subtask'] ?? '');

if ($id && $newText !== '') {
    $stmt = $conn->prepare("UPDATE subtasks SET subtask_text = ? WHERE id = ?");
    $stmt->bind_param("si", $newText, $id);
    $stmt->execute();
    $stmt->close();
    echo 'success';
} else {
    echo 'error';
}
?>
