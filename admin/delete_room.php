<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('Unauthorized');
}

include('../db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id = (int)$_POST['room_id'];
    
    // Check if room has any bookings
    $check_query = "SELECT COUNT(*) as count FROM bookings WHERE room_id = $room_id";
    $result = mysqli_query($conn, $check_query);
    $count = mysqli_fetch_assoc($result)['count'];
    
    if ($count > 0) {
        http_response_code(400);
        exit('Cannot delete room with existing bookings');
    }
    
    // Delete room
    $query = "DELETE FROM rooms WHERE id = $room_id";
    
    if (mysqli_query($conn, $query)) {
        http_response_code(200);
        exit('Success');
    } else {
        http_response_code(500);
        exit('Database error');
    }
} else {
    http_response_code(405);
    exit('Method not allowed');
} 