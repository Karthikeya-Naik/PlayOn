<?php
session_start();
require_once '../db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price_per_hour = floatval($_POST['price_per_hour']);
    $opening_time = mysqli_real_escape_string($conn, $_POST['opening_time']);
    $closing_time = mysqli_real_escape_string($conn, $_POST['closing_time']);
    $facilities = mysqli_real_escape_string($conn, $_POST['facilities']);
    $status = isset($_POST['status']) ? 1 : 0;
    
    // Handle image upload
    $image = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (in_array(strtolower($ext), $allowed)) {
            $new_filename = uniqid() . '.' . $ext;
            $destination = '../uploads/venues/' . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                $image = $new_filename;
            } else {
                $_SESSION['error'] = "Failed to upload image.";
            }
        } else {
            $_SESSION['error'] = "Invalid file type. Only JPG, JPEG, PNG and GIF allowed.";
        }
    }
    
    // Get latitude and longitude
    $latitude = !empty($_POST['latitude']) ? floatval($_POST['latitude']) : 0;
    $longitude = !empty($_POST['longitude']) ? floatval($_POST['longitude']) : 0;
    
    // Insert venue into database
    $query = "INSERT INTO venues (venue_name, description, address, city, price_per_hour, opening_time, closing_time, 
              facilities, image, latitude, longitude, is_active, created_at) 
              VALUES ('$name', '$description', '$address', '$city', $price_per_hour, '$opening_time', '$closing_time', 
              '$facilities', '$image', $latitude, $longitude, $status, NOW())";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Venue added successfully!";
        header("Location: venues.php");
        exit();
    } else {
        $_SESSION['error'] = "Error adding venue: " . mysqli_error($conn);
    }
}

include 'admin_header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Add New Venue</h2>
        <a href="venues.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Venues</a>
    </div>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <div class="card shadow">
        <div class="card-body">
            <form action="add_venue.php" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Venue Name*</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="location" class="form-label">Location/Area*</label>
                        <input type="text" class="form-control" id="location" name="location" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label for="address" class="form-label">Full Address*</label>
                        <textarea class="form-control" id="address" name="address" rows="2" required></textarea>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="city" class="form-label">City*</label>
                        <input type="text" class="form-control" id="city" name="city" required value="Hyderabad">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description*</label>
                    <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="price_per_hour" class="form-label">Price Per Hour (₹)*</label>
                        <input type="number" class="form-control" id="price_per_hour" name="price_per_hour" min="0" step="50" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="opening_time" class="form-label">Opening Time*</label>
                        <input type="time" class="form-control" id="opening_time" name="opening_time" value="09:00" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="closing_time" class="form-label">Closing Time*</label>
                        <input type="time" class="form-control" id="closing_time" name="closing_time" value="21:00" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="latitude" class="form-label">Latitude</label>
                        <input type="text" class="form-control" id="latitude" name="latitude">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="longitude" class="form-label">Longitude</label>
                        <input type="text" class="form-control" id="longitude" name="longitude">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="facilities" class="form-label">Facilities</label>
                    <textarea class="form-control" id="facilities" name="facilities" rows="3" placeholder="Enter comma-separated facilities (e.g., Parking, Washroom, Drinking Water)"></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="image" class="form-label">Venue Image</label>
                    <input type="file" class="form-control" id="image" name="image">
                    <small class="text-muted">Recommended size: 800x600 pixels</small>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="status" name="status" checked>
                    <label class="form-check-label" for="status">Active</label>
                </div>
                
                <button type="submit" class="btn btn-primary">Add Venue</button>
            </form>
        </div>
    </div>
</div>

<?php include 'admin_footer.php'; ?>