<?php
session_start();
include('db.php');
include('security.php');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    $_SESSION['error'] = "Invalid request method.";
    header('Location: register.php');
    exit;
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    log_security_event('csrf_token_failure', 'Registration attempt with invalid CSRF token');
    $_SESSION['error'] = "Security validation failed. Please try again.";
    header('Location: register.php');
    exit;
}

// Rate limiting
$rate_limit = check_rate_limit('register', 3, 60); // 3 registrations per minute
if (!$rate_limit['allowed']) {
    $remaining_time = $rate_limit['reset_time'] - time();
    $seconds = $remaining_time;
    log_security_event('rate_limit_exceeded', 'Registration rate limit exceeded');
    $_SESSION['error'] = "Too many registration attempts. Please try again in $seconds second(s).";
    header('Location: register.php');
    exit;
}

// Get and validate input
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

// Validate all fields are present
if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
    $_SESSION['error'] = "All fields are required.";
    header('Location: register.php');
    exit;
}

// Validate username
$username_validation = validate_username($username);
if (!$username_validation['valid']) {
    $_SESSION['error'] = $username_validation['error'];
    header('Location: register.php');
    exit;
}
$username = $username_validation['value'];

// Validate email
$email_validation = validate_email($email);
if (!$email_validation['valid']) {
    $_SESSION['error'] = $email_validation['error'];
    header('Location: register.php');
    exit;
}
$email = $email_validation['value'];

// Validate password
$password_validation = validate_password($password);
if (!$password_validation['valid']) {
    $_SESSION['error'] = $password_validation['error'];
    header('Location: register.php');
    exit;
}

// Validate password match
if ($password !== $confirm_password) {
    $_SESSION['error'] = "Passwords do not match.";
    header('Location: register.php');
    exit;
}

// Check for dangerous patterns in password
$dangerous_patterns = [
    "/(<script|<\/script>)/i",
    "/(javascript:|onerror=|onclick=)/i",
    "/(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC)/i"
];

foreach ($dangerous_patterns as $pattern) {
    if (preg_match($pattern, $password)) {
        log_security_event('suspicious_password_pattern', "Registration attempt with suspicious password pattern");
        $_SESSION['error'] = "Password contains invalid characters.";
        header('Location: register.php');
        exit;
    }
}

// Check if username exists using prepared statement
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
if (!$stmt) {
    log_security_event('database_error', 'Failed to prepare username check statement: ' . $conn->error);
    $_SESSION['error'] = "An error occurred. Please try again later.";
    header('Location: register.php');
    exit;
}

$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $stmt->close();
    $_SESSION['error'] = "Username already exists. Please choose a different username.";
    header('Location: register.php');
    exit;
}
$stmt->close();

// Check if email exists using prepared statement
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
if (!$stmt) {
    log_security_event('database_error', 'Failed to prepare email check statement: ' . $conn->error);
    $_SESSION['error'] = "An error occurred. Please try again later.";
    header('Location: register.php');
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $stmt->close();
    $_SESSION['error'] = "Email already registered. Please use a different email or try logging in.";
    header('Location: register.php');
    exit;
}
$stmt->close();

// Hash password using secure algorithm (PASSWORD_DEFAULT uses bcrypt)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

if ($hashed_password === false) {
    log_security_event('password_hash_error', 'Failed to hash password');
    $_SESSION['error'] = "An error occurred during registration. Please try again.";
    header('Location: register.php');
    exit;
}

// Generate verification code and expiry
include('send_email.php');
$verification_code = generate_verification_code();
$verification_expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

// Insert new user using prepared statement with verification fields
$stmt = $conn->prepare("INSERT INTO users (username, email, password, role, is_verified, verification_code, verification_code_expiry) VALUES (?, ?, ?, 'user', 0, ?, ?)");
if (!$stmt) {
    log_security_event('database_error', 'Failed to prepare insert statement: ' . $conn->error);
    $_SESSION['error'] = "An error occurred. Please try again later.";
    header('Location: register.php');
    exit;
}

$stmt->bind_param("sssss", $username, $email, $hashed_password, $verification_code, $verification_expiry);

if ($stmt->execute()) {
    // Get the new user's ID
    $user_id = $stmt->insert_id;
    $stmt->close();

    // Send verification email
    $email_result = send_verification_email($email, $username, $verification_code);

    if ($email_result['success']) {
        // Store user info in session for verification page
        $_SESSION['pending_verification_email'] = $email;
        $_SESSION['pending_verification_user_id'] = $user_id;

        // Clear rate limit on successful registration
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $ip_key = 'rate_limit_register_' . md5($ip);
        unset($_SESSION[$ip_key]);

        log_security_event('successful_registration', "New user registered (pending verification): $username");

        // Redirect to verification page
        $_SESSION['success'] = "Registration successful! Please check your email for the verification code.";
        header('Location: verify_email.php');
        exit;
    } else {
        // Delete the user if email fails to send
        $delete_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $delete_stmt->bind_param("i", $user_id);
        $delete_stmt->execute();
        $delete_stmt->close();

        log_security_event('email_send_failed', 'Failed to send verification email: ' . $email_result['message']);
        $_SESSION['error'] = "Failed to send verification email. Please try again later or contact support.";
        header('Location: register.php');
        exit;
    }
} else {
    $stmt->close();
    log_security_event('registration_failed', 'Database error: ' . $conn->error);
    $_SESSION['error'] = "Registration failed. Please try again.";
    header('Location: register.php');
    exit;
}
