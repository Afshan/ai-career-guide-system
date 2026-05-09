<?php
function getCareerSuggestionFromGemini($prompt) {
    $apiKey = GEMINI_API_KEY;  // from config.php
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent";

    $headers = [
        "Content-Type: application/json",
        "X-goog-api-key: $apiKey"
    ];

    $data = [
        "contents" => [
            [
                "parts" => [
                    ["text" => $prompt]
                ]
            ]
        ]
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $retryCount = 0;
$maxRetries = 3;
$waitSeconds = 3;
$response = null;

do {
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (strpos($response, 'model is overloaded') !== false) {
        sleep($waitSeconds);
        $retryCount++;
    } else {
        break;
    }
} while ($retryCount < $maxRetries);


    if (curl_errno($ch)) {
        return 'Request Error: ' . curl_error($ch);
    }

    curl_close($ch);

    $result = json_decode($response, true);

    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        return $result['candidates'][0]['content']['parts'][0]['text'];
    } elseif (isset($result['error'])) {
        return 'API Error: ' . $result['error']['message'];
    } else {
        return 'Unknown response from API.';
    }
}
?>
