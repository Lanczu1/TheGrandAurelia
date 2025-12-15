<?php
include('db.php');

// Admin credentials
$admin_username = 'admin';
$admin_email = 'admin@hotel.com';
$admin_password = 'admin123'; // This will be the admin password
$admin_role = 'admin';

// Hash the password
$hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

// First, check if admin already exists
$check_query = "SELECT id FROM users WHERE username = 'admin' OR email = 'admin@hotel.com'";
$result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($result) > 0) {
    // Update existing admin
    $update_query = "UPDATE users SET 
                    password = '$hashed_password',
                    role = 'admin'
                    WHERE username = 'admin' OR email = 'admin@hotel.com'";
    
    if (mysqli_query($conn, $update_query)) {
        echo "Admin account updated successfully!<br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br>";
        echo "Please delete this file after use for security.";
    } else {
        echo "Error updating admin account: " . mysqli_error($conn);
    }
} else {
    // Create new admin
    $insert_query = "INSERT INTO users (username, email, password, role) 
                     VALUES ('$admin_username', '$admin_email', '$hashed_password', '$admin_role')";
    
    if (mysqli_query($conn, $insert_query)) {
        echo "Admin account created successfully!<br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br>";
        echo "Please delete this file after use for security.";
    } else {
        echo "Error creating admin account: " . mysqli_error($conn);
    }
} 