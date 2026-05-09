<?php
session_start(); // Required for storing roadmap in session

header('Content-Type: application/json');

// Your Gemini API key
$api_key = 'AIzaSyAcQX9aKD9QlW7XGcJVvLQfhM79XppuioE';

// Get input
$input = json_decode(file_get_contents('php://input'), true);
$career = trim($input['career'] ?? '');

// If roadmap already exists in session, return it to avoid regenerating
if (isset($_SESSION['saved_roadmaps'][$career])) {
    echo json_encode(['steps' => $_SESSION['saved_roadmaps'][$career]]);
    exit;
}

// Validate input
if (empty($career)) {
    echo json_encode(['error' => 'No career provided.']);
    exit;
}

// Prompt Gemini
$prompt = "Create a career roadmap to become a {$career}, divided into three clearly labeled sections: 
Beginner, Intermediate, and Advanced.

For each section:
- Start with the section title: Beginner, Intermediate, or Advanced.
- List 3 to 5 short, practical checklist items.
- Use plain text only. No Markdown, symbols, or numbering styles. Just this format:

Beginner
Learn the basics of programming
Build small projects
Understand how the internet works

Intermediate
Contribute to open source
Build a portfolio
Learn APIs

Advanced
Apply for jobs
Prepare for interviews
Specialize in a field";


// Gemini API URL
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $api_key;

// Prepare request body
$body = [
    "contents" => [[
        "parts" => [[ "text" => $prompt ]]
    ]]
];

// Send API request
$options = [
    'http' => [
        'header'  => "Content-type: application/json",
        'method'  => 'POST',
        'content' => json_encode($body),
    ]
];
$context = stream_context_create($options);
$response = file_get_contents($url, false, $context);

// Handle failure
if ($response === false) {
    echo json_encode(['error' => 'API request failed']);
    exit;
}

// Decode response
$data = json_decode($response, true);
$rawText = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

if (!$rawText) {
    echo json_encode(['error' => 'No response from Gemini']);
    exit;
}

// Cleanup text
$cleanText = preg_replace('/\*\*(.*?)\*\*/', '$1', $rawText); // remove **bold**
$lines = preg_split('/\r\n|\r|\n/', $cleanText);
$steps = array_values(array_filter(array_map('trim', $lines)));

// Save the roadmap in session (you can replace this with DB save)
if (!isset($_SESSION['saved_roadmaps'])) {
    $_SESSION['saved_roadmaps'] = [];
}
$_SESSION['saved_roadmaps'][$career] = $steps;

// Send result
echo json_encode(['steps' => $steps]);
