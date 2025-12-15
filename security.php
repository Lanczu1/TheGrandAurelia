<?php
/**
 * Security Utility Functions
 * Provides security functions for input validation, sanitization, and protection
 */

/**
 * Sanitize input to prevent XSS attacks
 * @param string $data The input data to sanitize
 * @return string Sanitized data
 */
function sanitize_input($data) {
    if (is_null($data)) {
        return '';
    }
    
    // Remove whitespace from beginning and end
    $data = trim($data);
    
    // Remove backslashes
    $data = stripslashes($data);
    
    // Convert special characters to HTML entities
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    
    return $data;
}

/**
 * Validate and sanitize username
 * @param string $username The username to validate
 * @return array ['valid' => bool, 'value' => string, 'error' => string]
 */
function validate_username($username) {
    $username = trim($username);
    
    // Check if empty
    if (empty($username)) {
        return ['valid' => false, 'value' => '', 'error' => 'Username is required'];
    }
    
    // Check length
    if (strlen($username) < 3) {
        return ['valid' => false, 'value' => $username, 'error' => 'Username must be at least 3 characters long'];
    }
    
    if (strlen($username) > 30) {
        return ['valid' => false, 'value' => $username, 'error' => 'Username must be less than 30 characters'];
    }
    
    // Check for valid characters (alphanumeric and underscore only)
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        return ['valid' => false, 'value' => $username, 'error' => 'Username can only contain letters, numbers, and underscores'];
    }
    
    // Check for SQL injection patterns
    $dangerous_patterns = [
        "/(\b(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|EXECUTE|UNION|SCRIPT)\b)/i",
        "/([';\"])/",
        "/(<script|<\/script>)/i",
        "/(javascript:|onerror=|onclick=)/i"
    ];
    
    foreach ($dangerous_patterns as $pattern) {
        if (preg_match($pattern, $username)) {
            return ['valid' => false, 'value' => '', 'error' => 'Invalid characters detected in username'];
        }
    }
    
    return ['valid' => true, 'value' => $username, 'error' => ''];
}

/**
 * Validate email address
 * @param string $email The email to validate
 * @return array ['valid' => bool, 'value' => string, 'error' => string]
 */
function validate_email($email) {
    $email = trim($email);
    
    // Check if empty
    if (empty($email)) {
        return ['valid' => false, 'value' => '', 'error' => 'Email is required'];
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['valid' => false, 'value' => $email, 'error' => 'Invalid email format'];
    }
    
    // Check length
    if (strlen($email) > 255) {
        return ['valid' => false, 'value' => $email, 'error' => 'Email is too long'];
    }
    
    // Sanitize email
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    
    return ['valid' => true, 'value' => $email, 'error' => ''];
}

/**
 * Validate password strength
 * @param string $password The password to validate
 * @return array ['valid' => bool, 'strength' => string, 'error' => string]
 */
function validate_password($password) {
    if (empty($password)) {
        return ['valid' => false, 'strength' => 'weak', 'error' => 'Password is required'];
    }
    
    // Check minimum length
    if (strlen($password) < 8) {
        return ['valid' => false, 'strength' => 'weak', 'error' => 'Password must be at least 8 characters long'];
    }
    
    // Check maximum length
    if (strlen($password) > 128) {
        return ['valid' => false, 'strength' => 'weak', 'error' => 'Password is too long'];
    }
    
    // Check for dangerous patterns
    $dangerous_patterns = [
        "/(<script|<\/script>)/i",
        "/(javascript:|onerror=|onclick=)/i",
        "/(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC)/i"
    ];
    
    foreach ($dangerous_patterns as $pattern) {
        if (preg_match($pattern, $password)) {
            return ['valid' => false, 'strength' => 'weak', 'error' => 'Password contains invalid characters'];
        }
    }
    
    // Calculate password strength
    $strength = 'weak';
    $score = 0;
    
    // Length check
    if (strlen($password) >= 8) $score++;
    if (strlen($password) >= 12) $score++;
    
    // Character variety checks
    if (preg_match('/[a-z]/', $password)) $score++;
    if (preg_match('/[A-Z]/', $password)) $score++;
    if (preg_match('/[0-9]/', $password)) $score++;
    if (preg_match('/[^a-zA-Z0-9]/', $password)) $score++;
    
    if ($score >= 5) {
        $strength = 'strong';
    } elseif ($score >= 3) {
        $strength = 'medium';
    }
    
    return ['valid' => true, 'strength' => $strength, 'error' => ''];
}

/**
 * Generate CSRF token
 * @return string CSRF token
 */
function generate_csrf_token() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token The token to verify
 * @return bool True if valid, false otherwise
 */
function verify_csrf_token($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Rate limiting check
 * @param string $action The action being rate limited (e.g., 'login', 'register')
 * @param int $max_attempts Maximum attempts allowed
 * @param int $time_window Time window in seconds
 * @return array ['allowed' => bool, 'remaining' => int, 'reset_time' => int]
 */
function check_rate_limit($action, $max_attempts = 5, $time_window = 900) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $key = 'rate_limit_' . $action;
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $ip_key = $key . '_' . md5($ip);
    
    $current_time = time();
    
    if (!isset($_SESSION[$ip_key])) {
        $_SESSION[$ip_key] = [
            'attempts' => 1,
            'first_attempt' => $current_time,
            'last_attempt' => $current_time
        ];
        return ['allowed' => true, 'remaining' => $max_attempts - 1, 'reset_time' => $current_time + $time_window];
    }
    
    $rate_data = $_SESSION[$ip_key];
    
    // Reset if time window has passed
    if ($current_time - $rate_data['first_attempt'] > $time_window) {
        $_SESSION[$ip_key] = [
            'attempts' => 1,
            'first_attempt' => $current_time,
            'last_attempt' => $current_time
        ];
        return ['allowed' => true, 'remaining' => $max_attempts - 1, 'reset_time' => $current_time + $time_window];
    }
    
    // Check if max attempts reached
    if ($rate_data['attempts'] >= $max_attempts) {
        $reset_time = $rate_data['first_attempt'] + $time_window;
        return ['allowed' => false, 'remaining' => 0, 'reset_time' => $reset_time];
    }
    
    // Increment attempts
    $_SESSION[$ip_key]['attempts']++;
    $_SESSION[$ip_key]['last_attempt'] = $current_time;
    
    $remaining = $max_attempts - $_SESSION[$ip_key]['attempts'];
    return ['allowed' => true, 'remaining' => $remaining, 'reset_time' => $rate_data['first_attempt'] + $time_window];
}

/**
 * Log security event
 * @param string $event_type Type of event (e.g., 'failed_login', 'sql_injection_attempt')
 * @param string $details Additional details
 */
function log_security_event($event_type, $details = '') {
    $log_file = __DIR__ . '/logs/security.log';
    $log_dir = dirname($log_file);
    
    // Create logs directory if it doesn't exist
    if (!is_dir($log_dir)) {
        @mkdir($log_dir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $log_entry = "[$timestamp] [$event_type] IP: $ip | User-Agent: $user_agent | Details: $details\n";
    
    @file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}
?>

