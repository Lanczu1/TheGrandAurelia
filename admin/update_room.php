<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('Unauthorized');
}

include('../db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id = (int)$_POST['room_id'];
    $room_name = mysqli_real_escape_string($conn, $_POST['room_name']);
    $price = (float)$_POST['price'];
    $description = isset($_POST['description']) ? mysqli_real_escape_string($conn, $_POST['description']) : '';
    $room_image = isset($_POST['room_image']) ? mysqli_real_escape_string($conn, $_POST['room_image']) : '';
    
    // Validate input
    if (empty($room_name) || $price <= 0 || $room_id <= 0) {
        http_response_code(400);
        exit('Invalid input');
    }
    
    // Update room
    $query = "UPDATE rooms 
              SET room_name = '$room_name', 
                  price = $price, 
                  description = '$description', 
                  room_image = '$room_image' 
              WHERE id = $room_id";
    
    if (mysqli_query($conn, $query)) {
        http_response_code(200);
        exit('Success');
    } else {
        http_response_code(500);
        exit('Database error: ' . mysqli_error($conn));
    }
} else {
    http_response_code(405);
    exit('Method not allowed');
} 