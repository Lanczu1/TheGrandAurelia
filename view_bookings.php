<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'view_bookings.php';
    $_SESSION['error'] = "Please login to view your bookings";
    header('Location: login.php');
    exit;
}

include('header.php');
include('db.php');

// Get user's bookings
$user_id = $_SESSION['user_id'];

// Get room bookings
$bookings_query = "SELECT b.id, b.customer_name, b.check_in_date, b.check_out_date, b.status, b.total_price, 
                   r.room_name, r.price, 'room' as type
                   FROM bookings b 
                   JOIN rooms r ON b.room_id = r.id 
                   WHERE b.user_id = $user_id 
                   ORDER BY b.check_in_date DESC";

$bookings_result = mysqli_query($conn, $bookings_query);

// Get dining reservations
$dining_query = "SELECT id, customer_name, reservation_date, reservation_time, number_of_guests, 
                 venue, special_requests, status, created_at, 'dining' as type
                 FROM dining_reservations 
                 WHERE user_id = $user_id 
                 ORDER BY reservation_date DESC, reservation_time DESC";

$dining_result = mysqli_query($conn, $dining_query);

// Get spa bookings
$spa_query = "SELECT id, customer_name, spa_date AS reservation_date, spa_time AS reservation_time, guests AS number_of_guests,
                 treatment AS venue, special_requests, status, created_at, 'spa' as type
                 FROM spa_bookings
                 WHERE user_id = $user_id
                 ORDER BY spa_date DESC, spa_time DESC";

$spa_result = mysqli_query($conn, $spa_query);

// Combine results
$all_bookings = [];
while ($row = mysqli_fetch_assoc($bookings_result)) {
    $all_bookings[] = $row;
}
while ($row = mysqli_fetch_assoc($dining_result)) {
    $all_bookings[] = $row;
}
while ($row = mysqli_fetch_assoc($spa_result)) {
    $all_bookings[] = $row;
}

// Spa treatment price map (used to calculate totals for spa bookings)
// Keys are lowercased treatment names for case-insensitive lookup.
$spa_price_map = [
    'swedish massage' => 120.00,
    'deep tissue massage' => 140.00,
    'hot stone massage' => 160.00,
    'aromatherapy massage' => 150.00,
    // Facial examples (add or update as needed)
    'hydrating facial' => 100.00,
    'anti-aging facial' => 120.00
];

// Sort by date (most recent first)
usort($all_bookings, function ($a, $b) {
    $dateA = $a['type'] === 'room' ? $a['check_in_date'] : $a['reservation_date'];
    $dateB = $b['type'] === 'room' ? $b['check_in_date'] : $b['reservation_date'];
    return strtotime($dateB) - strtotime($dateA);
});

$has_bookings = count($all_bookings) > 0;

// Function to get status badge
function getStatusBadge($status)
{
    // Convert to lowercase to handle case sensitivity issues
    $status = strtolower($status);

    switch ($status) {
        case 'pending':
            return '<span class="badge booking-badge booking-pending">Pending Approval</span>';
        case 'approved':
            return '<span class="badge booking-badge booking-approved">Approved</span>';
        case 'rejected':
            return '<span class="badge booking-badge booking-rejected">Rejected</span>';
        case 'cancelled':
            return '<span class="badge booking-badge booking-cancelled">Cancelled</span>';
        default:
            return '<span class="badge booking-badge booking-unknown">Unknown</span>';
    }
}
?>

<style>
    .bookings-section {
        padding: 80px 0;
        background: linear-gradient(to bottom, #f8f9fa, #ffffff);
    }

    .bookings-card {
        border: none;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .bookings-card:hover {
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
    }

    .bookings-header {
        background-color: #1a1a1a;
        color: white;
        padding: 25px;
        text-align: center;
        position: relative;
    }

    .bookings-header h2 {
        margin: 0;
        font-weight: 700;
        font-size: 2.2rem;
        position: relative;
    }

    .bookings-header:after {
        content: "";
        display: block;
        width: 50px;
        height: 4px;
        background-color: #ffd700;
        margin: 15px auto 0;
    }

    .bookings-body {
        padding: 40px;
    }

    .bookings-table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }

    .bookings-table th {
        background-color: #f8f9fa;
        color: #333;
        font-weight: 600;
        padding: 15px;
        text-align: left;
        border-bottom: 2px solid #dee2e6;
    }

    .bookings-table td {
        padding: 15px;
        vertical-align: middle;
        border-bottom: 1px solid #eee;
    }

    .bookings-table tbody tr {
        transition: all 0.2s ease;
    }

    .bookings-table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .booking-badge {
        padding: 8px 12px;
        border-radius: 30px;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-block;
        text-align: center;
        min-width: 100px;
    }

    .booking-pending {
        background-color: #fff8e1;
        color: #f57c00;
    }

    .booking-approved {
        background-color: #e8f5e9;
        color: #2e7d32;
    }

    .booking-rejected {
        background-color: #ffebee;
        color: #c62828;
    }

    .booking-cancelled {
        background-color: #f5f5f5;
        color: #616161;
    }

    .booking-unknown {
        background-color: #e0e0e0;
        color: #424242;
    }

    .booking-info {
        font-weight: 600;
    }

    .booking-date {
        color: #555;
    }

    .booking-price {
        font-weight: 700;
        color: #1a1a1a;
    }

    .action-btn {
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        transition: all 0.3s;
    }

    .btn-edit {
        background-color: #1a76d2;
        border-color: #1a76d2;
    }

    .btn-edit:hover {
        background-color: #1565c0;
        transform: translateY(-2px);
        box-shadow: 0 3px 8px rgba(21, 101, 192, 0.3);
    }

    .btn-cancel {
        background-color: #e53935;
        border-color: #e53935;
    }

    .btn-cancel:hover {
        background-color: #d32f2f;
        transform: translateY(-2px);
        box-shadow: 0 3px 8px rgba(211, 47, 47, 0.3);
    }

    .empty-bookings {
        padding: 60px 30px;
        text-align: center;
    }

    .empty-icon {
        font-size: 5rem;
        color: #bdbdbd;
        margin-bottom: 20px;
    }

    .btn-browse {
        background-color: #1a1a1a;
        border: none;
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 600;
        letter-spacing: 0.5px;
        transition: all 0.3s;
    }

    .btn-browse:hover {
        background-color: #333;
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
</style>

<div class="bookings-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-11">
                <div class="bookings-card">
                    <div class="bookings-header">
                        <h2><i class="fas fa-calendar-check me-2"></i>My Bookings & Reservations</h2>
                    </div>

                    <div class="bookings-body">
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <?php
                                echo htmlspecialchars($_SESSION['success']);
                                unset($_SESSION['success']);
                                ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <?php
                                echo htmlspecialchars($_SESSION['error']);
                                unset($_SESSION['error']);
                                ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($has_bookings): ?>
                            <div class="table-responsive">
                                <table class="bookings-table">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Details</th>
                                            <th>Guest Name</th>
                                            <th>Date/Check-in</th>
                                            <th>Check-out/Time</th>
                                            <th>Duration/Guests</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($all_bookings as $booking): ?>
                                            <?php
                                            $type = $booking['type'];
                                            $status = strtolower($booking['status']);

                                            if ($type === 'room') {
                                                // Room booking
                                                $check_in = new DateTime($booking['check_in_date']);
                                                $check_out = new DateTime($booking['check_out_date']);
                                                $nights = $check_out->diff($check_in)->days;

                                                $today = new DateTime();
                                                $is_future_booking = $check_in > $today;
                                            } elseif ($type === 'dining') {
                                                // Dining reservation
                                                $reservation_date = new DateTime($booking['reservation_date']);
                                                $today = new DateTime();
                                                $is_future_booking = $reservation_date >= $today;

                                                // Format venue name
                                                $venue_names = [
                                                    'restaurant' => 'The Aurelia Restaurant',
                                                    'lounge' => 'The Skyline Lounge',
                                                    'grand_room' => 'The Grand Room',
                                                    'wine_cellar' => 'The Wine Cellar'
                                                ];
                                                $venue_display = isset($venue_names[$booking['venue']]) ? $venue_names[$booking['venue']] : ucfirst(str_replace('_', ' ', $booking['venue']));
                                            } elseif ($type === 'spa') {
                                                // Spa reservation
                                                $reservation_date = new DateTime($booking['reservation_date']);
                                                $today = new DateTime();
                                                $is_future_booking = $reservation_date >= $today;
                                                // For spa, 'venue' column contains the treatment name
                                                $venue_display = htmlspecialchars($booking['venue']);
                                            } else {
                                                // Fallback treat as dining-like
                                                $reservation_date = new DateTime($booking['reservation_date']);
                                                $today = new DateTime();
                                                $is_future_booking = $reservation_date >= $today;
                                                $venue_display = ucfirst(str_replace('_', ' ', $booking['venue'] ?? ''));
                                            }
                                            ?>
                                            <tr>
                                                <td>
                                                    <?php if ($type === 'room'): ?>
                                                        <span class="badge bg-primary"><i class="fas fa-hotel me-1"></i>Room</span>
                                                    <?php elseif ($type === 'dining'): ?>
                                                        <span class="badge bg-warning text-dark"><i class="fas fa-utensils me-1"></i>Dining</span>
                                                    <?php elseif ($type === 'spa'): ?>
                                                        <span class="badge bg-info text-dark"><i class="fas fa-spa me-1"></i>Spa</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Other</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="booking-info">
                                                    <?php if ($type === 'room'): ?>
                                                        <?php echo htmlspecialchars($booking['room_name']); ?>
                                                    <?php else: ?>
                                                        <?php echo htmlspecialchars($venue_display); ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($booking['customer_name']); ?></td>
                                                <td class="booking-date">
                                                    <?php if ($type === 'room'): ?>
                                                        <?php echo date('M d, Y h:i A', strtotime($booking['check_in_date'])); ?>
                                                    <?php else: ?>
                                                        <?php echo date('M d, Y', strtotime($booking['reservation_date'])); ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="booking-date">
                                                    <?php if ($type === 'room'): ?>
                                                        <?php echo date('M d, Y h:i A', strtotime($booking['check_out_date'])); ?>
                                                    <?php else: ?>
                                                        <?php echo date('g:i A', strtotime($booking['reservation_time'])); ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($type === 'room'): ?>
                                                        <?php echo $nights; ?> night<?php echo $nights != 1 ? 's' : ''; ?>
                                                    <?php else: ?>
                                                        <?php echo $booking['number_of_guests']; ?> guest<?php echo $booking['number_of_guests'] != 1 ? 's' : ''; ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="booking-price">
                                                    <?php if ($type === 'room'): ?>
                                                        <?php
                                                        if (isset($booking['total_price'])) {
                                                            echo '$' . number_format($booking['total_price'], 2);
                                                        } else {
                                                            echo '$' . number_format($nights * $booking['price'], 2);
                                                        }
                                                        ?>
                                                    <?php elseif ($type === 'spa'): ?>
                                                        <?php
                                                        $treatmentKey = strtolower(trim($booking['venue']));
                                                        $guests = isset($booking['number_of_guests']) ? max(1, (int)$booking['number_of_guests']) : 1;
                                                        if (isset($spa_price_map[$treatmentKey])) {
                                                            $pricePer = $spa_price_map[$treatmentKey];
                                                            $total = $pricePer * $guests;
                                                            echo '$' . number_format($total, 2) . ' <br><small class="text-muted">($' . number_format($pricePer, 2) . ' each)</small>';
                                                        } else {
                                                            echo '<span class="text-muted">N/A</span>';
                                                        }
                                                        ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">N/A</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    // Map confirmed to approved for both dining and spa; otherwise show status
                                                    if (($type === 'dining' || $type === 'spa') && $status === 'confirmed') {
                                                        echo getStatusBadge('approved');
                                                    } else {
                                                        echo getStatusBadge($status);
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php if ($type === 'room'): ?>
                                                        <?php if ($status === 'pending'): ?>
                                                            <div class="d-flex gap-2">
                                                                <a href="edit_booking.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm action-btn btn-edit">
                                                                    <i class="fas fa-edit"></i> Edit
                                                                </a>
                                                                <button type="button"
                                                                    class="btn btn-sm action-btn btn-cancel"
                                                                    onclick="confirmCancel(<?php echo $booking['id']; ?>)">
                                                                    <i class="fas fa-times"></i> Cancel
                                                                </button>
                                                            </div>
                                                        <?php elseif ($status === 'approved' && $is_future_booking): ?>
                                                            <span class="badge booking-badge booking-approved">Confirmed</span>
                                                        <?php elseif ($status === 'rejected'): ?>
                                                            <span class="badge booking-badge booking-rejected">Booking Rejected</span>
                                                        <?php elseif ($status === 'cancelled'): ?>
                                                            <span class="badge booking-badge booking-cancelled">Booking Cancelled</span>
                                                        <?php elseif (!$is_future_booking): ?>
                                                            <span class="badge booking-badge booking-cancelled">Past Booking</span>
                                                        <?php else: ?>
                                                            <span class="badge booking-badge booking-unknown">Status: <?php echo ucfirst($status); ?></span>
                                                        <?php endif; ?>
                                                    <?php elseif ($type === 'dining' || $type === 'spa'): ?>
                                                        <?php if ($status === 'pending'): ?>
                                                            <div class="d-flex gap-2">
                                                                <a href="edit_reservation.php?id=<?php echo $booking['id']; ?>&type=<?php echo $type; ?>" class="btn btn-sm action-btn btn-edit">
                                                                    <i class="fas fa-edit"></i> Edit
                                                                </a>
                                                                <button type="button"
                                                                    class="btn btn-sm action-btn btn-cancel"
                                                                    onclick="confirmCancelReservation(<?php echo $booking['id']; ?>, '<?php echo $type; ?>')">
                                                                    <i class="fas fa-times"></i> Cancel
                                                                </button>
                                                            </div>
                                                        <?php elseif ($status === 'confirmed' && $is_future_booking): ?>
                                                            <span class="badge booking-badge booking-approved"><i class="fas fa-check me-1"></i>Confirmed</span>
                                                        <?php elseif ($status === 'cancelled'): ?>
                                                            <span class="badge booking-badge booking-cancelled">Cancelled</span>
                                                        <?php elseif (!$is_future_booking): ?>
                                                            <span class="badge booking-badge booking-cancelled">Past Reservation</span>
                                                        <?php else: ?>
                                                            <span class="badge booking-badge booking-unknown">Status: <?php echo ucfirst($status); ?></span>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="badge booking-badge booking-unknown">Status: <?php echo ucfirst($status); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-bookings">
                                <i class="fas fa-calendar-times empty-icon"></i>
                                <h3 class="mt-3 mb-2">No Bookings or Reservations Found</h3>
                                <p class="text-muted mb-4">You don't have any room bookings or dining reservations yet.</p>
                                <div class="d-flex gap-3 justify-content-center">
                                    <a href="view_rooms.php" class="btn btn-primary btn-browse">
                                        <i class="fas fa-hotel me-2"></i>Browse Rooms
                                    </a>
                                    <a href="dining.php" class="btn btn-primary btn-browse">
                                        <i class="fas fa-utensils me-2"></i>Make Dining Reservation
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmCancel(bookingId) {
        if (confirm('Are you sure you want to cancel this booking?')) {
            window.location.href = 'cancel_booking.php?id=' + bookingId;
        }
    }
</script>

<script>
    function confirmCancelReservation(id, type) {
        if (confirm('Are you sure you want to cancel this reservation?')) {
            window.location.href = 'cancel_reservation.php?id=' + id + '&type=' + encodeURIComponent(type);
        }
    }
</script> 