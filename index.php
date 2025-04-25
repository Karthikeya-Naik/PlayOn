<?php
// Start session if not already started
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PlayOn - Box Cricket Booking</title>
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

    .hero-section {
        background: url('uploads/images/hero1.png') no-repeat center center/cover;
        min-height: 700px;
        display: flex;
        align-items: center;
        position: relative;
        overflow: hidden;
    }

    .hero-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        z-index: 1;
    }

    .hero-section .container {
        position: relative;
        z-index: 2;
    }

    .hero-section h1 {
        font-size: 4rem;
        font-weight: 800;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.4);
        animation: fadeInUp 1s ease;
    }

    .hero-section .lead {
        font-weight: 400;
        font-size: 1.8rem;
        margin-bottom: 2rem;
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

    /* For better text readability without gradient */
    .hero-section .btn-outline-light {
        border: 2px solid #fff;
    }

    .hero-section .btn-outline-light:hover {
        background-color: #fff;
        color: #0b4d8c;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .hero-section {
            min-height: 500px;
        }

        .hero-section h1 {
            font-size: 3rem;
        }

        .hero-section .lead {
            font-size: 1.4rem;
        }
    }

    /* Venue Cards */
    .venue-img {
        height: 200px;
        object-fit: cover;
        transition: all 0.6s ease;
    }

    .card {
        border: none;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.4s ease;
    }

    .card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
    }

    .card:hover .venue-img {
        transform: scale(1.08);
    }

    .card-title {
        font-weight: 700;
        margin-bottom: 8px;
        color: var(--dark);
    }

    .card-body {
        padding: 1.5rem;
    }

    .venue-location {
        font-size: 0.9rem;
    }

    .venue-location i {
        color: var(--primary);
    }

    .venue-price {
        font-weight: 600;
        color: var(--primary);
        margin-bottom: 1rem;
    }

    /* How It Works Section */
    .bg-light {
        background-color: #f8f9fa;
    }

    .icon-box {
        padding: 2.5rem;
        border-radius: 12px;
        background: white;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        height: 100%;
        transition: all 0.4s ease;
        border-bottom: 3px solid transparent;
    }

    .icon-box:hover {
        transform: translateY(-8px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        border-bottom: 3px solid var(--primary);
    }

    .icon-box i {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 85px;
        width: 85px;
        line-height: 85px;
        border-radius: 50%;
        background: rgba(11, 77, 140, 0.1);
        color: var(--primary);
        margin-bottom: 1.5rem;
        transition: all 0.6s ease;
    }

    .icon-box:hover i {
        background: var(--primary);
        color: white;
        transform: rotateY(360deg);
    }

    .icon-box h4 {
        font-weight: 700;
        margin-bottom: 1rem;
    }

    /* Stats Section */
    .stats-section {
        padding: 5rem 0;
        background: linear-gradient(rgba(8, 57, 104, 0.95), rgba(8, 57, 104, 0.95)),
            url('/api/placeholder/1920/600') no-repeat center center/cover;
    }

    .stat-item {
        text-align: center;
        padding: 2rem;
        transition: all 0.3s ease;
    }

    .stat-item:hover .stat-number {
        transform: scale(1.1);
    }

    .stat-number {
        font-size: 3.2rem;
        font-weight: 700;
        margin-bottom: 1rem;
        color: #fff;
        transition: all 0.3s ease;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .stat-label {
        font-size: 1.2rem;
        color: #e6f0fa;
    }

    /* Reviews Section */
    .reviews-section .card {
        height: 100%;
        padding: 1.5rem;
    }

    .reviews-section .card-body {
        position: relative;
        padding-top: 0;
    }

    .reviews-section .card-body::before {
        content: '\201C';
        font-family: Georgia, serif;
        font-size: 5rem;
        position: absolute;
        top: -30px;
        left: -10px;
        line-height: 1;
        color: rgba(11, 77, 140, 0.2);
    }

    /* Mobile App Features */
    .feature-item {
        display: flex;
        margin-bottom: 2rem;
        transition: all 0.3s ease;
    }

    .feature-item:hover {
        transform: translateX(5px);
    }

    .feature-icon {
        background-color: rgba(11, 77, 140, 0.1);
        color: var(--primary);
        width: 60px;
        height: 60px;
        line-height: 60px;
        border-radius: 50%;
        text-align: center;
        margin-right: 1.5rem;
        flex-shrink: 0;
        transition: all 0.3s ease;
    }

    .feature-item:hover .feature-icon {
        background-color: var(--primary);
        color: white;
        transform: rotateY(180deg);
    }

    .feature-content h5 {
        font-weight: 700;
        margin-bottom: 0.5rem;
        color: var(--dark);
    }

    /* FAQ Section */
    .accordion-item {
        border: none;
        margin-bottom: 1rem;
        border-radius: 10px !important;
        overflow: hidden;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
    }

    .accordion-button {
        padding: 1.25rem;
        font-weight: 600;
        background-color: white;
    }

    .accordion-button:not(.collapsed) {
        background-color: rgba(11, 77, 140, 0.1);
        color: var(--primary);
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
        background: url('/api/placeholder/800/600') no-repeat center center/cover;
        opacity: 0.1;
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

    /* Button styles */
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
    </style>
</head>

<body>
    <!-- Navigation Bar -->
    <?php include 'navbar.php'; ?>

    <!-- Hero Section -->
    <header class="hero-section text-white text-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8" data-aos="fade-up">
                    <h1 class="display-3 fw-bold mb-4">PlayOn</h1>
                    <p class="lead fs-4 mb-5">Book Your Box Cricket Experience Instantly</p>
                    <div class="d-flex justify-content-center gap-3 mt-4">
                        <a href="venues.php" class="btn btn-primary btn-lg">Find Venues</a>
                        <a href="about.php" class="btn btn-outline-light btn-lg">Learn More</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Stats Section -->
    <section class="stats-section text-white">
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

    <!-- Featured Venues Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5 fw-bold" data-aos="fade-up">Top Box Cricket Venues</h2>
            <div class="row">
                <?php
                include 'db_connect.php';
                $sql = "SELECT * FROM venues ORDER BY price_per_hour DESC LIMIT 4";
                $result = mysqli_query($conn, $sql);
                
                $delay = 100;
                while($row = mysqli_fetch_assoc($result)) {
                ?>
                <div class="col-md-3 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                    <div class="card h-100">
                        <img src="uploads/venues/<?php echo $row['image']; ?>" class="card-img-top venue-img"
                            alt="<?php echo $row['venue_name']; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $row['venue_name']; ?></h5>
                            <p class="card-text text-muted venue-location"><i
                                    class="fas fa-map-marker-alt me-2"></i><?php echo $row['city']; ?>,
                                <?php echo $row['state']; ?></p>
                            <p class="card-text venue-price">₹<?php echo $row['price_per_hour']; ?> per hour</p>
                            <a href="venue_details.php?id=<?php echo $row['venue_id']; ?>"
                                class="btn btn-primary w-100">View Details</a>
                        </div>
                    </div>
                </div>
                <?php 
                    $delay += 100;
                } 
                ?>
            </div>
            <div class="text-center mt-5" data-aos="fade-up">
                <a href="venues.php" class="btn btn-outline-primary btn-lg">View All Venues</a>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5 fw-bold" data-aos="fade-up">How It Works</h2>
            <div class="row">
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="icon-box text-center">
                        <i class="fas fa-search fa-3x"></i>
                        <h4>Find a Venue</h4>
                        <p>Search for nearby cricket venues and check availability</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="icon-box text-center">
                        <i class="fas fa-calendar-check fa-3x"></i>
                        <h4>Book Your Slot</h4>
                        <p>Choose your preferred date and time to reserve your spot</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="icon-box text-center">
                        <i class="fa-solid fa-baseball-bat-ball fa-3x"></i>
                        <h4>Play Cricket</h4>
                        <p>Arrive and enjoy your game with friends and colleagues</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mobile App Features -->
    <section class="app-features py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0" data-aos="fade-right">
                    <img src="uploads/images/mobile.png" alt="Mobile App" class="img-fluid rounded-3 shadow">
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <h2 class="mb-4 fw-bold">Download Our Mobile App</h2>
                    <p class="lead mb-4">Book cricket venues on the go with our easy-to-use mobile application.</p>

                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <div class="feature-content">
                            <h5>Quick Booking</h5>
                            <p>Book your preferred venue in less than 2 minutes</p>
                        </div>
                    </div>

                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-bell"></i>
                        </div>
                        <div class="feature-content">
                            <h5>Notifications</h5>
                            <p>Get instant notifications for booking confirmations and reminders</p>
                        </div>
                    </div>

                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="feature-content">
                            <h5>Team Management</h5>
                            <p>Create and manage your teams for upcoming matches</p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="#" class="btn btn-dark me-3">
                            <i class="fab fa-google-play me-2"></i> Google Play
                        </a>
                        <a href="#" class="btn btn-dark">
                            <i class="fab fa-apple me-2"></i> App Store
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Reviews Section -->
    <section class="py-5 bg-light reviews-section">
        <div class="container">
            <h2 class="text-center mb-5 fw-bold" data-aos="fade-up">What Our Users Say</h2>
            <div class="row">
                <?php
                $sql = "SELECT r.*, u.name, v.venue_name FROM reviews r 
                        JOIN users u ON r.user_id = u.user_id 
                        JOIN venues v ON r.venue_id = v.venue_id 
                        ORDER BY r.rating DESC LIMIT 4";
                $result = mysqli_query($conn, $sql);
                
                $delay = 100;
                while($row = mysqli_fetch_assoc($result)) {
                ?>
                <div class="col-md-6 col-lg-3 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="mb-3">
                                <?php for($i = 1; $i <= 5; $i++) { ?>
                                <i
                                    class="fas fa-star <?php echo ($i <= $row['rating']) ? 'text-warning' : 'text-muted'; ?>"></i>
                                <?php } ?>
                            </div>
                            <p class="card-text">"<?php echo $row['comment']; ?>"</p>
                            <div class="d-flex align-items-center mt-3">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3"
                                    style="width: 40px; height: 40px;">
                                    <span><?php echo substr($row['name'], 0, 1); ?></span>
                                </div>
                                <div>
                                    <p class="mb-0 fw-bold"><?php echo $row['name']; ?></p>
                                    <p class="card-text"><small class="text-muted">For
                                            <?php echo $row['venue_name']; ?></small></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php 
                    $delay += 100;
                } 
                ?>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section py-5">
        <div class="container">
            <h2 class="text-center mb-5 fw-bold" data-aos="fade-up">Frequently Asked Questions</h2>
            <div class="row justify-content-center">
                <div class="col-lg-8" data-aos="fade-up" data-aos-delay="100">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseOne">
                                    How do I book a cricket venue?
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne"
                                data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Booking a cricket venue is simple! Just browse our available venues, select your
                                    preferred date and time, and complete the payment process. You'll receive a
                                    confirmation email with all the details.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseTwo">
                                    Can I cancel my booking?
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo"
                                data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Yes, you can cancel your booking up to 24 hours before your scheduled time.
                                    Cancellations made within 24 hours of the booking time may be subject to a
                                    cancellation fee.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseThree">
                                    Is equipment provided at the venues?
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree"
                                data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Most venues provide basic cricket equipment like bats, balls, and stumps. However,
                                    specific equipment availability may vary by venue. You can check the venue details
                                    page for more information.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingFour">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseFour">
                                    How many players can play at once?
                                </button>
                            </h2>
                            <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour"
                                data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Box cricket venues typically accommodate teams of 5-8 players per side. The exact
                                    capacity depends on the venue size. Check the venue details for specific
                                    information.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-5 bg-primary text-white text-center cta-section">
        <div class="container position-relative" data-aos="zoom-in">
            <h2 class="mb-4 fw-bold">Ready to book your cricket experience?</h2>
            <p class="lead mb-4">Join thousands of cricket enthusiasts who book through PlayOn every day</p>
            <a href="venues.php" class="btn btn-light btn-lg px-5">Book Now</a>
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
    });
    </script>
</body>

</html>