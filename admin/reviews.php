<?php
session_start();
require_once '../db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle review status updates
if (isset($_POST['update_status'])) {
    $review_id = $_POST['review_id'];
    $is_approved = isset($_POST['is_approved']) ? 1 : 0;
    
    $update_query = "UPDATE reviews SET is_approved = ? WHERE review_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ii", $is_approved, $review_id);
    
    if ($stmt->execute()) {
        $success_message = "Review status updated successfully!";
    } else {
        $error_message = "Error updating review status: " . $conn->error;
    }
}

// Handle review deletion
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $review_id = $_GET['delete'];
    
    $delete_query = "DELETE FROM reviews WHERE review_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $review_id);
    
    if ($stmt->execute()) {
        $success_message = "Review deleted successfully!";
    } else {
        $error_message = "Error deleting review: " . $conn->error;
    }
}

// Get all reviews
$query = "SELECT r.*, u.name as user_name, v.venue_name 
          FROM reviews r
          JOIN users u ON r.user_id = u.user_id
          JOIN venues v ON r.venue_id = v.venue_id
          ORDER BY r.created_at DESC";
$result = $conn->query($query);

// Get rating statistics
$stats_query = "SELECT 
                ROUND(AVG(rating), 1) as avg_rating,
                COUNT(*) as total_reviews,
                SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
                FROM reviews";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

include 'admin_header.php';
?>

<div class="container-fluid py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h2 class="h3 mb-0 text-gray-800">Reviews Management</h2>
        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" id="exportReviews">
            <i class="fas fa-download fa-sm text-white-50 me-1"></i> Export Reviews
        </a>
    </div>
    
    <?php if(isset($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if(isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <!-- Stats Cards Row -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100 border-start border-primary border-4 rounded-3 overflow-hidden transition-all hover-shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="icon-circle bg-primary text-white">
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                        <div class="col">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Average Rating</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800"><?= $stats['avg_rating'] ?? 0 ?>/5</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100 border-start border-success border-4 rounded-3 overflow-hidden transition-all hover-shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="icon-circle bg-success text-white">
                                <i class="fas fa-comments"></i>
                            </div>
                        </div>
                        <div class="col">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Reviews</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800"><?= $stats['total_reviews'] ?? 0 ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
    </div>
    
    <div class="row mb-4">
        <!-- Rating Distribution Chart -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow border-0 rounded-3">
                <div class="card-header py-3 bg-white border-0">
                    <h6 class="m-0 font-weight-bold text-primary">Rating Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="rating-bars">
                        <div class="rating-bar mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>5 Stars</span>
                                <span><?= $stats['five_star'] ?? 0 ?> reviews</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: <?= $stats['total_reviews'] ? ($stats['five_star'] / $stats['total_reviews'] * 100) : 0 ?>%" 
                                     aria-valuenow="<?= $stats['five_star'] ?? 0 ?>" aria-valuemin="0" aria-valuemax="<?= $stats['total_reviews'] ?? 0 ?>"></div>
                            </div>
                        </div>
                        <div class="rating-bar mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>4 Stars</span>
                                <span><?= $stats['four_star'] ?? 0 ?> reviews</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-info" role="progressbar" 
                                     style="width: <?= $stats['total_reviews'] ? ($stats['four_star'] / $stats['total_reviews'] * 100) : 0 ?>%" 
                                     aria-valuenow="<?= $stats['four_star'] ?? 0 ?>" aria-valuemin="0" aria-valuemax="<?= $stats['total_reviews'] ?? 0 ?>"></div>
                            </div>
                        </div>
                        <div class="rating-bar mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>3 Stars</span>
                                <span><?= $stats['three_star'] ?? 0 ?> reviews</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-primary" role="progressbar" 
                                     style="width: <?= $stats['total_reviews'] ? ($stats['three_star'] / $stats['total_reviews'] * 100) : 0 ?>%" 
                                     aria-valuenow="<?= $stats['three_star'] ?? 0 ?>" aria-valuemin="0" aria-valuemax="<?= $stats['total_reviews'] ?? 0 ?>"></div>
                            </div>
                        </div>
                        <div class="rating-bar mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>2 Stars</span>
                                <span><?= $stats['two_star'] ?? 0 ?> reviews</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-warning" role="progressbar" 
                                     style="width: <?= $stats['total_reviews'] ? ($stats['two_star'] / $stats['total_reviews'] * 100) : 0 ?>%" 
                                     aria-valuenow="<?= $stats['two_star'] ?? 0 ?>" aria-valuemin="0" aria-valuemax="<?= $stats['total_reviews'] ?? 0 ?>"></div>
                            </div>
                        </div>
                        <div class="rating-bar mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>1 Star</span>
                                <span><?= $stats['one_star'] ?? 0 ?> reviews</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-danger" role="progressbar" 
                                     style="width: <?= $stats['total_reviews'] ? ($stats['one_star'] / $stats['total_reviews'] * 100) : 0 ?>%" 
                                     aria-valuenow="<?= $stats['one_star'] ?? 0 ?>" aria-valuemin="0" aria-valuemax="<?= $stats['total_reviews'] ?? 0 ?>"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filter Controls -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow border-0 rounded-3">
                <div class="card-header py-3 bg-white border-0">
                    <h6 class="m-0 font-weight-bold text-primary">Filter Reviews</h6>
                </div>
                <div class="card-body">
                    <form id="filterForm" class="row g-3">
                        <div class="col-md-4">
                            <label for="venueFilter" class="form-label">Venue</label>
                            <select id="venueFilter" class="form-select">
                                <option value="">All Venues</option>
                                <?php
                                $venues_query = "SELECT venue_id, venue_name FROM venues ORDER BY venue_name";
                                $venues_result = $conn->query($venues_query);
                                while ($venue = $venues_result->fetch_assoc()) {
                                    echo '<option value="' . $venue['venue_id'] . '">' . htmlspecialchars($venue['venue_name']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="ratingFilter" class="form-label">Rating</label>
                            <select id="ratingFilter" class="form-select">
                                <option value="">All Ratings</option>
                                <option value="5">5 Stars</option>
                                <option value="4">4 Stars</option>
                                <option value="3">3 Stars</option>
                                <option value="2">2 Stars</option>
                                <option value="1">1 Star</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="statusFilter" class="form-label">Status</label>
                            <select id="statusFilter" class="form-select">
                                <option value="">All Status</option>
                                <option value="1">Approved</option>
                                <option value="0">Pending</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="reset" class="btn btn-secondary w-100">Reset</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Reviews Table -->
    <div class="card shadow mb-4 border-0 rounded-3">
        <div class="card-header py-3 bg-white border-0">
            <h6 class="m-0 font-weight-bold text-primary">All Reviews</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="reviewsTable">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Venue</th>
                            <th>User</th>
                            <th>Rating</th>
                            <th>Review</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($result && $result->num_rows > 0): ?>
                            <?php while($review = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $review['review_id']; ?></td>
                                    <td>
                                        <a href="venue_details.php?id=<?php echo $review['venue_id']; ?>" class="text-primary">
                                            <?php echo htmlspecialchars($review['venue_name']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="userdetails.php?id=<?php echo $review['user_id']; ?>" class="text-primary">
                                            <?php echo htmlspecialchars($review['user_name']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div class="stars">
                                            <?php 
                                            for($i = 1; $i <= 5; $i++) {
                                                if($i <= $review['rating']) {
                                                    echo '<i class="fas fa-star text-warning"></i>';
                                                } else {
                                                    echo '<i class="far fa-star text-warning"></i>';
                                                }
                                            }
                                            ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="review-text">
                                            <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                                        </div>
                                    </td>
                                    <td><?php echo date('d-m-Y', strtotime($review['created_at'])); ?></td>
                                    <td>
                                        <?php if($review['is_approved']): ?>
                                            <span class="badge bg-success">Approved</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-primary update-status" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#updateStatusModal"
                                                    data-id="<?php echo $review['review_id']; ?>"
                                                    data-approved="<?php echo $review['is_approved']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="reviews.php?delete=<?php echo $review['review_id']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Are you sure you want to delete this review?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">No reviews found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="">
                <input type="hidden" name="review_id" id="review_id">
                <div class="modal-header">
                    <h5 class="modal-title">Update Review Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="is_approved" name="is_approved">
                        <label class="form-check-label" for="is_approved">Approve Review</label>
                    </div>
                    <p class="text-muted">Approved reviews will be visible to all users on the venue page.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_status" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.stars {
    color: #ffc107;
}
.review-text {
    max-width: 300px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.icon-circle {
    height: 3rem;
    width: 3rem;
    border-radius: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.hover-shadow:hover {
    transform: translateY(-5px);
    box-shadow: 0 1rem 3rem rgba(0,0,0,.175)!important;
}
.transition-all {
    transition: all 0.3s ease;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle status update modal
    const statusButtons = document.querySelectorAll('.update-status');
    statusButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const approved = parseInt(this.getAttribute('data-approved'));
            
            document.getElementById('review_id').value = id;
            document.getElementById('is_approved').checked = approved === 1;
        });
    });
    
    // Handle filtering
    const filterForm = document.getElementById('filterForm');
    const filterInputs = filterForm.querySelectorAll('select');
    const table = document.getElementById('reviewsTable');
    const tableRows = table.querySelectorAll('tbody tr');
    
    filterInputs.forEach(input => {
        input.addEventListener('change', filterTable);
    });
    
    filterForm.addEventListener('reset', function() {
        setTimeout(function() {
            filterTable();
        }, 10);
    });
    
    function filterTable() {
        const venueFilter = document.getElementById('venueFilter').value;
        const ratingFilter = document.getElementById('ratingFilter').value;
        const statusFilter = document.getElementById('statusFilter').value;
        
        tableRows.forEach(row => {
            const venue = row.querySelector('td:nth-child(2) a').getAttribute('href').split('=')[1];
            const rating = row.querySelectorAll('td:nth-child(4) .fa-star.text-warning').length;
            const status = row.querySelector('td:nth-child(7) .badge').classList.contains('bg-success') ? '1' : '0';
            
            const venueMatch = !venueFilter || venue === venueFilter;
            const ratingMatch = !ratingFilter || rating === parseInt(ratingFilter);
            const statusMatch = statusFilter === '' || status === statusFilter;
            
            if (venueMatch && ratingMatch && statusMatch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
    
    // Export functionality
    document.getElementById('exportReviews').addEventListener('click', function(e) {
        e.preventDefault();
        
        // In a real implementation, this would generate a CSV or Excel file
        alert('Export functionality would be implemented here.');
    });
});
</script>

<?php include 'admin_footer.php'; ?>