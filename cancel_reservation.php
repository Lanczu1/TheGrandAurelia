<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to cancel a reservation.";
    header('Location: login.php');
    exit;
}

include('db.php');

if (!isset($_GET['id']) || !isset($_GET['type'])) {
    $_SESSION['error'] = "Invalid request.";
    header('Location: view_bookings.php');
    exit;
}

$id = (int)$_GET['id'];
$type = $_GET['type'];
$user_id = (int)$_SESSION['user_id'];

try {
    if ($type === 'room') {
        // Delegate to existing cancel_booking.php for room bookings
        header('Location: cancel_booking.php?id=' . $id);
        exit;
    } elseif ($type === 'dining') {
        $check = mysqli_query($conn, "SELECT id, status, reservation_date FROM dining_reservations WHERE id = $id AND user_id = $user_id");
        if (!$check || mysqli_num_rows($check) === 0) {
            throw new Exception('Reservation not found or permission denied.');
        }
        $row = mysqli_fetch_assoc($check);
        $status = strtolower($row['status']);
        if ($status !== 'pending') throw new Exception('Only pending reservations can be cancelled.');
        $today = date('Y-m-d');
        if (!empty($row['reservation_date']) && $row['reservation_date'] <= $today) throw new Exception('Cannot cancel on or after reservation date.');

        if (!mysqli_query($conn, "UPDATE dining_reservations SET status = 'cancelled' WHERE id = $id")) {
            throw new Exception('Failed to cancel reservation.');
        }

        $_SESSION['success'] = 'Your dining reservation has been cancelled.';
        header('Location: view_bookings.php');
        exit;

    } elseif ($type === 'spa') {
        $check = mysqli_query($conn, "SELECT id, status, spa_date FROM spa_bookings WHERE id = $id AND user_id = $user_id");
        if (!$check || mysqli_num_rows($check) === 0) {
            throw new Exception('Booking not found or permission denied.');
        }
        $row = mysqli_fetch_assoc($check);
        $status = strtolower($row['status']);
        if ($status !== 'pending') throw new Exception('Only pending bookings can be cancelled.');
        $today = date('Y-m-d');
        if (!empty($row['spa_date']) && $row['spa_date'] <= $today) throw new Exception('Cannot cancel on or after booking date.');

        if (!mysqli_query($conn, "UPDATE spa_bookings SET status = 'cancelled' WHERE id = $id")) {
            throw new Exception('Failed to cancel booking.');
        }

        $_SESSION['success'] = 'Your spa booking has been cancelled.';
        header('Location: view_bookings.php');
        exit;

    } else {
        throw new Exception('Unknown reservation type.');
    }

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: view_bookings.php');
    exit;
}

?>
