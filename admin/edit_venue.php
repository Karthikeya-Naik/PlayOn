<?php
session_start();
require_once '../db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Check if venue ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid venue ID.";
    header("Location: venues.php");
    exit();
}

$venue_id = $_GET['id'];

// Fetch venue details
$query = "SELECT * FROM venues WHERE venue_id = $venue_id";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = "Venue not found.";
    header("Location: venues.php");
    exit();
}

$venue = mysqli_fetch_assoc($result);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price_per_hour = floatval($_POST['price_per_hour']);
    $start_time = mysqli_real_escape_string($conn, $_POST['start_time']);
    $end_time = mysqli_real_escape_string($conn, $_POST['end_time']);
    $facilities = mysqli_real_escape_string($conn, $_POST['facilities']);
    $status = isset($_POST['status']) ? 1 : 0;
    
    $image = $venue['image']; // Keep existing image by default
    
    // Handle image upload if new image is selected
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (in_array(strtolower($ext), $allowed)) {
            $new_filename = uniqid() . '.' . $ext;
            $destination = '../uploads/venues/' . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                // Delete old image if exists
                if (!empty($venue['image']) && file_exists('../uploads/venues/' . $venue['image'])) {
                    unlink('../uploads/venues/' . $venue['image']);
                }
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
    
    // Update venue in database
    $query = "UPDATE venues SET 
              venue_name = '$name', 
              city = '$location', 
              address = '$address', 
              city = '$city', 
              description = '$description', 
              price_per_hour = $price_per_hour, 
              opening_time = '$start_time', 
              closing_time = '$end_time', 
              facilities = '$facilities', 
              image = '$image', 
              latitude = $latitude, 
              longitude = $longitude, 
              is_active = $status, 
              created_at = NOW() 
              WHERE venue_id = $venue_id";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Venue updated successfully!";
        header("Location: venues.php");
        exit();
    } else {
        $_SESSION['error'] = "Error updating venue: " . mysqli_error($conn);
    }
}

include 'admin_header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Edit Venue</h2>
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
            <form action="edit_venue.php?id=<?= $venue_id ?>" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Venue Name*</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= $venue['venue_name'] ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="location" class="form-label">Location/Area*</label>
                        <input type="text" class="form-control" id="location" name="location" value="<?= $venue['city'] ?>" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label for="address" class="form-label">Full Address*</label>
                        <textarea class="form-control" id="address" name="address" rows="2" required><?= $venue['address'] ?></textarea>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="city" class="form-label">City*</label>
                        <input type="text" class="form-control" id="city" name="city" value="<?= $venue['city'] ?>" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description*</label>
                    <textarea class="form-control" id="description" name="description" rows="4" required><?= $venue['description'] ?></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="price_per_hour" class="form-label">Price Per Hour (₹)*</label>
                        <input type="number" class="form-control" id="price_per_hour" name="price_per_hour" min="0" step="50" value="<?= $venue['price_per_hour'] ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="start_time" class="form-label">Opening Time*</label>
                        <input type="time" class="form-control" id="start_time" name="start_time" value="<?= isset($venue['start_time']) ? $venue['start_time'] : '09:00' ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="end_time" class="form-label">Closing Time*</label>
                        <input type="time" class="form-control" id="end_time" name="end_time" value="<?= isset($venue['end_time']) ? $venue['end_time'] : '21:00' ?>" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="latitude" class="form-label">Latitude</label>
                        <input type="text" class="form-control" id="latitude" name="latitude" value="<?= $venue['latitude'] ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="longitude" class="form-label">Longitude</label>
                        <input type="text" class="form-control" id="longitude" name="longitude" value="<?= $venue['longitude'] ?>">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="facilities" class="form-label">Facilities</label>
                    <textarea class="form-control" id="facilities" name="facilities" rows="3" placeholder="Enter comma-separated facilities (e.g., Parking, Washroom, Drinking Water)"><?= $venue['facilities'] ?></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="image" class="form-label">Venue Image</label>
                    <?php if (!empty($venue['image'])): ?>
                        <div class="mb-2">
                            <img src="../uploads/venues/<?= $venue['image'] ?>" alt="<?= $venue['venue_name'] ?>" class="img-thumbnail" style="max-width: 200px;">
                            <p class="small text-muted">Current image</p>
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control" id="image" name="image">
                    <small class="text-muted">Leave empty to keep current image. Recommended size: 800x600 pixels</small>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="status" name="status" <?= $venue['is_active'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="status">Active</label>
                </div>
                
                <button type="submit" class="btn btn-primary">Update Venue</button>
            </form>
        </div>
    </div>
</div>

<?php include 'admin_footer.php'; ?>