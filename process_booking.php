<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'add_booking.php';
    $_SESSION['error'] = "Please login to make a booking";
    header('Location: login.php');
    exit;
}

include('db.php');

// Validate input
if (!isset($_POST['customer_name']) || !isset($_POST['room_id']) || 
    !isset($_POST['check_in_date']) || !isset($_POST['check_out_date'])) {
    $_SESSION['error'] = "All fields are required.";
    header('Location: add_booking.php');
    exit;
}

// Sanitize input
$customer_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
$room_id = (int)$_POST['room_id'];
$check_in_date = mysqli_real_escape_string($conn, $_POST['check_in_date']);
$check_out_date = mysqli_real_escape_string($conn, $_POST['check_out_date']);
$user_id = (int)$_SESSION['user_id'];

// Validate dates
$now = date('Y-m-d H:i:s');
if ($check_in_date < $now) {
    $_SESSION['error'] = "Cannot check in at a time in the past.";
    header('Location: add_booking.php');
    exit;
}

if ($check_out_date <= $check_in_date) {
    $_SESSION['error'] = "Check-out date must be after check-in date.";
    header('Location: add_booking.php');
    exit;
}

// Check if room exists
$room_query = "SELECT id, room_name, price FROM rooms WHERE id = $room_id";
$room_result = mysqli_query($conn, $room_query);

if (mysqli_num_rows($room_result) === 0) {
    $_SESSION['error'] = "Invalid room selection.";
    header('Location: add_booking.php');
    exit;
}

// CRITICAL: Check if room is already booked for any date in the selected range
$availability_query = "SELECT id FROM bookings 
                      WHERE room_id = $room_id 
                      AND status IN ('approved', 'pending')
                      AND (
                          ('$check_in_date' < DATE_ADD(check_out_date, INTERVAL 20 MINUTE) AND 
                           DATE_ADD('$check_out_date', INTERVAL 20 MINUTE) > check_in_date)
                      )";
$availability_result = mysqli_query($conn, $availability_query);

if (mysqli_num_rows($availability_result) > 0) {
    $_SESSION['error'] = "Sorry, this room is already booked for some or all of the selected dates. Please choose another date range or room.";
    header('Location: add_booking.php');
    exit;
}

// If we get here, the room is available. Let's book it!
try {
    // Start transaction
    mysqli_begin_transaction($conn);

    // Calculate number of nights and total price
    // Calculate number of nights AND hours
    $diff_seconds = strtotime($check_out_date) - strtotime($check_in_date);
    $diff_hours = $diff_seconds / 3600;
    
    $room = mysqli_fetch_assoc($room_result);
    // Be safe: if price_per_hour exists use it, else fallback or calculate
    $hourly_price = isset($room['price_per_hour']) && $room['price_per_hour'] > 0 
                    ? $room['price_per_hour'] 
                    : ceil($room['price'] * 0.15); // Fallback if 0 or column missing (though migration added it)

    if ($diff_hours < 24) {
        $hours = max(1, ceil($diff_hours));
        $total_price = $hours * $hourly_price;
    } else {
        $nights = floor($diff_hours / 24);
        $remaining_hours = ceil(fmod($diff_hours, 24));
        
        if ($remaining_hours > 0) {
            $night_price = $nights * $room['price'];
            $overage_price = $remaining_hours * $hourly_price;
            
            // If overage price is more than a full night, just charge the night
            if ($overage_price >= $room['price']) {
                $total_price = ($nights + 1) * $room['price'];
            } else {
                $total_price = $night_price + $overage_price;
            }
        } else {
            // Exact number of nights
            $nights = max(1, $nights); // safety check though logic implies >= 1
            $total_price = $nights * $room['price'];
        }
    }

    // Insert the booking with user_id and both check-in and check-out dates
    $insert_query = "INSERT INTO bookings (user_id, room_id, customer_name, check_in_date, check_out_date, total_price, status, created_at) 
                    VALUES ($user_id, $room_id, '$customer_name', '$check_in_date', '$check_out_date', $total_price, 'pending', NOW())";
    
    if (!mysqli_query($conn, $insert_query)) {
        throw new Exception("Failed to create booking.");
    }

    // Commit transaction
    mysqli_commit($conn);

    // Set success message
    $_SESSION['success'] = "Booking request submitted! You have requested the " 
                          . htmlspecialchars($room['room_name']) 
                          . " from " . date('F j, Y', strtotime($check_in_date))
                          . " to " . date('F j, Y', strtotime($check_out_date))
                          . " (" . $nights . " nights). Your booking will be reviewed by our staff.";

    // Redirect to confirmation page
    header('Location: booking_confirmation.php');
    exit;

} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    
    $_SESSION['error'] = "An error occurred while processing your booking. Please try again.";
    header('Location: add_booking.php');
    exit;
}
?>
