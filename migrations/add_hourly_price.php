<?php
include(__DIR__ . '/../db.php');

// Add price_per_hour column to rooms table if it doesn't exist
$check_query = "SHOW COLUMNS FROM rooms LIKE 'price_per_hour'";
$check_result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($check_result) == 0) {
    // Column doesn't exist, let's add it
    // We'll calculate a default based on price (e.g., ~15% of nightly price rounded) for existing data
    $sql = "ALTER TABLE rooms ADD COLUMN price_per_hour DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER price";
    
    if (mysqli_query($conn, $sql)) {
        echo "Successfully added price_per_hour column.\n";
        
        // Update existing records with a default estimate (price / 8 is valid proxy for hourly sometimes, or just a fixed %?)
        // Let's go with 15% of nightly price as a rough hourly rate standard
        $update_sql = "UPDATE rooms SET price_per_hour = CEIL(price * 0.15)";
        if (mysqli_query($conn, $update_sql)) {
            echo "Updated existing rooms with default hourly prices.\n";
        }
    } else {
        echo "Error adding column: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "Column price_per_hour already exists.\n";
}
?>
