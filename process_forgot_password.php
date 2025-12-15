<?php
session_start();
include('db.php');
include('send_email.php');
include('security.php'); // For CSRF validation

// Validate CSRF token
if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    $_SESSION['error'] = 'Invalid session. Please try again.';
    header('Location: forgot_password.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    // Basic validation
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Please enter a valid email address.";
        header("Location: forgot_password.php");
        exit;
    }

    // Check if email exists
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Generate reset code
        $reset_code = generate_verification_code();

        // Save code to DB first
        // Use MySQL time for consistency
        $update_stmt = $conn->prepare("UPDATE users SET reset_code = ?, reset_expiry = DATE_ADD(NOW(), INTERVAL 30 MINUTE) WHERE email = ?");
        $update_stmt->bind_param("ss", $reset_code, $email);

        if ($update_stmt->execute()) {
            // Send email
            $username = $user['username'];
            $email_result = send_password_reset_email($email, $username, $reset_code);

            if ($email_result['success']) {
                // Email sent successfully
                $_SESSION['success'] = "A reset code has been sent to your email address.";

                // Store email in session to pre-fill on reset page
                $_SESSION['reset_email'] = $email;

                header("Location: reset_password.php");
                exit;
            } else {
                // Email failed to send - Remove the code ("automatically remove")
                $clear_stmt = $conn->prepare("UPDATE users SET reset_code = NULL, reset_expiry = NULL WHERE email = ?");
                $clear_stmt->bind_param("s", $email);
                $clear_stmt->execute();

                $_SESSION['error'] = "Failed to send reset email. Please try again later. " . $email_result['message'];
                header("Location: forgot_password.php");
                exit;
            }
        } else {
            $_SESSION['error'] = "Database error. Please try again.";
            header("Location: forgot_password.php");
            exit;
        }
    } else {
        // Email not found
        // For security, we might want to show a generic message, but for this specific request and UX, 
        // the user seems to want to know if it worked or not? 
        // "if not i send the code we will automatically remove" usually implies logic about the code.
        // I'll show "Email not found" for now as it's friendlier for development, or a generic one.
        // Let's stick to helpful messages.
        $_SESSION['error'] = "No account found with this email address.";
        header("Location: forgot_password.php");
        exit;
    }
} else {
    header("Location: forgot_password.php");
    exit;
}
