<?php
session_start();
include('db.php');

// Require login to make a spa reservation
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'spa.php';
    $_SESSION['error'] = 'Please login to make a spa reservation.';
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid request.";
    header('Location: spa.php#spa-reserve');
    exit;
}

$customer_name = isset($_POST['name']) ? mysqli_real_escape_string($conn, trim($_POST['name'])) : '';
$email = isset($_POST['email']) ? mysqli_real_escape_string($conn, trim($_POST['email'])) : '';
$phone = isset($_POST['phone']) ? mysqli_real_escape_string($conn, trim($_POST['phone'])) : '';
$treatment = isset($_POST['treatment']) ? mysqli_real_escape_string($conn, trim($_POST['treatment'])) : '';
$spa_date = isset($_POST['date']) ? mysqli_real_escape_string($conn, $_POST['date']) : '';
$spa_time = isset($_POST['time']) ? mysqli_real_escape_string($conn, $_POST['time']) : '';
$guests = isset($_POST['guests']) ? (int)$_POST['guests'] : 0;
$special_requests = isset($_POST['special_requests']) ? mysqli_real_escape_string($conn, trim($_POST['special_requests'])) : '';
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : NULL;

$errors = [];
if (empty($customer_name)) $errors[] = "Full name is required.";
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
if (empty($treatment)) $errors[] = "Please select a treatment.";
if (empty($spa_date)) $errors[] = "Date is required.";
if (empty($spa_time)) $errors[] = "Time is required.";
if ($guests < 1 || $guests > 10) $errors[] = "Guests must be between 1 and 10.";

// Date cannot be in the past
if (!empty($spa_date)) {
    $today = date('Y-m-d');
    if ($spa_date < $today) {
        $errors[] = "Date cannot be in the past.";
    }
}

if (!empty($errors)) {
    $_SESSION['error'] = implode(" ", $errors);
    header('Location: spa.php#spa-reserve');
    exit;
}

try {
    $insert = "INSERT INTO spa_bookings 
        (user_id, customer_name, email, phone, treatment, spa_date, spa_time, guests, special_requests, status)
        VALUES (
            " . ($user_id ? $user_id : 'NULL') . ",
            '$customer_name',
            '$email',
            " . (!empty($phone) ? "'$phone'" : 'NULL') . ",
            '$treatment',
            '$spa_date',
            '$spa_time',
            $guests,
            " . (!empty($special_requests) ? "'$special_requests'" : 'NULL') . ",
            'pending'
        )";

    if (!mysqli_query($conn, $insert)) {
        throw new Exception("DB insert failed: " . mysqli_error($conn));
    }

    $_SESSION['success'] = "Spa booking received for $treatment on " .
        date('F j, Y', strtotime($spa_date)) . " at " . date('g:i A', strtotime($spa_time)) .
        " for $guests guest(s). We’ll confirm shortly.";
    header('Location: spa_confirmation.php');
    exit;
} catch (Exception $e) {
    error_log("Spa booking error: " . $e->getMessage());
    $_SESSION['error'] = "Could not complete your booking. Please try again.";
    header('Location: spa.php#spa-reserve');
    exit;
}
