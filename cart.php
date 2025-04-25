<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Remove item from cart
if(isset($_GET['remove']) && isset($_SESSION['cart'])) {
    $remove_index = $_GET['remove'];
    if(isset($_SESSION['cart'][$remove_index])) {
        unset($_SESSION['cart'][$remove_index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index array
        $_SESSION['success'] = "Item removed from cart.";
    }
    header("Location: cart.php");
    exit();
}

// Clear cart
if(isset($_GET['clear']) && isset($_SESSION['cart'])) {
    unset($_SESSION['cart']);
    $_SESSION['success'] = "Cart cleared successfully.";
    header("Location: cart.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - PlayOn</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- AOS Animation Library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <link rel="stylesheet" href="css/style.css">
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
        transition: all 0.3s ease;
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

    /* Cart header */
    .cart-header {
        background: linear-gradient(rgba(11, 77, 140, 0.85), rgba(8, 57, 104, 0.95));
        padding: 60px 0;
        color: white;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }

    .cart-header::before {
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

    .cart-header .container {
        position: relative;
        z-index: 1;
    }

    .cart-header h1 {
        font-weight: 800;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        animation: fadeInUp 1s ease;
    }

    .cart-header p {
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

    /* Breadcrumb */
    .breadcrumb-section {
        background-color: #f8f9fa;
        padding: 15px 0;
        border-bottom: 1px solid #eee;
        transition: all 0.3s ease;
    }

    .breadcrumb-section:hover {
        background-color: #e9ecef;
    }

    .breadcrumb {
        margin-bottom: 0;
    }

    .breadcrumb-item a {
        color: var(--primary);
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .breadcrumb-item a:hover {
        color: var(--primary-dark);
    }

    /* Cart table */
    .cart-table {
        background-color: #fff;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        margin-bottom: 30px;
        transition: all 0.3s ease;
        transform: translateY(0);
    }

    .cart-table:hover {
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transform: translateY(-5px);
    }

    .table {
        margin-bottom: 0;
    }

    .table thead th {
        background-color: var(--primary);
        color: white;
        font-weight: 600;
        border: none;
        padding: 15px;
        position: relative;
        overflow: hidden;
    }

    .table thead th::after {
        content: '';
        position: absolute;
        width: 100%;
        height: 2px;
        bottom: 0;
        left: 0;
        background-color: rgba(255, 255, 255, 0.3);
        transform: scaleX(0);
        transition: all 0.3s ease;
    }

    .cart-table:hover .table thead th::after {
        transform: scaleX(1);
    }

    .table td {
        vertical-align: middle;
        padding: 15px;
        transition: all 0.3s ease;
    }

    .table tr:hover td {
        background-color: rgba(11, 77, 140, 0.03);
    }

    .table tfoot {
        background-color: #f8f9fa;
    }

    .table tfoot td {
        font-weight: 600;
        padding: 15px;
    }

    /* Cart item */
    .cart-item-image {
        width: 70px;
        height: 70px;
        object-fit: cover;
        border-radius: 8px;
        margin-right: 15px;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .cart-item-image:hover {
        transform: scale(1.05);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
    }

    .cart-item-name {
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 0;
        transition: all 0.3s ease;
    }

    .d-flex:hover .cart-item-name {
        color: var(--primary);
    }

    /* Cart actions */
    .cart-actions {
        margin: 30px 0;
    }

    .btn {
        border-radius: 50px;
        padding: 10px 20px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .btn-primary {
        background-color: var(--primary);
        border: none;
    }

    .btn-primary:hover {
        background-color: var(--primary-dark);
    }

    .btn-success {
        padding: 12px 30px;
        font-size: 1.1rem;
        background-color: #198754;
        border: none;
    }

    .btn-success:hover {
        background-color: #146c43;
    }

    .btn-danger {
        border-radius: 30px;
    }

    .btn-outline-primary {
        border-color: var(--primary);
        color: var(--primary);
    }
    
    .btn-outline-primary:hover {
        background-color: var(--primary);
        border-color: var(--primary);
        color: white;
    }

    .btn-outline-danger {
        border-color: #dc3545;
        color: #dc3545;
    }
    
    .btn-outline-danger:hover {
        background-color: #dc3545;
        border-color: #dc3545;
        color: white;
    }

    /* Empty cart */
    .empty-cart {
        background-color: #f8f9fa;
        border-radius: 12px;
        padding: 40px;
        text-align: center;
        margin: 30px 0;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }

    .empty-cart:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .empty-cart i {
        font-size: 4rem;
        color: var(--primary);
        margin-bottom: 20px;
        transition: all 0.5s ease;
    }

    .empty-cart:hover i {
        transform: scale(1.1) rotate(10deg);
    }

    .empty-cart h4 {
        font-weight: 700;
        margin-bottom: 15px;
        color: var(--dark);
    }

    /* Alert styling */
    .alert {
        border-radius: 10px;
        border: none;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
        animation: fadeInDown 0.5s ease;
    }

    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .alert-success {
        background-color: #d1e7dd;
        color: #0f5132;
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
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Shopping Cart</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Cart Header -->
    <header class="cart-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-8" data-aos="fade-up">
                    <h1 class="display-4">Your Shopping Cart</h1>
                    <p class="lead fs-5 mb-0">
                        Review your selected bookings before proceeding to payment
                    </p>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert" data-aos="fade-down">
            <i class="fas fa-check-circle me-2"></i>
            <?php 
            echo $_SESSION['success']; 
            unset($_SESSION['success']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
        <div class="cart-table" data-aos="fade-up">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Venue</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Duration</th>
                            <th>Price</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $grand_total = 0;
                        foreach($_SESSION['cart'] as $index => $item): 
                            $grand_total += $item['total_amount'];
                        ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="uploads/venues/<?php echo $item['venue_image']; ?>"
                                        class="cart-item-image" alt="<?php echo $item['venue_name']; ?>">
                                    <span class="cart-item-name"><?php echo $item['venue_name']; ?></span>
                                </div>
                            </td>
                            <td><?php echo date('d M, Y', strtotime($item['booking_date'])); ?></td>
                            <td><?php echo date('h:i A', strtotime($item['start_time'])) . ' - ' . date('h:i A', strtotime($item['end_time'])); ?>
                            </td>
                            <td><?php echo $item['duration']; ?> hour(s)</td>
                            <td>₹<?php echo $item['price_per_hour']; ?> / hour</td>
                            <td><strong class="text-primary">₹<?php echo $item['total_amount']; ?></strong></td>
                            <td>
                                <a href="cart.php?remove=<?php echo $index; ?>" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Are you sure you want to remove this item?')">
                                    <i class="fas fa-trash"></i> Remove
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-end"><strong>Grand Total:</strong></td>
                            <td><strong class="text-primary fs-5">₹<?php echo $grand_total; ?></strong></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="cart-actions mt-4">
            <div class="row">
                <div class="col-md-6" data-aos="fade-right">
                    <a href="venues.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i> Continue Shopping
                    </a>
                    <a href="cart.php?clear=1" class="btn btn-outline-danger ms-2"
                        onclick="return confirm('Are you sure you want to clear your cart?')">
                        <i class="fas fa-trash me-2"></i> Clear Cart
                    </a>
                </div>
                <div class="col-md-6 text-end" data-aos="fade-left">
                    <a href="payment.php" class="btn btn-success btn-lg">
                        <i class="fas fa-credit-card me-2"></i> Proceed to Payment
                    </a>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="empty-cart" data-aos="fade-up">
            <i class="fas fa-shopping-cart mb-3"></i>
            <h4>Your cart is empty!</h4>
            <p class="lead">Browse our venues and book your favorite box cricket spots.</p>
            <a href="venues.php" class="btn btn-primary mt-3">
                <i class="fas fa-search me-2"></i> Browse Venues
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Back to top button -->
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center">
        <i class="fas fa-arrow-up"></i>
    </a>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
    // Initialize AOS animations
    document.addEventListener('DOMContentLoaded', function() {
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });
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
    </script>
</body>

</html>