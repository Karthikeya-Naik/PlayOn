<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $venue_id = $_POST['venue_id'];
    $booking_date = $_POST['booking_date'];
    $start_time = $_POST['start_time'];
    $duration = $_POST['duration'];
    $total_price = $_POST['total_price'];
    $user_id = $_SESSION['user_id'];
    
    // Create cart entry if it doesn't exist
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }
    
    // Generate a unique cart item ID
    $cart_item_id = uniqid();
    
    // Add to cart session
    $_SESSION['cart'][$cart_item_id] = array(
        'venue_id' => $venue_id,
        'booking_date' => $booking_date,
        'start_time' => $start_time,
        'duration' => $duration,
        'total_price' => $total_price
    );
    
    // Get venue details for display
    $venue_query = "SELECT name FROM venues WHERE id = ?";
    $stmt = $conn->prepare($venue_query);
    $stmt->bind_param("i", $venue_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $venue = $result->fetch_assoc();
    
    $_SESSION['success_message'] = "Added {$venue['name']} booking to cart!";
    header("Location: cart.php");
    exit();
} else {
    header("Location: venues.php");
    exit();
}
?>