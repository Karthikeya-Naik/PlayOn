<?php
session_start();
include 'db_connect.php';

// Check if form is submitted
if(isset($_POST['register'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = $_POST['password']; // Not hashing as per requirement
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    $errors = [];
    
    // Check if email already exists
    $check_email = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $check_email);
    
    if(mysqli_num_rows($result) > 0) {
        $errors[] = "Email already exists!";
    }
    
    // Password confirmation check
    if($password != $confirm_password) {
        $errors[] = "Passwords do not match!";
    }
    
    // If no errors, proceed with registration
    if(empty($errors)) {
        $query = "INSERT INTO users (name, email, phone, password) 
                  VALUES ('$name', '$email', '$phone', '$password')";
        
        if(mysqli_query($conn, $query)) {
            $_SESSION['success'] = "Registration successful! Please login.";
            header("Location: login.php");
            exit();
        } else {
            $errors[] = "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - PlayOn</title>
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
        
        /* Page header section */
        .page-header {
            background: linear-gradient(rgba(11, 77, 140, 0.85), rgba(8, 57, 104, 0.95)), 
                        url('/api/placeholder/1920/300') no-repeat center center/cover;
            padding: 80px 0;
            position: relative;
            overflow: hidden;
        }
        
        .page-header h1 {
            font-weight: 800;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            animation: fadeInUp 1s ease;
        }
        
        .page-header .lead {
            font-weight: 400;
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
        
        /* Register Card */
        .register-card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-top: -40px;
            background-color: #fff;
            position: relative;
            z-index: 10;
            transition: all 0.4s ease;
        }
        
        .register-card:hover {
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            transform: translateY(-5px);
        }
        
        .register-card .card-body {
            padding: 2.5rem;
        }
        
        .register-card .card-title {
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 1.5rem;
            position: relative;
        }
        
        .register-card .card-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background-color: var(--primary);
            border-radius: 3px;
        }
        
        /* Form styles */
        .form-control, .form-select {
            padding: 0.8rem 1rem;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(11, 77, 140, 0.25);
            border-color: var(--primary-light);
        }
        
        .input-group-text {
            background-color: var(--primary-light);
            border-color: #e0e0e0;
            color: var(--primary);
        }
        
        label.form-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
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
        
        /* Benefits cards */
        .benefit-card {
            border-radius: 12px;
            transition: all 0.4s ease;
            border: none;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .benefit-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .benefit-card .card-body {
            padding: 2rem 1.5rem;
        }
        
        .benefit-card i {
            transition: all 0.5s ease;
        }
        
        .benefit-card:hover i {
            transform: rotateY(360deg);
        }
        
        /* Form check */
        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .form-check-label a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .form-check-label a:hover {
            color: var(--primary-dark);
        }
        
        /* Alert styling */
        .alert {
            border-radius: 12px;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        /* Login link */
        .login-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .login-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background-color: var(--primary);
            transition: width 0.3s ease;
        }
        
        .login-link:hover::after {
            width: 100%;
        }
        
        /* Call to action section */
        .cta-section {
            background: linear-gradient(rgba(11, 77, 140, 0.95), rgba(8, 57, 104, 0.95)),
                url('/api/placeholder/1920/600') no-repeat center center/cover;
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
        
        /* Animations for benefits */
        .benefit-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 85px;
            width: 85px;
            line-height: 85px;
            border-radius: 50%;
            background: rgba(11, 77, 140, 0.1);
            color: var(--primary);
            margin: 0 auto 1.5rem;
            transition: all 0.6s ease;
        }
        
        .benefit-card:hover .benefit-icon {
            background: var(--primary);
            color: white;
            transform: rotateY(360deg);
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <?php include 'navbar.php'; ?>

    <!-- Page Header -->
    <header class="page-header text-white text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3" data-aos="fade-up">Join PlayOn Today</h1>
            <p class="lead fs-5 mb-0" data-aos="fade-up" data-aos-delay="100">Create your account and start booking the best cricket venues in Telangana</p>
        </div>
    </header>

    <!-- Registration Form Section -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8" data-aos="fade-up">
                    <div class="card register-card">
                        <div class="card-body">
                            <h2 class="card-title text-center">Create Your Account</h2>
                            
                            <?php if(!empty($errors)): ?>
                                <div class="alert alert-danger mb-4">
                                    <?php foreach($errors as $error): ?>
                                        <p class="mb-0"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></p>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form action="register.php" method="POST" class="row g-3">
                                <div class="col-md-6 mb-3" data-aos="fade-right" data-aos-delay="100">
                                    <label for="name" class="form-label">Full Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter your name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3" data-aos="fade-left" data-aos-delay="100">
                                    <label for="email" class="form-label">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3" data-aos="fade-right" data-aos-delay="200">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                        <input type="tel" class="form-control" id="phone" name="phone" placeholder="Enter your phone number" required value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3" data-aos="fade-left" data-aos-delay="200">
                                    <label for="city" class="form-label">City</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                        <select class="form-select" id="city" name="city" required>
                                            <option value="" selected disabled>Select your city</option>
                                            <option value="hyderabad" <?php echo (isset($_POST['city']) && $_POST['city'] == 'hyderabad') ? 'selected' : ''; ?>>Hyderabad</option>
                                            <option value="warangal" <?php echo (isset($_POST['city']) && $_POST['city'] == 'warangal') ? 'selected' : ''; ?>>Warangal</option>
                                            <option value="nizamabad" <?php echo (isset($_POST['city']) && $_POST['city'] == 'nizamabad') ? 'selected' : ''; ?>>Nizamabad</option>
                                            <option value="karimnagar" <?php echo (isset($_POST['city']) && $_POST['city'] == 'karimnagar') ? 'selected' : ''; ?>>Karimnagar</option>
                                            <option value="khammam" <?php echo (isset($_POST['city']) && $_POST['city'] == 'khammam') ? 'selected' : ''; ?>>Khammam</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3" data-aos="fade-right" data-aos-delay="300">
                                    <label for="password" class="form-label">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" id="password" name="password" placeholder="Choose a password" required>
                                    </div>
                                    <div class="form-text mt-1"><i class="fas fa-info-circle me-1"></i> Password must be at least 8 characters</div>
                                </div>
                                <div class="col-md-6 mb-3" data-aos="fade-left" data-aos-delay="300">
                                    <label for="confirm_password" class="form-label">Confirm Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                                    </div>
                                </div>
                                <div class="col-12 mt-2" data-aos="fade-up" data-aos-delay="400">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                        <label class="form-check-label" for="terms">
                                            I agree to the <a href="terms.php">Terms & Conditions</a> and <a href="privacy.php">Privacy Policy</a>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12 mt-4" data-aos="fade-up" data-aos-delay="500">
                                    <button type="submit" name="register" class="btn btn-primary btn-lg w-100">
                                        <i class="fas fa-user-plus me-2"></i>Create Account
                                    </button>
                                </div>
                            </form>
                            
                            <div class="text-center mt-4" data-aos="fade-up" data-aos-delay="600">
                                <p>Already have an account? <a href="login.php" class="login-link">Login here</a></p>
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
            <h3 class="text-center fw-bold mb-5" data-aos="fade-up">Benefits of Joining PlayOn</h3>
            <div class="row">
                <div class="col-md-3 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="card benefit-card h-100">
                        <div class="card-body text-center">
                            <div class="benefit-icon">
                                <i class="fas fa-calendar-alt fa-2x"></i>
                            </div>
                            <h5 class="fw-bold">Easy Booking</h5>
                            <p class="text-muted mb-0">Book your favorite venues with just a few clicks</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="card benefit-card h-100">
                        <div class="card-body text-center">
                            <div class="benefit-icon">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                            <h5 class="fw-bold">24/7 Access</h5>
                            <p class="text-muted mb-0">Book anytime, day or night, at your convenience</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="card benefit-card h-100">
                        <div class="card-body text-center">
                            <div class="benefit-icon">
                                <i class="fas fa-percent fa-2x"></i>
                            </div>
                            <h5 class="fw-bold">Special Discounts</h5>
                            <p class="text-muted mb-0">Get exclusive offers only available to members</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="card benefit-card h-100">
                        <div class="card-body text-center">
                            <div class="benefit-icon">
                                <i class="fas fa-history fa-2x"></i>
                            </div>
                            <h5 class="fw-bold">Booking History</h5>
                            <p class="text-muted mb-0">Track all your past and upcoming bookings</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonial Section -->
    <section class="py-5">
        <div class="container">
            <h3 class="text-center fw-bold mb-5" data-aos="fade-up">What Our Players Say</h3>
            <div class="row justify-content-center">
                <div class="col-lg-8" data-aos="fade-up" data-aos-delay="100">
                    <div id="testimonialCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body p-4 text-center">
                                        <div class="mb-3">
                                            <i class="fas fa-quote-left fa-3x text-primary opacity-25"></i>
                                        </div>
                                        <p class="lead">"PlayOn made it super easy to find and book cricket venues. The registration process was smooth, and I was playing with my friends the same day!"</p>
                                        <div class="d-flex justify-content-center align-items-center mt-4">
                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                                <span class="fw-bold">RK</span>
                                            </div>
                                            <div class="text-start">
                                                <h5 class="mb-0 fw-bold">Rahul Kumar</h5>
                                                <p class="text-muted mb-0">Hyderabad</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="carousel-item">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body p-4 text-center">
                                        <div class="mb-3">
                                            <i class="fas fa-quote-left fa-3x text-primary opacity-25"></i>
                                        </div>
                                        <p class="lead">"The member benefits are fantastic! I've received multiple discounts on my bookings, and the 24/7 booking system means we can plan late-night matches easily."</p>
                                        <div class="d-flex justify-content-center align-items-center mt-4">
                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                                <span class="fw-bold">AR</span>
                                            </div>
                                            <div class="text-start">
                                                <h5 class="mb-0 fw-bold">Amit Reddy</h5>
                                                <p class="text-muted mb-0">Warangal</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon bg-primary rounded-circle" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon bg-primary rounded-circle" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-5 cta-section text-white text-center">
        <div class="container position-relative" data-aos="zoom-in">
            <h2 class="mb-4 fw-bold">Ready to play cricket?</h2>
            <p class="lead mb-4">Join thousands of cricket enthusiasts already using PlayOn to book venues</p>
            <a href="#" class="btn btn-light btn-lg px-5" onclick="window.scrollTo({top: 0, behavior: 'smooth'}); return false;">
                <i class="fas fa-user-plus me-2"></i>Register Now
            </a>
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
    
    <!-- Custom JavaScript -->
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
            
            if(backToTopButton) {
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
            }
            
            // Password validation and matching
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            
            if(password && confirmPassword) {
                function validatePassword() {
                    if(password.value != confirmPassword.value) {
                        confirmPassword.setCustomValidity("Passwords Don't Match");
                    } else {
                        confirmPassword.setCustomValidity('');
                    }
                }
                
                password.onchange = validatePassword;
                confirmPassword.onkeyup = validatePassword;
            }
            
            // Input animation effect
            const formInputs = document.querySelectorAll('.form-control, .form-select');
            
            formInputs.forEach(input => {
                input.addEventListener('focus', () => {
                    input.parentElement.classList.add('input-focused');
                });
                
                input.addEventListener('blur', () => {
                    if (!input.value) {
                        input.parentElement.classList.remove('input-focused');
                    }
                });
                
                // Check on load if input has value
                if (input.value) {
                    input.parentElement.classList.add('input-focused');
                }
            });
        });
    </script>
    
    <style>
        /* Additional styles for cool effects */
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
        
        .input-focused {
            box-shadow: 0 0 0 0.2rem rgba(11, 77, 140, 0.2);
            transition: all 0.3s ease;
        }
        
        /* Carousel controls styling */
        .carousel-control-prev-icon, .carousel-control-next-icon {
            width: 40px;
            height: 40px;
            padding: 5px;
        }
        
        .carousel-item {
            transition: transform 0.8s ease;
        }
    </style>
</body>
</html>