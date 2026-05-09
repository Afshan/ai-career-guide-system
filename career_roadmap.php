<?php
session_start();
require_once 'backend/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$career = $_GET['career'] ?? '';

if (!$user_id || !$career) {
    header('Location: career_guide.php');
    exit();
}

if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    header('Content-Type: application/json');
    $career = $_GET['career'] ?? '';

    if (!$career) {
        echo json_encode(['error' => 'No career provided']);
        exit;
    }

    // Check DB
    $stmt = $conn->prepare("SELECT roadmap_text FROM saved_roadmaps WHERE user_id = ? AND career_title = ?");
    $stmt->bind_param("is", $user_id, $career);
    $stmt->execute();
    $stmt->bind_result($savedRoadmap);
    $stmt->fetch();
    $stmt->close();

    if (!$savedRoadmap) {
        // Call Gemini API
        $apiUrl = "http://localhost/fyp2/backend/gemini_roadmap_api.php";
        $postData = json_encode(["career" => $career]);
        $options = [
            "http" => [
                "header"  => "Content-Type: application/json\r\n",
                "method"  => "POST",
                "content" => $postData,
            ],
        ];
        $context  = stream_context_create($options);
        $result = file_get_contents($apiUrl, false, $context);

        $data = json_decode($result, true);
        if (isset($data['steps'])) {
            $savedRoadmap = implode("\n", $data['steps']);
            $stmt = $conn->prepare("INSERT INTO saved_roadmaps (user_id, career_title, roadmap_text) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $user_id, $career, $savedRoadmap);
            $stmt->execute();
            $stmt->close();
        }
    }

    echo json_encode(['roadmap' => $savedRoadmap ?: '']);
    exit;
}


// Check if roadmap already exists
$stmt = $conn->prepare("SELECT roadmap_text FROM saved_roadmaps WHERE user_id = ? AND career_title = ?");
$stmt->bind_param("is", $user_id, $career);
$stmt->execute();
$stmt->bind_result($savedRoadmap);
$stmt->fetch();
$stmt->close();

// Generate roadmap if not found
if (!$savedRoadmap) {
    $apiUrl = "http://localhost/fyp2/backend/gemini_roadmap_api.php"; // adjust path if needed

    $postData = json_encode(["career" => $career]);

    $options = [
        "http" => [
            "header"  => "Content-Type: application/json\r\n",
            "method"  => "POST",
            "content" => $postData,
        ],
    ];

    $context  = stream_context_create($options);
    $result = file_get_contents($apiUrl, false, $context);

    if ($result !== false) {
        $data = json_decode($result, true);

        if (isset($data['steps']) && !empty($data['steps'])) {
            $roadmap = implode("\n", $data['steps']);

            $stmt = $conn->prepare("INSERT INTO saved_roadmaps (user_id, career_title, roadmap_text) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $user_id, $career, $roadmap);
            $stmt->execute();
            $stmt->close();

            $savedRoadmap = $roadmap;
        } else {
            $error = "Gemini API returned no steps.";
        }
    } else {
        $error = "Failed to contact roadmap API.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Career Roadmap - <?php echo htmlspecialchars($career); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        .roadmap-section {
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
        }
        .Beginner { background-color: #e3f2fd; }
        .Intermediate { background-color: #fcefc5ff; }
        .Advanced { background-color: #f7cccfff; }
        .progress-container {
            margin: 30px 0;
        }
        .checklist-item {
            margin-bottom: 10px;
        }
        body {
            background-color: #e79ad7ff;
        }
        .sidebar{
                background-color: #e2c3daff;
            }
    </style>
</head>
<body class="">
<div class="container py-5">
    <h2 class="mb-4">Career Roadmap for <span class="text-primary"><?php echo htmlspecialchars($career); ?></span></h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php elseif ($savedRoadmap): ?>
        <div id="roadmap-container">
            <?php
            $sections = preg_split('/\r\n|\r|\n/', trim($savedRoadmap));
            $currentSection = '';
            $sectionData = ['Beginner' => [], 'Intermediate' => [], 'Advanced' => []];

            foreach ($sections as $line) {
                $line = trim($line);
                if (in_array($line, ['Beginner', 'Intermediate', 'Advanced'])) {
                    $currentSection = $line;
                } elseif (!empty($line) && $currentSection) {
                    $sectionData[$currentSection][] = $line;
                }
            }

            $totalItems = 0;
            foreach ($sectionData as $level => $items): ?>
                <div class="roadmap-section <?php echo $level; ?>">
                    <h4><?php echo $level; ?> Level</h4>
                    <?php foreach ($items as $item): $totalItems++; ?>
                        <div class="form-check checklist-item">
                            <input class="form-check-input roadmap-checkbox" type="checkbox" id="<?php echo md5($item); ?>">
                            <label class="form-check-label" for="<?php echo md5($item); ?>">
                                <?php echo htmlspecialchars($item); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="progress-container">
            <label>Overall Progress:</label>
            <div class="progress">
                <div id="overall-progress-bar" class="progress-bar bg-success" role="progressbar" style="width: 0%">
                    0%
                </div>
            </div>
        </div>

        <button class="btn btn-outline-primary mt-3" onclick="exportPDF()">Export as PDF</button>
    <?php else: ?>
        <div class="alert alert-warning">No roadmap generated yet.</div>
    <?php endif; ?>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
    const checkboxes = document.querySelectorAll('.roadmap-checkbox');
    const progressBar = document.getElementById('overall-progress-bar');

    function updateProgress() {
        let checked = 0;
        checkboxes.forEach(box => {
            if (box.checked) checked++;
        });
        const percent = Math.round((checked / checkboxes.length) * 100);
        progressBar.style.width = percent + '%';
        progressBar.innerText = percent + '%';
    }

    checkboxes.forEach(box => box.addEventListener('change', updateProgress));
    updateProgress();

    function exportPDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        doc.text("Career Roadmap for <?php echo htmlspecialchars($career); ?>", 10, 10);
        let y = 20;

        document.querySelectorAll('.roadmap-section').forEach(section => {
            const level = section.querySelector('h4').innerText;
            doc.setFont(undefined, 'bold');
            doc.text(level, 10, y);
            y += 7;
            doc.setFont(undefined, 'normal');
            section.querySelectorAll('label').forEach(label => {
                doc.text('- ' + label.innerText, 12, y);
                y += 7;
            });
            y += 5;
        });

        doc.save("career_roadmap_<?php echo strtolower($career); ?>.pdf");
    }
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).on("click", ".select-career-btn", function () {
    let career = $(this).data("career");
    let roadmap = $(this).data("roadmap");

    $.ajax({
        url: "backend/save_selected_career.php",
        type: "POST",
        data: { career: career, roadmap: roadmap },
        success: function (response) {
            if (response.trim() === "success") {
                alert("Career saved successfully ✅");
                location.reload(); // refresh to show the selected career block
            } else {
                alert("Failed to save career ❌ " + response);
            }
        }
    });
});
</script>

</body>
</html>
