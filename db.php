<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ecommerce_booking";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to ensure special characters are handled correctly
$conn->set_charset("utf8");
