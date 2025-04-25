<?php
session_start();
include 'admin_header.php';
include '../db_connect.php';

// Check if user ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: users.php");
    exit();
}

$user_id = $_GET['id'];

// Get user details
$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: users.php");
    exit();
}

$user = $result->fetch_assoc();

// Get user bookings
$bookings_query = "SELECT b.*, v.venue_name as venue_name 
                  FROM bookings b
                  JOIN venues v ON b.venue_id = v.venue_id
                  WHERE b.user_id = ?
                  ORDER BY b.booking_date DESC, b.start_time DESC";
$stmt = $conn->prepare($bookings_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bookings_result = $stmt->get_result();

// Handle user status updates
if (isset($_POST['update_status'])) {
    $status = isset($_POST['is_active']) ? 1 : 0;
    
    $update_query = "UPDATE users SET is_active = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ii", $status, $user_id);
    
    if ($stmt->execute()) {
        $success_message = "User status updated successfully!";
        // Refresh user data
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
    } else {
        $error_message = "Error updating user status: " . $conn->error;
    }
}
?>

<div class="admin-content">
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>User Details</h2>
            <a href="users.php" class="btn btn-secondary">Back to Users</a>
        </div>
        
        <?php if(isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if(isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <div class="row">
            <!-- User Information -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">User Information</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>ID:</strong> #<?php echo $user['user_id']; ?></p>
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone'] ?: 'Not provided'); ?></p>
                        <p>
                            <strong>Address:</strong><br>
                            <?php echo nl2br(htmlspecialchars($user['address'] ?: 'Not provided')); ?>
                        </p>
                        <p><strong>Registered On:</strong> <?php echo date('d-m-Y H:i', strtotime($user['created_at'])); ?></p>
                        <p>
                            <strong>Status:</strong> 
                            <?php if($user['is_active']): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Inactive</span>
                            <?php endif; ?>
                        </p>
                        
                        <!-- Status Update Form -->
                        <form method="post" action="" class="mt-3">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                       <?php echo $user['is_active'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                            <button type="submit" name="update_status" class="btn btn-primary w-100">Update Status</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- User Bookings -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Booking History</h5>
                    </div>
                    <div class="card-body">
                        <?php if($bookings_result->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Venue</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($booking = $bookings_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $booking['booking_id']; ?></td>
                                                <td><?php echo htmlspecialchars($booking['venue_name']); ?></td>
                                                <td><?php echo date('d-m-Y', strtotime($booking['booking_date'])); ?></td>
                                                <td><?php echo date('h:i A', strtotime($booking['start_time'])); ?></td>
                                                <td>₹<?php echo number_format($booking['total_amount'], 2); ?></td>
                                                <td>
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
                                                </td>
                                                <td>
                                                    <a href="booking_details.php?id=<?php echo $booking['booking_id']; ?>" class="btn btn-sm btn-primary">
                                                        View Details
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">This user has no booking history.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'admin_footer.php'; ?>