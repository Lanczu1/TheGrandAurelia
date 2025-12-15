<?php
session_start();

include('db.php');

// Require login to make a dining reservation
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'dining.php';
    $_SESSION['error'] = 'Please login to make a dining reservation.';
    header('Location: login.php');
    exit;
}

// Validate input
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid request method.";
    header('Location: dining.php#reservation');
    exit;
}

// Get and sanitize input
$customer_name = isset($_POST['name']) ? mysqli_real_escape_string($conn, trim($_POST['name'])) : '';
$email = isset($_POST['email']) ? mysqli_real_escape_string($conn, trim($_POST['email'])) : '';
$reservation_date = isset($_POST['date']) ? mysqli_real_escape_string($conn, $_POST['date']) : '';
$reservation_time = isset($_POST['time']) ? mysqli_real_escape_string($conn, $_POST['time']) : '';
$number_of_guests = isset($_POST['guests']) ? (int)$_POST['guests'] : 0;
$venue = isset($_POST['venue']) ? mysqli_real_escape_string($conn, $_POST['venue']) : '';
$special_requests = isset($_POST['special-requests']) ? mysqli_real_escape_string($conn, trim($_POST['special-requests'])) : '';

// Get user_id if logged in
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : NULL;

// Validate required fields
$errors = [];

if (empty($customer_name)) {
    $errors[] = "Full name is required.";
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Valid email address is required.";
}

if (empty($reservation_date)) {
    $errors[] = "Reservation date is required.";
}

if (empty($reservation_time)) {
    $errors[] = "Reservation time is required.";
}

if ($number_of_guests < 1 || $number_of_guests > 20) {
    $errors[] = "Number of guests must be between 1 and 20. For larger groups, please contact us directly.";
}

if (empty($venue)) {
    $errors[] = "Please select a venue.";
}

// Validate date is not in the past
if (!empty($reservation_date)) {
    $today = date('Y-m-d');
    if ($reservation_date < $today) {
        $errors[] = "Reservation date cannot be in the past.";
    }
}

// If there are validation errors, return them
if (!empty($errors)) {
    $_SESSION['error'] = implode(" ", $errors);
    header('Location: dining.php#reservation');
    exit;
}

// Validate venue
$valid_venues = ['restaurant', 'lounge', 'grand_room', 'wine_cellar'];
if (!in_array($venue, $valid_venues)) {
    $_SESSION['error'] = "Invalid venue selected.";
    header('Location: dining.php#reservation');
    exit;
}

// Check if there's a conflict (optional - you might want to check for time conflicts)
// For now, we'll allow multiple reservations at the same time

try {
    // Insert the reservation
    $insert_query = "INSERT INTO dining_reservations 
                    (user_id, customer_name, email, reservation_date, reservation_time, number_of_guests, venue, special_requests, status) 
                    VALUES (
                        " . ($user_id ? $user_id : 'NULL') . ",
                        '$customer_name',
                        '$email',
                        '$reservation_date',
                        '$reservation_time',
                        $number_of_guests,
                        '$venue',
                        " . (!empty($special_requests) ? "'$special_requests'" : 'NULL') . ",
                        'pending'
                    )";

    if (!mysqli_query($conn, $insert_query)) {
        throw new Exception("Failed to create reservation: " . mysqli_error($conn));
    }

    // Get the reservation ID
    $reservation_id = mysqli_insert_id($conn);

    // Format venue name for display
    $venue_names = [
        'restaurant' => 'The Aurelia Restaurant',
        'lounge' => 'The Skyline Lounge',
        'grand_room' => 'The Grand Room',
        'wine_cellar' => 'The Wine Cellar'
    ];
    $venue_display = isset($venue_names[$venue]) ? $venue_names[$venue] : $venue;

    // Format date and time for display
    $formatted_date = date('F j, Y', strtotime($reservation_date));
    $formatted_time = date('g:i A', strtotime($reservation_time));

    // Set success message
    $_SESSION['success'] = "Your reservation request has been submitted successfully! " .
        "We have received your request for " . $number_of_guests . " guest(s) at " .
        $venue_display . " on " . $formatted_date . " at " . $formatted_time .
        ". Our team will confirm your reservation shortly.";

    // Redirect to confirmation page
    header('Location: dining_confirmation.php');
    exit;
} catch (Exception $e) {
    $_SESSION['error'] = "An error occurred while processing your reservation. Please try again.";
    error_log("Dining reservation error: " . $e->getMessage());
    header('Location: dining.php#reservation');
    exit;
}
