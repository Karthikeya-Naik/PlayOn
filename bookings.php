<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit();
}

// Check if venue ID is provided
if(!isset($_GET['venue_id'])) {
    header("Location: venues.php");
    exit();
}

$venue_id = mysqli_real_escape_string($conn, $_GET['venue_id']);

// Get venue details
$query = "SELECT * FROM venues WHERE venue_id = '$venue_id' AND is_active = '1'";
$result = mysqli_query($conn, $query);

if(mysqli_num_rows($result) == 0) {
    header("Location: venues.php");
    exit();
}

$venue = mysqli_fetch_assoc($result);

// Process the booking form
if(isset($_POST['book_now'])) {
    $booking_date = mysqli_real_escape_string($conn, $_POST['booking_date']);
    $start_time = mysqli_real_escape_string($conn, $_POST['start_time']);
    $duration = mysqli_real_escape_string($conn, $_POST['duration']);
    
    // Calculate end time
    $end_time = date('H:i:s', strtotime("+$duration hours", strtotime($start_time)));
    
    // Calculate total amount
    $total_amount = $venue['price_per_hour'] * $duration;
    
    // Check if the slot is available
    $check_query = "SELECT * FROM bookings 
                   WHERE venue_id = '$venue_id' 
                   AND booking_date = '$booking_date' 
                   AND ((start_time <= '$start_time' AND end_time > '$start_time') 
                   OR (start_time < '$end_time' AND end_time >= '$end_time') 
                   OR (start_time >= '$start_time' AND end_time <= '$end_time'))
                   AND status != 'cancelled'";
    
    $check_result = mysqli_query($conn, $check_query);
    
    if(mysqli_num_rows($check_result) > 0) {
        $_SESSION['booking_error'] = "This slot is already booked. Please select another time.";
    } else {
        // Add to cart session
        if(!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        $cart_item = [
            'venue_id' => $venue_id,
            'venue_name' => $venue['name'],
            'venue_image' => $venue['image'],
            'booking_date' => $booking_date,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'duration' => $duration,
            'price_per_hour' => $venue['price_per_hour'],
            'total_amount' => $total_amount
        ];
        
        $_SESSION['cart'][] = $cart_item;
        header("Location: cart.php");
        exit();
    }
}

// Default venue operating hours (can be stored in database)
$opening_time = isset($venue['opening_time']) ? $venue['opening_time'] : '06:00:00';
$closing_time = isset($venue['closing_time']) ? $venue['closing_time'] : '22:00:00';

// Generate time slots with 1-hour intervals
function generateTimeSlots($opening, $closing) {
    $slots = [];
    $current = strtotime($opening);
    $end = strtotime($closing);
    
    while($current < $end) {
        $time = date('H:i:s', $current);
        $slots[] = [
            'start_time' => $time,
            'display_time' => date('h:i A', $current)
        ];
        $current = strtotime('+1 hour', $current);
    }
    
    return $slots;
}

$time_slots = generateTimeSlots($opening_time, $closing_time);

// Get unavailable slots for the selected date (to be used with JavaScript)
function getUnavailableSlots($venue_id, $date, $conn) {
    $unavailable = [];
    $query = "SELECT start_time, end_time FROM bookings 
              WHERE venue_id = '$venue_id' 
              AND booking_date = '$date' 
              AND status != 'cancelled'";
    
    $result = mysqli_query($conn, $query);
    while($row = mysqli_fetch_assoc($result)) {
        $unavailable[] = [
            'start' => $row['start_time'],
            'end' => $row['end_time']
        ];
    }
    
    return $unavailable;
}

// This will be used with AJAX later
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$unavailable_slots = getUnavailableSlots($venue_id, $selected_date, $conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book <?php echo $venue['venue_name']; ?> - PlayOn</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
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
        
        /* Booking header */
        .booking-header {
            background: linear-gradient(rgba(11, 77, 140, 0.85), rgba(8, 57, 104, 0.95)),
                        url('uploads/venues/<?php echo $venue['image']; ?>') no-repeat center center/cover;
            padding: 60px 0;
            color: white;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }
        
        .booking-header h1 {
            font-weight: 800;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
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
        
        /* Breadcrumb */
        .breadcrumb-section {
            background-color: #f8f9fa;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        /* Section headings */
        h3 {
            font-weight: 700;
            margin: 30px 0 15px;
            color: var(--dark);
            position: relative;
            padding-bottom: 10px;
        }
        
        h3:after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 3px;
            background-color: var(--primary);
            transition: width 0.3s ease;
        }
        
        h3:hover:after {
            width: 100px;
        }
        
        /* Booking card */
        .booking-card {
            background-color: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 40px;
            transition: all 0.4s ease;
            transform: translateY(0);
        }
        
        .booking-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }
        
        .booking-card .card-header {
            background-color: var(--primary);
            color: white;
            padding: 20px;
            border: none;
        }
        
        .booking-card .card-header h4 {
            font-weight: 700;
            margin: 0;
        }
        
        .booking-card .card-body {
            padding: 30px;
        }
        
        /* Venue info */
        .venue-info {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            transition: all 0.3s ease;
        }
        
        .venue-info:hover {
            transform: translateX(5px);
        }
        
        .venue-info img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .venue-info:hover img {
            transform: scale(1.05);
        }
        
        .venue-info-details {
            margin-left: 20px;
        }
        
        .venue-info-details h5 {
            font-weight: 700;
            margin-bottom: 8px;
            font-size: 1.25rem;
            color: var(--primary);
        }
        
        .venue-info-details p {
            margin-bottom: 5px;
            color: #6c757d;
        }
        
        /* Form styles */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-control, .form-select {
            padding: 0.7rem 1rem;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(11, 77, 140, 0.25);
            border-color: var(--primary);
            transform: translateY(-2px);
        }
        
        label {
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
            color: var(--dark);
        }
        
        /* Booking summary */
        .booking-summary {
            background-color: #f8f9fa;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .booking-summary:hover {
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transform: translateY(-5px);
        }
        
        .booking-summary h5 {
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--primary);
            position: relative;
            padding-bottom: 10px;
        }
        
        .booking-summary h5:after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 40px;
            height: 3px;
            background-color: var(--primary);
            transition: width 0.3s ease;
        }
        
        .booking-summary:hover h5:after {
            width: 80px;
        }
        
        /* Button styles */
        .btn-primary {
            background-color: var(--primary);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .btn-primary:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 0%;
            height: 100%;
            background-color: var(--primary-dark);
            transition: all 0.3s ease;
            z-index: -1;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(8, 57, 104, 0.3);
        }
        
        .btn-primary:hover:before {
            width: 100%;
        }
        
        /* Datepicker customization */
        .datepicker-dropdown {
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            animation: fadeInDown 0.3s ease;
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
        
        .datepicker table tr td.active.active,
        .datepicker table tr td.active:hover.active {
            background-color: var(--primary) !important;
            border-color: var(--primary) !important;
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
        
        /* Alert messages */
        .alert {
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 20px;
            animation: slideInDown 0.4s ease;
        }
        
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        
        /* Pulse animation for book now button */
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(11, 77, 140, 0.7);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(11, 77, 140, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(11, 77, 140, 0);
            }
        }
        
        .btn-primary.btn-lg {
            animation: pulse 2s infinite;
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
                    <li class="breadcrumb-item"><a href="index.php" style="text-decoration:none; color: var(--primary);">Home</a></li>
                    <li class="breadcrumb-item"><a href="venues.php" style="text-decoration:none; color: var(--primary);">Venues</a></li>
                    <li class="breadcrumb-item"><a href="venue_details.php?id=<?php echo $venue_id; ?>" style="text-decoration:none; color: var(--primary);"><?php echo $venue['venue_name']; ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Book Now</li>
                </ol>
            </nav>
        </div>
    </div>
    
    <!-- Booking Header -->
    <header class="booking-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-8" data-aos="fade-up">
                    <h1 class="display-5">Book Your Slot</h1>
                    <p class="lead fs-5 mb-0"><?php echo $venue['venue_name']; ?></p>
                    <p class="lead fs-6 mb-0"><i class="fas fa-map-marker-alt me-2"></i> <?php echo $venue['city']; ?>, Telangana</p>
                </div>
            </div>
        </div>
    </header>
    
    <div class="container">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="booking-card" data-aos="fade-up">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Book Your Slot</h4>
                    </div>
                    <div class="card-body">
                        <?php if(isset($_SESSION['booking_error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?php 
                                echo $_SESSION['booking_error']; 
                                unset($_SESSION['booking_error']);
                                ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <div class="venue-info" data-aos="fade-right">
                            <img src="uploads/venues/<?php echo $venue['image']; ?>" alt="<?php echo $venue['venue_name']; ?>">
                            <div class="venue-info-details">
                                <h5><?php echo $venue['venue_name']; ?></h5>
                                <p><i class="fas fa-map-marker-alt me-2"></i><?php echo $venue['city']; ?>, Telangana</p>
                                <p><i class="fas fa-rupee-sign me-2"></i><strong><?php echo $venue['price_per_hour']; ?></strong> per hour</p>
                                <p><i class="far fa-clock me-2"></i><?php echo date('h:i A', strtotime($opening_time)); ?> - <?php echo date('h:i A', strtotime($closing_time)); ?></p>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <form action="" method="POST" data-aos="fade-up">
                            <div class="form-group">
                                <label for="booking_date"><i class="far fa-calendar-alt me-2"></i>Select Date</label>
                                <input type="text" class="form-control datepicker" id="booking_date" name="booking_date" required autocomplete="off" placeholder="Choose a date">
                            </div>
                            
                            <div class="form-group">
                                <label for="start_time"><i class="far fa-clock me-2"></i>Select Start Time</label>
                                <select class="form-select" id="start_time" name="start_time" required>
                                    <option value="">Select a time slot</option>
                                    <?php foreach($time_slots as $slot): ?>
                                    <option value="<?php echo $slot['start_time']; ?>"><?php echo $slot['display_time']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="duration"><i class="fas fa-hourglass-half me-2"></i>Duration (hours)</label>
                                <select class="form-select" id="duration" name="duration" required>
                                    <option value="1">1 hour</option>
                                    <option value="2">2 hours</option>
                                    <option value="3">3 hours</option>
                                    <option value="4">4 hours</option>
                                </select>
                            </div>
                            
                            <div class="booking-summary mt-4" data-aos="fade-up">
                                <h5>Booking Summary</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Venue:</strong> <span id="summary-venue"><?php echo $venue['venue_name']; ?></span></p>
                                        <p><strong>Date:</strong> <span id="summary-date">Select a date</span></p>
                                        <p><strong>Time:</strong> <span id="summary-time">Select a time</span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Duration:</strong> <span id="summary-duration">1</span> hour(s)</p>
                                        <p><strong>Price per hour:</strong> ₹<span id="summary-price"><?php echo $venue['price_per_hour']; ?></span></p>
                                        <p><strong>Total Amount:</strong> <span class="fw-bold text-primary fs-5">₹<span id="summary-total"><?php echo $venue['price_per_hour']; ?></span></span></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group mt-4" data-aos="fade-up">
                                <button type="submit" name="book_now" class="btn btn-primary btn-lg btn-block w-100"><i class="fas fa-shopping-cart me-2"></i>Add to Cart</button>
                            </div>
                        </form>
                    </div>
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
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize AOS animations
            AOS.init({
                duration: 800,
                once: true,
                offset: 100
            });
            
            // Initialize datepicker
            $('.datepicker').datepicker({
                format: 'yyyy-mm-dd',
                startDate: new Date(),
                autoclose: true,
                todayHighlight: true
            }).on('changeDate', function(e) {
                // When date changes, update available time slots via AJAX
                var selectedDate = $(this).val();
                fetchAvailableSlots(selectedDate);
                
                // Update summary
                $('#summary-date').text(selectedDate);
            });
            
            // Function to fetch available slots for the selected date
            function fetchAvailableSlots(date) {
                $.ajax({
                    url: 'get_available_slots.php',
                    type: 'GET',
                    data: {
                        venue_id: <?php echo $venue_id; ?>,
                        date: date
                    },
                    success: function(response) {
                        var data = JSON.parse(response);
                        updateTimeSlots(data.available_slots, data.booked_slots);
                    },
                    error: function() {
                        console.error('Error fetching available slots');
                    }
                });
            }
            
            // Function to update time slots in the dropdown
            function updateTimeSlots(availableSlots, bookedSlots) {
                var $select = $('#start_time');
                $select.empty();
                $select.append('<option value="">Select a time slot</option>');
                
                availableSlots.forEach(function(slot) {
                    var isDisabled = isSlotBooked(slot.start_time, bookedSlots);
                    var option = new Option(slot.display_time, slot.start_time);
                    $(option).prop('disabled', isDisabled);
                    if (isDisabled) {
                        $(option).addClass('text-muted');
                        $(option).text(slot.display_time + ' (Booked)');
                    }
                    $select.append(option);
                });
            }
            
            // Check if a slot is already booked
            function isSlotBooked(startTime, bookedSlots) {
                for (var i = 0; i < bookedSlots.length; i++) {
                    var bookedStart = bookedSlots[i].start;
                    var bookedEnd = bookedSlots[i].end;
                    
                    if (startTime >= bookedStart && startTime < bookedEnd) {
                        return true;
                    }
                }
                return false;
            }
            
            // Update booking summary when form fields change
            $('#start_time').change(function() {
                var selectedTime = $(this).val();
                if (selectedTime) {
                    var displayTime = $('#start_time option:selected').text();
                    $('#summary-time').text(displayTime);
                } else {
                    $('#summary-time').text('Select a time');
                }
                updateSummary();
            });
            
            $('#duration').change(function() {
                var duration = $(this).val();
                $('#summary-duration').text(duration);
                updateSummary();
            });
            
            // Update booking summary
            function updateSummary() {
                var duration = $('#duration').val();
                var pricePerHour = <?php echo $venue['price_per_hour']; ?>;
                var totalAmount = pricePerHour * duration;
                
                $('#summary-total').text(totalAmount);
            }
        });
    </script>
</body>
</html>