<?php
session_start();
include('db.php'); // make sure this file connects correctly to your DB

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['interests']) && is_array($_POST['interests'])) {
    $interests = $_POST['interests'];

    // Delete previous entries (if any)
    $deleteQuery = "DELETE FROM student_interests WHERE user_id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    // Insert each selected interest
    $insertQuery = "INSERT INTO student_interests (user_id, interest) VALUES (?, ?)";
    $stmt = $conn->prepare($insertQuery);

    foreach ($interests as $interest) {
        $stmt->bind_param("is", $user_id, $interest);
        $stmt->execute();
    }

    // Redirect to academic info page
    header("Location: ../academic_info.php");
    exit();

} else {
    // No interests selected or something went wrong
    header("Location: ../add_interests.php?error=1");
    exit();
}
