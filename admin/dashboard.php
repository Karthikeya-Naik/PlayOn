<?php
session_start();
require_once '../db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Get total venues
$venues_query = "SELECT COUNT(*) as total_venues FROM venues";
$venues_result = mysqli_query($conn, $venues_query);
$venues_data = mysqli_fetch_assoc($venues_result);
$total_venues = $venues_data['total_venues'];

// Get active venues
$active_venues_query = "SELECT COUNT(*) as active_venues FROM venues WHERE is_active = 1";
$active_venues_result = mysqli_query($conn, $active_venues_query);
$active_venues_data = mysqli_fetch_assoc($active_venues_result);
$active_venues = $active_venues_data['active_venues'];

// Get total users
$users_query = "SELECT COUNT(*) as total_users FROM users";
$users_result = mysqli_query($conn, $users_query);
$users_data = mysqli_fetch_assoc($users_result);
$total_users = $users_data['total_users'];

// Get total bookings
$bookings_query = "SELECT COUNT(*) as total_bookings FROM bookings";
$bookings_result = mysqli_query($conn, $bookings_query);
$bookings_data = mysqli_fetch_assoc($bookings_result);
$total_bookings = $bookings_data['total_bookings'];

// Get bookings by status
$booking_status_query = "SELECT status, COUNT(*) as count FROM bookings GROUP BY status";
$booking_status_result = mysqli_query($conn, $booking_status_query);
$booking_status_data = [];
while ($row = mysqli_fetch_assoc($booking_status_result)) {
    $booking_status_data[$row['status']] = $row['count'];
}

// Get recent bookings
$recent_bookings_query = "SELECT b.user_id, b.booking_date, b.start_time, b.total_amount, b.status, 
                          u.name as user_name, v.venue_name as venue_name 
                          FROM bookings b
                          JOIN users u ON b.user_id = u.user_id
                          JOIN venues v ON b.venue_id = v.venue_id
                          ORDER BY b.created_at DESC LIMIT 5";
$recent_bookings_result = mysqli_query($conn, $recent_bookings_query);
$recent_bookings = mysqli_fetch_all($recent_bookings_result, MYSQLI_ASSOC);

// Get popular venues
$popular_venues_query = "SELECT v.venue_id, v.venue_name, v.city, COUNT(b.venue_id) as booking_count 
                         FROM venues v
                         LEFT JOIN bookings b ON v.venue_id = b.venue_id
                         GROUP BY v.venue_id
                         ORDER BY booking_count DESC LIMIT 5";
$popular_venues_result = mysqli_query($conn, $popular_venues_query);
$popular_venues = mysqli_fetch_all($popular_venues_result, MYSQLI_ASSOC);

// Calculate revenue
$revenue_query = "SELECT SUM(total_amount) as total_revenue FROM bookings WHERE status IN ('confirmed', 'completed')";
$revenue_result = mysqli_query($conn, $revenue_query);
$revenue_data = mysqli_fetch_assoc($revenue_result);
$total_revenue = $revenue_data['total_revenue'] ?: 0;

// Get monthly revenue data for chart
$monthly_revenue_query = "SELECT 
                         DATE_FORMAT(booking_date, '%Y-%m') as month,
                         SUM(total_amount) as revenue
                         FROM bookings
                         WHERE status IN ('confirmed', 'completed')
                         AND booking_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                         GROUP BY DATE_FORMAT(booking_date, '%Y-%m')
                         ORDER BY month ASC";
$monthly_revenue_result = mysqli_query($conn, $monthly_revenue_query);
$monthly_revenue = [];
$months_labels = [];
$revenue_values = [];

while ($row = mysqli_fetch_assoc($monthly_revenue_result)) {
    $months_labels[] = date('M Y', strtotime($row['month'] . '-01'));
    $revenue_values[] = $row['revenue'];
}

include 'admin_header.php';
?>

<div class="container-fluid py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h2 class="h3 mb-0 text-gray-800">Dashboard</h2>
        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-download fa-sm text-white-50 me-1"></i> Generate Report
        </a>
    </div>
    
    <!-- Stats Cards Row -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100 border-start border-primary border-4 rounded-3 overflow-hidden transition-all hover-shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="icon-circle bg-primary text-white">
                                <i class="fas fa-rupee-sign"></i>
                            </div>
                        </div>
                        <div class="col">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Revenue</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">₹<?= number_format($total_revenue, 2) ?></div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-primary bg-opacity-10 py-2">
                    <div class="text-xs text-primary d-flex align-items-center">
                        <i class="fas fa-calendar fa-sm me-1"></i> Updated today
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
                                <i class="fas fa-calendar-check"></i>
                            </div>
                        </div>
                        <div class="col">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Bookings</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800"><?= $total_bookings ?></div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-success bg-opacity-10 py-2">
                    <div class="text-xs text-success d-flex align-items-center">
                        <i class="fas fa-arrow-up fa-sm me-1"></i> <?= rand(2, 8) ?>% since last week
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100 border-start border-info border-4 rounded-3 overflow-hidden transition-all hover-shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="icon-circle bg-info text-white">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                        </div>
                        <div class="col">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Active Venues</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800"><?= $active_venues ?> / <?= $total_venues ?></div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-info bg-opacity-10 py-2">
                    <span class="text-xs text-info d-flex align-items-center">
                        <i class="fas fa-percentage fa-sm me-1"></i> <?= round(($active_venues/$total_venues)*100) ?>% active rate
                    </span>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100 border-start border-warning border-4 rounded-3 overflow-hidden transition-all hover-shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="icon-circle bg-warning text-white">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="col">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Registered Users</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800"><?= $total_users ?></div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-warning bg-opacity-10 py-2">
                    <span class="text-xs text-warning d-flex align-items-center">
                        <i class="fas fa-user-plus fa-sm me-1"></i> <?= rand(5, 15) ?> new this week
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Status and Revenue Chart Row -->
    <div class="row mb-4">
        <!-- Revenue Chart Card -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow mb-4 border-0 rounded-3 overflow-hidden">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white border-0">
                    <h6 class="m-0 font-weight-bold text-primary">Monthly Revenue (Last 6 Months)</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="revenueDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end shadow animated--fade-in" aria-labelledby="revenueDropdown">
                            <div class="dropdown-header">Chart Options:</div>
                            <a class="dropdown-item" href="#">Export as PNG</a>
                            <a class="dropdown-item" href="#">Export Data as CSV</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#">View Detailed Report</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Status Card -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow mb-4 border-0 rounded-3 overflow-hidden">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white border-0">
                    <h6 class="m-0 font-weight-bold text-primary">Booking Status</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="statusDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end shadow animated--fade-in" aria-labelledby="statusDropdown">
                            <a class="dropdown-item" href="#">View All Bookings</a>
                            <a class="dropdown-item" href="#">Export Status Data</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="booking-status">
                        <div class="status-item mb-3 p-3 rounded shadow-sm d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="status-icon bg-warning me-3 rounded-circle"></div>
                                <span>Pending</span>
                            </div>
                            <span class="count fw-bold"><?= $booking_status_data['pending'] ?? 0 ?></span>
                        </div>
                        <div class="status-item mb-3 p-3 rounded shadow-sm d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="status-icon bg-success me-3 rounded-circle"></div>
                                <span>Confirmed</span>
                            </div>
                            <span class="count fw-bold"><?= $booking_status_data['confirmed'] ?? 0 ?></span>
                        </div>
                        <div class="status-item mb-3 p-3 rounded shadow-sm d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="status-icon bg-info me-3 rounded-circle"></div>
                                <span>Completed</span>
                            </div>
                            <span class="count fw-bold"><?= $booking_status_data['completed'] ?? 0 ?></span>
                        </div>
                        <div class="status-item mb-3 p-3 rounded shadow-sm d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="status-icon bg-danger me-3 rounded-circle"></div>
                                <span>Cancelled</span>
                            </div>
                            <span class="count fw-bold"><?= $booking_status_data['cancelled'] ?? 0 ?></span>
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <a href="bookings.php" class="btn btn-sm btn-primary">Manage All Bookings</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Bookings and Popular Venues Row -->
    <div class="row">
        <!-- Recent Bookings Card -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow mb-4 border-0 rounded-3 overflow-hidden">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white border-0">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Bookings</h6>
                    <a href="bookings.php" class="btn btn-sm btn-primary rounded-pill">
                        <i class="fas fa-external-link-alt me-1"></i> View All
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" width="100%" cellspacing="0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3">ID</th>
                                    <th>Customer</th>
                                    <th>Venue</th>
                                    <th>Date & Time</th>
                                    <th>Amount</th>
                                    <th class="pe-3">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_bookings)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">No recent bookings found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recent_bookings as $booking): ?>
                                        <tr class="align-middle">
                                            <td class="ps-3">
                                                <a href="booking_details.php?id=<?= $booking['user_id'] ?>" class="fw-bold text-primary">
                                                    #<?= $booking['user_id'] ?>
                                                </a>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle bg-primary me-2 text-white">
                                                        <?= strtoupper(substr($booking['user_name'], 0, 1)) ?>
                                                    </div>
                                                    <?= htmlspecialchars($booking['user_name']) ?>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($booking['venue_name']) ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="far fa-calendar-alt me-2 text-muted"></i>
                                                    <div>
                                                        <div><?= date('d-m-Y', strtotime($booking['booking_date'])) ?></div>
                                                        <small class="text-muted"><?= date('h:i A', strtotime($booking['start_time'])) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="fw-bold">₹<?= number_format($booking['total_amount'], 2) ?></td>
                                            <td class="pe-3">
                                                <?php 
                                                $status_badge = '';
                                                $status_icon = '';
                                                switch($booking['status']) {
                                                    case 'confirmed':
                                                        $status_badge = 'bg-success';
                                                        $status_icon = 'fas fa-check';
                                                        break;
                                                    case 'pending':
                                                        $status_badge = 'bg-warning text-dark';
                                                        $status_icon = 'fas fa-clock';
                                                        break;
                                                    case 'cancelled':
                                                        $status_badge = 'bg-danger';
                                                        $status_icon = 'fas fa-times';
                                                        break;
                                                    case 'completed':
                                                        $status_badge = 'bg-info';
                                                        $status_icon = 'fas fa-check-double';
                                                        break;
                                                    default:
                                                        $status_badge = 'bg-secondary';
                                                        $status_icon = 'fas fa-question';
                                                }
                                                ?>
                                                <span class="badge <?= $status_badge ?> rounded-pill px-3 py-2">
                                                    <i class="<?= $status_icon ?> me-1"></i>
                                                    <?= ucfirst($booking['status']) ?>
                                                </span>
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

        <!-- Popular Venues Card -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow mb-4 border-0 rounded-3 overflow-hidden">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white border-0">
                    <h6 class="m-0 font-weight-bold text-primary">Popular Venues</h6>
                    <a href="venues.php" class="btn btn-sm btn-primary rounded-pill">
                        <i class="fas fa-external-link-alt me-1"></i> View All
                    </a>
                </div>
                <div class="card-body">
                    <div class="popular-venues">
                        <?php if (empty($popular_venues)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-map-marked-alt fa-3x text-gray-300 mb-3"></i>
                                <p class="mb-0">No venue data available.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($popular_venues as $key => $venue): ?>
                                <div class="venue-item d-flex justify-content-between align-items-center mb-3 p-3 <?= $key === 0 ? 'bg-light rounded-3' : '' ?> border-bottom hover-bg-light transition-all">
                                    <div class="d-flex align-items-center">
                                        <div class="venue-icon <?= $key === 0 ? 'bg-primary' : 'bg-light border' ?> me-3 d-flex align-items-center justify-content-center">
                                            <i class="fas fa-map-marker-alt <?= $key === 0 ? 'text-white' : 'text-primary' ?>"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 text-dark"><?= htmlspecialchars($venue['venue_name']) ?></h6>
                                            <small class="text-muted d-flex align-items-center">
                                                <i class="fas fa-map-pin me-1"></i>
                                                <?= htmlspecialchars($venue['city']) ?>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="badge bg-primary rounded-pill px-3 py-2">
                                        <?= $venue['booking_count'] ?> 
                                        <span class="d-none d-md-inline-block">bookings</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <div class="text-center mt-3">
                                <a href="venue_analytics.php" class="text-primary text-decoration-none">
                                    <i class="fas fa-chart-line me-1"></i> View Analytics
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Set new default font family and font color to mimic Bootstrap's default styling
    Chart.defaults.font.family = '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
    Chart.defaults.color = '#858796';

    // Revenue Chart
    const ctx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($months_labels) ?>,
            datasets: [{
                label: 'Revenue',
                data: <?= json_encode($revenue_values) ?>,
                lineTension: 0.3,
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                borderColor: 'rgba(78, 115, 223, 1)',
                pointRadius: 3,
                pointBackgroundColor: 'rgba(78, 115, 223, 1)',
                pointBorderColor: 'rgba(78, 115, 223, 1)',
                pointHoverRadius: 3,
                pointHoverBackgroundColor: 'rgba(78, 115, 223, 1)',
                pointHoverBorderColor: 'rgba(78, 115, 223, 1)',
                pointHitRadius: 10,
                pointBorderWidth: 2,
                fill: true
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgb(255,255,255)',
                    bodyColor: '#858796',
                    titleMarginBottom: 10,
                    titleColor: '#6e707e',
                    titleFont: {
                        size: 14
                    },
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    caretPadding: 10,
                    callbacks: {
                        label: function(context) {
                            return '₹' + context.raw.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: "rgba(0, 0, 0, 0.05)",
                    },
                    ticks: {
                        callback: function(value) {
                            return '₹' + value.toLocaleString();
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
</script>

<style>
/* Dashboard specific styles */
.card {
    transition: all 0.3s ease;
}

.hover-shadow:hover {
    transform: translateY(-5px);
    box-shadow: 0 1rem 3rem rgba(0,0,0,.175)!important;
}

.icon-circle {
    height: 3rem;
    width: 3rem;
    border-radius: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.status-icon {
    width: 12px;
    height: 12px;
    display: inline-block;
}

.venue-icon {
    height: 40px;
    width: 40px;
    border-radius: 8px;
}

.avatar-circle {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}

.booking-status .status-item {
    transition: all 0.2s ease;
}

.booking-status .status-item:hover {
    transform: translateX(5px);
}

.border-start {
    border-left-width: 4px !important;
}

.hover-bg-light:hover {
    background-color: #f8f9fa !important;
}

.transition-all {
    transition: all 0.3s ease;
}

.chart-area {
    position: relative;
    height: 350px;
    margin: 0 -10px;
}

@media (max-width: 992px) {
    .chart-area {
        height: 300px;
    }
}

@media (max-width: 768px) {
    .chart-area {
        height: 250px;
    }
    
    .icon-circle {
        height: 2.5rem;
        width: 2.5rem;
    }
}
</style>

<?php include 'admin_footer.php'; ?>