<?php
function logActivity($conn, $user_id, $role, $action, $details = null) {
    $stmt = $conn->prepare("INSERT INTO activity_log (user_id, role, action, details) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $role, $action, $details);
    $stmt->execute();
    $stmt->close();
}
?>
