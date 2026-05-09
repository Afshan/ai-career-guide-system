<?php
include('db.php');

$name = $_POST['name'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

// Check if email already exists
$check = $conn->prepare("SELECT id FROM users WHERE email = ?");
if (!$check) {
    die("Check prepare failed: " . $conn->error);
}
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo "Email already registered.";
} else {
    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    if (!$stmt) {
        die("Insert prepare failed: " . $conn->error);
    }
    $stmt->bind_param("sss", $name, $email, $password);
    if ($stmt->execute()) {
        // Get the inserted user ID
        $user_id = $stmt->insert_id;

        // Start a session and store user ID
        session_start();
        $_SESSION['user_id'] = $user_id;

        // Redirect to add_interests.php
        header("Location: ../add_interests.php");
        exit();
    } else {
        echo "Something went wrong while inserting user.";
    }
}
?>
