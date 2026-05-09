<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Information | Student Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4a90e2;
            --primary-hover: #357abd;
            --card-shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
        }
        
        body {
            background: linear-gradient(135deg, #e6f0ff 0%, #f0f7ff 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
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
        
        .academic-container {
            position: relative;
            z-index: 10;
            max-width: 500px;
            margin: 40px auto;
        }
        
        .academic-card {
            border: none;
            border-radius: 15px;
            background: white;
            box-shadow: var(--card-shadow);
            padding: 30px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .academic-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(31, 38, 135, 0.2);
        }
        
        .form-label {
            font-weight: 500;
            color: #495057;
            margin-top: 15px;
        }
        
        .form-control {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 12px 15px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.2);
        }
        
        .submit-btn {
            margin-top: 25px;
            width: 100%;
            padding: 12px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        .submit-btn:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(74, 144, 226, 0.3);
        }
        
        .submit-btn:active {
            transform: translateY(0);
        }
        
        .title-icon {
            color: var(--primary-color);
            font-size: 2rem;
            margin-bottom: 15px;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 767.98px) {
            body {
                padding: 15px;
            }
            
            .academic-card {
                padding: 25px;
            }
            
            .circle-big {
                width: 400px;
                height: 400px;
                top: -150px;
                right: -150px;
            }
            
            .circle-medium {
                width: 300px;
                height: 300px;
            }
            
            .circle-small {
                width: 150px;
                height: 150px;
            }
        }
        
        @media (max-width: 575.98px) {
            .academic-card {
                padding: 20px 15px;
            }
            
            .form-control {
                padding: 10px 12px;
            }
            
            .circle-medium, .circle-small {
                opacity: 0.1;
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
    
    <div class="academic-container">
        <div class="academic-card">
            <div class="text-center">
                <i class="fas fa-graduation-cap title-icon"></i>
                <h2 class="fw-bold">Academic Information</h2>
                <p class="text-muted">Complete your academic profile</p>
            </div>
            
            <form action="backend/save_academic_info.php" method="POST">
                <div class="mb-3">
                    <label for="degree_program" class="form-label">Degree Program</label>
                    <input type="text" class="form-control" id="degree_program" name="degree_program" required>
                </div>
                
                <div class="mb-3">
                    <label for="current_semester" class="form-label">Current Semester</label>
                    <select class="form-control" id="current_semester" name="current_semester" required>
                        <option value="">Select Semester</option>
                        <?php for($i=1; $i<=12; $i++): ?>
                            <option value="<?= $i ?>">Semester <?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="cgpa" class="form-label">Current CGPA</label>
                    <input type="number" class="form-control" id="cgpa" name="cgpa" min="0" max="4" step="0.01" required>
                </div>
                
                <button type="submit" class="submit-btn">
                    <i class="fas fa-check-circle me-2"></i> Submit & Go to Dashboard
                </button>
            </form>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Form submission loading state
        document.querySelector('form').addEventListener('submit', function(e) {
            const btn = this.querySelector('button[type="submit"]');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
            btn.disabled = true;
        });
    </script>
</body>
</html>