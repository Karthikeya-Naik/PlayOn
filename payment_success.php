<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if payment was successful
if(!isset($_SESSION['payment_success']) || $_SESSION['payment_success'] !== true) {
    header("Location: index.php");
    exit();
}

// Get booking details
$booking_number = $_SESSION['booking_number'];

// Clear success message after displaying it
$payment_success = $_SESSION['payment_success'];
unset($_SESSION['payment_success']);
unset($_SESSION['booking_number']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - PlayOn</title>
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
        --success: #198754;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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

    /* Breadcrumb */
    .breadcrumb-section {
        background-color: #f8f9fa;
        padding: 15px 0;
        border-bottom: 1px solid #eee;
    }

    .breadcrumb-section a {
        color: var(--primary);
        transition: all 0.3s ease;
    }

    .breadcrumb-section a:hover {
        color: var(--primary-dark);
    }

    /* Success header */
    .success-header {
        position: relative;
        background: linear-gradient(rgba(11, 77, 140, 0.9), rgba(8, 57, 104, 0.95));
        padding: 80px 0 60px;
        color: white;
        overflow: hidden;
    }

    .success-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: url('/api/placeholder/1920/600') no-repeat center center/cover;
        opacity: 0.1;
        z-index: 0;
    }

    .success-header .container {
        position: relative;
        z-index: 1;
    }

    .success-header h1 {
        font-weight: 800;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        animation: fadeInUp 1s ease;
    }

    .success-header p {
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

    /* Success cards */
    .success-card {
        background-color: #fff;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        margin-bottom: 30px;
        border: none;
        transition: all 0.4s ease;
    }

    .success-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
    }

    .card-header {
        color: white;
        font-weight: 600;
        border: none;
        padding: 18px 25px;
    }

    .card-body {
        padding: 30px;
    }

    .success-icon {
        font-size: 90px;
        color: var(--success);
        margin-bottom: 25px;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
        100% {
            transform: scale(1);
        }
    }

    /* Feature boxes */
    .feature-box {
        text-align: center;
        padding: 25px;
        border-radius: 12px;
        background: white;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        height: 100%;
        transition: all 0.4s ease;
        border-bottom: 3px solid transparent;
    }

    .feature-box:hover {
        transform: translateY(-8px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        border-bottom: 3px solid var(--primary);
    }

    .feature-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 70px;
        width: 70px;
        line-height: 70px;
        border-radius: 50%;
        background: rgba(11, 77, 140, 0.1);
        color: var(--primary);
        margin: 0 auto 20px;
        font-size: 28px;
        transition: all 0.6s ease;
    }

    .feature-box:hover .feature-icon {
        background: var(--primary);
        color: white;
        transform: rotateY(360deg);
    }

    /* Buttons */
    .btn {
        border-radius: 50px;
        padding: 12px 28px;
        font-weight: 600;
        transition: all 0.4s ease;
    }

    .btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 20px rgba(11, 77, 140, 0.3);
    }

    .btn-primary {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    .btn-primary:hover, 
    .btn-primary:focus {
        background-color: var(--primary-dark);
        border-color: var(--primary-dark);
    }

    .btn-outline-primary {
        border-color: var(--primary);
        color: var(--primary);
    }

    .btn-outline-primary:hover {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    .btn-success {
        padding: 12px 30px;
        font-size: 1.1rem;
    }

    .btn-lg {
        padding: 15px 35px;
    }

    /* Alert styling */
    .alert-info {
        background-color: var(--primary-light);
        color: var(--primary-dark);
        border: none;
        border-left: 4px solid var(--primary);
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

    /* Next steps section */
    .next-steps-section {
        padding: 30px 0 60px;
    }

    /* Additional content section */
    .additional-content {
        background-color: #f8f9fa;
        padding: 60px 0;
        margin-top: 30px;
    }

    .support-card {
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        text-align: center;
        transition: all 0.3s ease;
        height: 100%;
    }

    .support-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    }

    .support-icon {
        font-size: 40px;
        color: var(--primary);
        margin-bottom: 20px;
    }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <?php include 'navbar.php'; ?>
    
    <!-- Breadcrumb -->
    <div class="breadcrumb-section">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php" style="text-decoration:none;">Home</a></li>
                    <li class="breadcrumb-item"><a href="cart.php" style="text-decoration:none;">Shopping Cart</a></li>
                    <li class="breadcrumb-item"><a href="payment.php" style="text-decoration:none;">Checkout</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Payment Success</li>
                </ol>
            </nav>
        </div>
    </div>
    
    <!-- Success Header -->
    <header class="success-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <h1 class="display-4">Booking Confirmed!</h1>
                    <p class="lead fs-4 mb-0">
                        Thank you for choosing PlayOn. Your cricket venue is reserved and ready for action!
                    </p>
                </div>
            </div>
        </div>
    </header>
    
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="success-card" data-aos="fade-up">
                    <div class="card-header bg-success">
                        <h4 class="mb-0"><i class="fas fa-check-circle me-2"></i>Payment Successful</h4>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-4">
                            <i class="fas fa-check-circle success-icon"></i>
                        </div>
                        <h3 class="mb-3">Thank you for your booking!</h3>
                        <p class="lead mb-4">Your payment has been processed successfully.</p>
                        
                        <div class="alert alert-info my-4">
                            <p class="mb-0"><strong>Booking Number:</strong> <?php echo $booking_number; ?></p>
                            <p class="mb-0 small mt-2">Please save this booking number for future reference</p>
                        </div>
                        
                        <p>A confirmation email has been sent to your registered email address with all the booking details.</p>
                        
                        <div class="my-4 py-2">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-clock text-primary me-3" style="font-size: 24px;"></i>
                                        <div>
                                            <h6 class="mb-1">Arrive Early</h6>
                                            <p class="small mb-0">Please arrive 15 minutes before your slot</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-id-card text-primary me-3" style="font-size: 24px;"></i>
                                        <div>
                                            <h6 class="mb-1">Bring ID</h6>
                                            <p class="small mb-0">Please bring your ID for verification</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4 pt-2">
                            <a href="my_bookings.php" class="btn btn-primary me-3">
                                <i class="fas fa-list me-2"></i> View My Bookings
                            </a>
                            <a href="index.php" class="btn btn-outline-primary">
                                <i class="fas fa-home me-2"></i> Back to Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Next Steps Section -->
    <section class="next-steps-section">
        <div class="container">
            <h2 class="text-center mb-5 fw-bold" data-aos="fade-up">What's Next?</h2>
            <div class="row">
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h5>Check Email</h5>
                        <p>Verify all booking details in your confirmation email</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h5>Visit Venue</h5>
                        <p>Arrive 15 minutes early with your booking reference</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <h5>Rate Your Experience</h5>
                        <p>Share your feedback after playing to help others</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Additional Content -->
    <section class="additional-content">
        <div class="container">
            <h2 class="text-center mb-5 fw-bold" data-aos="fade-up">Need Assistance?</h2>
            <div class="row">
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="support-card">
                        <i class="fas fa-headset support-icon"></i>
                        <h5>Customer Support</h5>
                        <p>Our team is available 24/7 to assist you</p>
                        <a href="contact.php" class="btn btn-sm btn-primary mt-3">Contact Us</a>
                    </div>
                </div>
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="support-card">
                        <i class="fas fa-file-alt support-icon"></i>
                        <h5>FAQs</h5>
                        <p>Find answers to common booking questions</p>
                        <a href="faq.php" class="btn btn-sm btn-primary mt-3">Read FAQs</a>
                    </div>
                </div>
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="support-card">
                        <i class="fas fa-exchange-alt support-icon"></i>
                        <h5>Cancellation Policy</h5>
                        <p>Learn about our booking modification policies</p>
                        <a href="policy.php" class="btn btn-sm btn-primary mt-3">View Policy</a>
                    </div>
                </div>
            </div>
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
    });
    </script>
</body>
</html>