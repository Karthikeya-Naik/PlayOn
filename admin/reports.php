<?php
session_start();
require_once '../db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Set default filter values
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : 'booking';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$venue_filter = isset($_GET['venue_id']) ? $_GET['venue_id'] : 'all';
$export_format = isset($_GET['export']) ? $_GET['export'] : '';

// Get all venues for dropdown
$venues_query = "SELECT venue_id, venue_name FROM venues ORDER BY venue_name ASC";
$venues_result = mysqli_query($conn, $venues_query);
$venues = mysqli_fetch_all($venues_result, MYSQLI_ASSOC);

// Initialize report data
$report_data = [];
$chart_data = [];
$report_title = '';
$chart_title = '';
$chart_type = 'bar';

// Process report based on type
switch ($report_type) {
    case 'booking':
        $report_title = "Booking Report";
        
        // Build query conditions
        $conditions = ["b.booking_date BETWEEN '$start_date' AND '$end_date'"];
        if ($status_filter != 'all') {
            $conditions[] = "b.status = '$status_filter'";
        }
        if ($venue_filter != 'all') {
            $conditions[] = "b.venue_id = '$venue_filter'";
        }
        $condition_str = implode(' AND ', $conditions);
        
        // Get booking data
        $query = "SELECT b.booking_id, b.user_id, b.venue_id, b.booking_date, b.start_time, 
                 b.end_time, b.total_amount, b.status, u.name as user_name, v.venue_name 
                 FROM bookings b
                 JOIN users u ON b.user_id = u.user_id
                 JOIN venues v ON b.venue_id = v.venue_id
                 WHERE $condition_str
                 ORDER BY b.booking_date DESC";
        $result = mysqli_query($conn, $query);
        $report_data = mysqli_fetch_all($result, MYSQLI_ASSOC);
        
        // Get data for chart - bookings by status
        $chart_query = "SELECT status, COUNT(*) as count 
                        FROM bookings 
                        WHERE booking_date BETWEEN '$start_date' AND '$end_date'
                        GROUP BY status";
        $chart_result = mysqli_query($conn, $chart_query);
        $chart_data_raw = mysqli_fetch_all($chart_result, MYSQLI_ASSOC);
        
        $labels = [];
        $data = [];
        $colors = [
            'pending' => 'rgba(255, 193, 7, 0.8)',
            'confirmed' => 'rgba(40, 167, 69, 0.8)',
            'completed' => 'rgba(23, 162, 184, 0.8)',
            'cancelled' => 'rgba(220, 53, 69, 0.8)'
        ];
        $chart_bg_colors = [];
        
        foreach ($chart_data_raw as $item) {
            $labels[] = ucfirst($item['status']);
            $data[] = $item['count'];
            $chart_bg_colors[] = $colors[$item['status']] ?? 'rgba(108, 117, 125, 0.8)';
        }
        
        $chart_data = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Number of Bookings',
                    'data' => $data,
                    'backgroundColor' => $chart_bg_colors
                ]
            ]
        ];
        $chart_title = "Bookings by Status";
        break;
        
    case 'revenue':
        $report_title = "Revenue Report";
        
        // Build query conditions
        $conditions = ["booking_date BETWEEN '$start_date' AND '$end_date'"];
        if ($status_filter != 'all') {
            $conditions[] = "status = '$status_filter'";
        }
        if ($venue_filter != 'all') {
            $conditions[] = "venue_id = '$venue_filter'";
        }
        $condition_str = implode(' AND ', $conditions);
        
        // Get daily revenue data
        $query = "SELECT booking_date, SUM(total_amount) as daily_revenue, COUNT(*) as booking_count 
                 FROM bookings 
                 WHERE $condition_str 
                 GROUP BY booking_date 
                 ORDER BY booking_date ASC";
        $result = mysqli_query($conn, $query);
        $report_data = mysqli_fetch_all($result, MYSQLI_ASSOC);
        
        // Calculate totals
        $total_revenue = 0;
        $total_bookings = 0;
        foreach ($report_data as $row) {
            $total_revenue += $row['daily_revenue'];
            $total_bookings += $row['booking_count'];
        }
        
        // Get chart data - revenue by date
        $labels = [];
        $data = [];
        
        foreach ($report_data as $item) {
            $labels[] = date('d M', strtotime($item['booking_date']));
            $data[] = $item['daily_revenue'];
        }
        
        $chart_data = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Daily Revenue',
                    'data' => $data,
                    'borderColor' => 'rgba(78, 115, 223, 1)',
                    'backgroundColor' => 'rgba(78, 115, 223, 0.1)',
                    'pointBackgroundColor' => 'rgba(78, 115, 223, 1)',
                    'pointBorderColor' => 'rgba(78, 115, 223, 1)',
                    'pointHoverRadius' => 3,
                    'pointHoverBackgroundColor' => 'rgba(78, 115, 223, 1)',
                    'pointHoverBorderColor' => 'rgba(78, 115, 223, 1)',
                    'lineTension' => 0.3,
                    'fill' => true
                ]
            ]
        ];
        $chart_title = "Daily Revenue";
        $chart_type = 'line';
        break;
        
    case 'venue':
        $report_title = "Venue Performance Report";
        
        // Build query conditions
        $conditions = ["b.booking_date BETWEEN '$start_date' AND '$end_date'"];
        if ($status_filter != 'all') {
            $conditions[] = "b.status = '$status_filter'";
        }
        if ($venue_filter != 'all') {
            $conditions[] = "v.venue_id = '$venue_filter'";
        }
        $condition_str = implode(' AND ', $conditions);
        
        // Get venue performance data
        $query = "SELECT v.venue_id, v.venue_name, v.city, 
                 COUNT(b.booking_id) as booking_count,
                 SUM(b.total_amount) as total_revenue,
                 AVG(b.total_amount) as avg_booking_value
                 FROM venues v
                 LEFT JOIN bookings b ON v.venue_id = b.venue_id AND $condition_str
                 GROUP BY v.venue_id
                 ORDER BY total_revenue DESC";
        $result = mysqli_query($conn, $query);
        $report_data = mysqli_fetch_all($result, MYSQLI_ASSOC);
        
        // Get chart data - top venues by revenue
        $top_venues = array_slice($report_data, 0, 10); // Get top 10 venues
        
        $labels = [];
        $data = [];
        
        foreach ($top_venues as $venue) {
            if ($venue['total_revenue'] > 0) { // Only include venues with revenue
                $labels[] = $venue['venue_name'];
                $data[] = $venue['total_revenue'];
            }
        }
        
        // Create a colorful gradient for the chart
        $chart_bg_colors = [];
        for ($i = 0; $i < count($labels); $i++) {
            $opacity = 0.8;
            $hue = 200 + ($i * 15) % 180; // Cycle through blue to purple hues
            $chart_bg_colors[] = "hsla($hue, 70%, 60%, $opacity)";
        }
        
        $chart_data = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Revenue by Venue',
                    'data' => $data,
                    'backgroundColor' => $chart_bg_colors
                ]
            ]
        ];
        $chart_title = "Top Venues by Revenue";
        break;
        
    case 'user':
        $report_title = "User Activity Report";
        
        // Build query conditions
        $conditions = ["b.booking_date BETWEEN '$start_date' AND '$end_date'"];
        if ($status_filter != 'all') {
            $conditions[] = "b.status = '$status_filter'";
        }
        if ($venue_filter != 'all') {
            $conditions[] = "b.venue_id = '$venue_filter'";
        }
        $condition_str = implode(' AND ', $conditions);
        
        // Get user activity data
        $query = "SELECT u.user_id, u.name, u.email, u.phone,
                 COUNT(b.booking_id) as booking_count,
                 SUM(b.total_amount) as total_spent,
                 MAX(b.booking_date) as last_booking_date
                 FROM users u
                 LEFT JOIN bookings b ON u.user_id = b.user_id AND $condition_str
                 GROUP BY u.user_id
                 ORDER BY booking_count DESC, total_spent DESC";
        $result = mysqli_query($conn, $query);
        $report_data = mysqli_fetch_all($result, MYSQLI_ASSOC);
        
        // Get chart data - user booking frequency
        $booking_counts = [
            '0' => 0,
            '1' => 0,
            '2-5' => 0,
            '6-10' => 0,
            '10+' => 0
        ];
        
        foreach ($report_data as $user) {
            $count = $user['booking_count'];
            if ($count == 0) $booking_counts['0']++;
            elseif ($count == 1) $booking_counts['1']++;
            elseif ($count >= 2 && $count <= 5) $booking_counts['2-5']++;
            elseif ($count >= 6 && $count <= 10) $booking_counts['6-10']++;
            else $booking_counts['10+']++;
        }
        
        $labels = array_keys($booking_counts);
        $data = array_values($booking_counts);
        
        $chart_data = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Number of Users',
                    'data' => $data,
                    'backgroundColor' => [
                        'rgba(220, 53, 69, 0.8)',
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(23, 162, 184, 0.8)',
                        'rgba(78, 115, 223, 0.8)'
                    ]
                ]
            ]
        ];
        $chart_title = "User Booking Frequency";
        break;
}

// Handle export functionality
if (!empty($export_format)) {
    // Set headers based on export format
    switch ($export_format) {
        case 'csv':
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $report_type . '_report_' . date('Y-m-d') . '.csv"');
            
            // Open output stream
            $output = fopen('php://output', 'w');
            
            // Add headers based on report type
            switch ($report_type) {
                case 'booking':
                    fputcsv($output, ['Booking ID', 'User', 'Venue', 'Date', 'Time', 'Amount', 'Status']);
                    foreach ($report_data as $row) {
                        fputcsv($output, [
                            $row['booking_id'],
                            $row['user_name'],
                            $row['venue_name'],
                            $row['booking_date'],
                            $row['start_time'],
                            $row['total_amount'],
                            $row['status']
                        ]);
                    }
                    break;
                    
                case 'revenue':
                    fputcsv($output, ['Date', 'Revenue', 'Bookings']);
                    foreach ($report_data as $row) {
                        fputcsv($output, [
                            $row['booking_date'],
                            $row['daily_revenue'],
                            $row['booking_count']
                        ]);
                    }
                    break;
                    
                case 'venue':
                    fputcsv($output, ['Venue ID', 'Venue Name', 'City', 'Bookings', 'Total Revenue', 'Avg Booking Value']);
                    foreach ($report_data as $row) {
                        fputcsv($output, [
                            $row['venue_id'],
                            $row['venue_name'],
                            $row['city'],
                            $row['booking_count'],
                            $row['total_revenue'],
                            $row['avg_booking_value']
                        ]);
                    }
                    break;
                    
                case 'user':
                    fputcsv($output, ['User ID', 'Name', 'Email', 'Phone', 'Bookings', 'Total Spent', 'Last Booking']);
                    foreach ($report_data as $row) {
                        fputcsv($output, [
                            $row['user_id'],
                            $row['name'],
                            $row['email'],
                            $row['phone'],
                            $row['booking_count'],
                            $row['total_spent'],
                            $row['last_booking_date']
                        ]);
                    }
                    break;
            }
            
            fclose($output);
            exit;
            break;
            
        case 'pdf':
            // Here you would implement PDF generation
            // This typically requires a library like FPDF or TCPDF
            // For now, we'll just show a message
            echo "PDF export functionality requires additional libraries.";
            exit;
            break;
    }
}

include 'admin_header.php';
?>

<div class="container-fluid py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h2 class="h3 mb-0 text-gray-800">Reports</h2>
        <div>
            <button class="btn btn-sm btn-outline-primary me-2" type="button" data-bs-toggle="modal"
                data-bs-target="#exportModal">
                <i class="fas fa-download fa-sm me-1"></i> Export Report
            </button>
            <a href="#" class="btn btn-sm btn-primary" onclick="window.print();">
                <i class="fas fa-print fa-sm me-1"></i> Print Report
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow mb-4 border-0 rounded-3">
        <div class="card-header py-3 bg-white border-0">
            <h6 class="m-0 font-weight-bold text-primary">Report Filters</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="reports.php" class="row g-3">
                <div class="col-md-3">
                    <label for="report_type" class="form-label">Report Type</label>
                    <select class="form-select" id="report_type" name="report_type" onchange="this.form.submit()">
                        <option value="booking" <?= $report_type == 'booking' ? 'selected' : '' ?>>Booking Report
                        </option>
                        <option value="revenue" <?= $report_type == 'revenue' ? 'selected' : '' ?>>Revenue Report
                        </option>
                        <option value="venue" <?= $report_type == 'venue' ? 'selected' : '' ?>>Venue Performance
                        </option>
                        <option value="user" <?= $report_type == 'user' ? 'selected' : '' ?>>User Activity</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date"
                        value="<?= $start_date ?>">
                </div>
                <div class="col-md-2">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?= $end_date ?>">
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="all" <?= $status_filter == 'all' ? 'selected' : '' ?>>All Statuses</option>
                        <option value="confirmed" <?= $status_filter == 'confirmed' ? 'selected' : '' ?>>Confirmed
                        </option>
                        <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="completed" <?= $status_filter == 'completed' ? 'selected' : '' ?>>Completed
                        </option>
                        <option value="cancelled" <?= $status_filter == 'cancelled' ? 'selected' : '' ?>>Cancelled
                        </option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="venue_id" class="form-label">Venue</label>
                    <select class="form-select" id="venue_id" name="venue_id">
                        <option value="all" <?= $venue_filter == 'all' ? 'selected' : '' ?>>All Venues</option>
                        <?php foreach ($venues as $venue): ?>
                        <option value="<?= $venue['venue_id'] ?>"
                            <?= $venue_filter == $venue['venue_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($venue['venue_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-12 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i> Apply Filters
                    </button>
                    <a href="reports.php" class="btn btn-outline-secondary ms-2">
                        <i class="fas fa-redo me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Report Results Row -->
    <div class="row mb-4">
        <!-- Chart -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4 border-0 rounded-3">
                <div
                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white border-0">
                    <h6 class="m-0 font-weight-bold text-primary"><?= $chart_title ?></h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end shadow animated--fade-in"
                            aria-labelledby="dropdownMenuLink">
                            <div class="dropdown-header">Chart Options:</div>
                            <a class="dropdown-item" href="#" id="downloadChart">Download as Image</a>
                            <a class="dropdown-item" href="#">Change Chart Type</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="reportChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Card -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4 border-0 rounded-3">
                <div
                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white border-0">
                    <h6 class="m-0 font-weight-bold text-primary">Report Summary</h6>
                    <span class="badge bg-primary rounded-pill px-3 py-2">
                        <?= date('d M, Y', strtotime($start_date)) ?> - <?= date('d M, Y', strtotime($end_date)) ?>
                    </span>
                </div>
                <div class="card-body">
                    <?php if ($report_type == 'booking'): ?>
                    <div class="report-summary">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="p-3 border rounded-3 bg-light">
                                    <div class="fs-2 fw-bold text-primary"><?= count($report_data) ?></div>
                                    <div class="text-muted">Total Bookings</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-3 border rounded-3 bg-light">
                                    <div class="fs-2 fw-bold text-success">
                                        ₹<?= number_format(array_sum(array_column($report_data, 'total_amount')), 2) ?>
                                    </div>
                                    <div class="text-muted">Total Revenue</div>
                                </div>
                            </div>
                            <?php
                                $status_counts = [];
                                foreach ($report_data as $booking) {
                                    $status = $booking['status'];
                                    $status_counts[$status] = isset($status_counts[$status]) ? $status_counts[$status] + 1 : 1;
                                }
                                
                                $status_classes = [
                                    'pending' => 'warning',
                                    'confirmed' => 'success',
                                    'completed' => 'info',
                                    'cancelled' => 'danger'
                                ];
                                
                                foreach ($status_counts as $status => $count):
                                    $percent = round(($count / count($report_data)) * 100);
                                    $class = $status_classes[$status] ?? 'secondary';
                                ?>
                            <div class="col-sm-6">
                                <div class="p-3 border rounded-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="text-<?= $class ?>"><?= ucfirst($status) ?></span>
                                        <span class="badge bg-<?= $class ?> rounded-pill"><?= $count ?></span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-<?= $class ?>" role="progressbar"
                                            style="width: <?= $percent ?>%" aria-valuenow="<?= $percent ?>"
                                            aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php elseif ($report_type == 'revenue'): ?>
                    <div class="report-summary">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="p-3 border rounded-3 bg-light">
                                    <div class="fs-2 fw-bold text-primary">₹<?= number_format($total_revenue, 2) ?>
                                    </div>
                                    <div class="text-muted">Total Revenue</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-3 border rounded-3 bg-light">
                                    <div class="fs-2 fw-bold text-success"><?= $total_bookings ?></div>
                                    <div class="text-muted">Total Bookings</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-3 border rounded-3">
                                    <div class="fs-3 fw-bold text-info">
                                        ₹<?= number_format($total_revenue / ($total_bookings ?: 1), 2) ?></div>
                                    <div class="text-muted">Average Per Booking</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-3 border rounded-3">
                                    <div class="fs-3 fw-bold text-warning">
                                        ₹<?= number_format($total_revenue / (count($report_data) ?: 1), 2) ?></div>
                                    <div class="text-muted">Daily Average</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <h6 class="text-primary">Best Performance</h6>
                            <?php
                                $best_day = null;
                                $best_revenue = 0;
                                foreach ($report_data as $day) {
                                    if ($day['daily_revenue'] > $best_revenue) {
                                        $best_revenue = $day['daily_revenue'];
                                        $best_day = $day;
                                    }
                                }
                                
                                if ($best_day):
                                ?>
                            <div class="p-3 border rounded-3 bg-success bg-opacity-10">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-muted">Best Day</div>
                                        <div class="fs-5">
                                            <?= date('d M, Y (l)', strtotime($best_day['booking_date'])) ?></div>
                                    </div>
                                    <div class="text-end">
                                        <div class="text-muted">Revenue</div>
                                        <div class="fs-5 text-success">
                                            ₹<?= number_format($best_day['daily_revenue'], 2) ?></div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php elseif ($report_type == 'venue'): ?>
                    <div class="report-summary">
                        <?php
                            $venue_count = count($report_data);
                            $active_venues = 0;
                            $total_venue_revenue = 0;
                            $total_venue_bookings = 0;
                            
                            foreach ($report_data as $venue) {
                                $total_venue_revenue += $venue['total_revenue'];
                                $total_venue_bookings += $venue['booking_count'];
                                if ($venue['booking_count'] > 0) {
                                    $active_venues++;
                                }
                            }
                            ?>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="p-3 border rounded-3 bg-light">
                                    <div class="fs-2 fw-bold text-primary"><?= $venue_count ?></div>
                                    <div class="text-muted">Total Venues</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-3 border rounded-3 bg-light">
                                    <div class="fs-2 fw-bold text-success"><?= $active_venues ?></div>
                                    <div class="text-muted">Active Venues</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-3 border rounded-3">
                                    <div class="fs-3 fw-bold text-info">₹<?= number_format($total_venue_revenue, 2) ?>
                                    </div>
                                    <div class="text-muted">Total Revenue</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-3 border rounded-3">
                                    <div class="fs-3 fw-bold text-warning"><?= $total_venue_bookings ?></div>
                                    <div class="text-muted">Total Bookings</div>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($report_data) && $report_data[0]['total_revenue'] > 0): ?>
                        <div class="mt-4">
                            <h6 class="text-primary">Top Performing Venue</h6>
                            <div class="p-3 border rounded-3 bg-primary bg-opacity-10">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="venue-name mb-1 fs-5 fw-bold"><?= $report_data[0]['venue_name'] ?>
                                        </div>
                                        <div class="venue-location text-muted">
                                            <i class="fas fa-map-marker-alt me-1"></i> <?= $report_data[0]['city'] ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <div class="fs-4 text-primary">
                                            ₹<?= number_format($report_data[0]['total_revenue'], 2) ?></div>
                                        <div class="text-success"><?= $report_data[0]['booking_count'] ?> bookings</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php elseif ($report_type == 'user'): ?>
                    <div class="report-summary">
                        <?php
                            $total_users = count($report_data);
                            $active_users = 0;
                            $total_user_spent = 0;
                            $total_user_bookings = 0;
                            
                            foreach ($report_data as $user) {
                                $total_user_spent += $user['total_spent'];
                                $total_user_bookings += $user['booking_count'];
                                if ($user['booking_count'] > 0) {
                                    $active_users++;
                                }
                            }
                            ?>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="p-3 border rounded-3 bg-light">
                                    <div class="fs-2 fw-bold text-primary"><?= $total_users ?></div>
                                    <div class="text-muted">Total Users</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-3 border rounded-3 bg-light">
                                    <div class="fs-2 fw-bold text-success"><?= $active_users ?></div>
                                    <div class="text-muted">Active Users</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-3 border rounded-3">
                                    <div class="fs-3 fw-bold text-info">₹<?= number_format($total_user_spent, 2) ?>
                                    </div>
                                    <div class="text-muted">Total Spent</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-3 border rounded-3">
                                    <div class="fs-3 fw-bold text-warning">
                                        ₹<?= number_format($total_user_spent / ($active_users ?: 1), 2) ?></div>
                                    <div class="text-muted">Avg. Per User</div>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($report_data) && $report_data[0]['booking_count'] > 0): ?>
                        <div class="mt-4">
                            <h6 class="text-primary">Top Active User</h6>
                            <div class="p-3 border rounded-3 bg-primary bg-opacity-10">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="user-name mb-1 fs-5 fw-bold"><?= $report_data[0]['name'] ?></div>
                                        <div class="user-email text-muted">
                                            <i class="fas fa-envelope me-1"></i> <?= $report_data[0]['email'] ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <div class="fs-4 text-primary"><?= $report_data[0]['booking_count'] ?> bookings
                                        </div>
                                        <div class="text-success">
                                            ₹<?= number_format($report_data[0]['total_spent'], 2) ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table Section -->
    <div class="card shadow mb-4 border-0 rounded-3">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white border-0">
            <h6 class="m-0 font-weight-bold text-primary">
                <?= $report_title ?> - <?= count($report_data) ?> <?= count($report_data) == 1 ? 'record' : 'records' ?>
                found
            </h6>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="toggleAllData" checked>
                <label class="form-check-label" for="toggleAllData">Show All Data</label>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <?php if ($report_type == 'booking'): ?>
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Venue</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($report_data)): ?>
                        <tr>
                            <td colspan="7" class="text-center">No booking data available for the selected criteria.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($report_data as $booking): ?>
                        <tr>
                            <td>
                                <a href="booking_details.php?id=<?= $booking['booking_id'] ?>"
                                    class="fw-bold text-primary">
                                    #<?= $booking['booking_id'] ?>
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
                            <td><?= date('d-m-Y', strtotime($booking['booking_date'])) ?></td>
                            <td><?= date('h:i A', strtotime($booking['start_time'])) ?></td>
                            <td class="fw-bold">₹<?= number_format($booking['total_amount'], 2) ?></td>
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
                                <span class="badge <?= $status_badge ?> rounded-pill px-3 py-2">
                                    <?= ucfirst($booking['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php elseif ($report_type == 'revenue'): ?>
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Day</th>
                            <th>Bookings</th>
                            <th>Revenue</th>
                            <th>Avg. Booking Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($report_data)): ?>
                        <tr>
                            <td colspan="5" class="text-center">No revenue data available for the selected criteria.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($report_data as $day): ?>
                        <tr>
                            <td class="fw-bold"><?= date('d-m-Y', strtotime($day['booking_date'])) ?></td>
                            <td><?= date('l', strtotime($day['booking_date'])) ?></td>
                            <td><?= $day['booking_count'] ?></td>
                            <td class="fw-bold text-success">₹<?= number_format($day['daily_revenue'], 2) ?></td>
                            <td>₹<?= number_format($day['daily_revenue'] / $day['booking_count'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php elseif ($report_type == 'venue'): ?>
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Venue</th>
                            <th>City</th>
                            <th>Bookings</th>
                            <th>Revenue</th>
                            <th>Avg. Booking Value</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($report_data)): ?>
                        <tr>
                            <td colspan="6" class="text-center">No venue data available for the selected criteria.</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($report_data as $venue): ?>
                        <tr>
                            <td class="fw-bold"><?= htmlspecialchars($venue['venue_name']) ?></td>
                            <td><?= htmlspecialchars($venue['city']) ?></td>
                            <td><?= $venue['booking_count'] ?></td>
                            <td class="fw-bold text-success">₹<?= number_format($venue['total_revenue'], 2) ?></td>
                            <td>
                                <?php if ($venue['booking_count'] > 0): ?>
                                ₹<?= number_format($venue['avg_booking_value'], 2) ?>
                                <?php else: ?>
                                N/A
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="venue_details.php?id=<?= $venue['venue_id'] ?>"
                                    class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="reports.php?report_type=booking&venue_id=<?= $venue['venue_id'] ?>"
                                    class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-calendar-check"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php elseif ($report_type == 'user'): ?>
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Bookings</th>
                            <th>Total Spent</th>
                            <th>Last Booking</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($report_data)): ?>
                        <tr>
                            <td colspan="7" class="text-center">No user data available for the selected criteria.</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($report_data as $user): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle bg-primary me-2 text-white">
                                        <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                    </div>
                                    <?= htmlspecialchars($user['name']) ?>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['phone']) ?></td>
                            <td><?= $user['booking_count'] ?></td>
                            <td class="fw-bold text-success">₹<?= number_format($user['total_spent'], 2) ?></td>
                            <td>
                                <?php if ($user['last_booking_date']): ?>
                                <?= date('d-m-Y', strtotime($user['last_booking_date'])) ?>
                                <?php else: ?>
                                N/A
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="user_details.php?id=<?= $user['user_id'] ?>"
                                    class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="reports.php?report_type=booking&user_id=<?= $user['user_id'] ?>"
                                    class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-calendar-check"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportModalLabel">Export Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Choose export format for "<?= $report_title ?>":</p>
                <div class="d-grid gap-2">
                    <a href="reports.php?<?= http_build_query($_GET) ?>&export=csv" class="btn btn-outline-primary">
                        <i class="fas fa-file-csv me-2"></i> Export as CSV
                    </a>
                    <a href="reports.php?<?= http_build_query($_GET) ?>&export=pdf" class="btn btn-outline-danger">
                        <i class="fas fa-file-pdf me-2"></i> Export as PDF
                    </a>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Set new default font family and font color to mimic Bootstrap's default styling
Chart.defaults.font.family =
    '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
Chart.defaults.color = '#858796';

// Report Chart
const ctx = document.getElementById('reportChart').getContext('2d');
const chartData = <?= json_encode($chart_data) ?>;
const chartType = '<?= $chart_type ?>';

const reportChart = new Chart(ctx, {
    type: chartType,
    data: chartData,
    options: {
        maintainAspectRatio: false,
        responsive: true,
        plugins: {
            legend: {
                display: chartType === 'line' ? true : false,
                position: 'top',
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
                        if (context.parsed.y !== null) {
                            return chartType === 'line' ?
                                '₹' + context.parsed.y.toLocaleString() :
                                context.parsed.y.toLocaleString();
                        }
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: "rgba(0, 0, 0, 0.05)",
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

// Download chart as image
document.getElementById('downloadChart').addEventListener('click', function() {
    const link = document.createElement('a');
    link.download = '<?= $report_type ?>_chart.png';
    link.href = document.getElementById('reportChart').toDataURL('image/png');
    link.click();
});

// Initialize DataTables
$(document).ready(function() {
    $('#dataTable').DataTable({
        pageLength: 10,
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ]
    });

    // Toggle between all data and summarized data
    $('#toggleAllData').change(function() {
        if ($(this).is(':checked')) {
            $('.table-responsive').show();
        } else {
            $('.table-responsive').hide();
        }
    });
});
</script>

<style>
/* Report page specific styles */
.chart-container {
    position: relative;
    height: 350px;
    margin: 0 -10px;
}

.status-icon {
    width: 12px;
    height: 12px;
    display: inline-block;
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

/* Print styles */
@media print {

    .sidebar,
    .navbar,
    .btn,
    .form-check,
    .dropdown,
    .no-print {
        display: none !important;
    }

    .container-fluid {
        width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    .card {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }

    .chart-container {
        height: 300px !important;
    }

    @page {
        size: portrait;
        margin: 2cm;
    }
}

@media (max-width: 992px) {
    .chart-container {
        height: 300px;
    }
}

@media (max-width: 768px) {
    .chart-container {
        height: 250px;
    }
}
</style>

<?php include 'admin_footer.php'; ?>