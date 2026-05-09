<?php
session_start();
include 'db.php';


$email = $_POST['email'];
$password = $_POST['password'];

$stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows == 1) {
    $stmt->bind_result($id, $name, $hashed_password);
    $stmt->fetch();

    if (password_verify($password, $hashed_password)) {
        $_SESSION['user_id'] = $id;
        $_SESSION['user_name'] = $name;

        header("Location: ../dashboard.php");
        exit;
    } else {
        echo "Invalid password.";
    }
} else {
    echo "No user found with this email.";
}

$today = date('Y-m-d');

// Insert today’s activity if not already exists
$insertActivity = $conn->prepare("INSERT IGNORE INTO user_activity (user_id, activity_date) VALUES (?, ?)");
$insertActivity->bind_param("is", $user_id, $today);
$insertActivity->execute();
$insertActivity->close();

?>
