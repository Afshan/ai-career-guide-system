<?php
include '../backend/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "Unauthorized access.";
    exit;
}

$goal_id = $_GET['goal_id'] ?? null;

if (!$goal_id) {
    echo "Invalid goal ID.";
    exit;
}

$stmt = $conn->prepare("SELECT * FROM subtasks WHERE goal_id = ? ORDER BY id ASC");
$stmt->bind_param("i", $goal_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($subtask = $result->fetch_assoc()) {
        $completedClass = $subtask['is_completed'] ? 'completed' : '';
        $checked = $subtask['is_completed'] ? 'checked' : '';

        echo '<div class="subtask-item d-flex align-items-center mb-2" data-id="' . $subtask['id'] . '">
                <input type="checkbox" class="form-check-input me-2 toggle-complete" ' . $checked . '>
                <span class="subtask-text flex-grow-1 ' . $completedClass . '">' . htmlspecialchars($subtask['subtask_text']) . '</span>
                <button class="btn btn-danger btn-sm delete-btn ms-2"><i class="fas fa-trash"></i></button>
              </div>';
    }
} else {
    echo '';
}

$stmt->close();
?>
