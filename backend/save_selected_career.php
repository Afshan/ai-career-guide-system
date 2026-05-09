<?php
session_start();
include 'db.php'; // adjust path if needed

header('Content-Type: application/json');

// Check login
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'User not logged in'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];
$career_title = trim($_POST['career_title'] ?? '');

// Validate input
if (empty($career_title)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Career title is required'
    ]);
    exit;
}

try {
    // Check if the user already has a career saved
    $sql_check = "SELECT id FROM selected_career WHERE user_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $user_id);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        // Update existing career
        $sql_update = "UPDATE selected_career 
                       SET career_title = ?, updated_at = NOW() 
                       WHERE user_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("si", $career_title, $user_id);
        $stmt_update->execute();
        $stmt_update->close();

        $response = [
            'status' => 'success',
            'message' => 'Career updated successfully'
        ];
    } else {
        // Insert new career
        $sql_insert = "INSERT INTO selected_career (user_id, career_title, created_at, updated_at) 
                       VALUES (?, ?, NOW(), NOW())";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("is", $user_id, $career_title);
        $stmt_insert->execute();
        $stmt_insert->close();

        $response = [
            'status' => 'success',
            'message' => 'Career saved successfully'
        ];
    }

    $stmt_check->close();
    $conn->close();

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
