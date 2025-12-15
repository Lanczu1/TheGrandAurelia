<?php
include('db.php');

header('Content-Type: application/json');

// Check if required parameters are set
if (!isset($_POST['room_id']) || !isset($_POST['check_in_date']) || !isset($_POST['check_out_date'])) {
    echo json_encode(['available' => false, 'error' => 'Missing required parameters']);
    exit;
}

// Sanitize inputs
$room_id = (int)$_POST['room_id'];
$check_in_date = mysqli_real_escape_string($conn, $_POST['check_in_date']);
$check_out_date = mysqli_real_escape_string($conn, $_POST['check_out_date']);
$booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;

// Validate dates
if ($check_in_date >= $check_out_date) {
    echo json_encode(['available' => false, 'error' => 'Check-out date must be after check-in date']);
    exit;
}

// Check if the room exists
$room_query = "SELECT id FROM rooms WHERE id = $room_id";
$room_result = mysqli_query($conn, $room_query);

if (mysqli_num_rows($room_result) === 0) {
    echo json_encode(['error' => 'Invalid room ID']);
    exit;
}

// Build the query - exclude current booking ID if provided
$query = "SELECT id FROM bookings 
          WHERE room_id = $room_id 
          AND status IN ('approved', 'pending')";

// If we're editing an existing booking, exclude it from the check
if ($booking_id > 0) {
    $query .= " AND id != $booking_id";
}

// Add date range overlap conditions
// Add date range overlap conditions with 20 minutes buffer
$query .= " AND (
              ('$check_in_date' < DATE_ADD(check_out_date, INTERVAL 20 MINUTE) AND 
               DATE_ADD('$check_out_date', INTERVAL 20 MINUTE) > check_in_date)
          )";

$result = mysqli_query($conn, $query);

// Return availability status
echo json_encode(['available' => (mysqli_num_rows($result) === 0)]); 