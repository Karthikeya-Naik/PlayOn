<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $venue_id = $_POST['venue_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];
    $user_id = $_SESSION['user_id'];
    
    // Check if user has already reviewed this venue
    $check_query = "SELECT * FROM reviews WHERE user_id = ? AND venue_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("ii", $user_id, $venue_id);
    $check_stmt->execute();
    $existing_review = $check_stmt->get_result();
    
    if ($existing_review->num_rows > 0) {
        // Update existing review
        $update_query = "UPDATE reviews SET rating = ?, comment = ?, created_at = NOW() WHERE user_id = ? AND venue_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("isii", $rating, $comment, $user_id, $venue_id);
        if ($update_stmt->execute()) {
            $_SESSION['success_message'] = "Your review has been updated!";
        } else {
            $_SESSION['error_message'] = "Failed to update review. Please try again.";
        }
    } else {
        // Add new review
        $insert_query = "INSERT INTO reviews (user_id, venue_id, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("iiis", $user_id, $venue_id, $rating, $comment);
        if ($insert_stmt->execute()) {
            $_SESSION['success_message'] = "Thanks for your review!";
        } else {
            $_SESSION['error_message'] = "Failed to add review. Please try again.";
        }
    }
    
    // Calculate and update average rating for venue
    $avg_query = "SELECT AVG(rating) AS avg_rating FROM reviews WHERE venue_id = ?";
    $avg_stmt = $conn->prepare($avg_query);
    $avg_stmt->bind_param("i", $venue_id);
    $avg_stmt->execute();
    $avg_result = $avg_stmt->get_result();
    $avg_row = $avg_result->fetch_assoc();
    
    $update_venue = "UPDATE venues SET rating = ? WHERE venue_id = ?";
    $update_venue_stmt = $conn->prepare($update_venue);
    $update_venue_stmt->bind_param("di", $avg_row['avg_rating'], $venue_id);
    $update_venue_stmt->execute();
    
    header("Location: venue_details.php?id=$venue_id");
    exit();
} else {
    header("Location: venues.php");
    exit();
}
?>