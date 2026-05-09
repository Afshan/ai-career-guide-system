<?php
include '../backend/db.php';

$data = [
    'degree_labels' => [],
    'degree_counts' => [],
    'career_labels' => [],
    'career_counts' => [],
    'goal_labels' => ['Pending', 'In Progress', 'Completed'],
    'goal_counts' => []
];

// Degrees
$sql = "SELECT degree_program, COUNT(*) as count FROM academic_info GROUP BY degree_program";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $data['degree_labels'][] = $row['degree_program'];
    $data['degree_counts'][] = $row['count'];
}

// Careers
$sql = "SELECT career_title, COUNT(*) as count FROM selected_career GROUP BY career_title";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $data['career_labels'][] = $row['career_title'];
    $data['career_counts'][] = $row['count'];
}

// Goal Status
$statuses = ['Pending', 'In Progress', 'Completed'];
foreach ($statuses as $status) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM goals WHERE status = ?");
    $stmt->bind_param("s", $status);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $data['goal_counts'][] = $count;
    $stmt->close();
}

header('Content-Type: application/json');
echo json_encode($data);
