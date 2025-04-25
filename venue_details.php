<?php
session_start();
include 'db_connect.php';

// Check if venue ID is provided
if(!isset($_GET['id'])) {
    header("Location: venues.php");
    exit();
}

$venue_id = mysqli_real_escape_string($conn, $_GET['id']);

// Get venue details
$query = "SELECT * FROM venues WHERE venue_id = '$venue_id'";
$result = mysqli_query($conn, $query);

if(mysqli_num_rows($result) == 0) {
    header("Location: venues.php");
    exit();
}

$venue = mysqli_fetch_assoc($result);

// Get reviews for this venue
$reviews_query = "SELECT r.*, u.name as user_name FROM reviews r 
                 JOIN users u ON r.user_id = u.user_id 
                 WHERE r.venue_id = '$venue_id' 
                 ORDER BY r.created_at DESC";
$reviews_result = mysqli_query($conn, $reviews_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $venue['venue_name']; ?> - PlayOn</title>
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

    /* Breadcrumb */
    .breadcrumb-section {
        background-color: var(--primary-light);
        padding: 15px 0;
        border-bottom: 1px solid rgba(11, 77, 140, 0.1);
        font-weight: 500;
    }

    .breadcrumb-item a {
        color: var(--primary);
        text-decoration: none !important;
        transition: all 0.3s ease;
    }

    .breadcrumb-item a:hover {
        color: var(--primary-dark);
    }

    .breadcrumb-item.active {
        color: var(--dark);
        font-weight: 600;
    }

    /* Venue header */
    .venue-header {
        background: linear-gradient(rgba(11, 77, 140, 0.85), rgba(8, 57, 104, 0.95)),
            url('uploads/venues/<?php echo $venue['image']; ?>') no-repeat center center/cover;
        padding: 80px 0;
        color: white;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }

    .venue-header::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 5px;
        background: linear-gradient(to right, var(--primary-light), var(--primary), var(--primary-dark));
    }

    .venue-header h1 {
        font-weight: 800;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        animation: fadeInUp 1s ease;
    }

    .venue-header .lead {
        animation: fadeInUp 1.2s ease;
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

    /* Venue details */
    .venue-details {
        margin-bottom: 40px;
    }

    .venue-details h3 {
        font-weight: 700;
        margin: 30px 0 20px;
        color: var(--primary-dark);
        position: relative;
        padding-bottom: 10px;
    }

    .venue-details h3:after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 50px;
        height: 3px;
        background-color: var(--primary);
        transition: width 0.4s ease;
    }

    .venue-details h3:hover:after {
        width: 100px;
    }

    /* Gallery */
    .venue-gallery {
        margin: 30px 0;
    }

    .main-image {
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        height: 400px;
        object-fit: cover;
        transition: all 0.5s ease;
    }

    .main-image:hover {
        transform: scale(1.01);
    }

    .thumbnail {
        height: 100px;
        object-fit: cover;
        border-radius: 8px;
        transition: all 0.3s ease;
        cursor: pointer;
        border: 3px solid transparent;
    }

    .thumbnail:hover {
        transform: scale(1.05);
        border-color: var(--primary);
        box-shadow: 0 5px 15px rgba(11, 77, 140, 0.3);
    }

    /* Amenities */
    .amenities {
        background-color: var(--primary-light);
        border-radius: 12px;
        padding: 25px;
        margin: 30px 0;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    }

    .amenity {
        padding: 12px;
        transition: all 0.3s ease;
        border-radius: 8px;
        display: flex;
        align-items: center;
    }

    .amenity i {
        color: var(--primary);
        margin-right: 10px;
        font-size: 1.2rem;
        transition: all 0.3s ease;
    }

    .amenity:hover {
        background-color: white;
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    }

    .amenity:hover i {
        transform: scale(1.2);
    }

    /* Venue timings */
    .venue-timings {
        background-color: var(--primary-light);
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    }

    .venue-timings p {
        font-size: 1.1rem;
        margin-bottom: 0;
        display: flex;
        align-items: center;
    }

    .venue-timings i {
        color: var(--primary);
        margin-right: 10px;
        font-size: 1.3rem;
    }

    /* Book now button */
    .book-now {
        margin: 30px 0;
    }

    .btn-primary {
        background-color: var(--primary);
        border: none;
        padding: 12px 30px;
        font-weight: 600;
        border-radius: 50px;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background-color: var(--primary-dark);
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(11, 77, 140, 0.3);
    }

    /* Reviews section */
    .reviews {
        padding-top: 10px;
    }

    .reviews h3 {
        margin-bottom: 25px;
    }

    .review-card {
        background-color: #fff;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        transition: all 0.4s ease;
        border: 1px solid rgba(0, 0, 0, 0.08) !important;
    }

    .review-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(11, 77, 140, 0.1);
        border-color: var(--primary-light) !important;
    }

    .stars .fa-star {
        color: #ffc107;
    }

    /* Add review form */
    .add-review {
        background-color: var(--primary-light);
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        transform: translateY(0);
        transition: transform 0.4s ease, box-shadow 0.4s ease;
    }

    .add-review:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    }

    .add-review h4 {
        font-weight: 700;
        margin-bottom: 20px;
        color: var(--primary-dark);
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
        border-color: var(--primary);
    }

    /* Sidebar */
    .venue-sidebar {
        background-color: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        position: sticky;
        top: 20px;
        transition: transform 0.4s ease, box-shadow 0.4s ease;
    }

    .venue-sidebar:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
    }

    .venue-sidebar h3 {
        font-weight: 700;
        margin-bottom: 20px;
        color: var(--primary-dark);
        position: relative;
        padding-bottom: 10px;
    }

    .venue-sidebar h3:after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 40px;
        height: 3px;
        background-color: var(--primary);
        transition: width 0.3s ease;
    }

    .venue-sidebar h3:hover:after {
        width: 80px;
    }

    .venue-sidebar p {
        margin-bottom: 15px;
        font-size: 1.05rem;
        display: flex;
        align-items: center;
    }

    .venue-sidebar p i {
        color: var(--primary);
        width: 25px;
        margin-right: 10px;
        transition: transform 0.3s ease;
    }

    .venue-sidebar p:hover i {
        transform: scale(1.2);
    }

    .venue-price {
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--primary);
    }

    /* Map container */
    .map-container {
        position: relative;
        width: 100%;
        height: 300px;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 20px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        transition: all 0.4s ease;
    }

    .map-container:hover {
        box-shadow: 0 15px 30px rgba(11, 77, 140, 0.2);
        transform: scale(1.02);
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

    /* Ratings in header */
    .ratings {
        display: inline-flex;
        align-items: center;
        background-color: rgba(255, 255, 255, 0.1);
        padding: 8px 15px;
        border-radius: 50px;
        font-weight: 500;
        backdrop-filter: blur(5px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        transition: all 0.3s ease;
    }

    .ratings:hover {
        background-color: rgba(255, 255, 255, 0.2);
        transform: translateY(-3px);
    }

    /* No reviews state */
    .no-reviews {
        padding: 50px 0;
        text-align: center;
        background-color: var(--primary-light);
        border-radius: 12px;
        margin: 30px 0;
        transition: all 0.3s ease;
    }

    .no-reviews:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .no-reviews i {
        color: var(--primary);
        margin-bottom: 15px;
    }

    /* User avatar in reviews */
    .user-avatar {
        width: 40px;
        height: 40px;
        background-color: var(--primary);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .review-card:hover .user-avatar {
        transform: scale(1.1) rotate(5deg);
    }
    </style>
</head>

<body>
    <!-- Navigation Bar -->
    <?php include 'navbar.php'; ?>

    <!-- Breadcrumb -->
    <div class="breadcrumb-section">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="venues.php">Venues</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo $venue['venue_name']; ?></li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Hero Venue Header -->
    <header class="venue-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <h1 class="display-4"><?php echo $venue['venue_name']; ?></h1>
                    <p class="lead fs-5 mb-3"><i class="fas fa-map-marker-alt me-2"></i>
                        <?php echo $venue['address']; ?>, <?php echo $venue['city']; ?>, Telangana</p>

                    <!-- Dynamic rating display -->
                    <?php 
                    // Get average rating
                    $rating_query = "SELECT AVG(rating) as avg_rating, COUNT(*) as count FROM reviews WHERE venue_id = $venue_id";
                    $rating_result = mysqli_query($conn, $rating_query);
                    $rating_data = mysqli_fetch_assoc($rating_result);
                    $avg_rating = round($rating_data['avg_rating'], 1);
                    $review_count = $rating_data['count'];
                    ?>

                    <div class="ratings mt-3">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                        <i
                            class="fas fa-star <?php echo ($i <= $avg_rating) ? 'text-warning' : 'text-white-50'; ?>"></i>
                        <?php endfor; ?>
                        <span class="ms-2"><?php echo $avg_rating ? $avg_rating : '0'; ?> (<?php echo $review_count; ?>
                            reviews)</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <div class="venue-details">
                    <div class="venue-gallery my-4">
                        <div class="row">
                            <div class="col-md-12" data-aos="fade-up">
                                <img src="uploads/venues/<?php echo $venue['image']; ?>" class="img-fluid main-image"
                                    alt="<?php echo $venue['venue_name']; ?>">
                            </div>
                            <?php if(!empty($venue['gallery_images'])): 
                                  $gallery_images = explode(',', $venue['gallery_images']);
                                  $delay = 100;
                                  foreach($gallery_images as $image): ?>
                            <div class="col-md-3 col-6 mt-3" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                                <img src="uploads/venues/gallery/<?php echo $image; ?>" class="img-fluid thumbnail"
                                    alt="Gallery Image">
                            </div>
                            <?php 
                                  $delay += 100;
                                  endforeach; 
                                  endif; ?>
                        </div>
                    </div>

                    <h3 data-aos="fade-right">About this Venue</h3>
                    <p class="fs-5" data-aos="fade-up"><?php echo $venue['description']; ?></p>

                    <div class="amenities mb-4" data-aos="fade-up">
                        <h3>Amenities</h3>
                        <div class="row">
                            <?php
                            $amenities = explode(',', $venue['facilities']);
                            $delay = 100;
                            foreach($amenities as $amenity):
                            ?>
                            <div class="col-md-4 col-6 mb-2" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                                <div class="amenity">
                                    <i class="fas fa-check-circle text-primary"></i> <?php echo trim($amenity); ?>
                                </div>
                            </div>
                            <?php 
                            $delay += 100;
                            endforeach; ?>
                        </div>
                    </div>

                    <div class="venue-timings mb-4" data-aos="fade-up">
                        <h3>Opening Hours</h3>
                        <p><i class="far fa-clock"></i>
                            <?php echo date('h:i A', strtotime($venue['opening_time'])); ?> -
                            <?php echo date('h:i A', strtotime($venue['closing_time'])); ?></p>
                    </div>

                    <?php if(isset($_SESSION['user_id'])): ?>
                    <div class="book-now mb-5" data-aos="fade-up">
                        <a href="bookings.php?venue_id=<?php echo $venue_id; ?>" class="btn btn-primary btn-lg"><i
                                class="fas fa-calendar-check me-2"></i>Book Now</a>
                    </div>
                    <?php else: ?>
                    <div class="book-now mb-5" data-aos="fade-up">
                        <a href="login.php" class="btn btn-primary btn-lg"><i class="fas fa-sign-in-alt me-2"></i>Login
                            to Book</a>
                    </div>
                    <?php endif; ?>

                    <div class="reviews" data-aos="fade-up">
                        <h3>Reviews</h3>
                        <?php if(mysqli_num_rows($reviews_result) > 0): ?>
                        <div class="row">
                            <?php 
                            $delay = 100;
                            while($review = mysqli_fetch_assoc($reviews_result)): ?>
                            <div class="col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                                <div class="review-card p-4 h-100 border rounded">
                                    <div class="d-flex justify-content-between mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar">
                                                <?php echo substr($review['user_name'], 0, 1); ?>
                                            </div>
                                            <div>
                                                <h5 class="fw-bold mb-0"><?php echo $review['user_name']; ?></h5>
                                                <small class="text-muted"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="stars mb-3">
                                        <?php
                                        for($i = 1; $i <= 5; $i++) {
                                            if($i <= $review['rating']) {
                                                echo '<i class="fas fa-star"></i>';
                                            } else {
                                                echo '<i class="far fa-star"></i>';
                                            }
                                        }
                                        ?>
                                    </div>
                                    <p class="mb-0">"<?php echo $review['comment']; ?>"</p>
                                </div>
                            </div>
                            <?php 
                            $delay += 100;
                            endwhile; ?>
                        </div>
                        <?php else: ?>
                        <div class="no-reviews" data-aos="fade-up">
                            <i class="far fa-comment-dots fa-3x mb-3"></i>
                            <h5 class="fw-bold">No reviews yet</h5>
                            <p class="mb-0">Be the first to share your experience at this venue!</p>
                        </div>
                        <?php endif; ?>

                        <?php if(isset($_SESSION['user_id'])): ?>
                        <div class="add-review mt-5" data-aos="fade-up">
                            <h4>Write a Review</h4>
                            <form action="add_review.php" method="POST">
                                <input type="hidden" name="venue_id" value="<?php echo $venue_id; ?>">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Rating</label>
                                    <select class="form-select" name="rating" required>
                                        <option value="5">5 Stars - Excellent</option>
                                        <option value="4">4 Stars - Very Good</option>
                                        <option value="3">3 Stars - Good</option>
                                        <option value="2">2 Stars - Fair</option>
                                        <option value="1">1 Star - Poor</option>
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Your Review</label>
                                    <textarea class="form-control" name="comment" rows="3" placeholder="Share your experience with this venue..." required></textarea>
                                </div>
                                <button type="submit" name="submit_review" class="btn btn-primary"><i
                                        class="fas fa-paper-plane me-2"></i>Submit Review</button>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="venue-sidebar p-4" data-aos="fade-left">
                    <h3>Pricing</h3>
                    <p class="venue-price mb-4">₹<?php echo $venue['price_per_hour']; ?> <small class="text-muted">per
                            hour</small></p>

                    <h3>Contact</h3>
                    <p><i class="fas fa-phone"></i> +91 9121740296</p>
                    <p class="mb-4"><i class="fas fa-envelope"></i> boxcricket@playon.com</p>

                    <h3>Location</h3>
                    <div class="map-container">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d30452.76602782469!2d78.42754367424!3d17.43501249478!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3bcb972add11accd%3A0x2e8b3598468f3a5a!2sGachibowli%2C%20Hyderabad%2C%20Telangana!5e0!3m2!1sen!2sin!4v1714149150334!5m2!1sen!2sin"
                            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none;"
                            allowfullscreen="" loading="lazy">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Back to top button -->
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center">
        <i class="fas fa-arrow-up"></i>
    </a>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

        // Show or hide back-to-top button based on scroll position
        $(window).scroll(function() {
            if ($(this).scrollTop() > 300) {
                $('.back-to-top').fadeIn('slow');
            } else {
                $('.back-to-top').fadeOut('slow');
            }
        });

        // Smooth scroll to top when clicking back-to-top button
        $('.back-to-top').click(function(e) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: 0
            }, 800);
            return false;
        });

        // Gallery thumbnail click to show in main image
        $('.thumbnail').click(function() {
            var imgSrc = $(this).attr('src');
            $('.main-image').attr('src', imgSrc);
            // Add fade effect
            $('.main-image').css('opacity', '0.6');
            setTimeout(function() {
                $('.main-image').css('opacity', '1');
            }, 300);
        });
    });
    </script>
</body>

</html>