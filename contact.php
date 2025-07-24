<?php
session_start();
include 'db_connect.php';

// Process contact form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    
    $insert_query = "INSERT INTO contact_messages (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("ssss", $name, $email, $subject, $message);
    
    if ($insert_stmt->execute()) {
        $_SESSION['success_message'] = "Thank you for your message! We'll get back to you soon.";
    } else {
        $_SESSION['error_message'] = "Sorry, there was an error sending your message. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - PlayOn</title>
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
        background: linear-gradient(rgba(11, 78, 140, 0.71), rgba(8, 58, 104, 0.81)),
            url('uploads/images/hero1.webp') no-repeat center center/cover;
        padding: 100px 0;
        position: relative;
        overflow: hidden;
    }

    .page-header h1 {
        font-weight: 800;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
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

    /* Contact Form */
    .contact-form {
        border-radius: 12px;
        border: none;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
        transition: all 0.4s ease;
        overflow: hidden;
    }

    .contact-form:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
    }

    .form-control,
    .form-select {
        padding: 0.7rem 1rem;
        border-radius: 8px;
        border: 1px solid #e0e0e0;
        transition: all 0.3s ease;
    }

    .form-control:focus,
    .form-select:focus {
        box-shadow: 0 0 0 0.2rem rgba(11, 77, 140, 0.25);
        border-color: var(--primary);
    }

    .form-label {
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 0.5rem;
    }

    /* Contact Info Card */
    .contact-info-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
        height: 100%;
        transition: all 0.4s ease;
        overflow: hidden;
    }

    .contact-info-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
    }

    .contact-info-item {
        display: flex;
        margin-bottom: 1.5rem;
        transition: all 0.3s ease;
    }

    .contact-info-item:hover {
        transform: translateX(5px);
    }

    .contact-icon {
        width: 50px;
        height: 50px;
        background-color: rgba(11, 77, 140, 0.1);
        color: var(--primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        flex-shrink: 0;
        font-size: 1.2rem;
        transition: all 0.4s ease;
    }

    .contact-info-item:hover .contact-icon {
        background-color: var(--primary);
        color: white;
        transform: rotateY(180deg);
    }

    .contact-content h5 {
        font-weight: 700;
        font-size: 1.1rem;
        margin-bottom: 0.3rem;
        color: var(--dark);
    }

    .contact-content p {
        font-size: 0.95rem;
        color: #6c757d;
    }

    /* Google Map */
    .map-card {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        transition: all 0.4s ease;
        margin-top: 1.5rem;
    }

    .map-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
    }

    /* Social Icons */
    .social-links {
        display: flex;
        gap: 10px;
    }
    
    .social-links a {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        border: 1px solid var(--primary);
        color: var(--primary);
        transition: all 0.4s ease;
        font-size: 1rem;
    }

    .social-links a:hover {
        background-color: var(--primary);
        color: #fff !important;
        transform: translateY(-5px) rotate(360deg);
    }

    /* Call to Action Section */
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

    /* Buttons */
    .btn {
        border-radius: 50px;
        padding: 0.6rem 1.5rem;
        font-weight: 600;
        transition: all 0.4s ease;
    }

    .btn-lg {
        padding: 0.8rem 2rem;
    }

    .btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
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

    /* Alert styling */
    .alert {
        border-radius: 10px;
        border: none;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    }

    .alert-success {
        background-color: #d1e7dd;
        color: #0f5132;
    }

    .alert-danger {
        background-color: #f8d7da;
        color: #842029;
    }
    </style>
</head>

<body>
    <!-- Navigation Bar -->
    <?php include 'navbar.php'; ?>

    <!-- Page Header -->
    <header class="page-header text-white text-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8" data-aos="fade-up">
                    <h1 class="display-4 fw-bold mb-3">Contact Us</h1>
                    <p class="lead fs-5 mb-0">We'd love to hear from you. Reach out to us with any questions or inquiries.</p>
                </div>
            </div>
        </div>
    </header>

    <!-- Contact Section -->
    <section class="py-5">
        <div class="container">
            <?php if(isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert" data-aos="fade-up">
                    <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert" data-aos="fade-up">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <div class="col-lg-6" data-aos="fade-right" data-aos-delay="100">
                    <div class="card contact-form h-100">
                        <div class="card-body p-4">
                            <h3 class="fw-bold text-primary mb-3">Send Us a Message</h3>
                            <p class="mb-4">Have questions about our services? Fill out the form below and we'll get back to you as soon as possible.</p>

                            <form action="contact.php" method="post">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Your Name</label>
                                    <input type="text" class="form-control" id="name" name="name" placeholder="Enter your full name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email address" required>
                                </div>
                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject</label>
                                    <input type="text" class="form-control" id="subject" name="subject" placeholder="What is this regarding?" required>
                                </div>
                                <div class="mb-4">
                                    <label for="message" class="form-label">Your Message</label>
                                    <textarea class="form-control" id="message" name="message" rows="5" placeholder="Tell us what you need help with..." required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>Send Message
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6" data-aos="fade-left" data-aos-delay="200">
                    <div class="card contact-info-card">
                        <div class="card-body p-4">
                            <h3 class="fw-bold text-primary mb-3">Contact Information</h3>
                            <p class="mb-4">Feel free to get in touch with us through any of these channels:</p>

                            <div class="contact-info-item">
                                <div class="contact-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="contact-content">
                                    <h5>Our Location</h5>
                                    <p class="mb-0">123 Cricket Lane, Hyderabad, Telangana - 500001, India</p>
                                </div>
                            </div>

                            <div class="contact-info-item">
                                <div class="contact-icon">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="contact-content">
                                    <h5>Phone Number</h5>
                                    <p class="mb-0">+91 98765 43210, +91 87654 32109</p>
                                </div>
                            </div>

                            <div class="contact-info-item">
                                <div class="contact-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="contact-content">
                                    <h5>Email Address</h5>
                                    <p class="mb-0">info@playon.com, support@playon.com</p>
                                </div>
                            </div>

                            <div class="contact-info-item">
                                <div class="contact-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="contact-content">
                                    <h5>Business Hours</h5>
                                    <p class="mb-0">Mon-Sat: 9AM-9PM, Sun: 10AM-6PM</p>
                                </div>
                            </div>

                            <div class="mt-4">
                                <h5 class="fw-bold mb-3">Connect With Us</h5>
                                <div class="social-links">
                                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                                    <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                                    <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                                    <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                                </div>
                            </div>
                        </div>
                        <!-- Google Map -->
                        <div class="map-card" data-aos="fade-up" data-aos-delay="300">
                            <iframe
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d243647.3903447236!2d78.24323080089811!3d17.41229968168517!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3bcb99daeaebd2c7%3A0xae93b78392bafbc2!2sHyderabad%2C%20Telangana!5e0!3m2!1sen!2sin!4v1650025674524!5m2!1sen!2sin"
                                width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-5 text-white text-center cta-section">
        <div class="container position-relative" data-aos="fade-up">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h2 class="fw-bold mb-3">Ready to Play Cricket?</h2>
                    <p class="lead mb-4">Discover the perfect venue for your next cricket match</p>
                    <a href="venues.php" class="btn btn-light btn-lg px-5">
                        <i class="fas fa-search me-2"></i>Find Venues
                    </a>
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

        // Form input animation
        const formInputs = document.querySelectorAll('.form-control');
        formInputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('input-focused');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('input-focused');
            });
        });
    });

    // Add input focus effect
    document.head.insertAdjacentHTML('beforeend', 
        '<style>' +
        '.input-focused .form-label { color: var(--primary); }' +
        '.input-focused .form-control { border-color: var(--primary); box-shadow: 0 0 0 0.15rem rgba(11, 77, 140, 0.15); }' +
        '</style>'
    );
    </script>
</body>

</html>