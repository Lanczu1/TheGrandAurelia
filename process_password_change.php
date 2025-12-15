<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require('db.php'); // Adjust if you use a different file for DB

$user_id = $_SESSION['user_id'];
$current_password = $_POST['current_password'];
$new_password = $_POST['new_password'];
$confirm_password = $_POST['confirm_password'];

// Basic Validation
if ($new_password !== $confirm_password) {
    $_SESSION['error'] = 'New passwords do not match.';
    header('Location: settings.php');
    exit;
}

if (strlen($new_password) < 8) {
    $_SESSION['error'] = 'Password must be at least 8 characters long.';
    header('Location: settings.php');
    exit;
}

// Check Current Password
$stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || !password_verify($current_password, $user['password'])) {
    $_SESSION['error'] = 'Incorrect current password.';
    header('Location: settings.php');
    exit;
}

// Update Password
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
$update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
$update_stmt->bind_param("si", $hashed_password, $user_id);

if ($update_stmt->execute()) {
    $_SESSION['success'] = 'Password updated successfully.';
} else {
    $_SESSION['error'] = 'Failed to update password. Please try again.';
}

header('Location: settings.php');
exit;
