<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Require login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $profile_img = __DIR__ . '/profileimg/' . $user_id . '.jpg';

    // Check if file exists and delete it
    if (file_exists($profile_img)) {
        if (unlink($profile_img)) {
            $_SESSION['success'] = 'Profile picture removed successfully.';
        } else {
            $_SESSION['error'] = 'Failed to remove profile picture. Check permissions.';
        }
    } else {
        $_SESSION['error'] = 'No profile picture found to remove.';
    }
}

header('Location: profile.php');
exit;
