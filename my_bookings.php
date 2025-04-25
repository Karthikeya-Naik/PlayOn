<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Cancel booking
if(isset($_GET['cancel']) && isset($_GET['booking_id'])) {
    $booking_id = mysqli_real_escape_string($conn, $_GET['booking_id']);
    
    // Check if the booking belongs to the user
    $check_query = "SELECT * FROM bookings WHERE booking_id = '$booking_id' AND user_id = '$user_id'";
    $check_result = mysqli_query($conn, $check_query);
    
    if(mysqli_num_rows($check_result) > 0) {
        $booking = mysqli_fetch_assoc($check_result);
        
        // Only allow cancellation for future bookings and if not already cancelled
        $today = date('Y-m-d H:i:s');
        $booking_datetime = $booking['booking_date'] . ' ' . $booking['start_time'];
        
        if(strtotime($booking_datetime) > strtotime($today) && $booking['status'] != 'cancelled') {
            // Update booking status
            $update_query = "UPDATE bookings SET status = 'cancelled' WHERE booking_id = '$booking_id'";
            mysqli_query($conn, $update_query);
            
            $_SESSION['success_message'] = "Your booking has been cancelled successfully.";
        } else {
            $_SESSION['error_message'] = "This booking cannot be cancelled.";
        }
    } else {
        $_SESSION['error_message'] = "Invalid booking.";
    }
    
    header("Location: my_bookings.php");
    exit();
}

// Get user's bookings sorted by date (most recent first)
$query = "SELECT b.*, v.venue_name, v.image, v.city 
          FROM bookings b 
          JOIN venues v ON b.venue_id = v.venue_id 
          WHERE b.user_id = '$user_id' 
          ORDER BY b.booking_date DESC, b.start_time DESC";
          
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - PlayOn</title>
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
    
    /* Navbar improvements */
    .navbar {
        padding: 12px 0;
        box-shadow: 0 2px 15px rgba(0,0,0,0.1);
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
    
    /* Page header */
    .page-header {
        background: linear-gradient(rgba(11, 77, 140, 0.85), rgba(8, 57, 104, 0.95)), 
                    url('/api/placeholder/1920/300') no-repeat center center/cover;
        padding: 80px 0;
        color: white;
        text-align: center;
        margin-bottom: 40px;
        position: relative;
        overflow: hidden;
    }
    
    .page-header h1 {
        font-weight: 700;
        font-size: 2.5rem;
        margin-bottom: 0;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        animation: fadeInUp 1s ease;
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
    
    /* Custom nav tabs */
    .nav-tabs {
        border-bottom: 2px solid #dee2e6;
        margin-bottom: 30px;
    }
    
    .nav-tabs .nav-link {
        border: none;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
        color: var(--dark);
        font-weight: 600;
        padding: 12px 25px;
        transition: all 0.3s ease;
    }
    
    .nav-tabs .nav-link.active {
        color: var(--primary);
        border-bottom: 2px solid var(--primary);
        background-color: transparent;
    }
    
    .nav-tabs .nav-link:hover:not(.active) {
        border-color: transparent;
        color: var(--primary);
    }
    
    /* Booking cards */
    .booking-card {
        border: none;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        transition: all 0.4s ease;
        margin-bottom: 22px;
    }
    
    .booking-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 30px rgba(11, 77, 140, 0.15);
    }
    
    .booking-card .card-body {
        padding: 1.5rem;
    }
    
    .booking-img-container {
        height: 130px;
        position: relative;
        overflow: hidden;
        border-radius: 10px;
    }
    
    .booking-img-container img {
        height: 100%;
        width: 100%;
        object-fit: cover;
        transition: all 0.6s ease;
    }
    
    .booking-card:hover .booking-img-container img {
        transform: scale(1.08);
    }
    
    .venue-title {
        font-weight: 700;
        margin-bottom: 10px;
        color: var(--dark);
    }
    
    .venue-location {
        color: #6c757d;
        margin-bottom: 8px;
    }
    
    .venue-location i {
        color: var(--primary);
    }
    
    .booking-details {
        margin-bottom: 10px;
    }
    
    .booking-details i {
        color: var(--primary);
    }
    
    .badge {
        font-size: 0.85rem;
        padding: 7px 14px;
        border-radius: 50px;
        font-weight: 600;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .badge-success {
        background-color: #198754;
    }
    
    .badge-secondary {
        background-color: #6c757d;
    }
    
    .badge-danger {
        background-color: #dc3545;
    }
    
    .booking-price {
        font-weight: 700;
        font-size: 1.25rem;
        color: var(--primary);
        margin-bottom: 18px;
    }
    
    .btn {
        border-radius: 50px;
        padding: 8px 22px;
        font-weight: 600;
        transition: all 0.4s ease;
    }
    
    .btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.15);
    }
    
    .btn-outline-danger {
        border: 2px solid #dc3545;
        color: #dc3545;
    }
    
    .btn-outline-danger:hover {
        background-color: #dc3545;
        color: white;
    }
    
    .btn-outline-primary {
        border: 2px solid var(--primary);
        color: var(--primary);
    }
    
    .btn-outline-primary:hover {
        background-color: var(--primary);
        color: white;
    }
    
    /* Alert styling */
    .alert {
        border-radius: 12px;
        border: none;
        padding: 16px 20px;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }
    
    .alert::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 5px;
    }
    
    .alert-success {
        background-color: rgba(25, 135, 84, 0.1);
        color: #198754;
    }
    
    .alert-success::before {
        background-color: #198754;
    }
    
    .alert-danger {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }
    
    .alert-danger::before {
        background-color: #dc3545;
    }
    
    .alert-info {
        background-color: rgba(11, 77, 140, 0.1);
        color: var(--primary);
    }
    
    .alert-info::before {
        background-color: var(--primary);
    }
    
    .alert-link {
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    
    .alert-link:hover {
        text-decoration: underline;
    }
    
    /* Tab content animation */
    .tab-pane.fade {
        transition: opacity 0.3s ease-in-out;
    }
    
    .tab-pane.fade.show {
        opacity: 1;
    }
    
    /* Empty state */
    .empty-state {
        text-align: center;
        padding: 40px 0;
        background-color: var(--primary-light);
        border-radius: 12px;
        margin-bottom: 30px;
    }
    
    .empty-state i {
        font-size: 3rem;
        color: var(--primary);
        margin-bottom: 20px;
    }
    
    .empty-state h4 {
        color: var(--dark);
        font-weight: 700;
        margin-bottom: 10px;
    }
    
    .empty-state p {
        color: #6c757d;
        margin-bottom: 20px;
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
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .booking-img-container {
            height: 100px;
            margin-bottom: 15px;
        }
        
        .page-header {
            padding: 60px 0;
        }
        
        .page-header h1 {
            font-size: 2rem;
        }
        
        .nav-tabs .nav-link {
            padding: 10px 15px;
        }
    }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <?php include 'navbar.php'; ?>
    
    <!-- Page Header -->
    <header class="page-header">
        <div class="container" data-aos="fade-up">
            <h1>My Bookings</h1>
        </div>
    </header>
    
    <div class="container mb-5">
        <!-- Alerts Section -->
        <?php if(isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success" data-aos="fade-up">
                <i class="fas fa-check-circle me-2"></i>
                <?php 
                echo $_SESSION['success_message']; 
                unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger" data-aos="fade-up">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php 
                echo $_SESSION['error_message']; 
                unset($_SESSION['error_message']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php if(mysqli_num_rows($result) > 0): ?>
            <!-- Booking Navigation Tabs -->
            <ul class="nav nav-tabs" id="bookingTabs" role="tablist" data-aos="fade-up">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="upcoming-tab" data-bs-toggle="tab" data-bs-target="#upcoming" type="button" role="tab">
                        <i class="fas fa-calendar-day me-2"></i>Upcoming
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="past-tab" data-bs-toggle="tab" data-bs-target="#past" type="button" role="tab">
                        <i class="fas fa-history me-2"></i>Past
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="cancelled-tab" data-bs-toggle="tab" data-bs-target="#cancelled" type="button" role="tab">
                        <i class="fas fa-ban me-2"></i>Cancelled
                    </button>
                </li>
            </ul>
            
            <!-- Tab Content -->
            <div class="tab-content mt-4" id="bookingTabsContent">
                <!-- Upcoming Bookings -->
                <div class="tab-pane fade show active" id="upcoming" role="tabpanel" aria-labelledby="upcoming-tab">
                    <?php 
                    $has_upcoming = false;
                    mysqli_data_seek($result, 0); // Reset result pointer
                    $delay = 100;
                    
                    while($booking = mysqli_fetch_assoc($result)):
                        $booking_datetime = $booking['booking_date'] . ' ' . $booking['start_time'];
                        $today = date('Y-m-d H:i:s');
                        
                        if(strtotime($booking_datetime) > strtotime($today) && $booking['status'] != 'cancelled'):
                            $has_upcoming = true;
                    ?>
                    <div class="card booking-card" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    <div class="booking-img-container">
                                        <img src="uploads/venues/<?php echo $booking['image']; ?>" alt="<?php echo $booking['venue_name']; ?>">
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <h5 class="venue-title"><?php echo $booking['venue_name']; ?></h5>
                                    <p class="venue-location"><i class="fas fa-map-marker-alt me-2"></i><?php echo $booking['city']; ?>, Telangana</p>
                                    <p class="booking-details">
                                        <i class="far fa-calendar-alt me-2"></i> 
                                        <?php echo date('d M Y', strtotime($booking['booking_date'])); ?> | 
                                        <i class="far fa-clock ms-2 me-2"></i> 
                                        <?php echo date('h:i A', strtotime($booking['start_time'])); ?> - 
                                        <?php echo date('h:i A', strtotime($booking['end_time'])); ?>
                                    </p>
                                    <p>
                                        <span class="badge bg-success"><?php echo ucfirst($booking['status']); ?></span>
                                        <span class="text-muted ms-2">Booking ID: <?php echo $booking['booking_id']; ?></span>
                                    </p>
                                </div>
                                <div class="col-md-3 text-end">
                                    <div class="booking-price">₹<?php echo $booking['total_amount']; ?></div>
                                    <a href="my_bookings.php?cancel=1&booking_id=<?php echo $booking['booking_id']; ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to cancel this booking?')">
                                        <i class="fas fa-times me-2"></i> Cancel Booking
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php 
                        $delay += 100;
                        endif;
                    endwhile;
                    
                    if(!$has_upcoming):
                    ?>
                    <div class="empty-state" data-aos="fade-up">
                        <i class="fas fa-calendar-xmark"></i>
                        <h4>No Upcoming Bookings</h4>
                        <p>You don't have any upcoming cricket sessions scheduled.</p>
                        <a href="venues.php" class="btn btn-primary">Book a Venue Now</a>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Past Bookings -->
                <div class="tab-pane fade" id="past" role="tabpanel" aria-labelledby="past-tab">
                    <?php 
                    $has_past = false;
                    mysqli_data_seek($result, 0); // Reset result pointer
                    $delay = 100;
                    
                    while($booking = mysqli_fetch_assoc($result)):
                        $booking_datetime = $booking['booking_date'] . ' ' . $booking['start_time'];
                        $today = date('Y-m-d H:i:s');
                        
                        if(strtotime($booking_datetime) < strtotime($today) && $booking['status'] != 'cancelled'):
                            $has_past = true;
                    ?>
                    <div class="card booking-card" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    <div class="booking-img-container">
                                        <img src="uploads/venues/<?php echo $booking['image']; ?>" alt="<?php echo $booking['venue_name']; ?>">
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <h5 class="venue-title"><?php echo $booking['venue_name']; ?></h5>
                                    <p class="venue-location"><i class="fas fa-map-marker-alt me-2"></i><?php echo $booking['city']; ?>, Telangana</p>
                                    <p class="booking-details">
                                        <i class="far fa-calendar-alt me-2"></i> 
                                        <?php echo date('d M Y', strtotime($booking['booking_date'])); ?> | 
                                        <i class="far fa-clock ms-2 me-2"></i> 
                                        <?php echo date('h:i A', strtotime($booking['start_time'])); ?> - 
                                        <?php echo date('h:i A', strtotime($booking['end_time'])); ?>
                                    </p>
                                    <p>
                                        <span class="badge bg-secondary"><?php echo ucfirst($booking['status']); ?></span>
                                        <span class="text-muted ms-2">Booking ID: <?php echo $booking['booking_id']; ?></span>
                                    </p>
                                </div>
                                <div class="col-md-3 text-end">
                                    <div class="booking-price">₹<?php echo $booking['total_amount']; ?></div>
                                    <a href="booking_details.php?booking_id=<?php echo $booking['booking_id']; ?>" class="btn btn-outline-primary">
                                        <i class="fas fa-eye me-2"></i> View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php 
                        $delay += 100;
                        endif;
                    endwhile;
                    
                    if(!$has_past):
                    ?>
                    <div class="empty-state" data-aos="fade-up">
                        <i class="fas fa-history"></i>
                        <h4>No Past Bookings</h4>
                        <p>You haven't completed any cricket sessions yet.</p>
                        <a href="venues.php" class="btn btn-primary">Explore Venues</a>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Cancelled Bookings -->
                <div class="tab-pane fade" id="cancelled" role="tabpanel" aria-labelledby="cancelled-tab">
                    <?php 
                    $has_cancelled = false;
                    mysqli_data_seek($result, 0); // Reset result pointer
                    $delay = 100;
                    
                    while($booking = mysqli_fetch_assoc($result)):
                        if($booking['status'] == 'cancelled'):
                            $has_cancelled = true;
                    ?>
                    <div class="card booking-card" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    <div class="booking-img-container">
                                        <img src="uploads/venues/<?php echo $booking['image']; ?>" alt="<?php echo $booking['venue_name']; ?>">
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <h5 class="venue-title"><?php echo $booking['venue_name']; ?></h5>
                                    <p class="venue-location"><i class="fas fa-map-marker-alt me-2"></i><?php echo $booking['city']; ?>, Telangana</p>
                                    <p class="booking-details">
                                        <i class="far fa-calendar-alt me-2"></i> 
                                        <?php echo date('d M Y', strtotime($booking['booking_date'])); ?> | 
                                        <i class="far fa-clock ms-2 me-2"></i> 
                                        <?php echo date('h:i A', strtotime($booking['start_time'])); ?> - 
                                        <?php echo date('h:i A', strtotime($booking['end_time'])); ?>
                                    </p>
                                    <p>
                                        <span class="badge bg-danger"><?php echo ucfirst($booking['status']); ?></span>
                                        <span class="text-muted ms-2">Booking ID: <?php echo $booking['booking_id']; ?></span>
                                    </p>
                                </div>
                                <div class="col-md-3 text-end">
                                    <div class="booking-price">₹<?php echo $booking['total_amount']; ?></div>
                                    <a href="booking_details.php?booking_id=<?php echo $booking['booking_id']; ?>" class="btn btn-outline-primary">
                                        <i class="fas fa-eye me-2"></i> View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php 
                        $delay += 100;
                        endif;
                    endwhile;
                    
                    if(!$has_cancelled):
                    ?>
                    <div class="empty-state" data-aos="fade-up">
                        <i class="fas fa-ban"></i>
                        <h4>No Cancelled Bookings</h4>
                        <p>You don't have any cancelled cricket sessions.</p>
                        <a href="venues.php" class="btn btn-primary">Browse Venues</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-state" data-aos="fade-up">
                <i class="fas fa-calendar-plus"></i>
                <h4>No Bookings Found</h4>
                <p>You haven't made any cricket venue bookings yet.</p>
                <a href="venues.php" class="btn btn-primary">Book Your First Cricket Session</a>
            </div>
        <?php endif; ?>
    </div>
    
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
            offset: 100,
            easing: 'ease-in-out'
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