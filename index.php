<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Student Guide</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/basiclightbox/dist/basicLightbox.min.css" />

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color:rgba(214, 231, 253, 1);
            margin: 0;
            padding: 0;
            color: #333;
            padding-top: 60px;
        }

        nav {
    position: fixed !important;
    top: 0;
    left: 0;
    right: 0;
    width: 100%;
    z-index: 1000;
    background-color:rgb(200, 221, 240);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}


        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(to right, rgba(255, 173, 235, 1), #ffc4ffff);
            padding-top: 80px;
            padding-bottom: 40px;
        }

        .hero-content {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
        }

        .hero h1 {
            font-size: 3.5rem;
            font-weight: bold;
        }

        .hero p {
            font-size: 1.2rem;
            margin: 20px 0;
            max-width: 500px;
        }

        .hero img {
            max-width: 100%;
            height: auto;
        }

        .btn-custom {
            background-color: rgb(120, 186, 236);
            border: none;
            padding: 10px 25px;
            font-weight: bold;
            border-radius: 8px;
            color: #fff;
        }

        .btn-custom:hover {
            background-color: #98badc;
        }

        .section {
            padding: 80px 20px;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 40px;
            font-weight: bold;
        }

        .feature-card {
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            background-color: #ffffff;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .testimonial-card {
            border-radius: 16px;
            background-color: #ffffff;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .testimonial-card img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
        }

        .trust-bar {
            background-color: #85c4f1ff;
            padding: 20px;
            text-align: center;
            font-weight: bold;
            font-size: 1.1rem;
            color: #fff;
        }

        .screenshot-gallery img {
            width: 100%;
            height: auto;
            object-fit: cover;
            border-radius: 12px;
            cursor: pointer;
        }

        .footer {
            background-color: #d6e4f0;
            text-align: center;
            padding: 30px 20px;
        }

        .footer a {
            margin: 0 10px;
            color: #333;
            text-decoration: none;
        }

        .join-section {
            background-color: #ffe0ec;
            padding: 80px 20px;
            text-align: center;
        }

        .join-section h2 {
            font-size: 2.5rem;
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .hero {
                flex-direction: column;
                text-align: center;
                padding-top: 120px;
            }

            .hero-content {
                align-items: center;
            }

            .hero h1 {
                font-size: 2.5rem;
            }

            .hero p {
                font-size: 1rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .feature-card,
            .testimonial-card {
                margin-bottom: 20px;
            }

            .btn-custom {
                padding: 10px 20px;
                font-size: 0.9rem;
            }
        }

        .swiper-button-next,
            .swiper-button-prev {
                display: block;
            }

        .swiper {
            width: 100%;
            padding-top: 50px;
            padding-bottom: 50px;
        }

        .swiper-slide {
            text-align: center;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .swiper-pagination-bullet-active {
            background-color: #007bff !important;
        }

        .swiper-button-next,
        .swiper-button-prev {
            color: #007bff;
            font-weight: bold;
        }
        /* Hover Effect for Testimonial and How It Works Cards */
.testimonial-card:hover,
.how-it-works-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

/* Neon light effect for screenshots */
.gallery-img {
    box-shadow: 0 0 15px rgba(32, 193, 204, 0.6); /* Neon Cyan Glow */
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-radius: 8px; /* Optional: makes the image edges softer */
}

.gallery-img:hover {
    transform: scale(1.05);
}
.howit{
    background-color: #ffc3f0ff;
}
.bg-change{
    background-color: #fce5f6ff;

}
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-light">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="#">
            <i class="fas fa-graduation-cap fa-2x me-2"></i>
            <span class="fw-bold">AI Student Guide</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                <li class="nav-item"><a class="nav-link" href="#how">How It Works</a></li>
                <li class="nav-item"><a class="nav-link" href="#testimonials">Testimonials</a></li>
                <li class="nav-item"><a class="nav-link" href="login.html">Login</a></li>
                <li class="nav-item"><a class="btn btn-custom ms-2" href="register.html">Get Started</a></li>
            </ul>
        </div>
    </div>
</nav>


    <header class="hero" data-aos="fade-up">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-md-6 hero-content" data-aos="fade-right">
                    <h1>Unlock Your True Potential</h1>
                    <p>Track your progress, set your goals, and let AI guide your career journey. Designed to help you succeed with clarity and confidence.</p>
                    <a href="register.html" class="btn btn-custom mt-3">Start Now</a>
                </div>
                <div class="col-md-6" data-aos="fade-left">
                    <img src="images/dashboard.png" alt="Dashboard Mockup" class="img-fluid">
                </div>
            </div>
        </div>
    </header>

    <div class="trust-bar" data-aos="fade-up">
        3000+ Students • 1200+ Goals Created • 85% Career Alignment Rate
    </div>

    <section class="section" id="features">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Features</h2>
            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-right">
                    <div class="p-4 feature-card text-center">
                        <i class="fas fa-chart-line fa-2x mb-3"></i>
                        <h5>Performance Tracking</h5>
                        <p>Track your marks over time, visualize trends, and see detailed progress charts.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up">
                    <div class="p-4 feature-card text-center">
                        <i class="fas fa-bullseye fa-2x mb-3"></i>
                        <h5>Goal Management</h5>
                        <p>Create smart goals, track subtasks, and maintain streaks to keep yourself on track.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-left">
                    <div class="p-4 feature-card text-center">
                        <i class="fas fa-route fa-2x mb-3"></i>
                        <h5>AI Career Guidance</h5>
                        <p>Get personalized AI suggestions based on your academic strengths and goals.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section  howit" id="how">
        <div class="container ">
            <h2 class="section-title" data-aos="fade-up">How It Works</h2>
            <div class="row text-center g-4">
                <div class="col-md-3 how-it-works-card bg-change" data-aos="fade-up">
                    <img src="images/register.PNG" alt="Sign Up" class="img-fluid mb-3">
                    <h6>Step 1</h6>
                    <p>Sign Up and create your student profile</p>
                </div>
                <div class="col-md-3 how-it-works-card bg-light" data-aos="fade-up" data-aos-delay="100">
                    <img src="images/interests.PNG" alt="Input Data" class="img-fluid mb-3">
                    <h6>Step 2</h6>
                    <p>Input subjects, marks, and interests</p>
                </div>
                <div class="col-md-3 how-it-works-card bg-change" data-aos="fade-up" data-aos-delay="200">
                    <img src="images/chart.PNG" alt="View Insights" class="img-fluid mb-3">
                    <h6>Step 3</h6>
                    <p>View progress, charts, and insights</p>
                </div>
                <div class="col-md-3 how-it-works-card bg-light" data-aos="fade-up" data-aos-delay="300">
                    <img src="images/career.PNG" alt="Get Suggestions" class="img-fluid mb-3">
                    <h6>Step 4</h6>
                    <p>Receive AI career suggestions</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section screenshot-gallery">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Screenshots Gallery</h2>

            <div class="swiper mySwiper">
                <div class="swiper-wrapper">
                    <div class="swiper-slide">
                        <img src="images/screenshot1.PNG" class="gallery-img" alt="Screenshot 1">
                    </div>
                    <div class="swiper-slide">
                        <img src="images/screenshot2.PNG" class="gallery-img" alt="Screenshot 2">
                    </div>
                    <div class="swiper-slide">
                        <img src="images/screenshot3.PNG" class="gallery-img" alt="Screenshot 3">
                    </div>
                </div>
                <div class="swiper-pagination"></div>
                <!-- Navigation Arrows -->
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        </div>
    </section>

    <section class="section" id="testimonials">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">What Students Say</h2>
            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-right">
                    <div class="testimonial-card">
                        <img src="images/girl2.jpeg" alt="Sarah">
                        <p>"This system helped me understand my strengths and guided me to the right career path. Love the AI suggestions!"</p>
                        <h6 class="mt-3 mb-0">Sarah, Computer Science Student</h6>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up">
                    <div class="testimonial-card">
                        <img src="images/boy.webp" alt="Ahmed">
                        <p>"Setting goals and tracking progress has never been this easy and motivating. I feel more confident now!"</p>
                        <h6 class="mt-3 mb-0">Ahmed, Engineering Student</h6>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-left">
                    <div class="testimonial-card">
                        <img src="images/girl1.webp" alt="Fatima">
                        <p>"The visual progress tracking keeps me focused. The system is user-friendly and beautifully designed."</p>
                        <h6 class="mt-3 mb-0">Fatima, Business Student</h6>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="join-section">
        <div class="container">
            <h2 data-aos="fade-up">Ready to Start Your Journey?</h2>
            <a href="register.html" class="btn btn-custom mt-3">Join Now</a>
        </div>
    </section>

    <footer class="footer">
        <p>&copy; 2025 AI Student Guide. All rights reserved.</p>
        <div>
            <a href="#" class="me-3">Privacy Policy</a>
            <a href="#" class="me-3">Terms of Service</a>
            <a href="#" class="me-3">Contact Us</a>
        </div>
        <div class="mt-3">
            <a href="#" class="me-3 text-dark"><i class="fab fa-facebook fa-lg"></i></a>
            <a href="#" class="me-3 text-dark"><i class="fab fa-instagram fa-lg"></i></a>
            <a href="#" class="text-dark"><i class="fab fa-linkedin fa-lg"></i></a>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <script src="https://unpkg.com/basiclightbox/dist/basicLightbox.min.js"></script>

    <script>
        AOS.init();

        var swiper = new Swiper('.mySwiper', {
            slidesPerView: 1,
            spaceBetween: 30,
            loop: true,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            autoplay: {
                delay: 3000,
                disableOnInteraction: false,
            },
            speed: 800,
        });
    </script>
</body>

</html>
