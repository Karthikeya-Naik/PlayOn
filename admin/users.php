<?php
session_start();
include 'admin_header.php';
include '../db_connect.php';

// Handle user status updates
if (isset($_POST['update_status'])) {
    $user_id = $_POST['user_id'];
    $status = isset($_POST['is_active']) ? 1 : 0;
    
    $update_query = "UPDATE users SET is_active = ? WHERE user_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ii", $status, $user_id);
    
    if ($stmt->execute()) {
        $success_message = "User status updated successfully!";
    } else {
        $error_message = "Error updating user status: " . $conn->error;
    }
}

// Handle user deletion (soft delete)
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $user_id = $_GET['delete'];
    
    // Check if user has any bookings
    $check_query = "SELECT COUNT(*) as count FROM bookings WHERE user_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    
    if ($count > 0) {
        // Soft delete - just deactivate
        $update_query = "UPDATE users SET is_active = 0 WHERE user_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            $success_message = "User deactivated successfully. Cannot delete user with existing bookings.";
        } else {
            $error_message = "Error deactivating user: " . $conn->error;
        }
    } else {
        // Hard delete - no bookings exist
        $delete_query = "DELETE FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            $success_message = "User deleted successfully!";
        } else {
            $error_message = "Error deleting user: " . $conn->error;
        }
    }
}

// Get all users - FIXED QUERY (removed user_type filter)
$query = "SELECT u.*, 
          (SELECT COUNT(*) FROM bookings WHERE user_id = u.user_id) as booking_count
          FROM users u 
          ORDER BY u.created_at DESC";
$result = $conn->query($query);
?>

<div class="admin-content">
    <div class="container mt-4">
        <h2>Manage Users</h2>
        
        <?php if(isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if(isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body">
                <?php if($result && $result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Bookings</th>
                                    <th>Registered On</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($user = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $user['user_id']; ?></td>
                                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['phone'] ?: 'N/A'); ?></td>
                                        <td>
                                            <?php if($user['booking_count'] > 0): ?>
                                                <a href="bookings.php?user_id=<?php echo $user['user_id']; ?>" class="badge bg-info text-decoration-none">
                                                    <?php echo $user['booking_count']; ?> bookings
                                                </a>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">No bookings</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('d-m-Y H:i', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <?php if($user['is_active']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-warning edit-user" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editUserModal"
                                                    data-id="<?php echo $user['user_id']; ?>"
                                                    data-active="<?php echo $user['is_active']; ?>">
                                                Update Status
                                            </button>
                                            <a href="userdetails.php?id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-info mt-1">
                                                Details
                                            </a>
                                            <?php if($user['booking_count'] == 0): ?>
                                                <a href="users.php?delete=<?php echo $user['user_id']; ?>" 
                                                   class="btn btn-sm btn-danger mt-1" 
                                                   onclick="return confirm('Are you sure you want to delete this user?')">
                                                    Delete
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">No users found.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Update User Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active">
                            <label class="form-check-label" for="edit_is_active">Active</label>
                        </div>
                        <small class="text-muted">
                            Inactive users will not be able to log in or make new bookings.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_status" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle edit user modal data
    const editButtons = document.querySelectorAll('.edit-user');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const active = parseInt(this.getAttribute('data-active'));
            
            document.getElementById('edit_user_id').value = id;
            document.getElementById('edit_is_active').checked = active === 1;
        });
    });
});
</script>

<?php include 'admin_footer.php'; ?>