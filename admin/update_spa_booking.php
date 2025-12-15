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
    if ($booking_id <= 0 || !in_array($status, ['confirmed', 'cancelled'])) {
        http_response_code(400);
        exit('Invalid input');
    }
    
    // Ensure notification_seen column exists (best-effort)
    $alter = "ALTER TABLE spa_bookings ADD COLUMN IF NOT EXISTS notification_seen TINYINT(1) DEFAULT 0";
    @mysqli_query($conn, $alter);

    // Update spa booking status and mark notification as unseen
    $query = "UPDATE spa_bookings 
              SET status = '$status', 
                  status_updated_at = NOW(),
                  notification_seen = 0
              WHERE id = $booking_id";
    
    if (mysqli_query($conn, $query)) {
        // Try to insert a central notification (best-effort)
        $notif_user_id = null;
        $sel2 = "SELECT user_id, treatment FROM spa_bookings WHERE id = $booking_id LIMIT 1";
        $res2 = mysqli_query($conn, $sel2);
        if ($res2 && mysqli_num_rows($res2) > 0) {
            $row2 = mysqli_fetch_assoc($res2);
            $notif_user_id = (int)$row2['user_id'];
            $treatment = $row2['treatment'];
        }

        if ($notif_user_id) {
            $msg = "Your spa reservation";
            if (!empty($treatment)) {
                $msg .= " for " . mysqli_real_escape_string($conn, $treatment);
            }
            $msg .= " has been $status.";
            $url = 'view_bookings.php';
            $ins = "INSERT INTO notifications (user_id, type, reference_id, status, message, url) VALUES (
                        $notif_user_id, 'spa', $booking_id, '$status', '" . mysqli_real_escape_string($conn, $msg) . "', '$url')";
            @mysqli_query($conn, $ins);
        }

        http_response_code(200);
        exit('Success');
    } else {
        // If notification_seen column doesn't exist or another issue, try fallback without notification_seen
        $errno = mysqli_errno($conn);
        if ($errno === 1054) { // Unknown column
            $fallback = "UPDATE spa_bookings SET status = '$status', status_updated_at = NOW() WHERE id = $booking_id";
            if (mysqli_query($conn, $fallback)) {
                // try central notification as well
                $notif_user_id = null;
                $sel2 = "SELECT user_id, treatment FROM spa_bookings WHERE id = $booking_id LIMIT 1";
                $res2 = mysqli_query($conn, $sel2);
                if ($res2 && mysqli_num_rows($res2) > 0) {
                    $row2 = mysqli_fetch_assoc($res2);
                    $notif_user_id = (int)$row2['user_id'];
                    $treatment = $row2['treatment'];
                }
                if ($notif_user_id) {
                    $msg = "Your spa reservation";
                    if (!empty($treatment)) {
                        $msg .= " for " . mysqli_real_escape_string($conn, $treatment);
                    }
                    $msg .= " has been $status.";
                    $url = 'view_bookings.php';
                    $ins = "INSERT INTO notifications (user_id, type, reference_id, status, message, url) VALUES (
                                $notif_user_id, 'spa', $booking_id, '$status', '" . mysqli_real_escape_string($conn, $msg) . "', '$url')";
                    @mysqli_query($conn, $ins);
                }

                http_response_code(200);
                exit('Success');
            }
        }

        http_response_code(500);
        exit('Database error: ' . mysqli_error($conn));
    }
} else {
    http_response_code(405);
    exit('Method not allowed');
}
?>
