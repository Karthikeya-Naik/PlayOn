<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if cart exists and is not empty
if(!isset($_SESSION['cart']) || count($_SESSION['cart']) == 0) {
    header("Location: cart.php");
    exit();
}

// Get user details
$user_id = $_SESSION['user_id'];
$user_query = "SELECT * FROM users WHERE user_id = '$user_id'";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

// Calculate grand total
$grand_total = 0;
foreach($_SESSION['cart'] as $item) {
    $grand_total += $item['total_amount'];
}

// Process payment
if(isset($_POST['confirm_payment'])) {
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $contact_name = mysqli_real_escape_string($conn, $_POST['contact_name']);
    $contact_phone = mysqli_real_escape_string($conn, $_POST['contact_phone']);
    $contact_email = mysqli_real_escape_string($conn, $_POST['contact_email']);
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Create bookings for each cart item
        foreach($_SESSION['cart'] as $item) {
            // Generate booking number
            $booking_number = 'BK-' . date('Ymd') . '-' . sprintf('%03d', rand(1, 999));
            
            // Insert booking
            $venue_id = $item['venue_id'];
            $booking_date = $item['booking_date'];
            $start_time = $item['start_time'];
            $end_time = $item['end_time'];
            $duration = $item['duration'];
            $amount = $item['total_amount'];
            
            $booking_query = "INSERT INTO bookings (booking_id, user_id, venue_id, booking_date, start_time, end_time, duration, total_amount, status, payment_status) 
                              VALUES ('$booking_number', '$user_id', '$venue_id', '$booking_date', '$start_time', '$end_time', '$duration', '$amount', 'confirmed', 'completed')";
            
            if(!mysqli_query($conn, $booking_query)) {
                throw new Exception("Error creating booking: " . mysqli_error($conn));
            }
            
            $booking_id = mysqli_insert_id($conn);
            
            // Generate a transaction ID
            $transaction_id = 'TXN-' . rand(100000000, 999999999);
            
            // Insert payment
            $payment_query = "INSERT INTO payments (booking_id, amount, payment_method, transaction_id, status) 
                             VALUES ('$booking_id', '$amount', '$payment_method', '$transaction_id', 'completed')";
            
            if(!mysqli_query($conn, $payment_query)) {
                throw new Exception("Error recording payment: " . mysqli_error($conn));
            }
        }
        
        // Commit transaction
        mysqli_commit($conn);
        
        // Clear the cart
        unset($_SESSION['cart']);
        
        // Set success message
        $_SESSION['payment_success'] = true;
        $_SESSION['booking_number'] = $booking_number;
        
        // Redirect to success page 
        header("Location: payment_success.php");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        $error_message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - PlayOn</title>
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

    /* Navbar styles aligned with index.php */
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
        transition: all 0.4s ease;
    }

    .breadcrumb-section a {
        color: var(--primary);
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .breadcrumb-section a:hover {
        color: var(--primary-dark);
    }

    /* Payment Header */
    .payment-header {
        background: linear-gradient(rgba(11, 77, 140, 0.85), rgba(8, 57, 104, 0.95));
        padding: 60px 0;
        color: white;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }

    .payment-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: url('/api/placeholder/1920/600') no-repeat center center/cover;
        opacity: 0.1;
        z-index: 1;
    }

    .payment-header .container {
        position: relative;
        z-index: 2;
    }

    .payment-header h1 {
        font-weight: 800;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        animation: fadeInUp 1s ease;
    }

    .payment-header .lead {
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

    /* Payment cards */
    .payment-card {
        background-color: #fff;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        margin-bottom: 30px;
        transition: all 0.4s ease;
        border: none;
    }

    .payment-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        background-color: var(--primary);
        color: white;
        font-weight: 600;
        border: none;
        padding: 18px 20px;
    }

    .card-body {
        padding: 30px;
    }

    /* Form styles */
    .form-group {
        margin-bottom: 24px;
    }

    .form-control {
        padding: 12px 15px;
        border-radius: 8px;
        border: 1px solid #dee2e6;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.25rem rgba(11, 77, 140, 0.25);
    }

    .form-check-input {
        width: 1.1em;
        height: 1.1em;
        margin-top: 0.25em;
        transition: all 0.3s ease;
    }

    .form-check-input:checked {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    .form-check-label {
        font-weight: 500;
    }

    .form-check {
        margin-bottom: 12px;
        transition: all 0.3s ease;
    }

    .form-check:hover {
        transform: translateX(5px);
    }

    /* Payment method section */
    .payment-method-container {
        margin-top: 20px;
    }

    .payment-method-option {
        border: 1px solid #dee2e6;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .payment-method-option:hover {
        border-color: var(--primary);
        background-color: var(--primary-light);
        transform: translateY(-3px);
    }

    .payment-method-option.selected {
        border-color: var(--primary);
        background-color: var(--primary-light);
    }

    .payment-icon {
        font-size: 24px;
        color: var(--primary);
        margin-right: 10px;
        transition: all 0.3s ease;
    }

    /* Order summary */
    .order-item {
        transition: all 0.3s ease;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 15px;
        border-bottom: 1px solid #eee;
    }

    .order-item:hover {
        background-color: #f8f9fa;
        transform: translateY(-3px);
    }

    .img-thumbnail {
        border-radius: 10px;
        width: 70px;
        height: 70px;
        object-fit: cover;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .order-item:hover .img-thumbnail {
        transform: scale(1.05);
    }

    .order-details {
        margin-left: 10px;
    }

    .order-details p {
        margin-bottom: 8px;
    }

    .order-details i {
        color: var(--primary);
        width: 20px;
        text-align: center;
    }

    .order-total {
        background-color: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }

    .order-total:hover {
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    /* Button styles */
    .btn {
        border-radius: 50px;
        padding: 12px 25px;
        font-weight: 600;
        transition: all 0.4s ease;
    }

    .btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
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
        padding: 15px 30px;
        font-size: 1.1rem;
        background-color: #28a745;
        border-color: #28a745;
    }

    .btn-success:hover {
        background-color: #218838;
        border-color: #1e7e34;
    }

    .btn-lg {
        padding: 15px 30px;
        font-size: 1.1rem;
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

    /* Modal styles */
    .modal-header {
        background-color: var(--primary);
        color: white;
        border: none;
    }

    .modal-content {
        border-radius: 15px;
        border: none;
        overflow: hidden;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .payment-header {
            padding: 40px 0;
        }
        
        .payment-header h1 {
            font-size: 2.5rem;
        }
        
        .card-body {
            padding: 20px;
        }
    }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <?php include 'navbar.php'; ?>
    
    <!-- Breadcrumb -->
    <div class="breadcrumb-section" data-aos="fade-down">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="cart.php">Shopping Cart</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Checkout</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Payment Header -->
    <header class="payment-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-8" data-aos="fade-up">
                    <h1 class="display-4 mb-3">Complete Your Booking</h1>
                    <p class="lead fs-5 mb-0">
                        Just one step away from confirming your cricket experience
                    </p>
                </div>
            </div>
        </div>
    </header>
    
    <div class="container mb-5">
        <?php if(isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert" data-aos="fade-up">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-lg-8" data-aos="fade-up" data-aos-delay="100">
                <div class="payment-card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-user-circle me-2"></i> Contact Information</h4>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST" id="payment-form">
                            <div class="form-group">
                                <label for="contact_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="contact_name" name="contact_name" value="<?php echo $user['name']; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="contact_phone" class="form-label">Phone Number</label>
                                <input type="text" class="form-control" id="contact_phone" name="contact_phone" value="<?php echo $user['phone']; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="contact_email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="contact_email" name="contact_email" value="<?php echo $user['email']; ?>" required>
                            </div>
                            
                            <hr class="my-4">
                            
                            <h5 class="mb-4"><i class="fas fa-credit-card me-2"></i> Payment Method</h5>
                            <div class="payment-method-container">
                                <div class="payment-method-option selected">
                                    <input class="form-check-input" type="radio" id="payment_method_card" name="payment_method" value="Credit Card" checked style="display: none;">
                                    <label class="d-flex align-items-center" for="payment_method_card">
                                        <span class="payment-icon"><i class="fas fa-credit-card"></i></span>
                                        <div>
                                            <span class="fw-bold">Credit/Debit Card</span>
                                            <p class="text-muted mb-0 small">Pay securely with your card</p>
                                        </div>
                                    </label>
                                </div>
                                
                                <div class="payment-method-option">
                                    <input class="form-check-input" type="radio" id="payment_method_upi" name="payment_method" value="UPI" style="display: none;">
                                    <label class="d-flex align-items-center" for="payment_method_upi">
                                        <span class="payment-icon"><i class="fas fa-mobile-alt"></i></span>
                                        <div>
                                            <span class="fw-bold">UPI</span>
                                            <p class="text-muted mb-0 small">Google Pay, PhonePe, Paytm & more</p>
                                        </div>
                                    </label>
                                </div>
                                
                                <div class="payment-method-option">
                                    <input class="form-check-input" type="radio" id="payment_method_wallet" name="payment_method" value="Wallet" style="display: none;">
                                    <label class="d-flex align-items-center" for="payment_method_wallet">
                                        <span class="payment-icon"><i class="fas fa-wallet"></i></span>
                                        <div>
                                            <span class="fw-bold">Mobile Wallet</span>
                                            <p class="text-muted mb-0 small">Amazon Pay, Freecharge & more</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <div class="form-group form-check">
                                <input type="checkbox" class="form-check-input" id="terms" required>
                                <label class="form-check-label" for="terms">I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a></label>
                            </div>
                            
                            <button type="submit" name="confirm_payment" class="btn btn-success btn-lg mt-4 w-100">
                                <i class="fas fa-check-circle me-2"></i> Confirm and Pay ₹<?php echo number_format($grand_total * 1.18, 2); ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                <div class="payment-card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-shopping-cart me-2"></i> Order Summary</h4>
                    </div>
                    <div class="card-body">
                        <div class="order-summary">
                            <?php foreach($_SESSION['cart'] as $item): ?>
                            <div class="order-item">
                                <div class="d-flex align-items-center">
                                    <img src="uploads/venues/<?php echo $item['venue_image']; ?>" class="img-thumbnail" alt="<?php echo $item['venue_name']; ?>">
                                </div>
                                <div class="order-details mt-3">
                                    <p class="mb-1"><i class="far fa-calendar-alt me-2"></i> <?php echo date('d M, Y', strtotime($item['booking_date'])); ?></p>
                                    <p class="mb-1"><i class="far fa-clock me-2"></i> <?php echo date('h:i A', strtotime($item['start_time'])) . ' - ' . date('h:i A', strtotime($item['end_time'])); ?></p>
                                    <p class="mb-1"><i class="fas fa-hourglass-half me-2"></i> <?php echo $item['duration']; ?> hour(s)</p>
                                    <p class="mb-0 fw-bold text-primary"><i class="fas fa-rupee-sign me-2"></i> <?php echo $item['total_amount']; ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            
                            <div class="order-total mt-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal:</span>
                                    <span>₹<?php echo $grand_total; ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Tax (GST 18%):</span>
                                    <span>₹<?php echo number_format($grand_total * 0.18, 2); ?></span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong>Grand Total:</strong>
                                    <strong class="text-primary fs-4">₹<?php echo number_format($grand_total * 1.18, 2); ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div data-aos="fade-up" data-aos-delay="300">
                    <a href="cart.php" class="btn btn-outline-primary btn-lg w-100">
                        <i class="fas fa-arrow-left me-2"></i> Back to Cart
                    </a>
                </div>
                
                <div class="card mt-4 payment-card" data-aos="fade-up" data-aos-delay="400">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-shield-alt text-primary fs-3 me-3"></i>
                            <div>
                                <h6 class="fw-bold mb-0">100% Secure Payments</h6>
                                <p class="text-muted small mb-0">All transactions are secure</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-lock text-primary fs-3 me-3"></i>
                            <div>
                                <h6 class="fw-bold mb-0">Privacy Protected</h6>
                                <p class="text-muted small mb-0">Your data is always protected</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Terms and Conditions Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="termsModalLabel">Terms and Conditions</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h4>Booking Terms</h4>
                    <p>By booking a slot on PlayOn, you agree to the following terms:</p>
                    <ul>
                        <li>All bookings are subject to availability.</li>
                        <li>Arrive at least 15 minutes before your booked slot.</li>
                        <li>Cancellations must be made at least 24 hours before the scheduled time.</li>
                        <li>Late cancellations or no-shows may result in a full charge.</li>
                        <li>PlayOn reserves the right to cancel any booking due to unforeseen circumstances.</li>
                        <li>Refunds will be processed within 7-10 working days.</li>
                        <li>Users are responsible for any damage to venue property during their session.</li>
                    </ul>
                    
                    <h4>Payment Terms</h4>
                    <ul>
                        <li>All payments are processed securely through our payment gateways.</li>
                        <li>Prices include all applicable taxes.</li>
                        <li>Payment confirmation will be sent to your registered email address.</li>
                    </ul>
                    
                    <h4>Venue Rules</h4>
                    <ul>
                        <li>Follow all safety guidelines provided by the venue staff.</li>
                        <li>Appropriate sports attire and footwear must be worn.</li>
                        <li>No food or drinks allowed on the cricket pitch.</li>
                        <li>PlayOn is not responsible for any personal belongings left at the venue.</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">I Understand</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Back to top button -->
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center">
        <i class="fas fa-arrow-up"></i>
    </a>
    
    <!-- Footer -->
    <?php include 'footer.php'; ?>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
        
        // Show or hide back-to-top button based on scroll position
        $(window).scroll(function() {
            if ($(this).scrollTop() > 300) {
                $('.back-to-top').fadeIn('slow');
            } else {
                $('.back-to-top').fadeOut('slow');
            }
        });

        // Smooth scroll to top when clicking back-to-top button
        $('.back-to-top').click(function(e) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: 0
            }, 800);
            return false;
        });
        
        // Payment method selection animation
        $('.payment-method-option').click(function() {
            $('.payment-method-option').removeClass('selected');
            $(this).addClass('selected');
            $(this).find('input[type="radio"]').prop('checked', true);
        });
        
        // Form validation animation
        const form = document.getElementById('payment-form');
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                
                // Shake animation for invalid fields
                const invalidFields = form.querySelectorAll(':invalid');
                invalidFields.forEach(field => {
                    field.classList.add('shake');
                    setTimeout(() => {
                        field.classList.remove('shake');
                    }, 500);
                });
            }
            
            form.classList.add('was-validated');
        }, false);
        
        // Add shake animation CSS
        const style = document.createElement('style');
        style.innerHTML = `
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
                20%, 40%, 60%, 80% { transform: translateX(5px); }
            }`;
    });
    </script>
</body>
</html>