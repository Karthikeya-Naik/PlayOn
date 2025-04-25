<?php
session_start();
include 'db_connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - PlayOn</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- AOS Animation Library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <link href="css/style.css" rel="stylesheet">
    <style>
        :root {
            --primary: #0b4d8c;
            --primary-light: #e6f0fa;
            --primary-dark: #083968;
            --dark: #212529;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            scroll-behavior: smooth;
        }
        
        
        /* Page header section */
        .page-header {
            background: linear-gradient(rgba(11, 77, 140, 0.85), rgba(8, 57, 104, 0.95)), 
                        url('/api/placeholder/1920/300') no-repeat center center/cover;
            padding: 100px 0;
            position: relative;
            overflow: hidden;
        }
        
        .page-header h1 {
            font-weight: 800;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            animation: fadeInUp 1s ease;
        }
        
        .page-header .lead {
            animation: fadeInUp 1.2s ease;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* About sections */
        .about-intro {
            padding: 80px 0;
        }
        
        .about-image {
            border-radius: 10px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
            transition: all 0.5s ease;
            overflow: hidden;
        }
        
        .about-image img {
            transition: all 0.5s ease;
        }
        
        .about-image:hover img {
            transform: scale(1.05);
        }
        
        .section-title {
            position: relative;
            margin-bottom: 2rem;
            font-weight: 800;
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -10px;
            width: 50px;
            height: 3px;
            background-color: var(--primary);
            transition: width 0.3s ease;
        }
        
        .section-title:hover:after {
            width: 100px;
        }
        
        /* Features section */
        .features-section {
            background-color: #f8f9fa;
            padding: 80px 0;
        }
        
        .feature-card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.4s ease;
            height: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .feature-icon {
            height: 80px;
            width: 80px;
            line-height: 80px;
            margin: 0 auto 1.5rem;
            border-radius: 50%;
            background: rgba(11, 77, 140, 0.1);
            color: var(--primary);
            transition: all 0.4s ease;
        }
        
        .feature-card:hover .feature-icon {
            background: var(--primary);
            color: white;
            transform: rotateY(360deg);
        }
        
        /* Team section */
        .team-section {
            padding: 80px 0;
        }
        
        .team-card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.4s ease;
            height: 100%;
        }
        
        .team-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .team-image {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid rgba(11, 77, 140, 0.1);
            transition: all 0.4s ease;
        }
        
        .team-card:hover .team-image {
            border-color: var(--primary);
            transform: scale(1.05);
        }
        
        .team-card .social-icons a {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            width: 36px;
            height: 36px;
            background-color: rgba(11, 77, 140, 0.1);
            color: var(--primary);
            border-radius: 50%;
            margin: 0 5px;
            transition: all 0.3s ease;
        }
        
        .team-card .social-icons a:hover {
            background-color: var(--primary);
            color: white;
            transform: scale(1.1) rotate(360deg);
        }
        
        /* Mission section */
        .mission-section {
            background: linear-gradient(rgba(11, 77, 140, 0.85), rgba(8, 57, 104, 0.95)), 
                        url('/api/placeholder/1920/600') no-repeat center center/cover;
            padding: 80px 0;
            color: white;
            position: relative;
        }
        
        .mission-card {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 30px;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.4s ease;
            height: 100%;
        }
        
        .mission-card:hover {
            transform: translateY(-10px);
            background-color: rgba(255, 255, 255, 0.15);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        
        .mission-icon {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.4s ease;
        }
        
        .mission-card:hover .mission-icon {
            /* transform: rotateY(180deg); */
            color: #fff;
        }
        
        /* Call to Action */
        .cta-section {
            background: var(--primary);
            position: relative;
            overflow: hidden;
        }
        
        .cta-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('/api/placeholder/800/600') no-repeat center center/cover;
            opacity: 0.1;
        }
        
        .btn-lg {
            padding: 0.8rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.4s ease;
        }

        .btn-lg:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(11, 77, 140, 0.3);
        }
        
        /* Back to top button */
        .back-to-top {
            position: fixed;
            bottom: 25px;
            right: 25px;
            display: none;
            width: 50px;
            height: 50px;
            text-align: center;
            line-height: 50px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            z-index: 9999;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        }

        .back-to-top:hover {
            background: var(--primary-dark);
            color: white;
            transform: translateY(-5px);
        }
        
        /* Footer */
        footer {
            padding-top: 3rem;
        }
        
        footer a {
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        footer a:hover {
            color: var(--primary) !important;
            padding-left: 5px;
        }
        
        /* Button styles */
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover, .btn-primary:focus {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(11, 77, 140, 0.3);
        }
        
        .btn-outline-primary {
            border-color: var(--primary);
            color: var(--primary);
            transition: all 0.3s ease;
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary);
            border-color: var(--primary);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(11, 77, 140, 0.3);
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <?php include 'navbar.php'; ?>

    <!-- Page Header -->
    <header class="page-header text-white text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3" data-aos="fade-up">About Us</h1>
            <p class="lead fs-5 mb-0" data-aos="fade-up" data-aos-delay="200">Learn more about PlayOn and our mission</p>
        </div>
    </header>

    <!-- About Introduction Section -->
    <section class="about-intro">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0" data-aos="fade-right" data-aos-duration="1000">
                    <h2 class="section-title">Our Story</h2>
                    <p class="lead mb-4">Telangana's Premier Box Cricket Booking Platform</p>
                    <p>Founded in 2023, PlayOn has revolutionized how cricket enthusiasts book and play box cricket in Telangana. We started with a simple mission: to make box cricket accessible to everyone.</p>
                    <p>Our platform connects players with the best box cricket venues across the region, offering a seamless booking experience with real-time availability, secure payments, and exceptional customer service.</p>
                    <p>Whether you're organizing a friendly match with friends, a corporate tournament, or just looking to practice your skills, PlayOn has got you covered!</p>
                    <div class="mt-4">
                        <a href="venues.php" class="btn btn-primary">Explore Venues</a>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left" data-aos-duration="1000" data-aos-delay="200">
                    <div class="about-image">
                        <img src="uploads/images/img1.jpeg" alt="Box Cricket" class="img-fluid rounded">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission and Vision Section -->
    <section class="mission-section">
        <div class="container">
            <div class="row justify-content-center mb-5">
                <div class="col-lg-8 text-center" data-aos="fade-up">
                    <h2 class="fw-bold mb-4">Our Mission & Vision</h2>
                    <p class="lead">We're on a mission to make cricket accessible to everyone and build communities through sports</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="mission-card h-100">
                        <div class="mission-icon">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Our Mission</h4>
                        <p>To simplify the process of finding and booking cricket venues, making the sport more accessible to enthusiasts of all levels.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="mission-card h-100">
                        <div class="mission-icon">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Our Vision</h4>
                        <p>To become the most trusted platform for sports venue bookings across India, connecting millions of players with quality facilities.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="mission-card h-100">
                        <div class="mission-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Our Values</h4>
                        <p>We believe in transparency, reliability, customer satisfaction, and promoting a healthy lifestyle through sports.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <div class="row justify-content-center mb-5">
                <div class="col-lg-8 text-center" data-aos="fade-up">
                    <h2 class="fw-bold mb-4">Why Choose PlayOn?</h2>
                    <p class="lead">We offer the best cricket venue booking experience in Telangana</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="card feature-card text-center h-100">
                        <div class="card-body">
                            <div class="feature-icon">
                                <i class="fas fa-map-marker-alt fa-2x"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Best Venues</h4>
                            <p>We partner only with premium box cricket facilities that meet our strict quality standards for pitch, equipment, and amenities.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="card feature-card text-center h-100">
                        <div class="card-body">
                            <div class="feature-icon">
                                <i class="fas fa-bolt fa-2x"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Instant Booking</h4>
                            <p>Our platform allows you to book your preferred venue in just a few clicks. Confirm availability in real-time and secure your slot instantly.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="card feature-card text-center h-100">
                        <div class="card-body">
                            <div class="feature-icon">
                                <i class="fas fa-rupee-sign fa-2x"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Best Prices</h4>
                            <p>Enjoy competitive rates with no hidden charges or booking fees. We ensure you get the best value for your money.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="card feature-card text-center h-100">
                        <div class="card-body">
                            <div class="feature-icon">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                            <h4 class="fw-bold mb-3">24/7 Booking</h4>
                            <p>Our platform is available round the clock, allowing you to book venues anytime, anywhere, from any device.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="500">
                    <div class="card feature-card text-center h-100">
                        <div class="card-body">
                            <div class="feature-icon">
                                <i class="fas fa-shield-alt fa-2x"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Secure Payments</h4>
                            <p>Our payment gateway ensures your transactions are safe and secure, with multiple payment options for your convenience.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="600">
                    <div class="card feature-card text-center h-100">
                        <div class="card-body">
                            <div class="feature-icon">
                                <i class="fas fa-headset fa-2x"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Customer Support</h4>
                            <p>Our dedicated support team is always ready to assist you with any queries or issues you might have during the booking process.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="team-section">
        <div class="container">
            <div class="row justify-content-center mb-5">
                <div class="col-lg-8 text-center" data-aos="fade-up">
                    <h2 class="fw-bold mb-4">Meet Our Team</h2>
                    <p class="lead">The passionate individuals behind PlayOn's success</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="card team-card text-center h-100">
                        <div class="card-body">
                            <img src="uploads/images/team1.png" alt="Raj Kumar" class="team-image mb-4">
                            <h5 class="fw-bold mb-1">Raj Kumar</h5>
                            <p class="text-muted mb-3">Founder & CEO</p>
                            <p class="small">Cricket enthusiast with a vision to transform the sports booking experience in India.</p>
                            <div class="social-icons mt-3">
                                <a href="#"><i class="fab fa-facebook-f"></i></a>
                                <a href="#"><i class="fab fa-twitter"></i></a>
                                <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="card team-card text-center h-100">
                        <div class="card-body">
                            <img src="uploads/images/team2.jpg" alt="Priya Sharma" class="team-image mb-4">
                            <h5 class="fw-bold mb-1">Priya Sharma</h5>
                            <p class="text-muted mb-3">Operations Manager</p>
                            <p class="small">Ensuring smooth operations and exceptional service quality across all venues.</p>
                            <div class="social-icons mt-3">
                                <a href="#"><i class="fab fa-facebook-f"></i></a>
                                <a href="#"><i class="fab fa-twitter"></i></a>
                                <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="card team-card text-center h-100">
                        <div class="card-body">
                            <img src="uploads/images/team3.avif" alt="Arjun Reddy" class="team-image mb-4">
                            <h5 class="fw-bold mb-1">Arjun Reddy</h5>
                            <p class="text-muted mb-3">Marketing Director</p>
                            <p class="small">Creative genius behind PlayOn's brand and marketing strategies.</p>
                            <div class="social-icons mt-3">
                                <a href="#"><i class="fab fa-facebook-f"></i></a>
                                <a href="#"><i class="fab fa-twitter"></i></a>
                                <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="card team-card text-center h-100">
                        <div class="card-body">
                            <img src="uploads/images/team4.webp" alt="Deepa Patel" class="team-image mb-4">
                            <h5 class="fw-bold mb-1">Deepa Patel</h5>
                            <p class="text-muted mb-3">Customer Relations</p>
                            <p class="small">Dedicated to providing the best support and experience to our users.</p>
                            <div class="social-icons mt-3">
                                <a href="#"><i class="fab fa-facebook-f"></i></a>
                                <a href="#"><i class="fab fa-twitter"></i></a>
                                <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-5 bg-primary text-white text-center cta-section">
        <div class="container" data-aos="zoom-in">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h2 class="mb-4 fw-bold">Ready to book your cricket experience?</h2>
                    <p class="lead mb-4">Join thousands of cricket enthusiasts who book through PlayOn every day</p>
                    <a href="venues.php" class="btn btn-light btn-lg px-5">Book Now</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'footer.php'; ?>
    
    <!-- Back to top button -->
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center">
        <i class="fas fa-arrow-up"></i>
    </a>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    
    <script>
        // Initialize AOS animations
        document.addEventListener('DOMContentLoaded', function() {
            AOS.init({
                duration: 800,
                once: true,
                offset: 100
            });
            
            // Back to top button
            const backToTopButton = document.querySelector('.back-to-top');
            
            window.addEventListener('scroll', () => {
                if (window.pageYOffset > 300) {
                    backToTopButton.style.display = 'flex';
                } else {
                    backToTopButton.style.display = 'none';
                }
            });
            
            backToTopButton.addEventListener('click', (e) => {
                e.preventDefault();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>