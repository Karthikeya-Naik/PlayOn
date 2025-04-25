<?php
include 'admin_header.php';
include '../db_connect.php';

// Check if venue ID is provided
if (!isset($_GET['venue_id']) || empty($_GET['venue_id'])) {
    header("Location: venues.php");
    exit();
}

$venue_id = $_GET['venue_id'];

// Get venue details
$venue_query = "SELECT * FROM venues WHERE venue_id = ?";
$stmt = $conn->prepare($venue_query);
$stmt->bind_param("i", $venue_id);
$stmt->execute();
$venue_result = $stmt->get_result();

if ($venue_result->num_rows == 0) {
    header("Location: venues.php");
    exit();
}

$venue = $venue_result->fetch_assoc();

// Handle slot addition
if (isset($_POST['add_slot'])) {
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $price_per_hour = $_POST['price_per_hour'];
    $status = isset($_POST['is_active']) ? 1 : 0;
    
    $slot_query = "INSERT INTO venue_slots (venue_id, start_time, end_time, price_per_hour, is_active) 
                   VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($slot_query);
    $stmt->bind_param("issdi", $venue_id, $start_time, $end_time, $price_per_hour, $status);
    
    if ($stmt->execute()) {
        $success_message = "Slot added successfully!";
    } else {
        $error_message = "Error adding slot: " . $conn->error;
    }
}

// Handle slot deletion
if (isset($_GET['delete_slot']) && !empty($_GET['delete_slot'])) {
    $slot_id = $_GET['delete_slot'];
    
    $delete_query = "DELETE FROM venue_slots WHERE id = ? AND venue_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("ii", $slot_id, $venue_id);
    
    if ($stmt->execute()) {
        $success_message = "Slot deleted successfully!";
    } else {
        $error_message = "Error deleting slot: " . $conn->error;
    }
}

// Handle slot update
if (isset($_POST['update_slot'])) {
    $slot_id = $_POST['slot_id'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $price_per_hour = $_POST['price_per_hour'];
    $status = isset($_POST['is_active']) ? 1 : 0;
    
    $update_query = "UPDATE venue_slots SET start_time = ?, end_time = ?, price_per_hour = ?, is_active = ? 
                     WHERE id = ? AND venue_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssdiii", $start_time, $end_time, $price_per_hour, $status, $slot_id, $venue_id);
    
    if ($stmt->execute()) {
        $success_message = "Slot updated successfully!";
    } else {
        $error_message = "Error updating slot: " . $conn->error;
    }
}

// Get all slots for this venue
$slots_query = "SELECT * FROM venue_slots WHERE venue_id = ? ORDER BY start_time";
$stmt = $conn->prepare($slots_query);
$stmt->bind_param("i", $venue_id);
$stmt->execute();
$slots_result = $stmt->get_result();
?>

<div class="admin-content">
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Manage Slots for <?php echo htmlspecialchars($venue['name']); ?></h2>
            <a href="venues.php" class="btn btn-secondary">Back to Venues</a>
        </div>
        
        <?php if(isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if(isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <!-- Add New Slot Form -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Add New Slot</h5>
            </div>
            <div class="card-body">
                <form method="post" action="">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="start_time" class="form-label">Start Time</label>
                            <input type="time" class="form-control" id="start_time" name="start_time" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="end_time" class="form-label">End Time</label>
                            <input type="time" class="form-control" id="end_time" name="end_time" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="price_per_hour" class="form-label">Price Per Hour (₹)</label>
                            <input type="number" step="0.01" class="form-control" id="price_per_hour" name="price_per_hour" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label d-block">&nbsp;</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" name="add_slot" class="btn btn-primary">Add Slot</button>
                </form>
            </div>
        </div>
        
        <!-- Existing Slots Table -->
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Existing Slots</h5>
            </div>
            <div class="card-body">
                <?php if($slots_result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Price (₹/hr)</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($slot = $slots_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $slot['id']; ?></td>
                                        <td><?php echo date("h:i A", strtotime($slot['start_time'])); ?></td>
                                        <td><?php echo date("h:i A", strtotime($slot['end_time'])); ?></td>
                                        <td>₹<?php echo number_format($slot['price_per_hour'], 2); ?></td>
                                        <td>
                                            <?php if($slot['is_active']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-warning edit-slot" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editSlotModal"
                                                    data-id="<?php echo $slot['id']; ?>"
                                                    data-start="<?php echo $slot['start_time']; ?>"
                                                    data-end="<?php echo $slot['end_time']; ?>"
                                                    data-price="<?php echo $slot['price_per_hour']; ?>"
                                                    data-active="<?php echo $slot['is_active']; ?>">
                                                Edit
                                            </button>
                                            <a href="venue_slots.php?venue_id=<?php echo $venue_id; ?>&delete_slot=<?php echo $slot['id']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Are you sure you want to delete this slot?')">
                                                Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">No slots defined for this venue yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Edit Slot Modal -->
<div class="modal fade" id="editSlotModal" tabindex="-1" aria-labelledby="editSlotModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="">
                <input type="hidden" name="slot_id" id="edit_slot_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSlotModalLabel">Edit Slot</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_start_time" class="form-label">Start Time</label>
                        <input type="time" class="form-control" id="edit_start_time" name="start_time" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_end_time" class="form-label">End Time</label>
                        <input type="time" class="form-control" id="edit_end_time" name="end_time" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_price_per_hour" class="form-label">Price Per Hour (₹)</label>
                        <input type="number" step="0.01" class="form-control" id="edit_price_per_hour" name="price_per_hour" required>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active">
                            <label class="form-check-label" for="edit_is_active">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_slot" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle edit slot modal data
    const editButtons = document.querySelectorAll('.edit-slot');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const start = this.getAttribute('data-start');
            const end = this.getAttribute('data-end');
            const price = this.getAttribute('data-price');
            const active = parseInt(this.getAttribute('data-active'));
            
            document.getElementById('edit_slot_id').value = id;
            document.getElementById('edit_start_time').value = start;
            document.getElementById('edit_end_time').value = end;
            document.getElementById('edit_price_per_hour').value = price;
            document.getElementById('edit_is_active').checked = active === 1;
        });
    });
});
</script>

<?php include 'admin_footer.php'; ?>