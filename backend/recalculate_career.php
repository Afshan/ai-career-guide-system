<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

include 'backend/db.php';
include 'backend/config.php';
include 'backend/gemini_ai.php';

$user_id = $_SESSION['user_id'];

// === 1. Fetch student data ===
// Academic Info
$academic_stmt = $conn->prepare("SELECT degree_program, current_semester, cgpa FROM academic_info WHERE user_id = ?");
$academic_stmt->bind_param("i", $user_id);
$academic_stmt->execute();
$academic_result = $academic_stmt->get_result();
$academic = $academic_result->fetch_assoc();
$degree = $academic['degree_program'] ?? '';
$semester = $academic['current_semester'] ?? '';
$cgpa = $academic['cgpa'] ?? '';

// Interests
$interests = [];
$interest_result = $conn->query("SELECT interest FROM student_interests WHERE user_id = $user_id");
while ($row = $interest_result->fetch_assoc()) {
    $interests[] = $row['interest'];
}
$interest_str = implode(', ', $interests);

// Subjects + Marks
$subjects = [];
$subject_result = $conn->query("SELECT subject_name, marks FROM subjects WHERE user_id = $user_id");
while ($row = $subject_result->fetch_assoc()) {
    $subjects[] = "{$row['subject_name']} ({$row['marks']}%)";
}
$subject_str = implode(', ', $subjects);

// === 2. Build Prompt ===
$prompt = "Based on the following student profile:\n\n".
    "Degree Program: $degree\n".
    "Semester: $semester\n".
    "CGPA: $cgpa\n".
    "Subjects & Marks: $subject_str\n".
    "Interests: $interest_str\n\n".
    "Please suggest:\n".
    "1. The most suitable career path for this student\n".
    "2. 3 closely related career options\n".
    "3. 3 most popular careers in current market\n\n".
    "Format:\n".
    "Suggested Career: <Career Title>\n".
    "Reason: <Why this career is best>\n".
    "Related Careers:\n- Career 1\n- Career 2\n- Career 3\n".
    "Popular Careers:\n- Career A\n- Career B\n- Career C";

// === 3. Get AI Response ===
$response = getCareerSuggestionFromGemini($prompt);
if (!$response) {
    echo json_encode(['success' => false, 'error' => 'Failed to get response from AI']);
    exit;
}

// === 4. Parse AI response ===
$suggested_career = $reason = '';
$related_careers = $popular_careers = [];

preg_match('/Suggested Career:\s*(.+)/i', $response, $match1);
$suggested_career = $match1[1] ?? '';

preg_match('/Reason:\s*(.+?)(?=Related Careers:)/is', $response, $match2);
$reason = trim($match2[1] ?? '');

preg_match('/Related Careers:\s*([\s\S]*?)Popular Careers:/i', $response, $match3);
$related_raw = trim($match3[1] ?? '');
$related_careers = array_map('trim', preg_split('/\r\n|\n|\- /', $related_raw));
$related_careers = array_filter($related_careers); // Remove empty

preg_match('/Popular Careers:\s*([\s\S]*)$/i', $response, $match4);
$popular_raw = trim($match4[1] ?? '');
$popular_careers = array_map('trim', preg_split('/\r\n|\n|\- /', $popular_raw));
$popular_careers = array_filter($popular_careers); // Remove empty

// === 5. Save to DB ===

// Clear previous data (optional)
$conn->query("DELETE FROM career_suggestions WHERE user_id = $user_id");
$conn->query("DELETE FROM related_careers WHERE user_id = $user_id");
$conn->query("DELETE FROM popular_careers WHERE user_id = $user_id");

// Save main suggestion
$stmt = $conn->prepare("INSERT INTO career_suggestions (user_id, suggested_career, reason) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $user_id, $suggested_career, $reason);
$stmt->execute();

// Save related careers
$rel_stmt = $conn->prepare("INSERT INTO related_careers (user_id, related_career) VALUES (?, ?)");
foreach ($related_careers as $career) {
    $rel_stmt->bind_param("is", $user_id, $career);
    $rel_stmt->execute();
}

// Save popular careers
$pop_stmt = $conn->prepare("INSERT INTO popular_careers (user_id, popular_career) VALUES (?, ?)");
foreach ($popular_careers as $career) {
    $pop_stmt->bind_param("is", $user_id, $career);
    $pop_stmt->execute();
}

echo json_encode(['success' => true]);
