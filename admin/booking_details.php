<?php
session_start();
include 'admin_header.php';
include '../db_connect.php';

// Check if booking ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: bookings.php");
    exit();
}

$booking_id = $_GET['id'];

// Get booking details with user and venue information (removed payment_id and payment_method)
$query = "SELECT b.booking_id, b.user_id, b.venue_id, b.booking_date, b.start_time, 
                 b.duration, b.total_amount, b.status, b.created_at,
                 u.name as user_name, u.email, u.phone, u.address, 
                 v.venue_name as venue_name, v.address as venue_address, v.city, v.state 
          FROM bookings b
          JOIN users u ON b.user_id = u.user_id
          JOIN venues v ON b.venue_id = v.venue_id
          WHERE b.booking_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: bookings.php");
    exit();
}

$booking = $result->fetch_assoc();

// Handle booking status updates
if (isset($_POST['update_status'])) {
    $status = $_POST['status'];
    
    $update_query = "UPDATE bookings SET status = ? WHERE booking_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $status, $booking_id);
    
    if ($stmt->execute()) {
        $success_message = "Booking status updated successfully!";
        // Refresh booking data
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $booking = $result->fetch_assoc();
    } else {
        $error_message = "Error updating booking status: " . $conn->error;
    }
}
?>

<div class="admin-content">
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Booking Details</h2>
            <a href="bookings.php" class="btn btn-secondary">Back to Bookings</a>
        </div>
        
        <?php if(isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if(isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <div class="row">
            <!-- Booking Information -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Booking Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Booking ID:</strong> #<?php echo $booking['booking_id']; ?></p>
                                <p><strong>Venue:</strong> <?php echo htmlspecialchars($booking['venue_name']); ?></p>
                                <p><strong>Date:</strong> <?php echo date('d-m-Y', strtotime($booking['booking_date'])); ?></p>
                                <p><strong>Time:</strong> <?php echo date('h:i A', strtotime($booking['start_time'])); ?></p>
                                <p><strong>Duration:</strong> <?php echo $booking['duration']; ?> hour(s)</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Amount:</strong> ₹<?php echo number_format($booking['total_amount'], 2); ?></p>
                                <p><strong>Booked On:</strong> <?php echo date('d-m-Y H:i', strtotime($booking['created_at'])); ?></p>
                                <p>
                                    <strong>Status:</strong> 
                                    <?php 
                                    $status_badge = '';
                                    switch($booking['status']) {
                                        case 'confirmed':
                                            $status_badge = 'bg-success';
                                            break;
                                        case 'pending':
                                            $status_badge = 'bg-warning text-dark';
                                            break;
                                        case 'cancelled':
                                            $status_badge = 'bg-danger';
                                            break;
                                        case 'completed':
                                            $status_badge = 'bg-info';
                                            break;
                                        default:
                                            $status_badge = 'bg-secondary';
                                    }
                                    ?>
                                    <span class="badge <?php echo $status_badge; ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                        
                        <!-- Status Update Form -->
                        <form method="post" action="" class="mt-3">
                            <div class="row align-items-end">
                                <div class="col-md-6">
                                    <label for="status" class="form-label">Update Status</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="pending" <?php echo ($booking['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="confirmed" <?php echo ($booking['status'] == 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="cancelled" <?php echo ($booking['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                        <option value="completed" <?php echo ($booking['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Venue Information -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Venue Information</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($booking['venue_name']); ?></p>
                        <p><strong>Address:</strong> <?php echo htmlspecialchars($booking['venue_address']); ?></p>
                        <p><strong>City:</strong> <?php echo htmlspecialchars($booking['city']); ?></p>
                        <p><strong>State:</strong> <?php echo htmlspecialchars($booking['state']); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- User Information -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Customer Information</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($booking['user_name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($booking['email']); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($booking['phone']); ?></p>
                        <p>
                            <strong>Address:</strong><br>
                            <?php echo nl2br(htmlspecialchars($booking['address'])); ?>
                        </p>
                    </div>
                </div>
                
                <!-- Additional Notes -->
                <?php if(!empty($booking['notes'])): ?>
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">Customer Notes</h5>
                    </div>
                    <div class="card-body">
                        <p><?php echo nl2br(htmlspecialchars($booking['notes'])); ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'admin_footer.php'; ?>