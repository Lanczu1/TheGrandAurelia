<?php
// Email sending utility using Gmail SMTP
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader (if you're using Composer)
// If not using Composer, you'll need to include PHPMailer files manually
// Download PHPMailer from: https://github.com/PHPMailer/PHPMailer

require 'vendor/autoload.php'; // Comment this out if not using Composer

/**
 * Send verification email
 * @param string $to_email Recipient email address
 * @param string $to_name Recipient name
 * @param string $verification_code Verification code
 * @return array Result with 'success' boolean and 'message' string
 */
function send_verification_email($to_email, $to_name, $verification_code)
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'lanceoboza777@gmail.com';
        $mail->Password   = 'uewb lgrv cwxs egxz'; // App password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('lanceoboza777@gmail.com', 'The Grand Aurelia');
        $mail->addAddress($to_email, $to_name);
        $mail->addReplyTo('lanceoboza777@gmail.com', 'Support');

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Email Verification - The Grand Aurelia';

        // HTML email body
        $mail->Body = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    margin: 0;
                    padding: 0;
                    background-color: #f4f4f4;
                }
                .container {
                    max-width: 600px;
                    margin: 20px auto;
                    background: #ffffff;
                    border-radius: 10px;
                    overflow: hidden;
                    box-shadow: 0 0 20px rgba(0,0,0,0.1);
                }
                .header {
                    background: linear-gradient(135deg, #1a1a1a 0%, #333 100%);
                    color: white;
                    padding: 30px 20px;
                    text-align: center;
                }
                .header h1 {
                    margin: 0;
                    font-size: 28px;
                }
                .content {
                    padding: 40px 30px;
                }
                .verification-box {
                    background: #f8f9fa;
                    border-left: 4px solid #ffd700;
                    padding: 20px;
                    margin: 20px 0;
                    border-radius: 5px;
                }
                .verification-code {
                    font-size: 32px;
                    font-weight: bold;
                    color: #1a1a1a;
                    letter-spacing: 5px;
                    text-align: center;
                    padding: 15px;
                    background: white;
                    border-radius: 5px;
                    margin: 15px 0;
                }
                .button {
                    display: inline-block;
                    padding: 12px 30px;
                    background: #1a1a1a;
                    color: white;
                    text-decoration: none;
                    border-radius: 5px;
                    margin: 20px 0;
                }
                .footer {
                    background: #f8f9fa;
                    padding: 20px;
                    text-align: center;
                    font-size: 12px;
                    color: #666;
                }
                .warning {
                    color: #dc3545;
                    font-size: 14px;
                    margin-top: 15px;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🏨 The Grand Aurelia</h1>
                </div>
                <div class="content">
                    <h2>Welcome, ' . htmlspecialchars($to_name) . '!</h2>
                    <p>Thank you for registering with The Grand Aurelia. To complete your registration, please verify your email address.</p>
                    
                    <div class="verification-box">
                        <p style="margin: 0 0 10px 0;"><strong>Your Verification Code:</strong></p>
                        <div class="verification-code">' . $verification_code . '</div>
                        <p style="margin: 10px 0 0 0; font-size: 14px; color: #666;">This code will expire in 24 hours.</p>
                    </div>
                    
                    <p>Enter this code on the verification page to activate your account.</p>
                    
                    <p class="warning">⚠️ If you didn\'t create an account with The Grand Aurelia, please ignore this email.</p>
                </div>
                <div class="footer">
                    <p>© ' . date('Y') . ' The Grand Aurelia. All rights reserved.</p>
                    <p>This is an automated email. Please do not reply to this message.</p>
                </div>
            </div>
        </body>
        </html>
        ';

        // Plain text version for email clients that don't support HTML
        $mail->AltBody = "Welcome to The Grand Aurelia!\n\n"
            . "Your verification code is: $verification_code\n\n"
            . "This code will expire in 24 hours.\n\n"
            . "If you didn't create an account with The Grand Aurelia, please ignore this email.";

        $mail->send();
        return [
            'success' => true,
            'message' => 'Verification email has been sent!'
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Email could not be sent. Error: ' . $mail->ErrorInfo
        ];
    }
}

/**
 * Generate a random 6-digit verification code
 * @return string 6-digit code
 */

/**
 * Send password reset email
 * @param string $to_email Recipient email address
 * @param string $to_name Recipient name
 * @param string $reset_code Reset code
 * @return array Result with 'success' boolean and 'message' string
 */
function send_password_reset_email($to_email, $to_name, $reset_code)
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'lanceoboza777@gmail.com';
        $mail->Password   = 'uewb lgrv cwxs egxz'; // App password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('lanceoboza777@gmail.com', 'The Grand Aurelia');
        $mail->addAddress($to_email, $to_name);
        $mail->addReplyTo('lanceoboza777@gmail.com', 'Support');

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request - The Grand Aurelia';

        // HTML email body
        $mail->Body = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    margin: 0;
                    padding: 0;
                    background-color: #f4f4f4;
                }
                .container {
                    max-width: 600px;
                    margin: 20px auto;
                    background: #ffffff;
                    border-radius: 10px;
                    overflow: hidden;
                    box-shadow: 0 0 20px rgba(0,0,0,0.1);
                }
                .header {
                    background: linear-gradient(135deg, #1a1a1a 0%, #333 100%);
                    color: white;
                    padding: 30px 20px;
                    text-align: center;
                }
                .header h1 {
                    margin: 0;
                    font-size: 28px;
                }
                .content {
                    padding: 40px 30px;
                }
                .reset-box {
                    background: #f8f9fa;
                    border-left: 4px solid #ffd700;
                    padding: 20px;
                    margin: 20px 0;
                    border-radius: 5px;
                }
                .reset-code {
                    font-size: 32px;
                    font-weight: bold;
                    color: #1a1a1a;
                    letter-spacing: 5px;
                    text-align: center;
                    padding: 15px;
                    background: white;
                    border-radius: 5px;
                    margin: 15px 0;
                }
                .footer {
                    background: #f8f9fa;
                    padding: 20px;
                    text-align: center;
                    font-size: 12px;
                    color: #666;
                }
                .warning {
                    color: #dc3545;
                    font-size: 14px;
                    margin-top: 15px;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🏨 The Grand Aurelia</h1>
                </div>
                <div class="content">
                    <h2>Password Reset Request</h2>
                    <p>Hello ' . htmlspecialchars($to_name) . ',</p>
                    <p>We received a request to reset your password. Use the code below to reset it:</p>
                    
                    <div class="reset-box">
                        <p style="margin: 0 0 10px 0;"><strong>Your Reset Code:</strong></p>
                        <div class="reset-code">' . $reset_code . '</div>
                        <p style="margin: 10px 0 0 0; font-size: 14px; color: #666;">This code will expire in 30 minutes.</p>
                    </div>
                    
                    <p class="warning">⚠️ If you didn\'t request a password reset, you can safely ignore this email.</p>
                </div>
                <div class="footer">
                    <p>© ' . date('Y') . ' The Grand Aurelia. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ';

        // Plain text version
        $mail->AltBody = "Password Reset Request\n\n"
            . "Hello $to_name,\n\n"
            . "Your password reset code is: $reset_code\n\n"
            . "This code will expire in 30 minutes.\n\n"
            . "If you didn't request a password reset, please ignore this email.";

        $mail->send();
        return [
            'success' => true,
            'message' => 'Reset code has been sent!'
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Email could not be sent. Error: ' . $mail->ErrorInfo
        ];
    }
}

/**
 * Generate a random 6-digit verification code
 * @return string 6-digit code
 */
function generate_verification_code()
{
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
}
