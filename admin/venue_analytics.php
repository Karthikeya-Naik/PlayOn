<?php
session_start();
require_once '../db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Get filter parameters
$filter_city = isset($_GET['city']) ? $_GET['city'] : '';
$filter_period = isset($_GET['period']) ? $_GET['period'] : 'month';
$filter_sort = isset($_GET['sort']) ? $_GET['sort'] : 'bookings';

// Set time period based on filter
$period_clause = "";
switch ($filter_period) {
    case 'week':
        $period_clause = "AND b.booking_date >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK)";
        $period_format = "'%d %b'";
        $period_interval = "DAY";
        break;
    case 'month':
        $period_clause = "AND b.booking_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
        $period_format = "'%d %b'";
        $period_interval = "DAY";
        break;
    case 'quarter':
        $period_clause = "AND b.booking_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)";
        $period_format = "'%b %Y'";
        $period_interval = "WEEK";
        break;
    case 'year':
        $period_clause = "AND b.booking_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
        $period_format = "'%b %Y'";
        $period_interval = "MONTH";
        break;
    default:
        $period_clause = "AND b.booking_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
        $period_format = "'%d %b'";
        $period_interval = "DAY";
}

// Get all available cities
$cities_query = "SELECT DISTINCT city FROM venues ORDER BY city";
$cities_result = mysqli_query($conn, $cities_query);
$cities = mysqli_fetch_all($cities_result, MYSQLI_ASSOC);

// Build filter conditions
$filter_conditions = "";
if (!empty($filter_city)) {
    $filter_conditions .= " AND v.city = '" . mysqli_real_escape_string($conn, $filter_city) . "'";
}

// Get venues analytics
$sort_clause = "";
switch ($filter_sort) {
    case 'bookings':
        $sort_clause = "ORDER BY booking_count DESC";
        break;
    case 'revenue':
        $sort_clause = "ORDER BY total_revenue DESC";
        break;
    case 'rating':
        $sort_clause = "ORDER BY avg_rating DESC";
        break;
    case 'name':
        $sort_clause = "ORDER BY venue_name ASC";
        break;
    default:
        $sort_clause = "ORDER BY booking_count DESC";
}

$venues_query = "SELECT 
                v.venue_id, 
                v.venue_name, 
                v.city, 
                v.is_active,
                COUNT(b.booking_id) as booking_count, 
                SUM(CASE WHEN b.status IN ('confirmed', 'completed') THEN b.total_amount ELSE 0 END) as total_revenue,
                AVG(r.rating) as avg_rating,
                COUNT(DISTINCT b.user_id) as unique_customers
            FROM venues v
            LEFT JOIN bookings b ON v.venue_id = b.venue_id $period_clause
            LEFT JOIN reviews r ON v.venue_id = r.venue_id
            WHERE 1=1 $filter_conditions
            GROUP BY v.venue_id
            $sort_clause";

$venues_result = mysqli_query($conn, $venues_query);
$venues_data = mysqli_fetch_all($venues_result, MYSQLI_ASSOC);

// Get top performing venues
$top_venues_query = "SELECT 
                    v.venue_id, 
                    v.venue_name, 
                    COUNT(b.booking_id) as booking_count,
                    SUM(CASE WHEN b.status IN ('confirmed', 'completed') THEN b.total_amount ELSE 0 END) as total_revenue
                FROM venues v
                JOIN bookings b ON v.venue_id = b.venue_id
                WHERE b.status IN ('confirmed', 'completed') $period_clause $filter_conditions
                GROUP BY v.venue_id
                ORDER BY total_revenue DESC
                LIMIT 5";

$top_venues_result = mysqli_query($conn, $top_venues_query);
$top_venues = mysqli_fetch_all($top_venues_result, MYSQLI_ASSOC);

// Get booking trend data
$trend_query = "SELECT 
                DATE_FORMAT(b.booking_date, $period_format) as period,
                COUNT(b.booking_id) as bookings,
                SUM(CASE WHEN b.status IN ('confirmed', 'completed') THEN b.total_amount ELSE 0 END) as revenue
            FROM bookings b
            JOIN venues v ON b.venue_id = v.venue_id
            WHERE 1=1 $period_clause $filter_conditions
            GROUP BY period
            ORDER BY b.booking_date ASC";

$trend_result = mysqli_query($conn, $trend_query);
$trend_data = mysqli_fetch_all($trend_result, MYSQLI_ASSOC);

// Prepare data for charts
$periods = [];
$bookings = [];
$revenues = [];

foreach ($trend_data as $data) {
    $periods[] = $data['period'];
    $bookings[] = $data['bookings'];
    $revenues[] = $data['revenue'];
}

// Calculate summary metrics
$total_bookings = 0;
$total_revenue = 0;
$total_venues = count($venues_data);
$active_venues = 0;

foreach ($venues_data as $venue) {
    $total_bookings += $venue['booking_count'];
    $total_revenue += $venue['total_revenue'];
    if ($venue['is_active'] == 1) {
        $active_venues++;
    }
}

// Get city distribution
$city_query = "SELECT 
                v.city,
                COUNT(b.booking_id) as booking_count,
                SUM(CASE WHEN b.status IN ('confirmed', 'completed') THEN b.total_amount ELSE 0 END) as revenue
               FROM venues v
               LEFT JOIN bookings b ON v.venue_id = b.venue_id $period_clause
               WHERE 1=1 $filter_conditions
               GROUP BY v.city
               ORDER BY booking_count DESC";

$city_result = mysqli_query($conn, $city_query);
$city_data = mysqli_fetch_all($city_result, MYSQLI_ASSOC);

include 'admin_header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h2 class="h3 mb-0 text-gray-800">Venue Analytics</h2>
        <div>
            <button type="button" class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#filterModal">
                <i class="fas fa-filter me-1"></i> Filter Data
            </button>
            <a href="#" class="btn btn-primary">
                <i class="fas fa-download me-1"></i> Export Report
            </a>
        </div>
    </div>

    <!-- Filter Modal -->
    <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="filterModalLabel">Filter Analytics Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="GET">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="period" class="form-label">Time Period</label>
                            <select class="form-select" id="period" name="period">
                                <option value="week" <?= $filter_period == 'week' ? 'selected' : '' ?>>Last Week</option>
                                <option value="month" <?= $filter_period == 'month' ? 'selected' : '' ?>>Last Month</option>
                                <option value="quarter" <?= $filter_period == 'quarter' ? 'selected' : '' ?>>Last Quarter</option>
                                <option value="year" <?= $filter_period == 'year' ? 'selected' : '' ?>>Last Year</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="city" class="form-label">City</label>
                            <select class="form-select" id="city" name="city">
                                <option value="">All Cities</option>
                                <?php foreach ($cities as $city): ?>
                                    <option value="<?= htmlspecialchars($city['city']) ?>" <?= $filter_city == $city['city'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($city['city']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="sort" class="form-label">Sort By</label>
                            <select class="form-select" id="sort" name="sort">
                                <option value="bookings" <?= $filter_sort == 'bookings' ? 'selected' : '' ?>>Most Bookings</option>
                                <option value="revenue" <?= $filter_sort == 'revenue' ? 'selected' : '' ?>>Highest Revenue</option>
                                <option value="rating" <?= $filter_sort == 'rating' ? 'selected' : '' ?>>Best Rating</option>
                                <option value="name" <?= $filter_sort == 'name' ? 'selected' : '' ?>>Venue Name</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Filter Display -->
    <?php if (!empty($filter_city)  || $filter_period != 'month'): ?>
    <div class="alert alert-info d-flex align-items-center mb-4">
        <i class="fas fa-info-circle me-2"></i>
        <div>
            Showing data for: 
            <strong>
                <?php
                    $filters = [];
                    if (!empty($filter_city)) $filters[] = "City: " . htmlspecialchars($filter_city);
                    
                    $period_text = "";
                    switch ($filter_period) {
                        case 'week': $period_text = "Last Week"; break;
                        case 'month': $period_text = "Last Month"; break;
                        case 'quarter': $period_text = "Last Quarter"; break;
                        case 'year': $period_text = "Last Year"; break;
                    }
                    $filters[] = "Period: " . $period_text;
                    
                    echo implode(" | ", $filters);
                ?>
            </strong>
            <a href="venue_analytics.php" class="ms-3 text-decoration-none">
                <i class="fas fa-times-circle"></i> Clear Filters
            </a>
        </div>
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
                                <i class="fas fa-calendar-check"></i>
                            </div>
                        </div>
                        <div class="col">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Bookings</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800"><?= number_format($total_bookings) ?></div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-primary bg-opacity-10 py-2">
                    <div class="text-xs text-primary d-flex align-items-center">
                        <i class="fas fa-clock fa-sm me-1"></i> 
                        <?= ucfirst($filter_period) ?> data
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
                                <i class="fas fa-rupee-sign"></i>
                            </div>
                        </div>
                        <div class="col">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Revenue</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">₹<?= number_format($total_revenue, 2) ?></div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-success bg-opacity-10 py-2">
                    <div class="text-xs text-success d-flex align-items-center">
                        <i class="fas fa-clock fa-sm me-1"></i>
                        <?= ucfirst($filter_period) ?> data
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
                                <i class="fas fa-map-marked-alt"></i>
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
                    <div class="text-xs text-info d-flex align-items-center">
                        <i class="fas fa-percentage fa-sm me-1"></i>
                        <?= $total_venues > 0 ? round(($active_venues / $total_venues) * 100) : 0 ?>% active rate
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100 border-start border-warning border-4 rounded-3 overflow-hidden transition-all hover-shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="icon-circle bg-warning text-white">
                                <i class="fas fa-rupee-sign"></i>
                            </div>
                        </div>
                        <div class="col">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Avg. Revenue Per Booking</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                ₹<?= $total_bookings > 0 ? number_format($total_revenue / $total_bookings, 2) : '0.00' ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-warning bg-opacity-10 py-2">
                    <div class="text-xs text-warning d-flex align-items-center">
                        <i class="fas fa-chart-line fa-sm me-1"></i>
                        Per transaction average
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Trend Chart -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow mb-4 border-0 rounded-3 overflow-hidden">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white border-0">
                    <h6 class="m-0 font-weight-bold text-primary">Booking & Revenue Trends</h6>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-primary active" id="showBookings">Bookings</button>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="showRevenue">Revenue</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Distribution Charts -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow mb-4 border-0 rounded-3 overflow-hidden">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white border-0">
                    <h6 class="m-0 font-weight-bold text-primary">Category Distribution</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="categoryDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end shadow animated--fade-in" aria-labelledby="categoryDropdown">
                            <a class="dropdown-item" href="#" id="showCategoryBookings">Show Bookings</a>
                            <a class="dropdown-item" href="#" id="showCategoryRevenue">Show Revenue</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-pie mb-4">
                        <canvas id="categoryChart"></canvas>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>

    <!-- City Distribution and Top Venues Row -->
    <div class="row mb-4">
        <!-- City Distribution -->
        <div class="col-lg-5 mb-4">
            <div class="card shadow mb-4 border-0 rounded-3 overflow-hidden">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white border-0">
                    <h6 class="m-0 font-weight-bold text-primary">City Performance</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="cityDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end shadow animated--fade-in" aria-labelledby="cityDropdown">
                            <a class="dropdown-item" href="#" id="showCityBookings">Show Bookings</a>
                            <a class="dropdown-item" href="#" id="showCityRevenue">Show Revenue</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-bar">
                        <canvas id="cityChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Performing Venues -->
        <div class="col-lg-7 mb-4">
            <div class="card shadow mb-4 border-0 rounded-3 overflow-hidden">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white border-0">
                    <h6 class="m-0 font-weight-bold text-primary">Top Performing Venues</h6>
                    <a href="venues.php" class="btn btn-sm btn-primary rounded-pill">
                        <i class="fas fa-external-link-alt me-1"></i> View All Venues
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($top_venues)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-chart-bar fa-3x text-gray-300 mb-3"></i>
                            <p class="mb-0">No venue data available for the selected filters.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($top_venues as $index => $venue): ?>
                            <div class="venue-progress mb-4">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="fw-bold text-primary"><?= htmlspecialchars($venue['venue_name']) ?></span>
                                    <span class="text-gray-800">₹<?= number_format($venue['total_revenue'], 2) ?></span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <?php
                                        // Calculate percentage of max revenue (first venue)
                                        $max_revenue = $top_venues[0]['total_revenue'] > 0 ? $top_venues[0]['total_revenue'] : 1;
                                        $percentage = ($venue['total_revenue'] / $max_revenue) * 100;
                                        
                                        // Get color based on position
                                        $colors = ['bg-primary', 'bg-success', 'bg-info', 'bg-warning', 'bg-danger'];
                                        $color = $colors[$index] ?? 'bg-secondary';
                                    ?>
                                    <div class="progress-bar <?= $color ?>" role="progressbar" style="width: <?= $percentage ?>%" 
                                        aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="d-flex justify-content-between mt-1">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar-check me-1"></i> <?= $venue['booking_count'] ?> bookings
                                    </small>
                                    <small class="text-muted">
                                        <i class="fas fa-rupee-sign me-1"></i> 
                                        <?= $venue['booking_count'] > 0 ? number_format($venue['total_revenue'] / $venue['booking_count'], 2) : '0.00' ?> avg
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Venues Table -->
    <div class="card shadow mb-4 border-0 rounded-3 overflow-hidden">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white border-0">
            <h6 class="m-0 font-weight-bold text-primary">Venue Performance</h6>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-download me-1"></i> Export
                </button>
                <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                    <li><a class="dropdown-item" href="#">Export as CSV</a></li>
                    <li><a class="dropdown-item" href="#">Export as Excel</a></li>
                    <li><a class="dropdown-item" href="#">Export as PDF</a></li>
                </ul>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" width="100%" cellspacing="0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-3">Venue Name</th>
                            <th>Category</th>
                            <th>City</th>
                            <th>Bookings</th>
                            <th>Revenue</th>
                            <th>Avg. Rating</th>
                            <th>Status</th>
                            <th class="pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($venues_data)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">No venues found matching the selected criteria.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($venues_data as $venue): ?>
                                <tr>
                                    <td class="ps-3 fw-bold"><?= htmlspecialchars($venue['venue_name']) ?></td>
                                    <td>
                                        <span class="badge rounded-pill text-bg-light px-3 py-2">
                                            <?= htmlspecialchars($venue['category']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($venue['city']) ?></td>
                                    <td><?= number_format($venue['booking_count']) ?></td>
                                    <td class="fw-bold">₹<?= number_format($venue['total_revenue'], 2) ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="stars me-2">
                                                <?php
                                                $rating = round($venue['avg_rating'] * 2) / 2;
                                                $full_stars = floor($rating);
                                                $half_star = $rating - $full_stars >= 0.5;
                                                $empty_stars = 5 - $full_stars - ($half_star ? 1 : 0);
                                                
                                                for ($i = 0; $i < $full_stars; $i++) {
                                                    echo '<i class="fas fa-star text-warning"></i>';
                                                }
                                                if ($half_star) {
                                                    echo '<i class="fas fa-star-half-alt text-warning"></i>';
                                                }
                                                for ($i = 0; $i < $empty_stars; $i++) {
                                                    echo '<i class="far fa-star text-warning"></i>';
                                                }
                                                ?>
                                            </div>
                                            <span class="text-muted"><?= number_format($venue['avg_rating'], 1) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill <?= $venue['is_active'] ? 'bg-success' : 'bg-danger' ?>">
                                            <?= $venue['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td class="pe-3">
                                        <div class="btn-group">
                                            <a href="venue_details.php?id=<?= $venue['venue_id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-chart-line"></i>
                                            </a>
                                            <a href="edit_venue.php?id=<?= $venue['venue_id'] ?>" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-3">
            <nav aria-label="Venue pagination">
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item disabled">
                        <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                    </li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Set new default font family and font color to mimic Bootstrap's default styling
    Chart.defaults.font.family = '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
    Chart.defaults.color = '#858796';

    // Utility function to format currency
    function formatCurrency(value) {
        return '₹' + value.toLocaleString('en-IN');
    }

    // Chart color sets
    const primaryColors = {
        primary: '#4e73df',
        success: '#1cc88a',
        info: '#36b9cc',
        warning: '#f6c23e',
        danger: '#e74a3b',
        secondary: '#858796',
        dark: '#5a5c69',
        light: '#f8f9fc'
    };

    // Generate chart background colors
    function getChartColors(count, alpha = 0.5) {
        const colors = [
            `rgba(78, 115, 223, ${alpha})`,
            `rgba(28, 200, 138, ${alpha})`,
            `rgba(54, 185, 204, ${alpha})`,
            `rgba(246, 194, 62, ${alpha})`,
            `rgba(231, 74, 59, ${alpha})`,
            `rgba(133, 135, 150, ${alpha})`,
            `rgba(90, 92, 105, ${alpha})`,
            `rgba(183, 28, 28, ${alpha})`
        ];
        
        // Repeat colors if needed
        let result = [];
        for (let i = 0; i < count; i++) {
            result.push(colors[i % colors.length]);
        }
        
        return result;
    }

    // Generate chart border colors (solid version of background colors)
    function getChartBorderColors(count) {
        return getChartColors(count, 1);
    }

    // Trend Chart
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    
    // Prepare data
    const trendLabels = <?= json_encode($periods) ?>;
    const bookingsData = <?= json_encode($bookings) ?>;
    const revenueData = <?= json_encode($revenues) ?>;
    
    let trendActiveDataset = 'bookings';
    
    const trendChart = new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: trendLabels,
            datasets: [{
                label: 'Bookings',
                data: bookingsData,
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                borderColor: primaryColors.primary,
                pointRadius: 3,
                pointBackgroundColor: primaryColors.primary,
                pointBorderColor: primaryColors.primary,
                pointHoverRadius: 5,
                pointHoverBackgroundColor: primaryColors.primary,
                pointHoverBorderColor: 'rgba(78, 115, 223, 1)',
                pointHitRadius: 10,
                pointBorderWidth: 2,
                tension: 0.3,
                fill: true,
                hidden: false
            },
            {
                label: 'Revenue',
                data: revenueData,
                backgroundColor: 'rgba(28, 200, 138, 0.1)',
                borderColor: primaryColors.success,
                pointRadius: 3,
                pointBackgroundColor: primaryColors.success,
                pointBorderColor: primaryColors.success,
                pointHoverRadius: 5,
                pointHoverBackgroundColor: primaryColors.success,
                pointHoverBorderColor: 'rgba(28, 200, 138, 1)',
                pointHitRadius: 10,
                pointBorderWidth: 2,
                tension: 0.3,
                fill: true,
                hidden: true
            }]
        },
        options: {
            maintainAspectRatio: false,
            layout: {
                padding: {
                    left: 10,
                    right: 25,
                    top: 25,
                    bottom: 0
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    ticks: {
                        callback: function(value) {
                            return trendActiveDataset === 'revenue' ? formatCurrency(value) : value;
                        }
                    },
                    grid: {
                        color: "rgba(0, 0, 0, 0.05)",
                    }
                }
            },
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
                    padding: 15,
                    displayColors: false,
                    intersect: false,
                    mode: 'index',
                    caretPadding: 10,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.dataset.label === 'Revenue') {
                                label += formatCurrency(context.raw);
                            } else {
                                label += context.raw;
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
    
    // Toggle between bookings and revenue charts
    document.getElementById('showBookings').addEventListener('click', function() {
        trendActiveDataset = 'bookings';
        trendChart.data.datasets[0].hidden = false;
        trendChart.data.datasets[1].hidden = true;
        trendChart.update();
        
        this.classList.add('active');
        document.getElementById('showRevenue').classList.remove('active');
    });
    
    document.getElementById('showRevenue').addEventListener('click', function() {
        trendActiveDataset = 'revenue';
        trendChart.data.datasets[0].hidden = true;
        trendChart.data.datasets[1].hidden = false;
        trendChart.update();
        
        this.classList.add('active');
        document.getElementById('showBookings').classList.remove('active');
    });

    // Category Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    
    // Prepare category data
    const categoryLabels = <?= json_encode(array_column($category_data, 'category')) ?>;
    const categoryBookings = <?= json_encode(array_column($category_data, 'booking_count')) ?>;
    const categoryRevenue = <?= json_encode(array_column($category_data, 'revenue')) ?>;
    
    let categoryActiveDataset = 'bookings';
    
    const categoryChart = new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: categoryLabels,
            datasets: [{
                data: categoryBookings,
                backgroundColor: getChartColors(categoryLabels.length),
                borderColor: getChartBorderColors(categoryLabels.length),
                borderWidth: 1,
                hoverOffset: 5
            }]
        },
        options: {
            maintainAspectRatio: false,
            cutout: '70%',
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
                    padding: 15,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            
                            if (categoryActiveDataset === 'revenue') {
                                label += formatCurrency(categoryRevenue[context.dataIndex]);
                            } else {
                                label += categoryBookings[context.dataIndex] + ' bookings';
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
    
    // Toggle between category bookings and revenue
    document.getElementById('showCategoryBookings').addEventListener('click', function() {
        categoryActiveDataset = 'bookings';
        categoryChart.data.datasets[0].data = categoryBookings;
        categoryChart.update();
    });
    
    document.getElementById('showCategoryRevenue').addEventListener('click', function() {
        categoryActiveDataset = 'revenue';
        categoryChart.data.datasets[0].data = categoryRevenue;
        categoryChart.update();
    });

    // City Chart
    const cityCtx = document.getElementById('cityChart').getContext('2d');
    
    // Prepare city data
    const cityLabels = <?= json_encode(array_column($city_data, 'city')) ?>;
    const cityBookings = <?= json_encode(array_column($city_data, 'booking_count')) ?>;
    const cityRevenue = <?= json_encode(array_column($city_data, 'revenue')) ?>;
    
    let cityActiveDataset = 'bookings';
    
    const cityChart = new Chart(cityCtx, {
        type: 'bar',
        data: {
            labels: cityLabels,
            datasets: [{
                label: 'Bookings',
                data: cityBookings,
                backgroundColor: getChartColors(cityLabels.length),
                borderColor: getChartBorderColors(cityLabels.length),
                borderWidth: 1
            }]
        },
        options: {
            maintainAspectRatio: false,
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: "rgba(0, 0, 0, 0.05)",
                    },
                    ticks: {
                        callback: function(value) {
                            return cityActiveDataset === 'revenue' ? formatCurrency(value) : value;
                        }
                    }
                }
            },
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
                    padding: 15,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            
                            if (cityActiveDataset === 'revenue') {
                                label += formatCurrency(context.raw);
                            } else {
                                label += context.raw + ' bookings';
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
    
    // Toggle between city bookings and revenue
    document.getElementById('showCityBookings').addEventListener('click', function() {
        cityActiveDataset = 'bookings';
        cityChart.data.datasets[0].label = 'Bookings';
        cityChart.data.datasets[0].data = cityBookings;
        cityChart.update();
    });
    
    document.getElementById('showCityRevenue').addEventListener('click', function() {
        cityActiveDataset = 'revenue';
        cityChart.data.datasets[0].label = 'Revenue';
        cityChart.data.datasets[0].data = cityRevenue;
        cityChart.update();
    });
</script>

<style>
/* Analytics specific styles */
.chart-area {
    position: relative;
    height: 350px;
    margin: 0 -10px;
}

.chart-pie {
    position: relative;
    height: 300px;
    margin: 0 -10px;
}

.chart-bar {
    position: relative;
    height: 300px;
    margin: 0 -10px;
}

.venue-progress .progress {
    height: 10px;
    border-radius: 10px;
}

.venue-icon {
    height: 40px;
    width: 40px;
    border-radius: 8px;
}

.status-icon {
    width: 12px;
    height: 12px;
    display: inline-block;
}

.icon-circle {
    height: 3rem;
    width: 3rem;
    border-radius: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.border-start {
    border-left-width: 4px !important;
}

.hover-shadow:hover {
    transform: translateY(-5px);
    box-shadow: 0 1rem 3rem rgba(0,0,0,.175)!important;
}

.transition-all {
    transition: all 0.3s ease;
}

.stars .fa-star, .stars .fa-star-half-alt {
    font-size: 0.8rem;
}

@media (max-width: 992px) {
    .chart-area, .chart-pie, .chart-bar {
        height: 250px;
    }
    
    .icon-circle {
        height: 2.5rem;
        width: 2.5rem;
    }
}

@media (max-width: 768px) {
    .chart-area, .chart-pie, .chart-bar {
        height: 200px;
    }
}
</style>

<?php include 'admin_footer.php'; ?>