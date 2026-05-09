<?php  
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}
include 'backend/db.php';

include_once 'backend/config.php';
include_once 'backend/gemini_ai.php';
include 'backend/log_activity.php';

$user_id = $_SESSION['user_id'];

if (isset($_POST['refresh_ai'])) {
    $conn->query("UPDATE users SET ai_suggestion = NULL WHERE id = $user_id");
    header("Location: dashboard.php"); // reload to trigger fresh suggestion
    exit;
}


// Check if AI suggestion already saved
$checkStmt = $conn->prepare("SELECT ai_suggestion FROM users WHERE id = ?");
$checkStmt->bind_param("i", $user_id);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();
$existing = $checkResult->fetch_assoc();
$checkStmt->close();

if (empty($existing['ai_suggestion'])) {
    // Fetch subject data
    $subjectData = [];
    $stmt = $conn->prepare("SELECT subject_name, marks FROM subjects WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $subjectData[] = $row['subject_name'] . " (" . $row['marks'] . ")";
    }
    $stmt->close();

    // Fetch interests
    $interests = [];
    $res = $conn->prepare("SELECT interest FROM student_interests WHERE user_id = ?");
    $res->bind_param("i", $user_id);
    $res->execute();
    $interestResult = $res->get_result();
    while ($row = $interestResult->fetch_assoc()) {
        $interests[] = $row['interest'];
    }
    $res->close();

    // Create prompt
    $prompt = "Give exactly 3 very short study suggestions for today (4 to 6 words each). No introductions, no titles, no stars, no extra text. Format them like: - Suggestion 1\\n- Suggestion 2\\n- Suggestion 3 " . implode(', ', $subjectData) . " and interests: " . implode(', ', $interests) . ".";

    // Get Gemini suggestion
    $suggestion = getCareerSuggestionFromGemini($prompt);

    // Save to database
    $saveStmt = $conn->prepare("UPDATE users SET ai_suggestion = ? WHERE id = ?");
    $saveStmt->bind_param("si", $suggestion, $user_id);
    $saveStmt->execute();
    $saveStmt->close();
}


$showSubjectModal = false;

// Check if user has any subjects
$subjectCheckStmt = $conn->prepare("SELECT COUNT(*) as total FROM subjects WHERE user_id = ?");
$subjectCheckStmt->bind_param("i", $_SESSION['user_id']);
$subjectCheckStmt->execute();
$result = $subjectCheckStmt->get_result();
$row = $result->fetch_assoc();
$subjectCount = $row['total'];
$subjectCheckStmt->close();

// Show modal if subject count is 0
if ($subjectCount == 0) {
    $showSubjectModal = true;
}



$user_id = $_SESSION['user_id'];
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Student';

$subjectNames = [];
$subjectMarks = [];

$stmt = $conn->prepare("SELECT subject_name, marks FROM subjects WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $subjectNames[] = $row['subject_name'];
    $subjectMarks[] = $row['marks'];
}

$stmt->close();

// Fetch goal counts by status
$pendingCount = 0;
$inProgressCount = 0;

$goalStmt = $conn->prepare("SELECT status, COUNT(*) as count FROM goals WHERE user_id = ? GROUP BY status");
$goalStmt->bind_param("i", $user_id);
$goalStmt->execute();
$goalResult = $goalStmt->get_result();

while ($row = $goalResult->fetch_assoc()) {
    $status = strtolower(trim($row['status'])); // makes it case-insensitive and removes extra spaces

    if ($status === 'pending') {
        $pendingCount = $row['count'];
    } elseif ($status === 'in progress') {
        $inProgressCount = $row['count'];
    }
}

$goalStmt->close();

$daysOfWeek = ['S', 'M', 'T', 'W', 'T', 'F', 'S'];
$today = new DateTime();
$user_id = $_SESSION['user_id'];

// Get activity for the past 7 days
$weekStart = (clone $today)->modify('last sunday')->format('Y-m-d');
$weekEnd = (clone $today)->modify('next saturday')->format('Y-m-d');

$activityStmt = $conn->prepare("
    SELECT activity_date FROM user_activity 
    WHERE user_id = ? AND activity_date BETWEEN ? AND ?
");
$activityStmt->bind_param("iss", $user_id, $weekStart, $weekEnd);
$activityStmt->execute();
$activityResult = $activityStmt->get_result();

$activeDays = [];
while ($row = $activityResult->fetch_assoc()) {
    $activeDays[] = $row['activity_date'];
}
$activityStmt->close();

// Generate activity map for current week
$weekActivity = [];
$startOfWeek = new DateTime($weekStart);
for ($i = 0; $i < 7; $i++) {
    $day = clone $startOfWeek;
    $day->modify("+$i days");
    $dateStr = $day->format('Y-m-d');
    $weekActivity[] = in_array($dateStr, $activeDays);
}

// Calculate current streak
$streak = 0;
$streakDay = clone $today;
while (in_array($streakDay->format('Y-m-d'), $activeDays)) {
    $streak++;
    $streakDay->modify('-1 day');
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #fae2ebff;
        }
        .goal-card {
            margin: 0 auto;
            animation: zoomIn 0.6s ease;
        }
        @keyframes zoomIn {
            0% {
                transform: scale(0.8);
                opacity: 0;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
        .goal-chart-container {
            position: relative;
            width: 100%;
            max-width: 215px;
            margin: 0 auto;
            height: 215px;
            
        }
        
        @media (max-width: 576px) {
            .goal-card {
                max-width: 280px;
            }
            .goal-chart-container {
                max-width: 165px;
                height: 160px;
                
            }
        }
        .dashboard-card {
            min-height: 200px;
        }
        .btn-outline-primary:hover {
    background-color: #0d6efd;
    color: white;
}
.streak-icon i {
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}
.sidebar{
    background-color: #FFC8DD;
}
    
    </style>

    
</head>
<body>

<div class="container-fluid">
    <div class="row">
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
                <li class="nav-item"><a class="nav-link active" href="dashboard.php"><i class="fas fa-chart-line me-2"></i>Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="progress.php"><i class="fas fa-tasks me-2"></i>Progress</a></li>
                <li class="nav-item"><a class="nav-link" href="goals.php"><i class="fas fa-bullseye me-2"></i>Goals</a></li>
                <li class="nav-item"><a class="nav-link" href="career_guide.php"><i class="fas fa-route me-2"></i>Career Guide</a></li>
                <li class="nav-item"><a class="nav-link" href="view_subjects.php"><i class="fas fa-book me-2"></i>Subjects</a></li>
                <li class="nav-item"><a class="nav-link" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
            </ul>
        </div>

        <div id="overlay" class="overlay"></div>

        <div class="col-md-10 content position-relative">
            <div class="d-flex justify-content-between align-items-center py-3 px-4 border-bottom">
                <div class="d-flex align-items-center">
                    <button id="sidebarToggle" class="btn me-1 d-md-none">
                        <i class="fas fa-bars fa-lg"></i>
                    </button>
                    <h4 class="mb-0">Welcome, <?php echo htmlspecialchars($user_name); ?></h4>
                </div>
                <div class="d-flex align-items-center">
                    <i class="fas fa-bell me-3"></i>
                    <a href="profile.php" class="text-dark text-decoration-none">
                        <i class="fas fa-user fa-lg"></i>
                    </a>
                </div>
            </div>

            <div class="container-fluid mt-4">
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="card dashboard-card mb-3 fade-in">
                            <div class="card-body">
                                <h5 class="card-title">Performance Overview</h5>
                                <canvas id="performanceChart" height="150"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card dashboard-card mb-3 fade-in">
                            <div class="card-body">
                                <h5 class="card-title">AI Suggestions</h5>
                                <div class="alert alert-warning p-2 mb-0"><?php
$suggestionStmt = $conn->prepare("SELECT ai_suggestion FROM users WHERE id = ?");
$suggestionStmt->bind_param("i", $user_id);
$suggestionStmt->execute();
$suggestionResult = $suggestionStmt->get_result();
$suggestionData = $suggestionResult->fetch_assoc();
$suggestionStmt->close();

echo "<p>" . nl2br(htmlspecialchars($suggestionData['ai_suggestion'])) . "</p>";

?>

</div>
<form method="post">
  <div class="text-center mt-2">
    <button type="submit" name="refresh_ai" class="btn btn-outline-primary btn-sm rounded-pill">
      Refresh AI Suggestion
    </button>
  </div>
</form>


                            </div>
                        </div>
                        <div class="card dashboard-card mb-3 fade-in">
                            <div class="card-body text-center position-relative">
 

    <!-- Streak Count -->
    <h5 class="card-title mb-2">
      <strong><?php echo $streak; ?> day streak 🔥</strong>
    </h5>

    <!-- Motivation Message -->
    <p class="card-text text-muted mb-4">
      <?php echo $streak === 0 ? "Do a lesson today to start a new streak!" : "Keep the streak alive!"; ?>
    </p>

    <!-- Weekly Activity Circles -->
    <div class="d-flex justify-content-between px-4">
      <?php
      $startOfWeek = new DateTime($weekStart);
      for ($i = 0; $i < 7; $i++) {
          $day = clone $startOfWeek;
          $day->modify("+$i days");
          $dateStr = $day->format('Y-m-d');
          $dayLetter = $daysOfWeek[$i];
          
          $isActive = $weekActivity[$i];
          $isToday = ($dateStr === date('Y-m-d'));

          // Set color and border
          $circleColor = $isActive ? '#ffc107' : '#dee2e6'; // yellow or gray
          $borderStyle = $isToday ? '2px solid #007bff' : 'none'; // blue border for today
          $tick = $isActive ? '✔' : '';
          
          echo "
            <div class='text-center'>
              <div style='
                width: 35px;
                height: 35px;
                margin: auto;
                border-radius: 50%;
                background-color: $circleColor;
                border: $borderStyle;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 18px;
                font-weight: bold;
              '>$tick</div>
              <small class='text-dark d-block mt-1'>$dayLetter</small>
            </div>
          ";
      }
      ?>
  </div>
</div>

</div>

                        </div>
                    </div>
                </div>

                <div class="row g-4 mt-1">
                    <div class="col-lg-6">
                        <div class="card dashboard-card fade-in text-center p-3 goal-card">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Goal Tracker</h5>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="text-center">
                                        <p class="fw-bold text-muted mb-1">Pending</p>
                                        <p class="fs-4 text-warning fw-bold mb-0"><?php echo $pendingCount; ?></p>
                                    </div>

                                    <div class="goal-chart-container">
                                        <canvas id="goalProgressCircle"></canvas>
                                    </div>

                                    <div class="text-center">
                                        <p class="fw-bold text-muted mb-1">In Progress</p>
                                        <p class="fs-4 text-primary fw-bold mb-0"><?php echo $inProgressCount; ?></p>
                                    </div>
                                </div>


                                <p id="goalSummary" class="small text-muted mt-3">Loading progress...</p>
                                <a href="goals.php" class="btn btn-sm btn-primary mt-2">View All Goals</a>
                                <p class="mt-2 text-success fw-bold" id="completionBadge" style="display: none;">🎉 Goal Completed!</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card dashboard-card fade-in">
                            <div class="card-body">
                                <h6 class="card-title">Career Path</h6>
                                <p class="card-text">Explore recommended role and skills to advance your career</p>
                            </div>
                        </div>
                        <div class="card dashboard-card fade-in mt-3">
                            <div class="card-body">
                                <h6 class="card-title">To-Do</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check-circle me-2 text-success"></i>Complete Assignment 3</li>
                                    <li><i class="fas fa-check-circle me-2 text-success"></i>Revise Lecture Notes</li>
                                    <li><i class="fas fa-check-circle me-2 text-success"></i>Meet Project Supervisor</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// ==========================
// Chart: Performance Overview
// ==========================
const subjectNames = <?php echo json_encode($subjectNames); ?>;
const subjectMarks = <?php echo json_encode($subjectMarks); ?>;

const ctx = document.getElementById('performanceChart').getContext('2d');
const performanceChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: subjectNames,
        datasets: [{
            label: 'Marks',
            data: subjectMarks,
            backgroundColor: 'rgba(54, 162, 235, 0.6)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1,
            borderRadius: 5
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true,
                max: 100
            }
        }
    }
});

// ==========================
// Sidebar Toggle Functionality
// ==========================
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');

sidebarToggle.addEventListener('click', function () {
    sidebar.classList.toggle('active');
    document.body.classList.toggle('sidebar-open');
    overlay.classList.toggle('active');
});

document.addEventListener('click', function (event) {
    if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
        sidebar.classList.remove('active');
        document.body.classList.remove('sidebar-open');
        overlay.classList.remove('active');
    }
});

overlay.addEventListener('click', function () {
    sidebar.classList.remove('active');
    document.body.classList.remove('sidebar-open');
    overlay.classList.remove('active');
});

// ==========================
// Page Fade-in Animation
// ==========================
window.addEventListener('load', () => {
    document.querySelectorAll('.fade-in').forEach(el => {
        setTimeout(() => {
            el.classList.add('show');
        }, 200);
    });
});

// ==========================
// Goal Progress Circle Chart
// ==========================
let goalChart;

function fetchGoalProgress() {
    fetch('fetch_goal_progress.php')
        .then(response => response.json())
        .then(data => {
            const progress = data.progress;
            const completed = data.completed;
            const total = data.total;

            // Update progress summary text
            document.getElementById('goalSummary').textContent = `You have completed ${completed} out of ${total} subtasks. Keep going!`;

            // Show/hide completion badge
            document.getElementById('completionBadge').style.display = (progress === 100) ? 'block' : 'none';

            const ctx = document.getElementById('goalProgressCircle').getContext('2d');

            // Destroy previous chart if exists
            if (goalChart) {
                goalChart.destroy();
            }

            // Create doughnut chart with center percentage text
            goalChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: [progress, 100 - progress],
                        backgroundColor: ['#ffc107', '#e9ecef'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '75%',
                    plugins: {
                        tooltip: { enabled: false },
                        legend: { display: false }
                    }
                },
                plugins: [{
                    id: 'centerText',
                    beforeDraw: function (chart) {
                        const { width } = chart;
                        const { height } = chart;
                        const ctx = chart.ctx;
                        ctx.restore();
                        const fontSize = (height / 7).toFixed(2);

                        ctx.font = `${fontSize}px Arial`;
                        ctx.textBaseline = 'middle';
                        ctx.fillStyle = '#000';

                        const text = `${progress}%`;
                        const textX = Math.round((width - ctx.measureText(text).width) / 2);
                        const textY = height / 2;

                        ctx.fillText(text, textX, textY);
                        ctx.save();
                    }
                }]
            });
        })
        .catch(error => {
            console.error('Error fetching progress:', error);
        });
}

// Fetch goal progress on page load
window.addEventListener('load', fetchGoalProgress);

</script>

<?php if ($showSubjectModal): ?>
<script>
    window.addEventListener('load', function () {
        var modal = new bootstrap.Modal(document.getElementById('subjectModal'));
        modal.show();
    });
</script>
<?php endif; ?>


<?php if ($showSubjectModal): ?>
<!-- Subject Modal -->
<div class="modal fade" id="subjectModal" tabindex="-1" aria-labelledby="subjectModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="subjectModalLabel">Add Your Subjects</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <p>You haven't added any subjects yet. Please add your subjects to get started.</p>
        <a href="student_subjects.php" class="btn btn-primary">Add Subjects</a>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

</body>
</html>
