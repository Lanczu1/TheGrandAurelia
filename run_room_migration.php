<?php
include 'db.php';

echo "Running Room Interaction Migration...<br>";

// Create room_reviews table
$sql1 = "CREATE TABLE IF NOT EXISTS room_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
)";

if (mysqli_query($conn, $sql1)) {
    echo "Table room_reviews created successfully.<br>";
} else {
    echo "Error creating room_reviews: " . mysqli_error($conn) . "<br>";
}

// Create room_likes table
$sql2 = "CREATE TABLE IF NOT EXISTS room_likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_like (room_id, user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
)";

if (mysqli_query($conn, $sql2)) {
    echo "Table room_likes created successfully.<br>";
} else {
    echo "Error creating room_likes: " . mysqli_error($conn) . "<br>";
}

echo "Migration Done.";
