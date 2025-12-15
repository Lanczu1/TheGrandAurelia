<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in 
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'notifications.php';
    $_SESSION['error'] = "Please login to view your notifications";
    header('Location: login.php');
    exit;
}

include('db.php');

// Mark as read function - must be before any HTML output
if (isset($_GET['mark_read']) && $_GET['mark_read'] == 'all') {
    $user_id = $_SESSION['user_id'];

    // Check if central notifications table exists
    $check_table = @mysqli_query($conn, "SHOW TABLES LIKE 'notifications'");
    $table_exists = ($check_table && mysqli_num_rows($check_table) > 0);

    if ($table_exists) {
        // Mark central notifications as read
        $update = "UPDATE notifications SET seen = 1 WHERE user_id = $user_id AND seen = 0";
        @mysqli_query($conn, $update);
    } else {
        // Fall back to legacy per-table updates
        $update_rooms = "UPDATE bookings SET notification_seen = 1 
                        WHERE user_id = $user_id AND notification_seen = 0";
        @mysqli_query($conn, $update_rooms);

        $update_dining = "UPDATE dining_reservations SET notification_seen = 1 
                          WHERE user_id = $user_id AND notification_seen = 0";
        @mysqli_query($conn, $update_dining);

        $update_spa = "UPDATE spa_bookings SET notification_seen = 1 
                       WHERE user_id = $user_id AND notification_seen = 0";
        @mysqli_query($conn, $update_spa);
    }

    // Redirect to remove the query string
    header('Location: notifications.php');
    exit;
}

// Include header after redirect logic
include('header.php');

// Get user's ID
$user_id = $_SESSION['user_id'];

// Load notifications from central notifications table
$notifications = [];
$notif_q = "SELECT * FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC";
$notif_res = mysqli_query($conn, $notif_q);
if ($notif_res) {
    while ($row = mysqli_fetch_assoc($notif_res)) {
        $notifications[] = $row;
    }
}

// Unread count (central)
$unread_count = 0;
$unread_res = mysqli_query($conn, "SELECT COUNT(*) as c FROM notifications WHERE user_id = $user_id AND seen = 0");
$unread_count = ($unread_res) ? (int)mysqli_fetch_assoc($unread_res)['c'] : 0;
?>

<style>
    .notifications-section {
        padding: 80px 0;
        background: linear-gradient(to bottom, #f8f9fa, #ffffff);
    }

    .notifications-card {
        border: none;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .notifications-card:hover {
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
    }

    .notifications-header {
        background-color: #1a1a1a;
        color: white;
        padding: 25px;
        position: relative;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .notifications-header h2 {
        margin: 0;
        font-weight: 700;
        font-size: 2rem;
        position: relative;
    }

    .notifications-header:after {
        content: "";
        display: block;
        width: 50px;
        height: 4px;
        background-color: #ffd700;
        margin-top: 15px;
        position: absolute;
        bottom: 12px;
        left: 25px;
    }

    .notifications-badge {
        background-color: #ffd700;
        color: #1a1a1a;
        font-weight: 700;
        font-size: 0.9rem;
        padding: 8px 15px;
        border-radius: 30px;
    }

    .notifications-body {
        padding: 0;
    }

    .notification-item {
        padding: 20px 25px;
        border-bottom: 1px solid #eee;
        transition: all 0.2s ease;
    }

    .notification-item:hover {
        background-color: #f9f9f9;
    }

    .notification-item:last-child {
        border-bottom: none;
    }

    .notification-header {
        display: flex;
        align-items: center;
        margin-bottom: 12px;
    }

    .notification-icon {
        font-size: 1.2rem;
        margin-right: 10px;
    }

    .notification-icon.approved {
        color: #2e7d32;
    }

    .notification-icon.rejected {
        color: #c62828;
    }

    .notification-title {
        font-weight: 600;
        color: #333;
        font-size: 1.1rem;
        margin: 0;
    }

    .notification-date {
        margin-left: auto;
        color: #777;
        font-size: 0.9rem;
    }

    .notification-content {
        color: #555;
        margin-bottom: 10px;
    }

    .notification-details {
        background-color: #f5f5f5;
        border-radius: 8px;
        padding: 12px 15px;
        font-size: 0.9rem;
        color: #666;
    }

    .notification-detail-item {
        display: flex;
        margin-bottom: 5px;
    }

    .notification-detail-item:last-child {
        margin-bottom: 0;
    }

    .notification-detail-label {
        font-weight: 600;
        width: 90px;
    }

    .empty-notifications {
        padding: 60px 30px;
        text-align: center;
    }

    .empty-icon {
        font-size: 5rem;
        color: #bdbdbd;
        margin-bottom: 20px;
    }

    .mark-read-btn {
        display: block;
        background-color: #f8f9fa;
        border: 1px solid #ddd;
        color: #555;
        font-weight: 600;
        padding: 12px;
        text-align: center;
        transition: all 0.3s;
        text-decoration: none;
    }

    .mark-read-btn:hover {
        background-color: #e9ecef;
        color: #333;
    }

    .view-bookings-btn {
        background-color: transparent;
        border: 1px solid #1a1a1a;
        color: #1a1a1a;
        font-weight: 600;
        padding: 12px 24px;
        border-radius: 10px;
        transition: all 0.3s;
    }

    .view-bookings-btn:hover {
        background-color: #1a1a1a;
        color: white;
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
</style>

<div class="notifications-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="notifications-card">
                    <div class="notifications-header">
                        <h2>Your Notifications</h2>
                        <?php if ($unread_count > 0): ?>
                            <span class="notifications-badge"><?php echo $unread_count; ?> New</span>
                        <?php endif; ?>
                    </div>

                    <div class="notifications-body">
                        <?php if (!empty($notifications)): ?>
                            <div class="notification-list">
                                <?php foreach ($notifications as $notification): ?>
                                    <?php
                                    $type = $notification['type'] ?? 'room';
                                    $status = $notification['status'] ?? '';
                                    $is_positive = in_array($status, ['approved', 'confirmed']);
                                    $status_icon = $is_positive ? 'check-circle' : 'times-circle';
                                    $status_class = $is_positive ? 'approved' : 'rejected';
                                    $date_formatted = date('M j, Y', strtotime($notification['created_at']));
                                    $message = $notification['message'] ?? '';
                                    $url = $notification['url'] ?? '';
                                    ?>

                                    <div class="notification-item">
                                        <div class="notification-header">
                                            <i class="fas fa-<?php echo $status_icon; ?> notification-icon <?php echo $status_class; ?>"></i>
                                            <span class="notification-title"><?php echo ($type === 'dining') ? 'Dining Reservation' : (($type === 'spa') ? 'Spa Reservation' : 'Booking'); ?></span>
                                            <span class="notification-date"><?php echo $date_formatted; ?></span>
                                        </div>

                                        <div class="notification-content">
                                            <?php echo htmlspecialchars($message); ?>
                                        </div>

                                        <?php if (!empty($url)): ?>
                                            <div class="mt-2">
                                                <a href="<?php echo htmlspecialchars($url); ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <a href="?mark_read=all" class="mark-read-btn">
                                <i class="fas fa-check-double me-2"></i>Mark All as Read
                            </a>
                        <?php else: ?>
                            <div class="empty-notifications">
                                <i class="fas fa-bell-slash empty-icon"></i>
                                <h3 class="mt-3 mb-2">No Notifications</h3>
                                <p class="text-muted mb-0">You have no notifications at this time.</p>
                                <p class="text-muted mb-0">We'll notify you when there are updates to your bookings.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <p class="text-muted mb-4">
                        <i class="fas fa-info-circle me-1"></i>
                        Notifications show status updates for your bookings from the last 30 days
                    </p>
                    <a href="view_bookings.php" class="btn view-bookings-btn">
                        <i class="fas fa-list me-2"></i>View All My Bookings
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>