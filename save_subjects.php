<?php
session_start();
include 'backend/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Check if subject_name and marks are set and are arrays
    if (isset($_POST['subject_name'], $_POST['marks']) && is_array($_POST['subject_name']) && is_array($_POST['marks'])) {
        $subject_names = $_POST['subject_name'];
        $marks = $_POST['marks'];

        // Loop through and insert each subject
        for ($i = 0; $i < count($subject_names); $i++) {
            $name = trim($subject_names[$i]);
            $mark = trim($marks[$i]);

            if (!empty($name) && is_numeric($mark)) {
                $stmt = $conn->prepare("INSERT INTO subjects (user_id, subject_name, marks) VALUES (?, ?, ?)");
                $stmt->bind_param("isi", $user_id, $name, $mark);
                $stmt->execute();
            }
        }

        // Set a session variable for success message
        $_SESSION['subjects_saved_success'] = true;
    }
}

// Redirect to the view page
header("Location: view_subjects.php");
exit;
