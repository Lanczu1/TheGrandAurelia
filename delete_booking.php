<?php
include('db.php');

// Check if a booking ID is passed
if (isset($_GET['id'])) {
    $booking_id = $_GET['id'];

    // Delete the booking from the database
    $delete_sql = "DELETE FROM bookings WHERE id = $booking_id";

    if (mysqli_query($conn, $delete_sql)) {
        echo "<div class='alert alert-success'>Booking deleted successfully!</div>";
        // Redirect to bookings page after deletion
        header("Location: view_bookings.php");
        exit;
    } else {
        echo "<div class='alert alert-danger'>Error deleting booking: " . mysqli_error($conn) . "</div>";
    }
} else {
    echo "Invalid booking ID.";
    exit;
}
?>
