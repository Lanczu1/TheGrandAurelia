<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('Unauthorized');
}

include('../db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = (int)$_POST['booking_id'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    // Validate input
    if ($booking_id <= 0 || !in_array($status, ['approved', 'rejected'])) {
        http_response_code(400);
        exit('Invalid input');
    }

    // Update booking status
    $sql = "
    UPDATE bookings
    SET 
        status = ?,
        status_updated_at = NOW(),
        notification_seen = 0
    WHERE id = ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $booking_id);

    if ($stmt->execute()) {
        // attempt to insert a central notification for the user
        $notif_user_id = null;
        $sel = "SELECT b.user_id, r.room_name FROM bookings b JOIN rooms r ON b.room_id = r.id WHERE b.id = $booking_id LIMIT 1";
        $res = mysqli_query($conn, $sel);
        if ($res && mysqli_num_rows($res) > 0) {
            $r = mysqli_fetch_assoc($res);
            $notif_user_id = (int)$r['user_id'];
            $room_name = $r['room_name'];
        }
        if ($notif_user_id) {
            $msg = "Your room booking for " . mysqli_real_escape_string($conn, $room_name) . " has been $status.";
            $url = 'view_bookings.php';
            $ins = "INSERT INTO notifications (user_id, type, reference_id, status, message, url) VALUES (
                        $notif_user_id, 'room', $booking_id, '$status', '" . mysqli_real_escape_string($conn, $msg) . "', '$url')";
            @mysqli_query($conn, $ins);
        }

        http_response_code(200);
        exit('Success');
    } else {
        http_response_code(500);
        exit('Database error: ' . mysqli_error($conn));
    }
} else {
    http_response_code(405);
    exit('Method not allowed');
}
