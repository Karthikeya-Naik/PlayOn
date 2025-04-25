<?php
session_start();
include 'admin_header.php';
include '../db_connect.php';

// Handle booking status updates
if (isset($_POST['update_status'])) {
    $booking_id = $_POST['booking_id'];
    $status = $_POST['status'];
    
    $update_query = "UPDATE bookings SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $status, $booking_id);
    
    if ($stmt->execute()) {
        $success_message = "Booking status updated successfully!";
    } else {
        $error_message = "Error updating booking status: " . $conn->error;
    }
}

// Get all bookings with user and venue details
$query = "SELECT b.*, u.name as user_name, u.email, v.venue_name as venue_name 
          FROM bookings b
          JOIN users u ON b.user_id = u.user_id
          JOIN venues v ON b.venue_id = v.venue_id
          ORDER BY b.booking_date DESC, b.start_time DESC";
$result = $conn->query($query);
?>

<div class="admin-content">
    <div class="container mt-4">
        <h2>Manage Bookings</h2>
        
        <?php if(isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if(isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body">
                <?php if($result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Venue</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Duration</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Booked On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($booking = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $booking['booking_id']; ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($booking['user_name']); ?><br>
                                            <small><?php echo htmlspecialchars($booking['email']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($booking['venue_name']); ?></td>
                                        <td><?php echo date('d-m-Y', strtotime($booking['booking_date'])); ?></td>
                                        <td><?php echo date('h:i A', strtotime($booking['start_time'])); ?></td>
                                        <td><?php echo $booking['duration']; ?> hr(s)</td>
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
                                        <td><?php echo date('d-m-Y H:i', strtotime($booking['created_at'])); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary edit-booking" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editBookingModal"
                                                    data-id="<?php echo $booking['booking_id']; ?>"
                                                    data-status="<?php echo $booking['status']; ?>">
                                                Update Status
                                            </button>
                                            <a href="booking_details.php?id=<?php echo $booking['booking_id']; ?>" class="btn btn-sm btn-info mt-1">
                                                Details
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">No bookings found.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Edit Booking Modal -->
<div class="modal fade" id="editBookingModal" tabindex="-1" aria-labelledby="editBookingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="">
                <input type="hidden" name="booking_id" id="edit_booking_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="editBookingModalLabel">Update Booking Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="edit_status" name="status" required>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle edit booking modal data
    const editButtons = document.querySelectorAll('.edit-booking');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const status = this.getAttribute('data-status');
            
            document.getElementById('edit_booking_id').value = id;
            document.getElementById('edit_status').value = status;
        });
    });
});
</script>

<?php include 'admin_footer.php'; ?>