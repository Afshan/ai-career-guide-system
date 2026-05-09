<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}
include 'backend/db.php'; // adjust if your db.php path is different

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $subject1 = $_POST['subject1'];
    $subject2 = $_POST['subject2'];
    $interest = $_POST['interest'];
    $skills = $_POST['skills'];

    $stmt = $conn->prepare("INSERT INTO student_data (user_id, subject1_marks, subject2_marks, interest, skills) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiss", $user_id, $subject1, $subject2, $interest, $skills);

    if ($stmt->execute()) {
        echo "<script>alert('Data saved successfully!');</script>";
    } else {
        echo "<script>alert('Error saving data');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Data Input</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h3>Enter Your Academic and Interest Data</h3>
    <form method="POST" action="">
        <div class="mb-3">
            <label>Subject 1 Marks:</label>
            <input type="number" name="subject1" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Subject 2 Marks:</label>
            <input type="number" name="subject2" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Your Interests:</label>
            <textarea name="interest" class="form-control" rows="2" placeholder="e.g. Web Development, AI, Writing..."></textarea>
        </div>
        <div class="mb-3">
            <label>Your Skills:</label>
            <textarea name="skills" class="form-control" rows="2" placeholder="e.g. HTML, CSS, Python..."></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Save Data</button>
    </form>
</div>
</body>
</html>
