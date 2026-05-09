<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch selected interests for current user
$selected_interests = [];
require 'backend/db.php';
$stmt = $conn->prepare("SELECT interest FROM student_interests WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $selected_interests[] = $row['interest'];
}
$interests = ["Web Development", "Mobile App Development", "Data Science", "Cyber Security", "AI & ML", "Graphic Designing", "Game Development", "UI/UX Design", "Networking", "Cloud Computing"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Your Interests | Student Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4a90e2;
            --primary-hover: #357abd;
            --success-color: #28a745;
            --success-hover: #218838;
            --card-shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
        }
        
        body {
            background: linear-gradient(135deg, #e6f0ff 0%, #f0f7ff 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 40px 20px;
        }
        
        /* Circle Background */
        .circle-background {
            position: fixed;
            width: 100vw;
            height: 100vh;
            top: 0;
            left: 0;
            z-index: 0;
            overflow: hidden;
        }
        
        .circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(74, 144, 226, 0.15);
            animation: float 15s infinite ease-in-out;
        }
        
        .circle-big {
            width: 600px;
            height: 600px;
            top: -200px;
            right: -200px;
            background-color: #c5f0f9;
        }
        
        .circle-medium {
            width: 400px;
            height: 400px;
            bottom: -100px;
            left: -100px;
            background-color: #ffe0ec;
            animation-delay: 5s;
        }
        
        .circle-small {
            width: 200px;
            height: 200px;
            bottom: 100px;
            right: 100px;
            background-color: #d0f4de;
            animation-delay: 10s;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(20px, 20px); }
        }
        
        .interests-container {
            position: relative;
            z-index: 10;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .interests-card {
            border: none;
            border-radius: 15px;
            background: white;
            box-shadow: var(--card-shadow);
            padding: 30px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .interests-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(31, 38, 135, 0.2);
        }
        
        .interest-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: center;
            margin-top: 25px;
        }
        
        .interest-option {
            border: 2px solid #dee2e6;
            border-radius: 25px;
            padding: 12px 20px;
            cursor: pointer;
            background: #fff;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            user-select: none;
            font-size: 15px;
        }
        
        .interest-option:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }
        
        .interest-option.selected {
            background-color: var(--primary-color);
            color: #fff;
            border-color: var(--primary-color);
            font-weight: 600;
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(74, 144, 226, 0.3);
        }
        
        .submit-btn {
            margin-top: 30px;
            width: 100%;
            padding: 12px;
            background-color: var(--success-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .submit-btn:hover {
            background-color: var(--success-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }
        
        .submit-btn:active {
            transform: translateY(0);
        }
        
        /* Responsive Adjustments */
        @media (max-width: 767.98px) {
            body {
                padding: 20px 15px;
            }
            
            .interests-card {
                padding: 25px;
            }
            
            .interest-option {
                padding: 10px 16px;
                font-size: 14px;
            }
        }
        
        @media (max-width: 575.98px) {
            body {
                padding: 15px 10px;
            }
            
            .interests-card {
                padding: 20px 15px;
            }
            
            .interest-grid {
                gap: 8px;
            }
            
            .interest-option {
                padding: 8px 14px;
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <!-- Circle Background -->
    <div class="circle-background">
        <div class="circle circle-big"></div>
        <div class="circle circle-medium"></div>
        <div class="circle circle-small"></div>
    </div>
    
    <div class="interests-container">
        <div class="interests-card">
            <div class="text-center mb-4">
                <i class="fas fa-star fa-2x" style="color: var(--primary-color); margin-bottom: 10px;"></i>
                <h2 class="fw-bold">Select Your Interests</h2>
                <p class="text-muted">Choose topics you're passionate about</p>
            </div>
            
            <form action="backend/save_interests.php" method="POST">
                <div class="interest-grid">
                    <?php foreach ($interests as $interest): ?>
                        <?php $isSelected = in_array($interest, $selected_interests); ?>
                        <div class="interest-option <?= $isSelected ? 'selected' : '' ?>" data-value="<?= htmlspecialchars($interest) ?>">
                            <input type="checkbox" name="interests[]" value="<?= htmlspecialchars($interest) ?>" <?= $isSelected ? 'checked' : '' ?> hidden>
                            <?= htmlspecialchars($interest) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <button type="submit" class="submit-btn">
                    <i class="fas fa-arrow-right me-2"></i> Continue
                </button>
            </form>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const options = document.querySelectorAll('.interest-option');
            
            options.forEach(option => {
                const checkbox = option.querySelector('input');
                
                option.addEventListener('click', () => {
                    checkbox.checked = !checkbox.checked;
                    option.classList.toggle('selected', checkbox.checked);
                    
                    // Add ripple effect
                    const ripple = document.createElement('span');
                    ripple.style.position = 'absolute';
                    ripple.style.borderRadius = '50%';
                    ripple.style.backgroundColor = 'rgba(255, 255, 255, 0.4)';
                    ripple.style.transform = 'scale(0)';
                    ripple.style.animation = 'ripple 0.6s linear';
                    ripple.style.pointerEvents = 'none';
                    
                    // Set ripple size and position
                    const size = Math.max(option.offsetWidth, option.offsetHeight);
                    const rect = option.getBoundingClientRect();
                    
                    ripple.style.width = ripple.style.height = `${size}px`;
                    ripple.style.left = `${event.clientX - rect.left - size/2}px`;
                    ripple.style.top = `${event.clientY - rect.top - size/2}px`;
                    
                    option.appendChild(ripple);
                    
                    // Remove ripple after animation
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });
            
            // Form submission loading state
            document.querySelector('form').addEventListener('submit', function(e) {
                const btn = this.querySelector('button[type="submit"]');
                btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
                btn.disabled = true;
            });
        });
        
        // Add ripple animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(2.5);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>