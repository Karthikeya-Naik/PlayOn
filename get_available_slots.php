<?php
// get_available_slots.php
session_start();
include 'db_connect.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

// Check if required parameters are provided
if(!isset($_GET['venue_id']) || !isset($_GET['date'])) {
    echo json_encode(['error' => 'Missing parameters']);
    exit();
}

$venue_id = mysqli_real_escape_string($conn, $_GET['venue_id']);
$date = mysqli_real_escape_string($conn, $_GET['date']);

// Get venue details (for operating hours)
$venue_query = "SELECT * FROM venues WHERE venue_id = '$venue_id' AND is_active = '1'";
$venue_result = mysqli_query($conn, $venue_query);

if(mysqli_num_rows($venue_result) == 0) {
    echo json_encode(['error' => 'Venue not found']);
    exit();
}

$venue = mysqli_fetch_assoc($venue_result);

// Use venue's operating hours or defaults
$opening_time = isset($venue['opening_time']) ? $venue['opening_time'] : '06:00:00';
$closing_time = isset($venue['closing_time']) ? $venue['closing_time'] : '22:00:00';

// Get day of week for potential day-specific restrictions
$day_of_week = date('l', strtotime($date));

// Generate all possible time slots
function generateTimeSlots($opening, $closing) {
    $slots = [];
    $current = strtotime($opening);
    $end = strtotime($closing);
    
    while($current < $end) {
        $time = date('H:i:s', $current);
        $slots[] = [
            'start_time' => $time,
            'display_time' => date('h:i A', $current)
        ];
        $current = strtotime('+1 hour', $current);
    }
    
    return $slots;
}

$available_slots = generateTimeSlots($opening_time, $closing_time);

// Get booked slots for this date
$booked_query = "SELECT start_time, end_time FROM bookings 
                WHERE venue_id = '$venue_id' 
                AND booking_date = '$date' 
                AND status != 'cancelled'";

$booked_result = mysqli_query($conn, $booked_query);
$booked_slots = [];

while($row = mysqli_fetch_assoc($booked_result)) {
    $booked_slots[] = [
        'start' => $row['start_time'],
        'end' => $row['end_time']
    ];
}

// Return JSON with available slots and booked slots info
echo json_encode([
    'available_slots' => $available_slots,
    'booked_slots' => $booked_slots,
    'date' => $date,
    'day_of_week' => $day_of_week
]);
?>