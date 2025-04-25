<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    
    // Check if email already exists for another user
    $check_query = "SELECT user_id FROM users WHERE email = ? AND user_id != ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("si", $email, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $_SESSION['error_message'] = "Email already exists. Please use a different email.";
    } else {
        $update_query = "UPDATE users SET name = ?, email = ?, phone = ? WHERE user_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("sssi", $name, $email, $phone, $user_id);
        
        if ($update_stmt->execute()) {
            $_SESSION['success_message'] = "Profile updated successfully!";
            $_SESSION['user_name'] = $name; // Update session with new name
        } else {
            $_SESSION['error_message'] = "Failed to update profile. Please try again.";
        }
    }
}

// Get user data
$user_query = "SELECT name, email, phone FROM users WHERE user_id = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

// Get user's booking history
$bookings_query = "SELECT b.venue_id, b.booking_date, b.start_time, b.duration, b.total_amount, b.status, 
                  v.venue_name as venue_name, v.address 
                  FROM bookings b 
                  JOIN venues v ON b.venue_id = v.venue_id 
                  WHERE b.user_id = ? 
                  ORDER BY b.booking_date DESC";
$bookings_stmt = $conn->prepare($bookings_query);
$bookings_stmt->bind_param("i", $user_id);
$bookings_stmt->execute();
$bookings_result = $bookings_stmt->get_result();

include 'navbar.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - PlayOn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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

    /* Breadcrumb */
    .breadcrumb-section {
        background-color: var(--primary-light);
        padding: 15px 0;
        border-bottom: 1px solid rgba(11, 77, 140, 0.1);
        transition: all 0.3s ease;
    }
    
    .breadcrumb-item a {
        color: var(--primary);
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    
    .breadcrumb-item a:hover {
        color: var(--primary-dark);
    }
    
    .breadcrumb-item.active {
        color: var(--dark);
        font-weight: 500;
    }

    /* Profile header */
    .profile-header {
        background: linear-gradient(rgba(11, 77, 140, 0.9), rgba(8, 57, 104, 0.95));
        padding: 70px 0;
        color: white;
        margin-bottom: 40px;
        position: relative;
        overflow: hidden;
    }
    
    .profile-header::before {
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

    .profile-header .container {
        position: relative;
        z-index: 1;
    }

    .profile-header h1 {
        font-weight: 800;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        animation: fadeInUp 0.8s ease;
    }

    .profile-header .lead {
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

    /* Profile card */
    .profile-card {
        background-color: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        margin-bottom: 40px;
        border: none;
        transition: all 0.4s ease;
    }

    .profile-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
    }

    .profile-card .card-header {
        background-color: var(--primary);
        color: white;
        padding: 20px 25px;
        border: none;
    }

    .profile-card .card-header h4 {
        font-weight: 700;
        margin: 0;
        padding-bottom: 0;
    }

    .profile-card .card-body {
        padding: 30px;
    }

    /* Form styles */
    .form-group {
        margin-bottom: 20px;
    }

    .form-control {
        padding: 0.8rem 1.2rem;
        border-radius: 8px;
        border: 1px solid #e0e0e0;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        box-shadow: 0 0 0 0.25rem rgba(11, 77, 140, 0.25);
        border-color: rgba(11, 77, 140, 0.5);
    }

    label {
        font-weight: 600;
        margin-bottom: 10px;
        display: block;
        color: var(--dark);
    }

    /* Button styles */
    .btn {
        border: none;
        padding: 12px 30px;
        font-weight: 600;
        border-radius: 50px;
        transition: all 0.4s ease;
    }

    .btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .btn-primary {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    .btn-primary:hover, .btn-primary:focus {
        background-color: var(--primary-dark);
        border-color: var(--primary-dark);
    }

    .btn-secondary {
        background-color: #6c757d;
    }

    .btn-secondary:hover {
        background-color: #5a6268;
    }

    /* Table styling */
    .table {
        margin-bottom: 0;
    }

    .table th {
        font-weight: 600;
        border-top: none;
        background-color: rgba(11, 77, 140, 0.05);
        padding: 15px;
    }

    .table td {
        padding: 15px;
        vertical-align: middle;
        transition: all 0.3s ease;
    }
    
    .table tr {
        transition: all 0.3s ease;
    }
    
    .table tr:hover {
        background-color: rgba(11, 77, 140, 0.03);
    }

    .badge {
        padding: 8px 12px;
        border-radius: 30px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    /* Alert styling */
    .alert {
        border-radius: 10px;
        padding: 18px 20px;
        margin-bottom: 30px;
        border: none;
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

    .alert-success {
        background-color: #d1e7dd;
        color: #0f5132;
        border-left: 4px solid #0f5132;
    }

    .alert-danger {
        background-color: #f8d7da;
        color: #842029;
        border-left: 4px solid #842029;
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
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
    }
    
    /* Empty bookings state */
    .empty-bookings {
        padding: 40px 0;
        text-align: center;
    }
    
    .empty-bookings i {
        font-size: 5rem;
        color: rgba(11, 77, 140, 0.2);
        margin-bottom: 20px;
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
    
    /* Input icon styling */
    .input-icon {
        position: relative;
    }
    
    .input-icon i {
        position: absolute;
        left: 15px;
        top: 15px;
        color: var(--primary);
        transition: all 0.3s ease;
    }
    
    .input-icon input {
        padding-left: 40px;
    }
    
    .input-icon input:focus + i {
        color: var(--primary-dark);
    }
    
    /* Profile info counter */
    .profile-counter {
        background: var(--primary-light);
        padding: 15px;
        border-radius: 10px;
        margin-top: 20px;
        display: flex;
        justify-content: space-around;
        align-items: center;
    }
    
    .counter-item {
        text-align: center;
    }
    
    .counter-number {
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 5px;
    }
    
    .counter-label {
        font-size: 0.9rem;
        color: var(--dark);
    }
    
    /* Status badge animations */
    @keyframes fadeInRight {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    .badge {
        animation: fadeInRight 0.5s ease;
    }
    </style>
</head>

<body>
    <!-- Breadcrumb -->
    <div class="breadcrumb-section">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">My Profile</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Profile Header -->
    <header class="profile-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8" data-aos="fade-up">
                    <h1 class="display-5 mb-3">My Profile</h1>
                    <p class="lead fs-5 mb-2">Welcome back, <?php echo htmlspecialchars($user['name']); ?>!</p>
                    <p class="fs-6 mb-0"><i class="fas fa-user-circle me-2"></i> Manage your account and track your bookings</p>
                </div>
                <div class="col-lg-4 text-lg-end" data-aos="fade-up" data-aos-delay="200">
                    <div class="d-inline-block bg-white p-3 rounded-3 shadow-sm">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <span class="fs-3 fw-bold"><?php echo substr($user['name'], 0, 1); ?></span>
                            </div>
                            <div>
                                <h5 class="mb-1 text-dark"><?php echo htmlspecialchars($user['name']); ?></h5>
                                <p class="mb-0 text-muted small"><i class="fas fa-calendar-check me-1"></i> Member since 
                                <?php echo date('M Y'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container my-5">
        <?php if(isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success" data-aos="fade-in">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
        </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger" data-aos="fade-in">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
        </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="profile-card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-user-circle me-2"></i>My Profile</h4>
                    </div>
                    <div class="card-body">
                        <form action="profile.php" method="post">
                            <div class="form-group">
                                <label for="name"><i class="fas fa-user me-2"></i>Name</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email"><i class="fas fa-envelope me-2"></i>Email</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="phone"><i class="fas fa-phone me-2"></i>Phone</label>
                                <input type="text" class="form-control" id="phone" name="phone"
                                    value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                            <button type="submit" class="btn btn-primary btn-block w-100 mb-3">
                                <i class="fas fa-save me-2"></i>Update Profile
                            </button>
                        </form>
                        <hr class="my-4">
                        <a href="change_password.php" class="btn btn-secondary btn-block w-100">
                            <i class="fas fa-lock me-2"></i>Change Password
                        </a>
                        
                        <!-- Profile Stats Counter -->
                        <div class="profile-counter mt-4">
                            <div class="counter-item" data-aos="fade-up" data-aos-delay="100">
                                <div class="counter-number"><?php echo $bookings_result->num_rows; ?></div>
                                <div class="counter-label">Bookings</div>
                            </div>
                            <div class="counter-item" data-aos="fade-up" data-aos-delay="200">
                                <div class="counter-number">0</div>
                                <div class="counter-label">Reviews</div>
                            </div>
                            <div class="counter-item" data-aos="fade-up" data-aos-delay="300">
                                <div class="counter-number">0</div>
                                <div class="counter-label">Favorites</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Account Security Card -->
                <div class="profile-card" data-aos="fade-up" data-aos-delay="300">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Account Security</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <i class="fas fa-check-circle text-success fa-2x"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Email Verified</h6>
                                <p class="mb-0 text-muted small">Your email has been verified</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-lock text-primary fa-2x"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Password Protection</h6>
                                <p class="mb-0 text-muted small">Last updated: <?php echo date('d M Y'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8" data-aos="fade-up" data-aos-delay="200">
                <div class="profile-card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-calendar-check me-2"></i>My Bookings</h4>
                    </div>
                    <div class="card-body">
                        <?php if($bookings_result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Venue</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Duration</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $delay = 100;
                                    while($booking = $bookings_result->fetch_assoc()): 
                                    ?>
                                    <tr data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                                        <td class="fw-medium"><?php echo htmlspecialchars($booking['venue_name']); ?></td>
                                        <td><?php echo date('d M Y', strtotime($booking['booking_date'])); ?></td>
                                        <td><?php echo date('h:i A', strtotime($booking['start_time'])); ?></td>
                                        <td><?php echo $booking['duration']; ?> hr</td>
                                        <td class="fw-bold">₹<?php echo number_format($booking['total_amount']); ?></td>
                                        <td>
                                            <?php if($booking['status'] == 'confirmed'): ?>
                                            <span class="badge bg-success">Confirmed</span>
                                            <?php elseif($booking['status'] == 'pending'): ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                            <?php elseif($booking['status'] == 'cancelled'): ?>
                                            <span class="badge bg-danger">Cancelled</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php 
                                    $delay += 50;
                                    endwhile; 
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="empty-bookings" data-aos="fade-up">
                            <i class="fas fa-calendar-times text-muted"></i>
                            <h4 class="mt-3 mb-2">No Bookings Yet</h4>
                            <p class="text-muted mb-4">You haven't made any bookings yet. Start exploring venues and book your first cricket match!</p>
                            <a href="venues.php" class="btn btn-primary mt-2">
                                <i class="fas fa-search me-2"></i>Explore Venues
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Recent Activity Card -->
                <div class="profile-card" data-aos="fade-up" data-aos-delay="300">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-history me-2"></i>Recent Activity</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <div class="rounded-circle bg-primary-light d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                    <i class="fas fa-user text-primary"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-1">Profile Updated</h6>
                                <p class="mb-0 text-muted small">Today, <?php echo date('h:i A'); ?></p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <div class="rounded-circle bg-primary-light d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                    <i class="fas fa-sign-in-alt text-primary"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-1">Login Successful</h6>
                                <p class="mb-0 text-muted small">Today, <?php echo date('h:i A', strtotime('-10 minutes')); ?></p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <div class="rounded-circle bg-primary-light d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                    <i class="fas fa-eye text-primary"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-1">Viewed Venues</h6>
                                <p class="mb-0 text-muted small">Yesterday, <?php echo date('h:i A', strtotime('-1 day')); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Back to top button -->
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center">
        <i class="fas fa-arrow-up"></i>
    </a>

    <?php include 'footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize AOS animations
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
        
        // Form field focus effect
        const formControls = document.querySelectorAll('.form-control');
        formControls.forEach(control => {
            control.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            control.addEventListener('blur', function() {
                this.parentElement.classList.remove('focused');
            });
        });
        
        // Alert auto-dismiss
        const alerts = document.querySelectorAll('.alert');
        if (alerts.length > 0) {
            setTimeout(() => {
                alerts.forEach(alert => {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        alert.style.display = 'none';
                    }, 500);
                });
            }, 5000);
        }
    });
    </script>
</body>

</html>