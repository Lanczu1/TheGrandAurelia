<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

include('../header.php');
include('sidebar.php');
include('../db.php');

// Get pending bookings with error handling
$pending_bookings_query = "SELECT b.*, r.room_name, u.username 
                          FROM bookings b 
                          JOIN rooms r ON b.room_id = r.id 
                          JOIN users u ON b.user_id = u.id 
                          WHERE b.status = 'pending' 
                          ORDER BY b.created_at DESC";
$pending_bookings = mysqli_query($conn, $pending_bookings_query);

if (!$pending_bookings) {
    $error_message = "Error fetching bookings: " . mysqli_error($conn);
}

// Get hotel statistics
// Total rooms
$total_rooms_query = "SELECT COUNT(*) as total FROM rooms";
$total_rooms_result = mysqli_query($conn, $total_rooms_query);
$total_rooms = mysqli_fetch_assoc($total_rooms_result)['total'] ?? 0;

// Currently booked rooms
$booked_rooms_query = "SELECT COUNT(DISTINCT room_id) as booked FROM bookings 
                      WHERE status = 'approved' 
                      AND (NOW() BETWEEN check_in_date AND DATE_ADD(check_out_date, INTERVAL 20 MINUTE))";
$booked_rooms_result = mysqli_query($conn, $booked_rooms_query);
$booked_rooms = mysqli_fetch_assoc($booked_rooms_result)['booked'] ?? 0;

// Available rooms
$available_rooms = $total_rooms - $booked_rooms;

// Pending bookings count
$pending_count = mysqli_num_rows($pending_bookings);

// Total bookings
$total_bookings_query = "SELECT COUNT(*) as total FROM bookings";
$total_bookings_result = mysqli_query($conn, $total_bookings_query);
$total_bookings = mysqli_fetch_assoc($total_bookings_result)['total'] ?? 0;

// Recent bookings (last 7 days)
$recent_bookings_query = "SELECT COUNT(*) as recent FROM bookings WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
$recent_bookings_result = mysqli_query($conn, $recent_bookings_query);
$recent_bookings = mysqli_fetch_assoc($recent_bookings_result)['recent'] ?? 0;

// Dining reservation stats
$pending_dining_query = "SELECT COUNT(*) as pending FROM dining_reservations WHERE status = 'pending'";
$pending_dining_result = mysqli_query($conn, $pending_dining_query);
$pending_dining = mysqli_fetch_assoc($pending_dining_result)['pending'] ?? 0;

$total_dining_query = "SELECT COUNT(*) as total FROM dining_reservations";
$total_dining_result = mysqli_query($conn, $total_dining_query);
$total_dining = mysqli_fetch_assoc($total_dining_result)['total'] ?? 0;

// Spa booking stats
$pending_spa_query = "SELECT COUNT(*) as pending FROM spa_bookings WHERE status = 'pending'";
$pending_spa_result = mysqli_query($conn, $pending_spa_query);
$pending_spa = mysqli_fetch_assoc($pending_spa_result)['pending'] ?? 0;

$total_spa_query = "SELECT COUNT(*) as total FROM spa_bookings";
$total_spa_result = mysqli_query($conn, $total_spa_query);
$total_spa = mysqli_fetch_assoc($total_spa_result)['total'] ?? 0;
?>

<!-- Header -->
<div class="mb-8">
    <h2 class="text-3xl font-bold font-serif text-gray-900 mb-2">Admin Dashboard</h2>
    <p class="text-gray-500">Welcome back, Admin. Here's what's happening today.</p>
</div>

<?php if (isset($error_message)): ?>
    <div class="bg-red-50 text-red-600 p-4 rounded-xl border border-red-100 flex items-center mb-6">
        <i class="fas fa-exclamation-circle mr-3"></i>
        <?php echo htmlspecialchars($error_message); ?>
    </div>
<?php endif; ?>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Rooms -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 group">
        <div class="flex items-center justify-between mb-4">
            <div class="d-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary" style="width: 3rem; height: 3rem;">
                <i class="fas fa-door-open text-xl"></i>
            </div>
            <!-- <span class="text-green-500 text-sm font-bold">+2.5%</span> -->
        </div>
        <h3 class="text-gray-500 text-sm font-medium uppercase tracking-wide">Total Rooms</h3>
        <div class="flex items-baseline mt-1">
            <p class="text-3xl font-bold text-gray-900"><?php echo $total_rooms; ?></p>
        </div>
        <a href="rooms.php" class="text-sm font-medium text-blue-600 mt-3 inline-block hover:text-blue-700">Manage Rooms &rarr;</a>
    </div>

    <!-- Available Rooms -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 group">
        <div class="flex items-center justify-between mb-4">
            <div class="d-flex align-items-center justify-content-center rounded-circle bg-success-subtle text-success" style="width: 3rem; height: 3rem;">
                <i class="fas fa-check-circle text-xl"></i>
            </div>
        </div>
        <h3 class="text-gray-500 text-sm font-medium uppercase tracking-wide">Available Rooms</h3>
        <div class="flex items-baseline mt-1">
            <p class="text-3xl font-bold text-gray-900"><?php echo $available_rooms; ?></p>
            <span class="ml-2 text-sm text-gray-400">/ <?php echo $total_rooms; ?></span>
        </div>
        <p class="text-sm text-gray-400 mt-3"><?php echo $booked_rooms; ?> currently booked</p>
    </div>

    <!-- Pending Bookings -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 group">
        <div class="flex items-center justify-between mb-4">
            <div class="d-flex align-items-center justify-content-center rounded-circle bg-warning-subtle text-warning" style="width: 3rem; height: 3rem;">
                <i class="fas fa-clock text-xl"></i>
            </div>
            <?php if ($pending_count > 0): ?>
                <span class="bg-red-100 text-red-600 text-xs font-bold px-2 py-1 rounded-full animate-pulse">Action Needed</span>
            <?php endif; ?>
        </div>
        <h3 class="text-gray-500 text-sm font-medium uppercase tracking-wide">Pending Bookings</h3>
        <div class="flex items-baseline mt-1">
            <p class="text-3xl font-bold text-gray-900"><?php echo $pending_count; ?></p>
        </div>
        <a href="bookings.php?status=pending" class="text-sm font-medium text-yellow-600 mt-3 inline-block hover:text-yellow-700">Review Now &rarr;</a>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 group">
        <div class="flex items-center justify-between mb-4">
            <div class="d-flex align-items-center justify-content-center rounded-circle bg-info-subtle text-info" style="width: 3rem; height: 3rem;">
                <i class="fas fa-chart-line text-xl"></i>
            </div>
        </div>
        <h3 class="text-gray-500 text-sm font-medium uppercase tracking-wide">Recent Bookings</h3>
        <div class="flex items-baseline mt-1">
            <p class="text-3xl font-bold text-gray-900"><?php echo $recent_bookings; ?></p>
        </div>
        <p class="text-sm text-gray-400 mt-3">In the last 7 days</p>
    </div>
</div>

<!-- Secondary Stats (Dining & Spa) -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center shrink-0">
                <i class="fas fa-utensils"></i>
            </div>
            <div>
                <h4 class="text-gray-500 text-xs font-bold uppercase">Total Dining</h4>
                <p class="text-xl font-bold text-gray-900"><?php echo $total_dining; ?></p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                <i class="fas fa-exclamation"></i>
            </div>
            <div>
                <h4 class="text-gray-500 text-xs font-bold uppercase">Pending Dining</h4>
                <p class="text-xl font-bold text-gray-900"><?php echo $pending_dining; ?></p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-full bg-teal-100 text-teal-600 flex items-center justify-center shrink-0">
                <i class="fas fa-spa"></i>
            </div>
            <div>
                <h4 class="text-gray-500 text-xs font-bold uppercase">Total Spa</h4>
                <p class="text-xl font-bold text-gray-900"><?php echo $total_spa; ?></p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                <i class="fas fa-exclamation"></i>
            </div>
            <div>
                <h4 class="text-gray-500 text-xs font-bold uppercase">Pending Spa</h4>
                <p class="text-xl font-bold text-gray-900"><?php echo $pending_spa; ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Pending Bookings Table -->
<div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden mb-8">
    <div class="p-6 bg-white border-b border-gray-100 flex justify-between items-center">
        <h3 class="font-bold font-serif text-xl text-gray-900">Pending Approvals</h3>
        <a href="bookings.php?status=pending" class="text-sm font-bold text-yellow-600 hover:text-yellow-700">View All</a>
    </div>
    <div class="overflow-x-auto">
        <?php if ($pending_bookings && mysqli_num_rows($pending_bookings) > 0): ?>
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <th class="p-4 font-bold">User</th>
                        <th class="p-4 font-bold">Room</th>
                        <th class="p-4 font-bold">Check-in / Check-out</th>
                        <th class="p-4 font-bold">Customer</th>
                        <th class="p-4 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php while ($booking = mysqli_fetch_assoc($pending_bookings)): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="p-4">
                                <span class="font-bold text-gray-900"><?php echo htmlspecialchars($booking['username']); ?></span>
                            </td>
                            <td class="p-4">
                                <span class="text-gray-600"><?php echo htmlspecialchars($booking['room_name']); ?></span>
                            </td>
                            <td class="p-4">
                                <div class="text-sm text-gray-900">
                                    <span class="text-xs font-bold text-gray-500 uppercase mr-1">In:</span>
                                    <?php echo !empty($booking['check_in_date']) ? date('M j, g:i A', strtotime($booking['check_in_date'])) : 'N/A'; ?>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    <span class="text-xs font-bold text-gray-400 uppercase mr-1">Out:</span>
                                    <?php echo !empty($booking['check_out_date']) ? date('M j, Y g:i A', strtotime($booking['check_out_date'])) : 'N/A'; ?>
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="text-gray-900"><?php echo htmlspecialchars($booking['customer_name']); ?></span>
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <button class="approve-booking px-3 py-1.5 bg-green-100 text-green-700 rounded-lg text-xs font-bold hover:bg-green-200 transition-colors"
                                        data-booking-id="<?php echo $booking['id']; ?>">
                                        Approve
                                    </button>
                                    <button class="reject-booking px-3 py-1.5 bg-red-100 text-red-700 rounded-lg text-xs font-bold hover:bg-red-200 transition-colors"
                                        data-booking-id="<?php echo $booking['id']; ?>">
                                        Reject
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="p-12 text-center text-gray-400">
                <i class="fas fa-check-circle text-4xl mb-4 text-gray-200"></i>
                <p>All caught up! No pending bookings.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Approve booking
        document.querySelectorAll('.approve-booking').forEach(button => {
            button.addEventListener('click', function() {
                const bookingId = this.dataset.bookingId;
                if (confirm('Are you sure you want to approve this booking?')) {
                    updateBookingStatus(bookingId, 'approved');
                }
            });
        });

        // Reject booking
        document.querySelectorAll('.reject-booking').forEach(button => {
            button.addEventListener('click', function() {
                const bookingId = this.dataset.bookingId;
                if (confirm('Are you sure you want to reject this booking?')) {
                    updateBookingStatus(bookingId, 'rejected');
                }
            });
        });

        async function updateBookingStatus(bookingId, status) {
            try {
                const response = await fetch('../admin/update_booking.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `booking_id=${bookingId}&status=${status}`
                });

                if (response.ok) {
                    location.reload();
                } else {
                    alert('Error updating booking status');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error updating booking status');
            }
        }
    });
</script>

</main>
</div>
</div>

<?php include('../footer.php'); ?>