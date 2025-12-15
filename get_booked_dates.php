<?php
include('db.php');

header('Content-Type: application/json');

// Check if room_id is provided
if (!isset($_GET['room_id'])) {
    echo json_encode(['error' => 'Room ID is required']);
    exit;
}

$room_id = (int)$_GET['room_id'];

// Get all approved bookings for this room
$query = "SELECT check_in_date, DATE_ADD(check_out_date, INTERVAL 20 MINUTE) as check_out_date FROM bookings 
          WHERE room_id = $room_id 
          AND status IN ('approved', 'pending')
          AND DATE_ADD(check_out_date, INTERVAL 20 MINUTE) >= NOW()";

$result = mysqli_query($conn, $query);

$booked_periods = [];
while ($booking = mysqli_fetch_assoc($result)) {
    $booked_periods[] = [
        'start' => $booking['check_in_date'],
        'end' => $booking['check_out_date']
    ];
}

echo json_encode(['booked_periods' => $booked_periods]); 