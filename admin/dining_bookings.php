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
$query = "SELECT d.*, u.username, u.email 
          FROM dining_reservations d 
          LEFT JOIN users u ON d.user_id = u.id 
          WHERE 1=1";

if ($status_filter !== 'all') {
    $query .= " AND d.status = '" . mysqli_real_escape_string($conn, $status_filter) . "'";
}



$query .= " ORDER BY d.created_at DESC";

$bookings = mysqli_query($conn, $query);
?>

<!-- Header -->
<div class="mb-8">
    <h2 class="text-3xl font-bold font-serif text-gray-900 mb-2">Dining Reservations</h2>
    <p class="text-gray-500">Manage all dining reservations from guests.</p>
</div>

<!-- Filters -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8">
    <form class="flex flex-col md:flex-row gap-4">
        <div class="flex-shrink-0 w-full md:w-48">
            <select class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 outline-none transition-all appearance-none cursor-pointer" name="status" onchange="this.form.submit()">
                <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Reservations</option>
                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
            </select>
        </div>

    </form>
</div>

<!-- Reservations Table -->
<div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <?php if ($bookings && mysqli_num_rows($bookings) > 0): ?>
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <th class="p-4 font-bold">ID</th>
                        <th class="p-4 font-bold">Guest</th>
                        <th class="p-4 font-bold">Date & Time</th>
                        <th class="p-4 font-bold">Guests</th>
                        <th class="p-4 font-bold">Venue</th>
                        <th class="p-4 font-bold">Status</th>
                        <th class="p-4 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php while ($booking = mysqli_fetch_assoc($bookings)): ?>
                        <?php
                        $status = strtolower($booking['status'] ?? 'pending');
                        $status_styles = [
                            'pending' => 'bg-yellow-100 text-yellow-700',
                            'confirmed' => 'bg-green-100 text-green-700',
                            'cancelled' => 'bg-gray-100 text-gray-500'
                        ];
                        $status_class = $status_styles[$status] ?? 'bg-gray-100 text-gray-500';
                        ?>
                        <tr class="hover:bg-gray-50 transition-colors group">
                            <td class="p-4 font-mono text-xs text-gray-400">#<?php echo $booking['id']; ?></td>
                            <td class="p-4">
                                <div class="font-bold text-gray-900"><?php echo htmlspecialchars($booking['customer_name']); ?></div>
                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($booking['email'] ?? 'No email'); ?></div>
                            </td>
                            <td class="p-4">
                                <div class="text-sm text-gray-900"><?php echo isset($booking['reservation_date']) ? date('M d, Y', strtotime($booking['reservation_date'])) : '-'; ?></div>
                                <div class="text-xs text-gray-400"><?php echo isset($booking['reservation_time']) ? date('g:i A', strtotime($booking['reservation_time'])) : '-'; ?></div>
                            </td>
                            <td class="p-4 text-sm text-gray-600"><?php echo htmlspecialchars($booking['number_of_guests']); ?></td>
                            <td class="p-4 text-sm font-medium text-gray-800"><?php echo htmlspecialchars($booking['venue'] ?? 'Not specified'); ?></td>
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
                                    if ($status === 'confirmed') $status_badge_class = 'bg-green-500 text-white shadow-sm';
                                    if ($status === 'cancelled') $status_badge_class = 'bg-red-500 text-white shadow-sm';
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
                    <i class="fas fa-utensils text-2xl"></i>
                </div>
                <p class="text-gray-500 font-medium">No reservations found matching your criteria.</p>
                <a href="dining_bookings.php" class="text-yellow-600 hover:text-yellow-700 text-sm font-bold mt-2 inline-block">Clear Filters</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Helper function to update dining reservation status
        async function updateDiningStatus(reservationId, status) {
            // Friendly action name for the confirm dialog
            const actionName = status === 'confirmed' ? 'approve' : 'reject';

            if (confirm(`Are you sure you want to ${actionName} this reservation?`)) {
                try {
                    const response = await fetch('update_dining_booking.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `reservation_id=${reservationId}&status=${status}`
                    });

                    if (response.ok) {
                        location.reload();
                    } else {
                        alert('Error updating reservation status');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error updating reservation status');
                }
            }
        }

        // Approve buttons
        document.querySelectorAll('.approve-booking').forEach(button => {
            button.addEventListener('click', function() {
                const bookingId = this.dataset.bookingId;
                updateDiningStatus(bookingId, 'confirmed');
            });
        });

        // Reject buttons
        document.querySelectorAll('.reject-booking').forEach(button => {
            button.addEventListener('click', function() {
                const bookingId = this.dataset.bookingId;
                updateDiningStatus(bookingId, 'cancelled');
            });
        });
    });
</script>

</main>
</div>
</div>

<?php include('../footer.php'); ?>