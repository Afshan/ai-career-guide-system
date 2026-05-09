<?php
session_start();
include('db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$degree_program = $_POST['degree_program'];
$current_semester = $_POST['current_semester'];
$cgpa = $_POST['cgpa'];

// Check if academic info already exists
$check_query = "SELECT * FROM academic_info WHERE user_id = ?";
$stmt = $conn->prepare($check_query);
if (!$stmt) {
    die("Check prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update
    $update_query = "UPDATE academic_info SET degree_program=?, current_semester=?, cgpa=? WHERE user_id=?";
    $stmt = $conn->prepare($update_query);
    if (!$stmt) {
        die("Update prepare failed: " . $conn->error);
    }
    $stmt->bind_param("sidi", $degree_program, $current_semester, $cgpa, $user_id);
    $stmt->execute();
} else {
    // Insert
    $insert_query = "INSERT INTO academic_info (user_id, degree_program, current_semester, cgpa) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    if (!$stmt) {
        die("Insert prepare failed: " . $conn->error);
    }
    $stmt->bind_param("isid", $user_id, $degree_program, $current_semester, $cgpa);
    $stmt->execute();
}

$stmt->close();
$conn->close();

$_SESSION['show_add_subjects_modal'] = true;

header("Location: ../dashboard.php");
exit();
