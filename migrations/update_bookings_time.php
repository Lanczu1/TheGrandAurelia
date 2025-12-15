<?php
include(__DIR__ . '/../db.php');

// Add time columns or modify existing date columns to datetime
$alter_query = "ALTER TABLE bookings 
    MODIFY COLUMN check_in_date DATETIME NOT NULL,
    MODIFY COLUMN check_out_date DATETIME NOT NULL";

if (mysqli_query($conn, $alter_query)) {
    echo "Successfully updated bookings table to use DATETIME.\n";
} else {
    // If it fails, it might be because the columns are already DATETIME or some other error.
    // Let's try to see if we can just proceed or if we need to check column type.
    echo "Error updating table: " . mysqli_error($conn) . "\n";
}
?>
