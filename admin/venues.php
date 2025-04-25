<?php
session_start();
require_once '../db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle venue deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $venue_id = $_GET['delete'];
    // Delete reviews first to satisfy foreign key constraint
$delete_reviews = "DELETE FROM reviews WHERE venue_id = $venue_id";
mysqli_query($conn, $delete_reviews);

// Then delete the venue
$delete_venue = "DELETE FROM venues WHERE venue_id = $venue_id";
if (mysqli_query($conn, $delete_venue)) {
    $_SESSION['success'] = "Venue deleted successfully!";
} else {
    $_SESSION['error'] = "Error deleting venue: " . mysqli_error($conn);
}

}

// Fetch all venues
$query = "SELECT * FROM venues ORDER BY venue_name ASC";
$result = mysqli_query($conn, $query);
$venues = mysqli_fetch_all($result, MYSQLI_ASSOC);

include 'admin_header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Venues</h2>
        <a href="add_venue.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Venue</a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $_SESSION['success']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $_SESSION['error']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Location</th>
                            <th>Price/Hour</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($venues)): ?>
                        <tr>
                            <td colspan="8" class="text-center">No venues found.</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($venues as $venue): ?>
                        <tr>
                            <td><?= $venue['venue_id'] ?></td>
                            <td>
                                <?php if (!empty($venue['image'])): ?>
                                <img src="../uploads/venues/<?= $venue['image'] ?>" alt="<?= $venue['venue_name'] ?>"
                                    class="img-thumbnail" style="width: 200px;">
                                <?php else: ?>
                                <img src="../assets/img/default-venue.jpg" alt="Default" class="img-thumbnail"
                                    style="width: 100px;">
                                <?php endif; ?>
                            </td>
                            <td><?= $venue['venue_name'] ?></td>
                            <td><?= $venue['city'] ?></td>
                            <td>₹<?= $venue['price_per_hour'] ?></td>
                            <td>
                                <?php if ($venue['is_active'] == 1): ?>
                                <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                <span class="badge bg-danger">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="edit_venue.php?id=<?= $venue['venue_id'] ?>"
                                    class="btn btn-sm btn-primary mb-1"><i class="fas fa-edit"></i> Edit</a>
                                <a href="venue_slots.php?venue_id=<?= $venue['venue_id'] ?>"
                                    class="btn btn-sm btn-info mb-1"><i class="fas fa-clock"></i> Slots</a>
                                <a href="venue_images.php?venue_id=<?= $venue['venue_id'] ?>"
                                    class="btn btn-sm btn-warning mb-1"><i class="fas fa-images"></i> Images</a>
                                <a href="venues.php?delete=<?= $venue['venue_id'] ?>" class="btn btn-sm btn-danger mb-1"
                                    onclick="return confirm('Are you sure you want to delete this venue?')"><i
                                        class="fas fa-trash"></i> Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'admin_footer.php'; ?>