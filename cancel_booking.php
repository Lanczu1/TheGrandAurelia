<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to cancel a booking";
    header('Location: login.php');
    exit;
}

include('db.php');

// Check if booking ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Invalid booking ID.";
    header('Location: view_bookings.php');
    exit;
}

$booking_id = (int)$_GET['id'];
$user_id = (int)$_SESSION['user_id'];

// Verify that this booking belongs to the user
$check_query = "SELECT id, status, check_in_date FROM bookings 
                WHERE id = $booking_id AND user_id = $user_id";
$result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($result) === 0) {
    $_SESSION['error'] = "Booking not found or you don't have permission to cancel it.";
    header('Location: view_bookings.php');
    exit;
}

$booking = mysqli_fetch_assoc($result);

// Check if booking is in pending status - convert to lowercase for case-insensitive comparison
$booking_status = strtolower($booking['status']);
if ($booking_status !== 'pending') {
    $_SESSION['error'] = "Only pending bookings can be cancelled.";
    header('Location: view_bookings.php');
    exit;
}

// Check if check-in date is in the future
$today = date('Y-m-d');
if (!empty($booking['check_in_date']) && $booking['check_in_date'] <= $today) {
    $_SESSION['error'] = "Cannot cancel bookings on or after the check-in date.";
    header('Location: view_bookings.php');
    exit;
}

// All checks passed, cancel the booking
try {
    // Start transaction
    mysqli_begin_transaction($conn);
    
    // Set status to 'cancelled'
    $cancel_query = "UPDATE bookings SET status = 'cancelled' WHERE id = $booking_id";
    
    if (!mysqli_query($conn, $cancel_query)) {
        throw new Exception("Failed to cancel booking.");
    }
    
    // Commit transaction
    mysqli_commit($conn);
    
    $_SESSION['success'] = "Your booking has been successfully cancelled.";
    header('Location: view_bookings.php');
    exit;
    
} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    
    $_SESSION['error'] = "An error occurred while cancelling your booking. Please try again.";
    header('Location: view_bookings.php');
    exit;
}
?> 