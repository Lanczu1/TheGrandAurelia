<?php
session_start();
include('db.php');
include('security.php');
include('send_email.php');

// Check if user has pending verification
if (!isset($_SESSION['pending_verification_email']) || !isset($_SESSION['pending_verification_user_id'])) {
    $_SESSION['error'] = "No pending verification found.";
    header('Location: register.php');
    exit;
}

// Rate limiting - stricter for resend
$rate_limit = check_rate_limit('resend_verification', 3, 3600); // 3 resends per hour
if (!$rate_limit['allowed']) {
    $remaining_time = $rate_limit['reset_time'] - time();
    $minutes = ceil($remaining_time / 60);
    log_security_event('rate_limit_exceeded', 'Resend verification rate limit exceeded');
    $_SESSION['error'] = "Too many resend requests. Please try again in $minutes minute(s).";
    header('Location: verify_email.php');
    exit;
}

$user_id = $_SESSION['pending_verification_user_id'];
$email = $_SESSION['pending_verification_email'];

// Get user from database
$stmt = $conn->prepare("SELECT id, username, email, is_verified FROM users WHERE id = ? AND email = ? LIMIT 1");
if (!$stmt) {
    log_security_event('database_error', 'Failed to prepare user check statement: ' . $conn->error);
    $_SESSION['error'] = "An error occurred. Please try again later.";
    header('Location: verify_email.php');
    exit;
}

$stmt->bind_param("is", $user_id, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $_SESSION['error'] = "Account not found.";
    unset($_SESSION['pending_verification_email']);
    unset($_SESSION['pending_verification_user_id']);
    header('Location: register.php');
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

// Check if already verified
if ($user['is_verified'] == 1) {
    $_SESSION['success'] = "Your account is already verified. Please login.";
    unset($_SESSION['pending_verification_email']);
    unset($_SESSION['pending_verification_user_id']);
    header('Location: login.php');
    exit;
}

// Generate new verification code
$verification_code = generate_verification_code();
$verification_expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

// Update user with new code
$stmt = $conn->prepare("UPDATE users SET verification_code = ?, verification_code_expiry = ? WHERE id = ?");
if (!$stmt) {
    log_security_event('database_error', 'Failed to prepare update statement: ' . $conn->error);
    $_SESSION['error'] = "An error occurred. Please try again later.";
    header('Location: verify_email.php');
    exit;
}

$stmt->bind_param("ssi", $verification_code, $verification_expiry, $user_id);

if ($stmt->execute()) {
    $stmt->close();

    // Send new verification email
    $email_result = send_verification_email($email, $user['username'], $verification_code);

    if ($email_result['success']) {
        log_security_event('verification_resent', "Verification code resent to user {$user['username']}");
        $_SESSION['success'] = "A new verification code has been sent to your email!";
        header('Location: verify_email.php');
        exit;
    } else {
        log_security_event('resend_email_failed', 'Failed to resend verification email: ' . $email_result['message']);
        $_SESSION['error'] = "Failed to send verification email. Please try again later.";
        header('Location: verify_email.php');
        exit;
    }
} else {
    $stmt->close();
    log_security_event('resend_update_failed', 'Database error: ' . $conn->error);
    $_SESSION['error'] = "Failed to generate new code. Please try again.";
    header('Location: verify_email.php');
    exit;
}
