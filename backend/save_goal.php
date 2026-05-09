<?php
session_start();
include 'db.php'; // contains your DB connection code

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in."]);
    exit;
}

$user_id = $_SESSION['user_id'];
$goal_text = $_POST['goal'];

if (!empty($goal_text)) {
    $stmt = $conn->prepare("INSERT INTO goals (user_id, goal_text) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $goal_text);
    if ($stmt->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to save goal."]);
    }
    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Empty goal."]);
}
?>
