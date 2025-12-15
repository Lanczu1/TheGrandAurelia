<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('Unauthorized');
}

include('../db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reservation_id = (int)$_POST['reservation_id'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    // Validate input
    if ($reservation_id <= 0 || !in_array($status, ['confirmed', 'cancelled'])) {
        http_response_code(400);
        exit('Invalid input');
    }
    
    // Ensure notification_seen column exists (best-effort)
    $alter = "ALTER TABLE dining_reservations ADD COLUMN IF NOT EXISTS notification_seen TINYINT(1) DEFAULT 0";
    @mysqli_query($conn, $alter);

    // Update dining reservation status and mark notification as unseen
    $query = "UPDATE dining_reservations 
              SET status = '$status', 
                  status_updated_at = NOW(),
                  notification_seen = 0
              WHERE id = $reservation_id";
    
    if (mysqli_query($conn, $query)) {
        // central notification insertion (best-effort)
        $notif_user_id = null;
        $sel2 = "SELECT user_id, venue, reservation_date, reservation_time FROM dining_reservations WHERE id = $reservation_id LIMIT 1";
        $res2 = mysqli_query($conn, $sel2);
        if ($res2 && mysqli_num_rows($res2) > 0) {
            $row2 = mysqli_fetch_assoc($res2);
            $notif_user_id = (int)$row2['user_id'];
            $venue = $row2['venue'];
            $rdate = $row2['reservation_date'];
            $rtime = $row2['reservation_time'];
        }
        if ($notif_user_id) {
            $msg = "Your dining reservation";
            if (!empty($venue)) $msg .= " at " . mysqli_real_escape_string($conn, $venue);
            $msg .= " on " . mysqli_real_escape_string($conn, $rdate) . " has been $status.";
            $url = 'view_bookings.php';
            $ins = "INSERT INTO notifications (user_id, type, reference_id, status, message, url) VALUES (
                        $notif_user_id, 'dining', $reservation_id, '$status', '" . mysqli_real_escape_string($conn, $msg) . "', '$url')";
            @mysqli_query($conn, $ins);
        }

        http_response_code(200);
        exit('Success');
    } else {
        // If notification_seen column doesn't exist or another issue, try fallback without notification_seen
        $errno = mysqli_errno($conn);
        if ($errno === 1054) { // Unknown column
            $fallback = "UPDATE dining_reservations SET status = '$status', status_updated_at = NOW() WHERE id = $reservation_id";
            if (mysqli_query($conn, $fallback)) {
                // attempt central notification too
                $notif_user_id = null;
                $sel2 = "SELECT user_id, venue, reservation_date, reservation_time FROM dining_reservations WHERE id = $reservation_id LIMIT 1";
                $res2 = mysqli_query($conn, $sel2);
                if ($res2 && mysqli_num_rows($res2) > 0) {
                    $row2 = mysqli_fetch_assoc($res2);
                    $notif_user_id = (int)$row2['user_id'];
                    $venue = $row2['venue'];
                    $rdate = $row2['reservation_date'];
                }
                if ($notif_user_id) {
                    $msg = "Your dining reservation";
                    if (!empty($venue)) $msg .= " at " . mysqli_real_escape_string($conn, $venue);
                    $msg .= " on " . mysqli_real_escape_string($conn, $rdate) . " has been $status.";
                    $url = 'view_bookings.php';
                    $ins = "INSERT INTO notifications (user_id, type, reference_id, status, message, url) VALUES (
                                $notif_user_id, 'dining', $reservation_id, '$status', '" . mysqli_real_escape_string($conn, $msg) . "', '$url')";
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
