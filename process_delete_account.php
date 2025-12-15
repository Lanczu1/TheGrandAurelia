<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require('db.php');

$user_id = $_SESSION['user_id'];
$confirmation = $_POST['confirmation'];

// Safety check
if ($confirmation !== 'DELETE') {
    $_SESSION['error'] = 'Incorrect confirmation code. Account was NOT deleted.';
    header('Location: settings.php');
    exit;
}

// Start Transaction to ensure cleanup
$conn->begin_transaction();

try {
    // 1. Delete user files
    $profile_img = __DIR__ . '/profileimg/' . $user_id . '.jpg';
    $cover_img = __DIR__ . '/coverimg/' . $user_id . '.jpg';

    if (file_exists($profile_img)) @unlink($profile_img);
    if (file_exists($cover_img)) @unlink($cover_img);

    // 2. Delete User Data (Handle Foreign Keys Explicitly)
    // We must delete children records first because FK constraints are RESTRICT (not CASCADE)

    // Delete Dining Reservations
    $stmt_dining = $conn->prepare("DELETE FROM dining_reservations WHERE user_id = ?");
    $stmt_dining->bind_param("i", $user_id);
    $stmt_dining->execute();
    $stmt_dining->close();

    // Delete Spa Bookings
    $stmt_spa = $conn->prepare("DELETE FROM spa_bookings WHERE user_id = ?");
    $stmt_spa->bind_param("i", $user_id);
    $stmt_spa->execute();
    $stmt_spa->close();

    // Delete Room Bookings
    $stmt_bookings = $conn->prepare("DELETE FROM bookings WHERE user_id = ?");
    $stmt_bookings->bind_param("i", $user_id);
    $stmt_bookings->execute();
    $stmt_bookings->close();

    // 3. Delete User
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);

    if (!$stmt->execute()) {
        throw new Exception("Failed to delete user record: " . $stmt->error);
    }

    $conn->commit();

    // 4. Kill Session
    session_destroy();
    session_start();
    $_SESSION['success'] = 'Your account has been successfully deleted. We are sorry to see you go.';
    header('Location: login.php?notify=account_deleted');
    exit;
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = 'System Error: ' . $e->getMessage();
    header('Location: settings.php');
    exit;
}
