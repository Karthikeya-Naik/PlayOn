<?php
session_start();
include 'db_connect.php';

// Default query
$query = "SELECT * FROM venues WHERE is_active = 1";
$params = array();

// Search functionality
if(isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $query .= " AND (venue_name LIKE ? OR address LIKE ? OR city LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Price filter
if(isset($_GET['min_price']) && !empty($_GET['min_price'])) {
    $min_price = (int)$_GET['min_price'];
    $query .= " AND price_per_hour >= ?";
    $params[] = $min_price;
}

if(isset($_GET['max_price']) && !empty($_GET['max_price'])) {
    $max_price = (int)$_GET['max_price'];
    $query .= " AND price_per_hour <= ?";
    $params[] = $max_price;
}

// City filter
if(isset($_GET['city']) && !empty($_GET['city'])) {
    $city = mysqli_real_escape_string($conn, $_GET['city']);
    $query .= " AND city = ?";
    $params[] = $city;
}

// Sort options
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'price_asc';
switch($sort) {
    case 'price_desc':
        $query .= " ORDER BY price_per_hour DESC";
        break;
    case 'rating':
        $query .= " ORDER BY (SELECT AVG(rating) FROM reviews WHERE venues.venue_id = reviews.venue_id) DESC";
        break;
    case 'name':
        $query .= " ORDER BY venue_name ASC";
        break;
    case 'price_asc':
    default:
        $query .= " ORDER BY price_per_hour ASC";
        break;
}

// Prepare and execute statement
$stmt = mysqli_prepare($conn, $query);

if (!empty($params)) {
    $types = str_repeat('s', count($params));
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Get all cities for the filter dropdown
$city_query = "SELECT DISTINCT city FROM venues ORDER BY city";
$city_result = mysqli_query($conn, $city_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Box Cricket Venues - PlayOn</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- AOS Animation Library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <link href="css/style.css" rel="stylesheet">
    <style>
    :root {
        --primary: #0b4d8c;
        --primary-light: #e6f0fa;
        --primary-dark: #083968;
        --dark: #212529;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        scroll-behavior: smooth;
    }

    /* Page header section */
    .page-header {
        background: linear-gradient(rgba(11, 77, 140, 0.85), rgba(8, 57, 104, 0.95)),
            url('/api/placeholder/1920/300') no-repeat center center/cover;
        padding: 80px 0;
        position: relative;
        overflow: hidden;
    }

    .page-header h1 {
        font-weight: 800;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        animation: fadeInDown 1s ease;
    }

    .page-header p {
        animation: fadeInUp 1.2s ease;
    }

    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Filter Card */
    .filter-card {
        border-radius: 15px;
        border: none;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        margin-bottom: 40px;
        transition: all 0.4s ease;
        transform: translateY(-30px);
        background-color: white;
        position: relative;
        z-index: 10;
    }

    .filter-card:hover {
        box-shadow: 0 15px 35px rgba(11, 77, 140, 0.1);
    }

    .filter-card .card-body {
        padding: 30px;
    }

    .form-control,
    .form-select {
        padding: 0.7rem 1rem;
        border-radius: 8px;
        border: 1px solid #e0e0e0;
        transition: all 0.3s ease;
    }

    .form-control:focus,
    .form-select:focus {
        box-shadow: 0 0 0 0.25rem rgba(11, 77, 140, 0.25);
        border-color: var(--primary-light);
    }

    /* Filter buttons */
    .filter-btn {
        border-radius: 8px;
        padding: 0.7rem 1.8rem;
        font-weight: 600;
        transition: all 0.4s ease;
    }

    .filter-btn:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(11, 77, 140, 0.2);
    }

    .btn-primary {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    .btn-primary:hover,
    .btn-primary:focus {
        background-color: var(--primary-dark);
        border-color: var(--primary-dark);
    }

    .btn-outline-primary {
        border-color: var(--primary);
        color: var(--primary);
    }

    .btn-outline-primary:hover {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    /* Venue Cards */
    .venue-card {
        border: none;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.4s ease;
        height: 100%;
    }

    .venue-card:hover {
        transform: translateY(-12px);
        box-shadow: 0 15px 30px rgba(11, 77, 140, 0.15);
    }

    .venue-img {
        height: 220px;
        object-fit: cover;
        transition: all 0.6s ease;
    }

    .venue-card:hover .venue-img {
        transform: scale(1.08);
    }

    .venue-card .card-body {
        padding: 1.8rem;
    }

    .venue-card .card-title {
        font-weight: 700;
        margin-bottom: 10px;
        color: var(--dark);
    }

    .venue-location {
        font-size: 0.9rem;
    }

    .venue-location i {
        color: var(--primary);
    }

    .venue-price {
        font-weight: 700;
        color: var(--primary);
        font-size: 1.1rem;
    }

    .venue-timing {
        font-size: 0.85rem;
    }

    /* Ratings */
    .ratings .fa-star {
        font-size: 0.9rem;
    }

    /* No results */
    .no-results {
        padding: 60px 0;
        text-align: center;
    }

    .no-results i {
        color: var(--primary-light);
    }

    .no-results h4 {
        font-weight: 700;
        margin-bottom: 1rem;
        color: var(--dark);
    }

    .no-results p {
        font-size: 1.1rem;
        margin-bottom: 1.5rem;
        color: #6c757d;
    }

    /* CTA Section */
    .cta-section {
        background: linear-gradient(rgba(11, 77, 140, 0.95), rgba(8, 57, 104, 0.9)),
            url('/api/placeholder/1920/600') no-repeat center center/cover;
        position: relative;
        overflow: hidden;
    }

    .cta-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: url('/api/placeholder/800/600') no-repeat center center/cover;
        opacity: 0.1;
    }

    .cta-section h2 {
        font-weight: 800;
        animation: fadeInUp 1s ease;
    }

    .cta-section p {
        animation: fadeInUp 1.2s ease;
    }

    .cta-section .btn {
        animation: fadeInUp 1.4s ease;
    }

    /* Back to top button */
    .back-to-top {
        position: fixed;
        bottom: 25px;
        right: 25px;
        display: none;
        width: 50px;
        height: 50px;
        text-align: center;
        line-height: 50px;
        background: var(--primary);
        color: white;
        border-radius: 50%;
        z-index: 9999;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
    }

    .back-to-top:hover {
        background: var(--primary-dark);
        color: white;
        transform: translateY(-5px);
    }

    /* Button styles */
    .btn {
        border-radius: 8px;
        padding: 0.6rem 1.5rem;
        font-weight: 600;
        transition: all 0.4s ease;
    }

    .btn-lg {
        padding: 0.8rem 2rem;
        border-radius: 50px;
    }

    .btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    /* Section heading styles */
    .section-heading {
        position: relative;
        padding-bottom: 15px;
        margin-bottom: 30px;
    }

    .section-heading:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 60px;
        height: 3px;
        background-color: var(--primary);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .page-header {
            padding: 60px 0;
        }

        .filter-card {
            transform: translateY(-20px);
        }

        .filter-card .card-body {
            padding: 20px;
        }
    }
    </style>
</head>

<body>
    <!-- Navigation Bar -->
    <?php include 'navbar.php'; ?>

    <!-- Page Header -->
    <header class="page-header text-white text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3">Box Cricket Venues</h1>
            <p class="lead fs-5 mb-0">Find and book the perfect cricket venue for your next game</p>
        </div>
    </header>

    <!-- Search and Filter Section -->
    <section class="py-0">
        <div class="container">
            <div class="card filter-card" data-aos="fade-up">
                <div class="card-body">
                    <form action="venues.php" method="get" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label fw-bold">Search</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="search" placeholder="Search venues..."
                                    name="search"
                                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i>
                                    Search</button>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label for="city" class="form-label fw-bold">City</label>
                            <select class="form-select" id="city" name="city">
                                <option value="">All Cities</option>
                                <?php while($city_row = mysqli_fetch_assoc($city_result)): ?>
                                <option value="<?php echo $city_row['city']; ?>"
                                    <?php echo (isset($_GET['city']) && $_GET['city'] == $city_row['city']) ? 'selected' : ''; ?>>
                                    <?php echo $city_row['city']; ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="min_price" class="form-label fw-bold">Min Price</label>
                            <input type="number" class="form-control" id="min_price" placeholder="₹500" name="min_price"
                                value="<?php echo isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : ''; ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="max_price" class="form-label fw-bold">Max Price</label>
                            <input type="number" class="form-control" id="max_price" placeholder="₹5000"
                                name="max_price"
                                value="<?php echo isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : ''; ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="sort" class="form-label fw-bold">Sort By</label>
                            <select class="form-select" id="sort" name="sort">
                                <option value="price_asc"
                                    <?php echo (!isset($_GET['sort']) || $_GET['sort'] == 'price_asc') ? 'selected' : ''; ?>>
                                    Price: Low to High</option>
                                <option value="price_desc"
                                    <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_desc') ? 'selected' : ''; ?>>
                                    Price: High to Low</option>
                                <option value="rating"
                                    <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'rating') ? 'selected' : ''; ?>>
                                    Top Rated</option>
                                <option value="name"
                                    <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'name') ? 'selected' : ''; ?>>
                                    Name</option>
                            </select>
                        </div>
                        <div class="col-12 mt-4 d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary filter-btn"><i class="fas fa-filter me-2"></i>Apply
                                Filters</button>
                            <a href="venues.php" class="btn btn-outline-secondary filter-btn"><i
                                    class="fas fa-redo me-2"></i>Reset</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Venues Listing -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5 fw-bold section-heading" data-aos="fade-up">Available Venues</h2>
            
            <?php if(mysqli_num_rows($result) > 0): ?>
            <div class="row">
                <?php 
                $delay = 100;
                while($row = mysqli_fetch_assoc($result)): 
                ?>
                <div class="col-md-6 col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                    <div class="card venue-card h-100">
                        <img src="uploads/venues/<?php echo $row['image']; ?>" class="card-img-top venue-img"
                            alt="<?php echo $row['venue_name']; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $row['venue_name']; ?></h5>
                            <p class="card-text text-muted venue-location mb-2"><i
                                    class="fas fa-map-marker-alt me-2"></i><?php echo $row['address']; ?>,
                                <?php echo $row['city']; ?>, <?php echo $row['state']; ?></p>

                            <?php
                                    // Get average rating
                                    $venue_id = $row['venue_id'];
                                    $rating_query = "SELECT AVG(rating) as avg_rating, COUNT(*) as count FROM reviews WHERE venue_id = $venue_id";
                                    $rating_result = mysqli_query($conn, $rating_query);
                                    $rating_data = mysqli_fetch_assoc($rating_result);
                                    $avg_rating = round($rating_data['avg_rating'], 1);
                                    $review_count = $rating_data['count'];
                                    ?>

                            <div class="ratings mb-2">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                <i
                                    class="fas fa-star <?php echo ($i <= $avg_rating) ? 'text-warning' : 'text-muted'; ?>"></i>
                                <?php endfor; ?>
                                <small class="ms-1 text-muted">(<?php echo $review_count; ?> reviews)</small>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <p class="venue-timing mb-0"><i
                                        class="far fa-clock me-2"></i><?php echo date('h:i A', strtotime($row['opening_time'])); ?>
                                    - <?php echo date('h:i A', strtotime($row['closing_time'])); ?></p>
                                <p class="venue-price mb-0">₹<?php echo $row['price_per_hour']; ?>/hr</p>
                            </div>

                            <div class="d-flex mt-3">
                                <a href="venue_details.php?id=<?php echo $row['venue_id']; ?>"
                                    class="btn btn-primary w-100">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php 
                    $delay += 100;
                endwhile; 
                ?>
            </div>
            <?php else: ?>
            <div class="no-results" data-aos="fade-up">
                <div class="row justify-content-center">
                    <div class="col-md-6 text-center">
                        <i class="fas fa-search fa-4x text-muted mb-4"></i>
                        <h4>No venues found matching your criteria</h4>
                        <p>Try adjusting your search or filters to find available cricket venues</p>
                        <a href="venues.php" class="btn btn-primary btn-lg">Reset Filters</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-5 text-white text-center cta-section">
        <div class="container position-relative" data-aos="zoom-in">
            <h2 class="mb-4 fw-bold">Can't find what you're looking for?</h2>
            <p class="lead mb-4">Contact us and we'll help you find the perfect venue for your cricket match!</p>
            <a href="contact.php" class="btn btn-light btn-lg px-5">Contact Us</a>
        </div>
    </section>

    <!-- Back to top button -->
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center">
        <i class="fas fa-arrow-up"></i>
    </a>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>

    <script>
    // Initialize AOS animations
    document.addEventListener('DOMContentLoaded', function() {
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });

        // Back to top button
        const backToTopButton = document.querySelector('.back-to-top');

        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                backToTopButton.style.display = 'flex';
            } else {
                backToTopButton.style.display = 'none';
            }
        });

        backToTopButton.addEventListener('click', (e) => {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    });
    </script>
</body>

</html>