<?php

/**
 * Database Migration Script - Add Email Verification
 * Run this file once in your browser to add verification fields
 */

include('db.php');

echo "<h2>Email Verification Database Migration</h2>";
echo "<p>Adding email verification fields to users table...</p>";

// Check if columns already exist
$check = $conn->query("SHOW COLUMNS FROM users LIKE 'is_verified'");
if ($check && $check->num_rows > 0) {
    echo "<div style='color: orange;'>⚠️ Verification columns already exist. Skipping migration.</div>";
    exit;
}

// Add verification columns
$sql1 = "ALTER TABLE users 
         ADD COLUMN is_verified TINYINT(1) DEFAULT 0,
         ADD COLUMN verification_code VARCHAR(6) DEFAULT NULL,
         ADD COLUMN verification_code_expiry DATETIME DEFAULT NULL";

if ($conn->query($sql1) === TRUE) {
    echo "<div style='color: green;'>✓ Successfully added verification columns!</div>";

    // Add index
    $sql2 = "ALTER TABLE users ADD INDEX idx_verification_code (verification_code)";
    if ($conn->query($sql2) === TRUE) {
        echo "<div style='color: green;'>✓ Successfully added index!</div>";
    } else {
        echo "<div style='color: orange;'>⚠️ Index may already exist: " . $conn->error . "</div>";
    }

    // Optional: Auto-verify existing users
    echo "<h3>Optional: Verify Existing Users</h3>";
    echo "<p>Click the button below to automatically verify all existing users:</p>";
    echo "<form method='post'>";
    echo "<button type='submit' name='verify_existing' style='padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer;'>Verify All Existing Users</button>";
    echo "</form>";

    if (isset($_POST['verify_existing'])) {
        $sql3 = "UPDATE users SET is_verified = 1 WHERE verification_code IS NULL";
        if ($conn->query($sql3) === TRUE) {
            $affected = $conn->affected_rows;
            echo "<div style='color: green;'>✓ Verified $affected existing user(s)!</div>";
        } else {
            echo "<div style='color: red;'>✗ Error: " . $conn->error . "</div>";
        }
    }

    echo "<hr>";
    echo "<div style='color: green; font-weight: bold;'>✓ Migration completed successfully!</div>";
    echo "<p><a href='register.php'>Go to Registration Page</a></p>";
} else {
    echo "<div style='color: red;'>✗ Error adding columns: " . $conn->error . "</div>";
}

$conn->close();
?>

<style>
    body {
        font-family: Arial, sans-serif;
        max-width: 800px;
        margin: 50px auto;
        padding: 20px;
        background: #f5f5f5;
    }

    h2 {
        color: #333;
    }

    div {
        padding: 10px;
        margin: 10px 0;
        border-radius: 5px;
        background: white;
    }
</style>