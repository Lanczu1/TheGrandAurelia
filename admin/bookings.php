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

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';


// Build the query
$query = "SELECT b.*, r.room_name, u.username, u.email 
          FROM bookings b 
          LEFT JOIN rooms r ON b.room_id = r.id 
          LEFT JOIN users u ON b.user_id = u.id 
          WHERE 1=1";

if ($status_filter !== 'all') {
    $query .= " AND b.status = '" . mysqli_real_escape_string($conn, $status_filter) . "'";
}



$query .= " ORDER BY b.created_at DESC";

$bookings = mysqli_query($conn, $query);
?>

<!-- Header -->
<div class="mb-8">
    <h2 class="text-3xl font-bold font-serif text-gray-900 mb-2">Room Bookings</h2>
    <p class="text-gray-500">View and manage all room reservations.</p>
</div>

<!-- Filters -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8">
    <form class="flex flex-col md:flex-row gap-4">
        <div class="flex-shrink-0 w-full md:w-48">
            <select class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 outline-none transition-all appearance-none cursor-pointer" name="status" onchange="this.form.submit()">
                <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Bookings</option>
                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
            </select>
        </div>

    </form>
</div>

<!-- Bookings Table -->
<div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <?php if ($bookings && mysqli_num_rows($bookings) > 0): ?>
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <th class="p-4 font-bold">Booking ID</th>
                        <th class="p-4 font-bold">Guest</th>
                        <th class="p-4 font-bold">Room</th>
                        <th class="p-4 font-bold">Dates</th>
                        <th class="p-4 font-bold">Duration</th>
                        <th class="p-4 font-bold">Status</th>
                        <th class="p-4 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php while ($booking = mysqli_fetch_assoc($bookings)): ?>
                        <?php
                        // Calculate nights if both dates are available
                        $nights = '';
                        $total_price = '';

                        if (
                            isset($booking['check_in_date']) && !empty($booking['check_in_date']) &&
                            isset($booking['check_out_date']) && !empty($booking['check_out_date'])
                        ) {
                            $check_in = new DateTime($booking['check_in_date']);
                            $check_out = new DateTime($booking['check_out_date']);
                            $nights = $check_out->diff($check_in)->days;

                            if (isset($booking['total_price'])) {
                                $total_price = '$' . number_format($booking['total_price'], 2);
                            }
                        }

                        // Status styles
                        $status = strtolower($booking['status'] ?? 'pending');
                        $status_styles = [
                            'pending' => 'bg-yellow-100 text-yellow-700',
                            'approved' => 'bg-green-100 text-green-700',
                            'rejected' => 'bg-red-100 text-red-700',
                            'cancelled' => 'bg-gray-100 text-gray-500'
                        ];
                        $status_class = $status_styles[$status] ?? 'bg-gray-100 text-gray-500';
                        ?>
                        <tr class="hover:bg-gray-50 transition-colors group">
                            <td class="p-4 font-mono text-xs text-gray-400">#<?php echo $booking['id']; ?></td>
                            <td class="p-4">
                                <div class="font-bold text-gray-900"><?php echo htmlspecialchars($booking['username'] ?? 'Unknown'); ?></div>
                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($booking['email'] ?? 'No email'); ?></div>
                            </td>
                            <td class="p-4">
                                <div class="font-bold text-gray-700"><?php echo htmlspecialchars($booking['room_name'] ?? 'Unknown Room'); ?></div>
                                <?php if ($total_price): ?>
                                    <div class="text-xs text-gray-500"><?php echo $total_price; ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="p-4">
                                <div class="text-sm text-gray-900">
                                    <span class="text-xs text-gray-400">In:</span>
                                    <?php echo isset($booking['check_in_date']) ? date('M d, Y g:i A', strtotime($booking['check_in_date'])) : '-'; ?>
                                </div>
                                <div class="text-xs text-gray-400 mt-0.5">
                                    <span class="text-xs text-gray-400">Out:</span>
                                    <?php echo isset($booking['check_out_date']) ? date('M d, Y g:i A', strtotime($booking['check_out_date'])) : '-'; ?>
                                </div>
                            </td>
                            <td class="p-4 text-sm text-gray-600">
                                <?php echo $nights ? $nights . ' nights' : '-'; ?>
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase <?php echo $status_class; ?>">
                                    <?php echo ucfirst($status); ?>
                                </span>
                            </td>
                            <td class="p-4 text-right">
                                <?php if ($status === 'pending'): ?>
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
                                <?php else: ?>
                                    <?php
                                    $status_badge_class = 'text-gray-400';
                                    if ($status === 'approved') $status_badge_class = 'bg-green-500 text-white shadow-sm';
                                    if ($status === 'rejected') $status_badge_class = 'bg-red-500 text-white shadow-sm';
                                    ?>
                                    <div class="inline-block px-3 py-1.5 text-xs <?php echo $status_badge_class; ?> font-bold rounded-lg">
                                        <?php echo ucfirst($status); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-300">
                    <i class="fas fa-calendar-times text-2xl"></i>
                </div>
                <p class="text-gray-500 font-medium">No bookings found matching your criteria.</p>
                <a href="bookings.php" class="text-yellow-600 hover:text-yellow-700 text-sm font-bold mt-2 inline-block">Clear Filters</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Helper function to update booking status
        async function updateBookingStatus(bookingId, status) {
            if (confirm(`Are you sure you want to ${status} this booking?`)) {
                try {
                    const response = await fetch('update_booking.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `booking_id=${bookingId}&status=${status}` // Mapping actions to statuses
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
        }

        // Approve buttons
        document.querySelectorAll('.approve-booking').forEach(button => {
            button.addEventListener('click', function() {
                const bookingId = this.dataset.bookingId;
                updateBookingStatus(bookingId, 'approved');
            });
        });

        // Reject buttons
        document.querySelectorAll('.reject-booking').forEach(button => {
            button.addEventListener('click', function() {
                const bookingId = this.dataset.bookingId;
                updateBookingStatus(bookingId, 'rejected');
            });
        });
    });
</script>

</main>
</div>
</div>

<?php include('../footer.php'); ?>