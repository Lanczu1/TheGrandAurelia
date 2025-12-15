<?php
session_start();
include('db.php');
include('security.php');

// Validate CSRF token
if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    $_SESSION['error'] = 'Invalid session. Please try again.';
    header('Location: reset_password.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $code = trim($_POST['code']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if (empty($email) || empty($code) || empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: reset_password.php");
        exit;
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: reset_password.php");
        exit;
    }

    if (strlen($password) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters long.";
        header("Location: reset_password.php");
        exit;
    }

    // Verify Code and Expiry
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND reset_code = ? AND reset_expiry > NOW()");
    $stmt->bind_param("ss", $email, $code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Valid code
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Update password and clear reset code
        $update_stmt = $conn->prepare("UPDATE users SET password = ?, reset_code = NULL, reset_expiry = NULL WHERE email = ?");
        $update_stmt->bind_param("ss", $hashed_password, $email);

        if ($update_stmt->execute()) {
            $_SESSION['success'] = "Password has been reset successfully! You can now login.";

            // Clear reset email from session
            unset($_SESSION['reset_email']);

            header("Location: login.php");
            exit;
        } else {
            $_SESSION['error'] = "Database error. Please try again.";
            header("Location: reset_password.php");
            exit;
        }
    } else {
        // Invalid or expired code
        $_SESSION['error'] = "Invalid or expired reset code. Please request a new one.";
        header("Location: reset_password.php");
        exit;
    }
} else {
    header("Location: reset_password.php");
    exit;
}
