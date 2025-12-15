<?php
session_start();
include('db.php');
include('security.php');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    $_SESSION['error'] = "Invalid request method.";
    header('Location: verify_email.php');
    exit;
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    log_security_event('csrf_token_failure', 'Verification attempt with invalid CSRF token');
    $_SESSION['error'] = "Security validation failed. Please try again.";
    header('Location: verify_email.php');
    exit;
}

// Check if user has pending verification
if (!isset($_SESSION['pending_verification_email']) || !isset($_SESSION['pending_verification_user_id'])) {
    $_SESSION['error'] = "No pending verification found.";
    header('Location: register.php');
    exit;
}

// Rate limiting
$rate_limit = check_rate_limit('verify_email', 5, 600); // 5 attempts per 10 minutes
if (!$rate_limit['allowed']) {
    $remaining_time = $rate_limit['reset_time'] - time();
    $minutes = ceil($remaining_time / 60);
    log_security_event('rate_limit_exceeded', 'Verification rate limit exceeded');
    $_SESSION['error'] = "Too many verification attempts. Please try again in $minutes minute(s).";
    header('Location: verify_email.php');
    exit;
}

// Get input
$verification_code = isset($_POST['verification_code']) ? trim($_POST['verification_code']) : '';

// Validate verification code format
if (empty($verification_code)) {
    $_SESSION['error'] = "Please enter the verification code.";
    header('Location: verify_email.php');
    exit;
}

if (!preg_match('/^\d{6}$/', $verification_code)) {
    $_SESSION['error'] = "Invalid verification code format. Please enter a 6-digit code.";
    header('Location: verify_email.php');
    exit;
}

// Get user details from session
$user_id = $_SESSION['pending_verification_user_id'];
$email = $_SESSION['pending_verification_email'];

// Fetch user from database
$stmt = $conn->prepare("SELECT id, username, email, verification_code, verification_code_expiry, is_verified FROM users WHERE id = ? AND email = ? LIMIT 1");
if (!$stmt) {
    log_security_event('database_error', 'Failed to prepare verification check statement: ' . $conn->error);
    $_SESSION['error'] = "An error occurred. Please try again later.";
    header('Location: verify_email.php');
    exit;
}

$stmt->bind_param("is", $user_id, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    log_security_event('verification_failed', 'User not found for verification');
    $_SESSION['error'] = "Account not found. Please register again.";

    // Clear session data
    unset($_SESSION['pending_verification_email']);
    unset($_SESSION['pending_verification_user_id']);

    header('Location: register.php');
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

// Check if already verified
if ($user['is_verified'] == 1) {
    log_security_event('already_verified', "User {$user['username']} already verified");
    $_SESSION['success'] = "Your account is already verified. Please login.";

    // Clear session data
    unset($_SESSION['pending_verification_email']);
    unset($_SESSION['pending_verification_user_id']);

    header('Location: login.php');
    exit;
}

// Check if code is expired
$expiry_time = strtotime($user['verification_code_expiry']);
if (time() > $expiry_time) {
    log_security_event('verification_code_expired', "Expired verification code for user {$user['username']}");
    $_SESSION['error'] = "Verification code has expired. Please request a new one.";
    header('Location: verify_email.php');
    exit;
}

// Verify the code
if ($verification_code !== $user['verification_code']) {
    log_security_event('verification_code_mismatch', "Invalid verification code attempt for user {$user['username']}");
    $_SESSION['error'] = "Invalid verification code. Please check your email and try again.";
    header('Location: verify_email.php');
    exit;
}

// Code is correct - verify the user
$stmt = $conn->prepare("UPDATE users SET is_verified = 1, verification_code = NULL, verification_code_expiry = NULL WHERE id = ?");
if (!$stmt) {
    log_security_event('database_error', 'Failed to prepare update statement: ' . $conn->error);
    $_SESSION['error'] = "An error occurred. Please try again later.";
    header('Location: verify_email.php');
    exit;
}

$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    $stmt->close();

    // Log the user in
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = 'user';

    // Clear verification session data
    unset($_SESSION['pending_verification_email']);
    unset($_SESSION['pending_verification_user_id']);

    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);

    // Clear rate limit
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $ip_key = 'rate_limit_verify_email_' . md5($ip);
    unset($_SESSION[$ip_key]);

    log_security_event('email_verified', "User {$user['username']} successfully verified their email");

    // Redirect to home page
    $_SESSION['success'] = "Email verified successfully! Welcome to The Grand Aurelia.";
    header('Location: index.php');
    exit;
} else {
    $stmt->close();
    log_security_event('verification_update_failed', 'Database error: ' . $conn->error);
    $_SESSION['error'] = "Verification failed. Please try again.";
    header('Location: verify_email.php');
    exit;
}
