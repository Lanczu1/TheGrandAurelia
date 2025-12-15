<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('db.php');
include('security.php');

// Check request method
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    $_SESSION['error'] = "Invalid request method.";
    header('Location: login.php');
    exit;
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    log_security_event('csrf_token_failure', 'Login attempt with invalid CSRF token');
    $_SESSION['error'] = "Security validation failed. Please try again.";
    header('Location: login.php');
    exit;
}

// Rate limiting
$rate_limit = check_rate_limit('login', 5, 900); // 5 attempts per 15 minutes
if (!$rate_limit['allowed']) {
    $remaining_time = $rate_limit['reset_time'] - time();
    $minutes = ceil($remaining_time / 60);
    log_security_event('rate_limit_exceeded', 'Login rate limit exceeded');
    $_SESSION['error'] = "Too many login attempts. Please try again in $minutes minute(s).";
    header('Location: login.php');
    exit;
}

// Get and validate input
$username_input = isset($_POST['username']) ? $_POST['username'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Validate username/email
$username_validation = validate_username($username_input);
$email_validation = validate_email($username_input);

// Determine if input is email or username
$is_email = filter_var($username_input, FILTER_VALIDATE_EMAIL);
$field = $is_email ? 'email' : 'username';

if ($is_email) {
    if (!$email_validation['valid']) {
        $_SESSION['error'] = $email_validation['error'];
        header('Location: login.php');
        exit;
    }
    $username = $email_validation['value'];
} else {
    if (!$username_validation['valid']) {
        $_SESSION['error'] = $username_validation['error'];
        header('Location: login.php');
        exit;
    }
    $username = $username_validation['value'];
}

// Validate password
if (empty($password)) {
    $_SESSION['error'] = "Password is required.";
    header('Location: login.php');
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
        log_security_event('suspicious_password_pattern', "Login attempt with suspicious password pattern from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        $_SESSION['error'] = "Invalid credentials.";
        header('Location: login.php');
        exit;
    }
}

// Use prepared statement to prevent SQL injection
$stmt = $conn->prepare("SELECT id, username, email, password, role, is_verified, verification_code, verification_code_expiry FROM users WHERE $field = ? LIMIT 1");
if (!$stmt) {
    log_security_event('database_error', 'Failed to prepare login statement: ' . $conn->error);
    $_SESSION['error'] = "An error occurred. Please try again later.";
    header('Location: login.php');
    exit;
}

$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();

    // Verify password using password_verify (secure hash comparison)
    if (password_verify($password, $user['password'])) {
        // Check if email is verified
        if (isset($user['is_verified']) && $user['is_verified'] == 0) {
            // Email not verified - redirect to verification page
            $_SESSION['pending_verification_email'] = $user['email'];
            $_SESSION['pending_verification_user_id'] = $user['id'];

            log_security_event('unverified_login_attempt', "Login attempt with unverified email for user: {$user['username']}");
            $_SESSION['error'] = "Please verify your email address before logging in. Check your inbox for the verification code.";
            header('Location: verify_email.php');
            exit;
        }

        // Login successful
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'] ?? 'user';

        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);

        // Clear rate limit on successful login
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $ip_key = 'rate_limit_login_' . md5($ip);
        unset($_SESSION[$ip_key]);

        // Redirect based on role
        if ($user['role'] === 'admin') {
            header('Location: admin/dashboard.php');
        } else {
            // Redirect to intended page or home
            $redirect = isset($_SESSION['redirect_after_login'])
                ? $_SESSION['redirect_after_login']
                : 'index.php';
            unset($_SESSION['redirect_after_login']);
            header("Location: $redirect");
        }
        exit;
    } else {
        // Invalid password
        log_security_event('failed_login', "Failed login attempt for $field: $username");
        $_SESSION['error'] = "Invalid username/email or password.";
    }
} else {
    // User not found
    log_security_event('failed_login', "Login attempt for non-existent $field: $username");
    $_SESSION['error'] = "Invalid username/email or password.";
}

$stmt->close();

// Login failed
header('Location: login.php');
exit;
