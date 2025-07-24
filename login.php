<?php
session_start();
include 'db_connect.php';

$error = '';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password']; // Not hashing as per requirement
    
    // Check if it's an admin login
    if(isset($_POST['admin_login']) && $_POST['admin_login'] == 1) {
        $query = "SELECT * FROM admins WHERE email='$email' AND password='$password'";
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);
            $_SESSION['admin_id'] = $row['admin_id'];
            $_SESSION['admin_name'] = $row['name'];
            $_SESSION['admin_email'] = $row['email'];
            header("Location: admin/dashboard.php");
            exit();
        } else {
            $error = "Invalid admin credentials";
        }
    } else {
        // Regular user login
        $query = "SELECT * FROM users WHERE email='$email' AND password='$password'";
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['name'] = $row['name'];
            $_SESSION['email'] = $row['email'];
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid email or password";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PlayOn</title>
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
        background-color: #f8f9fa;
        scroll-behavior: smooth;
    }

    /* Navbar improvements */
    .navbar {
        padding: 12px 0;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
    }

    .navbar-brand {
        font-weight: 700;
        font-size: 1.5rem;
    }

    .nav-link {
        font-weight: 500;
        margin: 0 5px;
        position: relative;
    }
    
    .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 30px;
        height: 2px;
        background-color: var(--primary);
    }

    /* Page header section */
    .page-header {
        background: linear-gradient(rgba(11, 77, 140, 0.85), rgba(8, 57, 104, 0.95)),
            url('uploads/images/hero1.webp') no-repeat center center/cover;
        padding: 80px 0;
        position: relative;
        overflow: hidden;
    }

    .page-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.2);
        z-index: 1;
    }

    .page-header .container {
        position: relative;
        z-index: 2;
    }

    .page-header h1 {
        font-weight: 800;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        animation: fadeInDown 1s ease;
    }

    .page-header p {
        animation: fadeInUp 1.2s ease;
    }

    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
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

    /* Login Card */
    .login-card {
        border-radius: 15px;
        border: none;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.18);
        overflow: hidden;
        margin-bottom: 40px;
        background-color: white;
        transition: all 0.4s ease;
    }

    .login-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
    }

    .login-card .card-body {
        padding: 40px;
    }

    .login-card .card-title {
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 30px;
    }

    /* Tabs styling */
    .nav-tabs {
        border-bottom: 2px solid #eee;
        margin-bottom: 25px;
    }

    .nav-tabs .nav-link {
        border: none;
        font-weight: 600;
        padding: 12px 20px;
        margin-right: 10px;
        color: #6c757d;
        border-radius: 0;
        transition: all 0.3s ease;
    }

    .nav-tabs .nav-link.active {
        color: var(--primary);
        border-bottom: 3px solid var(--primary);
        background-color: transparent;
    }

    .nav-tabs .nav-link:hover:not(.active) {
        border-bottom: 3px solid #e0e0e0;
        color: var(--primary-dark);
    }

    /* Form styling */
    .form-control, .form-select {
        padding: 0.8rem 1rem;
        border-radius: 8px;
        border: 1px solid #e0e0e0;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        box-shadow: 0 0 0 0.25rem rgba(11, 77, 140, 0.25);
        border-color: #86b7fe;
        transform: translateY(-2px);
    }

    .input-group-text {
        background-color: var(--primary-light);
        border-color: #e0e0e0;
        color: var(--primary);
    }

    .form-label {
        font-weight: 600;
        margin-bottom: 0.7rem;
        color: #495057;
    }

    /* Buttons */
    .btn {
        border-radius: 8px;
        padding: 0.7rem 1.5rem;
        font-weight: 600;
        transition: all 0.4s ease;
    }

    .btn-lg {
        padding: 0.9rem 2rem;
        font-size: 1.1rem;
    }

    .btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(11, 77, 140, 0.3);
    }

    .btn-primary {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    .btn-primary:hover, .btn-primary:focus {
        background-color: var(--primary-dark);
        border-color: var(--primary-dark);
    }

    .btn-danger {
        background-color: #dc3545;
        border-color: #dc3545;
    }

    /* Benefits cards */
    .benefit-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
        transition: all 0.4s ease;
        height: 100%;
        overflow: hidden;
    }

    .benefit-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(11, 77, 140, 0.15);
    }

    .benefit-card .card-body {
        padding: 30px 20px;
    }

    .benefit-icon {
        font-size: 3rem;
        color: var(--primary);
        margin-bottom: 20px;
        transition: all 0.4s ease;
    }

    .benefit-card:hover .benefit-icon {
        transform: scale(1.1) rotateY(180deg);
    }

    .benefit-card .card-title {
        font-weight: 700;
        margin-bottom: 15px;
    }

    /* Alert styling */
    .alert {
        border-radius: 8px;
        padding: 1rem 1.25rem;
        margin-bottom: 25px;
        border-left: 5px solid;
        animation: fadeIn 0.5s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    .alert-danger {
        background-color: #fff5f5;
        border-left-color: #dc3545;
        color: #dc3545;
    }

    .alert-success {
        background-color: #f0fff4;
        border-left-color: #28a745;
        color: #28a745;
    }

    /* Links */
    a {
        color: var(--primary);
        text-decoration: none;
        transition: all 0.3s ease;
    }

    a:hover {
        color: var(--primary-dark);
        text-decoration: underline;
    }

    /* Form check */
    .form-check {
        padding-left: 1.8rem;
    }

    .form-check-input {
        width: 1.2rem;
        height: 1.2rem;
        margin-left: -1.8rem;
        margin-top: 0.15rem;
        transition: all 0.3s ease;
    }

    .form-check-input:checked {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    .form-check-label {
        font-size: 0.95rem;
    }

    /* CTA Section */
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
        background: url('uploads/images/hero1.webp') no-repeat center center/cover;
        opacity: 0.1;
    }

    /* Tab Content Animation */
    .tab-pane.fade {
        transition: all 0.3s ease;
    }

    .tab-pane.fade.show {
        animation: fadeInUp 0.5s ease;
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
    </style>
</head>

<body>
    <!-- Navigation Bar -->
    <?php include 'navbar.php'; ?>

    <!-- Page Header -->
    <header class="page-header text-white text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3" data-aos="fade-down">Welcome Back!</h1>
            <p class="lead fs-5 mb-0" data-aos="fade-up">Sign in to your PlayOn account to book cricket venues and manage your reservations</p>
        </div>
    </header>

    <!-- Login Form Section -->
    <section class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="card login-card" data-aos="fade-up">
                    <div class="card-body">
                        <h2 class="text-center card-title">Login to Your Account</h2>
                        
                        <?php if($error != ''): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if(isset($_SESSION['success'])): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <ul class="nav nav-tabs" id="loginTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="user-tab" data-bs-toggle="tab" data-bs-target="#user-login" type="button" role="tab">
                                    <i class="fas fa-user me-2"></i>User Login
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="admin-tab" data-bs-toggle="tab" data-bs-target="#admin-login" type="button" role="tab">
                                    <i class="fas fa-user-shield me-2"></i>Admin Login
                                </button>
                            </li>
                        </ul>
                        
                        <div class="tab-content" id="loginTabContent">
                            <!-- User Login -->
                            <div class="tab-pane fade show active" id="user-login" role="tabpanel">
                                <form action="login.php" method="post">
                                    <div class="mb-4">
                                        <label for="user-email" class="form-label">Email Address</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                            <input type="email" class="form-control" id="user-email" name="email" placeholder="Enter your email" required>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <label for="user-password" class="form-label">Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                            <input type="password" class="form-control" id="user-password" name="password" placeholder="Enter your password" required>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="remember-me">
                                            <label class="form-check-label" for="remember-me">Remember me</label>
                                        </div>
                                        <a href="forgot-password.php" style="text-decoration:none" class="small">Forgot password?</a>
                                    </div>
                                    <div class="d-grid mb-4">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-sign-in-alt me-2"></i>Login
                                        </button>
                                    </div>
                                    <div class="text-center">
                                        <p class="mb-0">Don't have an account? <a href="register.php" style="text-decoration:none">Register now</a></p>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- Admin Login -->
                            <div class="tab-pane fade" id="admin-login" role="tabpanel">
                                <form action="login.php" method="post">
                                    <input type="hidden" name="admin_login" value="1">
                                    <div class="mb-4">
                                        <label for="admin-email" class="form-label">Admin Email</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-user-shield"></i></span>
                                            <input type="email" class="form-control" id="admin-email" name="email" placeholder="Enter admin email" required>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <label for="admin-password" class="form-label">Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                            <input type="password" class="form-control" id="admin-password" name="password" placeholder="Enter admin password" required>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <div class="form-text text-muted">
                                            <i class="fas fa-info-circle me-1"></i> This section is restricted to system administrators only.
                                        </div>
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-danger btn-lg">
                                            <i class="fas fa-user-cog me-2"></i>Admin Login
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <h3 class="text-center mb-5 fw-bold" data-aos="fade-up">Why Choose PlayOn?</h3>
            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="card benefit-card">
                        <div class="card-body text-center">
                            <i class="fa-solid fa-baseball-bat-ball benefit-icon"></i>
                            <h5 class="card-title">Top Quality Venues</h5>
                            <p class="card-text">Access to premium box cricket venues across Telangana with professional-grade equipment and amenities.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="card benefit-card">
                        <div class="card-body text-center">
                            <i class="fas fa-calendar-check benefit-icon"></i>
                            <h5 class="card-title">Easy Booking</h5>
                            <p class="card-text">Simple and hassle-free online booking system with real-time availability and instant confirmations.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="card benefit-card">
                        <div class="card-body text-center">
                            <i class="fas fa-headset benefit-icon"></i>
                            <h5 class="card-title">24/7 Support</h5>
                            <p class="card-text">Round-the-clock customer support for all your needs, from booking assistance to venue-related queries.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section text-white" data-aos="fade-up">
        <div class="container">
            <div class="row">
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
                    <div class="stat-item">
                        <div class="stat-number" data-count="50">0+</div>
                        <div class="stat-label">Cricket Venues</div>
                    </div>
                </div>
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
                    <div class="stat-item">
                        <div class="stat-number" data-count="10000">0+</div>
                        <div class="stat-label">Happy Players</div>
                    </div>
                </div>
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
                    <div class="stat-item">
                        <div class="stat-number" data-count="20">0+</div>
                        <div class="stat-label">Cities Covered</div>
                    </div>
                </div>
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="400">
                    <div class="stat-item">
                        <div class="stat-number" data-count="5000">0+</div>
                        <div class="stat-label">Matches Played</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-5 cta-section text-white text-center">
        <div class="container position-relative" data-aos="zoom-in">
            <h2 class="mb-4 fw-bold">Ready to play cricket?</h2>
            <p class="lead mb-4">Join thousands of cricket enthusiasts who book their favorite venues through PlayOn!</p>
            <a href="register.php" class="btn btn-light btn-lg px-5" style="text-decoration:none">Register Now</a>
        </div>
    </section>

    <!-- Back to top button -->
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center">
        <i class="fas fa-arrow-up"></i>
    </a>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

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

        // Number counter animation for stats
        const countElements = document.querySelectorAll('.stat-number');

        countElements.forEach(element => {
            const target = parseInt(element.getAttribute('data-count'));
            let count = 0;
            const increment = target > 1000 ? Math.floor(target / 20) : 1;

            const updateCount = () => {
                if (count < target) {
                    count += increment;
                    if (count > target) count = target;
                    element.textContent = count.toLocaleString() + "+";
                    setTimeout(updateCount, 50);
                }
            };

            // Start the animation when element is in viewport
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        updateCount();
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.5
            });

            observer.observe(element);
        });

        // Form input animation
        const formInputs = document.querySelectorAll('.form-control');
        formInputs.forEach(input => {
            input.addEventListener('focus', () => {
                input.parentElement.classList.add('input-focused');
            });
            input.addEventListener('blur', () => {
                input.parentElement.classList.remove('input-focused');
            });
        });
    });
    </script>
</body>

</html>