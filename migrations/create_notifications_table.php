<?php
// Run this script once (via browser or CLI) to create the notifications table.
// Example (PowerShell):
// php migrations\create_notifications_table.php

if (session_status() === PHP_SESSION_NONE) {
    // no session needed
}

include_once __DIR__ . '/../db.php';

$sql = "CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('room','dining','spa') NOT NULL,
    reference_id INT NOT NULL,
    status VARCHAR(64) NOT NULL,
    message TEXT DEFAULT NULL,
    url VARCHAR(255) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    seen TINYINT(1) DEFAULT 0,
    INDEX (user_id),
    INDEX (seen),
    INDEX (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($conn, $sql)) {
    echo "Notifications table created or already exists.\n";
} else {
    echo "Error creating notifications table: " . mysqli_error($conn) . "\n";
}

mysqli_close($conn);
