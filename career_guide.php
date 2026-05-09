<?php
session_start();
include 'backend/db.php';
include 'backend/config.php';
include 'backend/gemini_ai.php';
include 'backend/log_activity.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Student';

// 0. Fetch selected career (if any)
$selected_career_title = '';
$stmt = $conn->prepare("SELECT career_title FROM selected_career WHERE user_id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($selected_career_title);
$stmt->fetch();
$stmt->close();


// === Fetch dynamic student data ===

// 1. Degree from academic_info
$degree = '';
$stmt = $conn->prepare("SELECT degree_program FROM academic_info WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($degree);
$stmt->fetch();
$stmt->close();

// 2. Subjects and marks
$subjects = [];
$stmt = $conn->prepare("SELECT subject_name, marks FROM subjects WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $subjects[$row['subject_name']] = $row['marks'];
}
$stmt->close();

// 3. Interests
$interests = [];
$stmt = $conn->prepare("SELECT interest FROM student_interests WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $interests[] = $row['interest'];
}
$stmt->close();

// 4. Goals
$goals = [];
$stmt = $conn->prepare("SELECT title FROM goals WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $goals[] = $row['title'];
}
$stmt->close();

// === Build the prompt ===
$subjectText = '';
foreach ($subjects as $subject => $mark) {
    $subjectText .= "$subject: $mark, ";
}
$prompt = "I am a $degree student. My subjects and marks are: $subjectText. My interests are: " . implode(', ', $interests) . ". My goals are: " . implode(', ', $goals) . ". 

Please suggest ONE most suitable career path and return in the following format:

Title: <career title>
Summary: <one line summary explaining the fit>
Breakdown:
- <reason 1>
- <reason 2>
- <reason 3>";

$relatedPrompt = "I am a $degree student. My subjects and marks are: $subjectText. My interests are: " . implode(', ', $interests) . ". Based on this, suggest 6 alternative career options that I might also consider (different from the main suggestion). 

Return only the titles with 1-line descriptions in this format:

- <Title>: <Short Description>";

$popularPrompt = "List 6 of the most popular and in-demand careers in the world this month. For each, provide a one-line description and mention if it's known for high demand or high salary. 

Return only in this format:
- <Career Title>: <Short Description> (High Demand or High Salary)";



// === Get suggestion from Gemini AI ===
$suggestion = getCareerSuggestionFromGemini($prompt);

$relatedCareersRaw = getCareerSuggestionFromGemini($relatedPrompt);

$popularCareersRaw = getCareerSuggestionFromGemini($popularPrompt);


$related_careers = [];

if (!str_starts_with($relatedCareersRaw, 'API Error:') && !str_starts_with($relatedCareersRaw, 'Request Error:')) {
    $lines = explode("\n", trim($relatedCareersRaw));
    foreach ($lines as $line) {
        if (preg_match('/^\-\s*(.+?):\s*(.+)$/', $line, $matches)) {
            $related_careers[] = [
                'title' => trim(str_replace('*', '', $matches[1])),
                'desc' => trim(str_replace('*', '', $matches[2])),
                'slug' => strtolower(str_replace(' ', '-', $matches[1]))
            ];
        }
    }
}

$popular_careers = [];

if (!str_starts_with($popularCareersRaw, 'API Error:') && !str_starts_with($popularCareersRaw, 'Request Error:')) {
    $lines = explode("\n", trim($popularCareersRaw));
    foreach ($lines as $line) {
        if (preg_match('/^\-\s*(.+?):\s*(.+?)(?:\s*\((.+?)\))?$/', $line, $matches)) {
            $popular_careers[] = [
                'title' => trim(str_replace('*', '', $matches[1])),
                'desc' => trim(str_replace('*', '', $matches[2])),
                'tag'  => isset($matches[3]) ? trim($matches[3]) : ''
            ];
        }
    }
}




if (str_starts_with($suggestion, 'API Error:') || str_starts_with($suggestion, 'Request Error:')) {
    $career_title = "Oops! AI Error";
    $career_summary = $suggestion;
    $career_details = "";
} else {
    file_put_contents('ai_debug_response.txt', $suggestion); // debug log

    // Extract fields from AI response
    preg_match('/Title:\s*(.+)/i', $suggestion, $titleMatch);
    preg_match('/Summary:\s*(.+)/i', $suggestion, $summaryMatch);
    preg_match('/Breakdown:\s*((?:- .+\n?)*)/i', $suggestion, $breakdownMatch);

    // Clean and format breakdown with bold tags and emojis
    if (!empty($breakdownMatch[1])) {
        // Map keywords → emojis
        $emoji_map = [
            'web'        => '💻',
            'design'     => '🎨',
            'cyber'      => '🔐',
            'security'   => '🛡️',
            'growth'     => '🌱',
            'goals'      => '🎯',
            'demand'     => '📈',
            'strength'   => '💪',
            'interest'   => '🔗',
            'future'     => '🚀',
        ];

        // Convert **Markdown** → <strong> + emoji
        $breakdown_clean = preg_replace_callback('/\*\*(.*?)\*\*/', function ($m) use ($emoji_map) {
            $heading = rtrim($m[1], ':');
            $emoji = '💡';
            foreach ($emoji_map as $key => $symbol) {
                if (stripos($heading, $key) !== false) {
                    $emoji = $symbol;
                    break;
                }
            }
            return $emoji . ' <strong>' . htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') . ':</strong>';
        }, $breakdownMatch[1]);

        // Turn each line into list item
        $lines = preg_split('/\r\n|\r|\n/', trim($breakdown_clean));
        $career_details = "<ul class='mb-0'>";
        foreach ($lines as $line) {
            if (trim($line) !== '') {
                $career_details .= '<li>' . ltrim($line, "- \t") . '</li>';
            }
        }
        $career_details .= '</ul>';
    } else {
        $career_details = 'No breakdown provided.';
    }
}



    



    // Cleanup title
    $career_title = isset($titleMatch[1]) 
    ? htmlspecialchars(trim(preg_replace('/\*+/', '', $titleMatch[1])), ENT_QUOTES, 'UTF-8') 
    : 'Unknown';


    // Short summary (clean and trimmed to ~150 characters, ends with a full stop)
$summary_raw = isset($summaryMatch[1]) ? trim($summaryMatch[1]) : 'No summary available.';
$summary_clean = strip_tags($summary_raw);
$summary_clean = preg_replace('/\*+/', '', $summary_clean); // remove markdown asterisks
$summary_clean = preg_replace('/\s+/', ' ', $summary_clean); // remove extra spaces


if (strlen($summary_clean) > 150) {
    $summary_trimmed = substr($summary_clean, 0, strrpos(substr($summary_clean, 0, 150), ' ')) . '.';
} else {
    $summary_trimmed = rtrim($summary_clean, '.') . '.';
}

$career_summary = $summary_trimmed;





?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Career Guide</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .icon-circle { width: 60px; height: 60px; background-color: rgb(187, 236, 243); border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: background-color 0.3s, box-shadow 0.3s; }
        .icon-circle:hover { background-color: #b2ebf2; box-shadow: 0 0 12px rgba(0, 0, 0, 0.2); }
        #overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.3); display: none; z-index: 998; }
        #overlay.active { display: block; }
        .sidebar.active { left: 0; }
        .sidebar { transition: left 0.3s ease-in-out; }
        @media (max-width: 768px) { .sidebar { position: fixed; top: 0; left: -250px; width: 250px; height: 100%; background-color: white; z-index: 999; overflow-y: auto; } }
        .career-badge { font-size: 0.9rem; padding: 0.25em 0.5em; background-color: #e7f1ff; border-radius: 6px; color: #007bff; }
        .card:hover { transform: translateY(-5px); transition: all 0.3s ease; }
        .badge.bg-success {
    background-color: #28a745 !important;
}
.badge.bg-warning {
    background-color: #ffc107 !important;
    color: #212529;
}
.popular-career-card {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 18px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    padding: 25px;
    color: #fff;
    background-image: linear-gradient(135deg, #6e8efb, #a777e3);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.popular-career-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 25px rgba(0,0,0,0.2);
}
.popular-career-card h4 {
    font-size: 20px;
    font-weight: bold;
    margin-bottom: 10px;
}
.popular-career-card .badge {
    font-size: 13px;
    background: #fff;
    color: white;
    padding: 4px 10px;
    border-radius: 12px;
    margin-left: 10px;
}
.btn-outline-white {
    color: white;
    border: 1px solid white;
    background-color: transparent;
}

.btn-outline-white:hover {
    color: #000;
    background-color: white;
    border-color: white;
}
.swiper-slide {
      display: flex;
      justify-content: center;
      align-items: center;
    }
    .career-card {
      width: 100%;
      max-width: 800px;
      height: auto;
    }
    .career-img {
      max-height: 300px;
      object-fit: cover;
      border-radius: 15px;
    }
    .badge {
      margin-right: 5px;
    }
    body {
            background-color: #fae2ebff;
        }
        .sidebar{
            background-color: #FFC8DD;
        }
.hero-section {
    position: relative;
    background: url('images/career-guide.jpg') center/cover no-repeat;
    padding: 130px 20px 100px;
    border-radius: 15px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.hero-section::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    width: 100%;
    background-color: rgba(0, 0, 0, 0.55); /* dark overlay */
    z-index: 0;
}

.hero-section .content {
    position: relative;
    z-index: 1;
    text-align: center;
    padding: 0 15px;
    color: #ffffff;
}

.hero-section .content h1 {
    font-weight: 800;
    font-size: 3.2rem;
    margin-bottom: 5px;
    color: #ffffff;
    text-shadow: 2px 2px 12px rgba(0, 0, 0, 0.7);
}

.hero-section .content p {
    font-size: 1.3rem;
    font-weight: 500;
    color: #ffffff;
    max-width: 800px;
    
    text-shadow: 1px 1px 10px rgba(0, 0, 0, 0.6);
}

.btn-success {
    transition: all 0.3s ease-in-out;
}

.btn-success:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 15px rgba(0, 128, 0, 0.3);
}

/* Career Cards Section */
.career-card {
    border: none;
    border-radius: 18px;
    background: linear-gradient(145deg, #ffffff, #f3f6ff);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    overflow: hidden;
    position: relative;
}

/* Floating Icon Style */
.career-icon {
    font-size: 40px;
    color: #0d6efd;
    margin-bottom: 15px;
    transition: transform 0.3s ease;
}

/* Hover Effect */
.career-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

.career-card:hover .career-icon {
    transform: scale(1.1) rotate(5deg);
}

.career-card .card-body {
    padding: 1.8rem;
    text-align: center;
}

.career-card .card-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #0d6efd;
    margin-bottom: 0.6rem;
}

.career-card .card-text {
    font-size: 0.95rem;
    color: #444;
    min-height: 65px;
}

/* Stylish Button */
.career-card .btn {
    border-radius: 25px;
    padding: 0.45rem 1.2rem;
    font-weight: 500;
    transition: background-color 0.2s ease, color 0.2s ease;
}

.career-card .btn:hover {
    background-color: #0d6efd;
    color: #fff;
}


</style>


</head>

<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 sidebar p-3" id="sidebar">
            <div class="text-center mb-4">
                <a href="dashboard.php" class="text-decoration-none text-dark d-flex align-items-center justify-content-center">
                    <div class="icon-circle me-2">
                        <i class="fas fa-graduation-cap fa-2x"></i>
                    </div>
                    <div class="d-flex flex-column text-start">
                        <span class="fw-bold">AI</span>
                        <span class="fw-bold">Student</span>
                        <span class="fw-bold">Guide</span>
                    </div>
                </a>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fas fa-chart-line me-2"></i>Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="progress.php"><i class="fas fa-tasks me-2"></i>Progress</a></li>
                <li class="nav-item"><a class="nav-link" href="goals.php"><i class="fas fa-bullseye me-2"></i>Goals</a></li>
                <li class="nav-item"><a class="nav-link active" href="career_guide.php"><i class="fas fa-route me-2"></i>Career Guide</a></li>
                <li class="nav-item"><a class="nav-link" href="view_subjects.php"><i class="fas fa-book me-2"></i>Subjects</a></li>
                <li class="nav-item"><a class="nav-link" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="col-md-10 content position-relative">
            <!-- Topbar -->
            <div class="d-flex justify-content-between align-items-center py-3 px-4 px-md-4 ps-2 ps-md-4 border-bottom">
                <div class="d-flex align-items-center">
                    <button id="sidebarToggle" class="btn me-1 d-md-none">
                        <i class="fas fa-bars fa-lg"></i>
                    </button>
                    <h4 class="mb-0">Career Guide - <?php echo htmlspecialchars($user_name); ?></h4>
                </div>
                <div class="flex-grow-1"></div>
                <div class="d-flex align-items-center">
                    <i class="fas fa-bell me-3"></i>
                    <a href="profile.php" class="text-dark text-decoration-none">
                        <i class="fas fa-user fa-lg"></i>
                    </a>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="container my-4">
                <!-- Hero / Introduction Section -->
                <div class="hero-section text-center mb-5">
                    <div class="content">
                        <h1>Career Guide</h1>
                        <p>Not sure what to pursue? Let’s help you find your perfect career path based on your academic performance and interests.</p>
                    </div>
                </div>


                <?php if (!empty($selected_career_title)): ?>
                    <div class="p-4 rounded-4 text-center shadow-lg mt-4" style="background: linear-gradient(135deg, #c7f9cc, #d0f4de); border: 2px solid #b2f7ef;">
                        <h5 class="mb-2">🎯 You’ve already selected a career:</h5>
                        
                        <h2 class="fw-bold text-success mb-3">
                            <?= htmlspecialchars($selected_career_title) ?> ✅
                        </h2>

                        <form action="career_roadmap.php" method="get">
                            <input type="hidden" name="career" value="<?= htmlspecialchars($selected_career_title) ?>">
                            <button type="submit" class="btn btn-success px-4 py-2 rounded-pill shadow-sm fw-semibold">
                                📍 View Saved Roadmap
                            </button>
                        </form>
                    </div>
                <?php endif; ?>


                <!-- AI Suggested Career -->

<div class="alert alert-primary text-center shadow-sm">
    <h4 class="mb-1 c-title">Your Recommended Career</h4>
    <h2 class="fw-semibold"><?= htmlspecialchars($career_title) ?></h2>
    <p class="text-muted px-4 py-3"><?= $career_summary ?></p>

    <a href="#" id="toggleBreakdown" class="btn btn-link btn-sm">View Full Breakdown</a>
    <form id="careerForm">
  <input type="hidden" name="career" value="<?= htmlspecialchars($career_title) ?>">
  <button class="btn btn-primary select-career-btn"
        data-career="<?= htmlspecialchars($career_title) ?>"
        data-roadmap="<?= htmlspecialchars($roadmap) ?>">
    Select this career
</button>

</form>

<div id="loading" style="display:none;" class="alert alert-info mt-3 text-center">
    <div class="spinner-border text-primary" role="status"></div>
    <span class="ms-2">Generating roadmap… please wait</span>
</div>

<script>
document.getElementById("careerForm").addEventListener("submit", function(e) {
    e.preventDefault();
    const career = this.querySelector("input[name=career]").value;

    // Show loading spinner
    document.getElementById("loading").style.display = "block";

    fetch("career_roadmap.php?ajax=1&career=" + encodeURIComponent(career))
        .then(res => res.json())
        .then(data => {
            document.getElementById("loading").style.display = "none";
            if (data.roadmap) {
                // Redirect to roadmap page normally
                window.location.href = "career_roadmap.php?career=" + encodeURIComponent(career);
            } else {
                alert("Failed to generate roadmap. Try again.");
            }
        })
        .catch(err => {
            document.getElementById("loading").style.display = "none";
            alert("Error contacting server.");
            console.error(err);
        });
});
</script>






    <div id="breakdownContent" class="mt-3 text-start" style="display:none;">
        <div class="card card-body bg-light border-0">
<?= $career_details ?>


        </div>
    </div>

</div>

<script>
    document.getElementById('toggleBreakdown').addEventListener('click', function (e) {
        e.preventDefault();
        const content = document.getElementById('breakdownContent');
        if (content.style.display === 'none') {
            content.style.display = 'block';
            this.textContent = 'Hide Breakdown';
        } else {
            content.style.display = 'none';
            this.textContent = 'View Full Breakdown';
        }
    });
</script>




                <!-- Recalculate Button -->
                <div class="text-center mb-5">
                    <a href="career_guide.php" class="btn btn-outline-secondary btn-sm">Recalculate Suggestion</a>
                </div>

                <div class="container my-5">
  <h2 class="text-center mb-4">🎯 Trending in 2025</h2>
  <div class="swiper mySwiper">
    <div class="swiper-wrapper">

      <!-- Career 1 -->
      <div class="swiper-slide">
        <div class="card career-card shadow-lg p-4">
          <h3>AI / Machine Learning Engineer</h3>
          <p>Build smart systems that learn and improve automatically — from recommendation engines to self-driving tech.</p>
          <p>
            <span class="badge bg-warning text-dark">🔥 Trending</span>
            <span class="badge bg-success">💸 High Salary</span>
            <span class="badge bg-primary">🧠 Complex but Rewarding</span>
          </p>
          <img src="images/robot.jpg" alt="AI Engineer" class="img-fluid career-img" />
        </div>
      </div>

      <!-- Career 2 -->
      <div class="swiper-slide">
        <div class="card career-card shadow-lg p-4">
          <h3>Data Scientist / Data Analyst</h3>
          <p>Turn raw data into powerful insights that guide business strategies and product decisions.</p>
          <p>
            <span class="badge bg-info text-dark">📊 In-Demand</span>
            <span class="badge bg-success">💼 Remote-Friendly</span>
            <span class="badge bg-danger">💸 High Salary</span>
          </p>
          <img src="images/data analyst.jpg" alt="Data Scientist" class="img-fluid career-img" />
        </div>
      </div>

      <!-- Career 3 -->
      <div class="swiper-slide">
        <div class="card career-card shadow-lg p-4">
          <h3>Web Developer (Frontend/Backend)</h3>
          <p>Create and maintain websites and web applications, ensuring functionality and great user experience.</p>
          <p>
            <span class="badge bg-secondary">🌐 Universal Skill</span>
            <span class="badge bg-primary">👩‍💻 Beginner-Friendly</span>
            <span class="badge bg-success">💼 Freelance Options</span>
          </p>
          <img src="images/web developer.jpg" alt="Web Developer" class="img-fluid career-img" />
        </div>
      </div>

      <!-- Career 4 -->
      <div class="swiper-slide">
        <div class="card career-card shadow-lg p-4">
          <h3>UI/UX Designer</h3>
          <p>Design visually appealing and user-friendly interfaces for websites and mobile apps.</p>
          <p>
            <span class="badge bg-info text-dark">🎨 Creative Field</span>
            <span class="badge bg-primary">📱 App & Web</span>
            <span class="badge bg-warning text-dark">🧠 Human-centered</span>
          </p>
          <img src="images/ux.jpg" alt="UI/UX Designer" class="img-fluid career-img" />
        </div>
      </div>

      <!-- Career 5 -->
      <div class="swiper-slide">
        <div class="card career-card shadow-lg p-4">
          <h3>Cybersecurity Specialist</h3>
          <p>Protect systems and networks from cyber threats, ensuring data security and privacy.</p>
          <p>
            <span class="badge bg-danger">🛡️ High Demand</span>
            <span class="badge bg-dark">💼 Government + Corporate</span>
            <span class="badge bg-success">🧠 Critical Thinking</span>
          </p>
          <img src="images/cyber.jpg" alt="Cybersecurity" class="img-fluid career-img" />
        </div>
      </div>

      <!-- Career 6 -->
      <div class="swiper-slide">
        <div class="card career-card shadow-lg p-4">
          <h3>Cloud Computing / DevOps</h3>
          <p>Work with cloud platforms (AWS, Azure) and streamline software delivery using DevOps practices.</p>
          <p>
            <span class="badge bg-warning text-dark">☁️ Future-Proof</span>
            <span class="badge bg-info text-dark">⚙️ Infrastructure</span>
            <span class="badge bg-success">💰 High Paying</span>
          </p>
          <img src="images/cloud.jpg" alt="Cloud Computing" class="img-fluid career-img" />
        </div>
      </div>

      <!-- Career 7 -->
      <div class="swiper-slide">
        <div class="card career-card shadow-lg p-4">
          <h3>Mobile App Developer</h3>
          <p>Build applications for iOS and Android using tools like React Native, Flutter, or native code.</p>
          <p>
            <span class="badge bg-primary">📱 High Usage</span>
            <span class="badge bg-warning text-dark">🌍 Wide Reach</span>
            <span class="badge bg-success">💼 Startup-Friendly</span>
          </p>
          <img src="images/mobile.jpg" alt="Mobile Developer" class="img-fluid career-img" />
        </div>
      </div>

      <!-- Career 8 -->
      <div class="swiper-slide">
        <div class="card career-card shadow-lg p-4">
          <h3>Digital Marketing Specialist</h3>
          <p>Use SEO, content marketing, and social media to promote brands and grow businesses online.</p>
          <p>
            <span class="badge bg-info text-dark">📈 High Growth</span>
            <span class="badge bg-primary">🧠 Strategy-Based</span>
            <span class="badge bg-success">💻 Work from Anywhere</span>
          </p>
          <img src="images/social.jpg" alt="Digital Marketing" class="img-fluid career-img" />
        </div>
      </div>

      <!-- Career 9 -->
      <div class="swiper-slide">
        <div class="card career-card shadow-lg p-4">
          <h3>Product Manager</h3>
          <p>Bridge the gap between tech, business, and users to lead the development of successful products.</p>
          <p>
            <span class="badge bg-secondary">🧠 Vision + Strategy</span>
            <span class="badge bg-warning text-dark">🎯 Leadership Role</span>
            <span class="badge bg-success">💼 Managerial Growth</span>
          </p>
          <img src="images/product.jpg" alt="Product Manager" class="img-fluid career-img" />
        </div>
      </div>

      <!-- Career 10 -->
      <div class="swiper-slide">
        <div class="card career-card shadow-lg p-4">
          <h3>Tech Entrepreneur / Startup Founder</h3>
          <p>Build your own product or service by solving a problem through tech innovation.</p>
          <p>
            <span class="badge bg-danger">🚀 Risk + Reward</span>
            <span class="badge bg-success">🌱 High Growth</span>
            <span class="badge bg-dark">💡 Creative Freedom</span>
          </p>
          <img src="images/enterpreneur.jpg" alt="Tech Entrepreneur" class="img-fluid career-img" />
        </div>
      </div>

    </div>

    <!-- Swiper Controls -->
    <div class="swiper-button-next"></div>
    <div class="swiper-button-prev"></div>
    <div class="swiper-pagination"></div>
  </div>
</div>

                <!-- Career Explorer -->
<h3 class="mb-3">Explore Other Career Options</h3>
<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
    <?php if (empty($related_careers)): ?>
        <p class="text-muted">No related careers found at the moment.</p>
    <?php endif; ?>

    <?php foreach ($related_careers as $career): ?>
        <?php
        $career_title = strtolower($career['title']);

        // Default icon
        $icon_class = 'fas fa-briefcase';

        // Dynamic icon mapping based on keywords in the career title
        if (strpos($career_title, 'software') !== false || strpos($career_title, 'developer') !== false || strpos($career_title, 'programmer') !== false) {
            $icon_class = 'fas fa-laptop-code';
        } elseif (strpos($career_title, 'doctor') !== false || strpos($career_title, 'medical') !== false) {
            $icon_class = 'fas fa-stethoscope';
        } elseif (strpos($career_title, 'design') !== false || strpos($career_title, 'artist') !== false) {
            $icon_class = 'fas fa-paint-brush';
        } elseif (strpos($career_title, 'teacher') !== false || strpos($career_title, 'lecturer') !== false) {
            $icon_class = 'fas fa-chalkboard-teacher';
        } elseif (strpos($career_title, 'data') !== false || strpos($career_title, 'analytics') !== false) {
            $icon_class = 'fas fa-database';
        } elseif (strpos($career_title, 'business') !== false || strpos($career_title, 'manager') !== false) {
            $icon_class = 'fas fa-briefcase';
        } elseif (strpos($career_title, 'law') !== false || strpos($career_title, 'legal') !== false) {
            $icon_class = 'fas fa-gavel';
        }
        ?>
        
        <div class="col">
            <div class="card career-card h-100 shadow-sm">
                <div class="card-body">
                    <!-- Dynamic Icon -->
                    <i class="career-icon <?php echo $icon_class; ?>"></i>

                    <h5 class="card-title"><?php echo $career['title']; ?></h5>
                    <p class="card-text"><?php echo $career['desc']; ?></p>

                    <form action="career_roadmap.php" method="get">
                        <input type="hidden" name="career" value="<?php echo htmlspecialchars($career['title']); ?>">
                        <button type="submit" class="btn btn-outline-primary">Select this Career</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>


                    <!-- Most Popular Career Trends Section -->
<h3 class="mt-5 mb-3">🌟 Most Popular Career Trends</h3>
<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 ">
    <?php if (empty($popular_careers)): ?>
        <p class="text-muted">No trending careers available right now.</p>
    <?php endif; ?>

    <?php foreach ($popular_careers as $career): ?>
        <div class="col">
            <div class="card h-100 border shadow-sm">
                <div class="card-body popular-career-card">
                    <h5 class="card-title"><?php echo htmlspecialchars($career['title']); ?></h5>
                    <p class="card-text text-dark"><?php echo htmlspecialchars($career['desc']); ?></p>
                    <?php if (!empty($career['tag'])): ?>
                        <span class="badge bg-<?php echo stripos($career['tag'], 'salary') !== false ? 'success' : 'warning'; ?>">
                            <?php echo htmlspecialchars($career['tag']); ?>
                        </span>
                    <?php endif; ?>
                    <form action="career_roadmap.php" method="get">
    <input type="hidden" name="career" value="<?php echo htmlspecialchars($career['title']); ?>">
    <button type="submit" class="btn btn-outline-primary">Select this Career</button>
</form>




                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>



<!-- Swiper JS -->
<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>



<!-- Swiper Config -->
<script>

  var swiper = new Swiper(".mySwiper", {
    slidesPerView: 1,
    spaceBetween: 30,
    loop: true,
    pagination: {
      el: ".swiper-pagination",
      clickable: true,
    },
    navigation: {
      nextEl: ".swiper-button-next",
      prevEl: ".swiper-button-prev",
    },
  });
</script>


                </div>
            </div>
        </div>
    </div>
</div>

<div id="overlay"></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');

    sidebarToggle.addEventListener('click', function () {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    });

    document.addEventListener('click', function (event) {
        if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        }
    });

    overlay.addEventListener('click', function () {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
    });

   
</script>

<script>
document.querySelectorAll('.career-selection-form').forEach(form => {
    form.querySelector('button').addEventListener('click', function () {
        const careerTitle = form.getAttribute('data-career-title');

        fetch('backend/save_selected_career.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `career_title=${encodeURIComponent(careerTitle)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                window.location.href = 'career_roadmap.php'; // Redirect on success
            } else {
                alert(data.message || 'Something went wrong!');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Something went wrong!');
        });
    });
});
</script>



</body>
</html>