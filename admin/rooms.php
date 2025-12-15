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

// Get all rooms with their booking status
$rooms_query = "SELECT r.*, 
                (SELECT COUNT(*) FROM bookings b 
                WHERE b.room_id = r.id 
                AND b.status = 'approved'
                AND (
                    CURDATE() BETWEEN b.check_in_date AND b.check_out_date
                    OR b.check_in_date >= CURDATE()
                )) as is_booked,
                (SELECT MIN(b.check_out_date) FROM bookings b 
                WHERE b.room_id = r.id 
                AND b.status = 'approved'
                AND b.check_in_date >= CURDATE()) as next_available_date
                FROM rooms r
                ORDER BY room_name ASC";
$rooms = mysqli_query($conn, $rooms_query);

// Function to format price
function formatPrice($price)
{
    return number_format($price, 2);
}
?>

<!-- Header -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
    <div>
        <h2 class="text-3xl font-bold font-serif text-gray-900 mb-2">Room Management</h2>
        <p class="text-gray-500">Manage your hotel rooms, prices, and availability.</p>
    </div>
    <button class="px-6 py-3 bg-yellow-600 hover:bg-yellow-700 text-white font-bold rounded-xl shadow-lg shadow-yellow-500/30 transition-all transform hover:-translate-y-1 flex items-center gap-2" data-bs-toggle="modal" data-bs-target="#addRoomModal">
        <i class="fas fa-plus"></i> Add New Room
    </button>
</div>

<!-- Rooms Grid -->
<?php if ($rooms && mysqli_num_rows($rooms) > 0): ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 gap-8 mb-8">
        <?php while ($room = mysqli_fetch_assoc($rooms)): ?>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300 group flex flex-col h-full">
                <!-- Image Area -->
                <div class="relative h-64 overflow-hidden bg-gray-100">
                    <?php if (!empty($room['room_image'])): ?>
                        <div id="admin-carousel-<?php echo $room['id']; ?>" class="carousel slide h-full" data-bs-ride="carousel">
                            <div class="carousel-indicators">
                                <button type="button" data-bs-target="#admin-carousel-<?php echo $room['id']; ?>" data-bs-slide-to="0" class="active"></button>
                                <?php
                                $roomType = strtolower(explode(' ', $room['room_name'])[0]);
                                $additionalImages = glob("../images/{$roomType}*.jpg");
                                $count = count($additionalImages);
                                for ($i = 1; $i < min($count, 4); $i++) {
                                    echo '<button type="button" data-bs-target="#admin-carousel-' . $room['id'] . '" data-bs-slide-to="' . $i . '"></button>';
                                }
                                ?>
                            </div>
                            <div class="carousel-inner h-full">
                                <div class="carousel-item active h-full">
                                    <img src="../images/<?php echo htmlspecialchars($room['room_image']); ?>"
                                        class="d-block w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700"
                                        alt="<?php echo htmlspecialchars($room['room_name']); ?>">
                                </div>
                                <?php
                                foreach ($additionalImages as $index => $image) {
                                    if ($index < 3) {
                                        $imageName = basename($image);
                                        echo '<div class="carousel-item h-full">';
                                        echo '<img src="../images/' . $imageName . '" class="d-block w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700" alt="Room view">';
                                        echo '</div>';
                                    }
                                }
                                ?>
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#admin-carousel-<?php echo $room['id']; ?>" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon bg-black/50 rounded-full p-2"></span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#admin-carousel-<?php echo $room['id']; ?>" data-bs-slide="next">
                                <span class="carousel-control-next-icon bg-black/50 rounded-full p-2"></span>
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center bg-gray-100 text-gray-400">
                            <i class="fas fa-image text-4xl"></i>
                        </div>
                    <?php endif; ?>

                    <!-- Price Tag -->
                    <div class="absolute top-4 right-4 bg-black/70 backdrop-blur-sm px-4 py-2 rounded-xl border border-orange-500 transition-all hover:scale-105" style="box-shadow: 0 0 15px rgba(249, 115, 22, 0.6);">
                        <span class="font-bold text-lg text-orange-400" style="text-shadow: 0 0 8px rgba(249, 115, 22, 0.8);">
                            $<?php echo formatPrice($room['price']); ?>
                        </span>
                        <span class="text-xs text-gray-300 font-normal">/night</span>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-6 flex flex-col flex-grow">
                    <div class="mb-4">
                        <h3 class="text-xl font-bold font-serif text-gray-900 mb-2 truncate"><?php echo htmlspecialchars($room['room_name']); ?></h3>
                        <div class="h-1 w-12 bg-yellow-500 rounded-full mb-3"></div>

                        <?php if (!empty($room['description'])): ?>
                            <p class="text-gray-500 text-sm line-clamp-3 mb-4"><?php echo htmlspecialchars($room['description']); ?></p>
                        <?php else: ?>
                            <p class="text-gray-400 text-sm italic mb-4">No description available</p>
                        <?php endif; ?>

                        <!-- Features -->
                        <div class="flex flex-wrap gap-2 text-xs font-medium text-gray-600 mb-4">
                            <?php
                            switch ($room['room_name']) {
                                case 'Penthouse Suite':
                                    echo '<span class="px-2 py-1 bg-gray-50 rounded-md border border-gray-100"><i class="fas fa-mountain mr-1 text-yellow-600"></i> City View</span>';
                                    echo '<span class="px-2 py-1 bg-gray-50 rounded-md border border-gray-100"><i class="fas fa-couch mr-1 text-yellow-600"></i> Living Area</span>';
                                    break;
                                case 'Bridal Suite':
                                    echo '<span class="px-2 py-1 bg-gray-50 rounded-md border border-gray-100"><i class="fas fa-bed mr-1 text-yellow-600"></i> King Bed</span>';
                                    echo '<span class="px-2 py-1 bg-gray-50 rounded-md border border-gray-100"><i class="fas fa-bath mr-1 text-yellow-600"></i> Luxury Bath</span>';
                                    break;
                                default:
                                    echo '<span class="px-2 py-1 bg-gray-50 rounded-md border border-gray-100"><i class="fas fa-wifi mr-1 text-yellow-600"></i> WiFi</span>';
                                    echo '<span class="px-2 py-1 bg-gray-50 rounded-md border border-gray-100"><i class="fas fa-tv mr-1 text-yellow-600"></i> TV</span>';
                            }
                            ?>
                        </div>
                    </div>

                    <div class="mt-auto pt-4 border-t border-gray-100">
                        <div class="flex items-center justify-between mb-4">
                            <?php if ($room['is_booked'] > 0): ?>
                                <div class="flex flex-col">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                        Unavailable
                                    </span>
                                    <?php if (!empty($room['next_available_date'])): ?>
                                        <span class="text-[10px] text-gray-500 mt-1">Free: <?php echo date('M j', strtotime($room['next_available_date'])); ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                    Available Now
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <button class="edit-room w-full py-2.5 bg-gray-50 text-gray-700 font-bold rounded-xl hover:bg-gray-100 border border-gray-200 transition-colors flex items-center justify-center gap-2"
                                data-room-id="<?php echo $room['id']; ?>"
                                data-room-name="<?php echo htmlspecialchars($room['room_name']); ?>"
                                data-room-price="<?php echo $room['price']; ?>"
                                data-room-description="<?php echo htmlspecialchars($room['description'] ?? ''); ?>"
                                data-room-image="<?php echo htmlspecialchars($room['room_image'] ?? ''); ?>">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="delete-room w-full py-2.5 bg-white text-red-600 font-bold rounded-xl hover:bg-red-50 border border-red-100 transition-colors flex items-center justify-center gap-2"
                                data-room-id="<?php echo $room['id']; ?>">
                                <i class="fas fa-trash-alt"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <div class="text-center py-20 bg-white rounded-3xl shadow-sm border border-dashed border-gray-200">
        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
            <i class="fas fa-door-closed text-2xl"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-2">No Rooms Found</h3>
        <p class="text-gray-500 mb-6">Get started by adding your first room to the system.</p>
        <button class="px-6 py-3 bg-yellow-600 hover:bg-yellow-700 text-white font-bold rounded-xl transition-colors" data-bs-toggle="modal" data-bs-target="#addRoomModal">
            Create Room
        </button>
    </div>
<?php endif; ?>

<!-- Add Room Modal -->
<div class="modal fade" id="addRoomModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-2xl border-0 shadow-2xl overflow-hidden">
            <div class="modal-header bg-gray-900 text-white border-0 px-6 py-4">
                <h5 class="modal-title font-serif font-bold text-xl"><i class="fas fa-plus-circle mr-2 text-yellow-500"></i>Add New Room</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-6 bg-gray-50">
                <form id="addRoomForm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <label for="roomName" class="block text-xs font-bold text-gray-500 uppercase mb-2">Room Name</label>
                            <input type="text" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 transition-all outline-none" id="roomName" required>
                        </div>
                        <div>
                            <label for="roomPrice" class="block text-xs font-bold text-gray-500 uppercase mb-2">Price per Night</label>
                            <div class="relative">
                                <span class="absolute left-4 top-3 text-gray-400">$</span>
                                <input type="number" class="w-full pl-8 pr-4 py-3 rounded-xl border border-gray-200 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 transition-all outline-none" id="roomPrice" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="roomImage" class="block text-xs font-bold text-gray-500 uppercase mb-2">Image Filename</label>
                        <input type="text" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 transition-all outline-none" id="roomImage" placeholder="e.g. room.jpg">
                        <small class="text-gray-400 text-xs mt-1 block">Enter the filename from the images folder</small>
                    </div>
                    <div class="mb-4">
                        <label for="roomDescription" class="block text-xs font-bold text-gray-500 uppercase mb-2">Description</label>
                        <textarea class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 transition-all outline-none" id="roomDescription" rows="4"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-t border-gray-100 bg-white px-6 py-4">
                <button type="button" class="px-5 py-2.5 rounded-xl text-gray-600 font-bold hover:bg-gray-100 transition-colors" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="px-5 py-2.5 rounded-xl bg-gray-900 text-white font-bold hover:bg-gray-800 transition-colors shadow-lg shadow-gray-900/20" id="saveRoom">Save Room</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Room Modal -->
<div class="modal fade" id="editRoomModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-2xl border-0 shadow-2xl overflow-hidden">
            <div class="modal-header bg-gray-900 text-white border-0 px-6 py-4">
                <h5 class="modal-title font-serif font-bold text-xl"><i class="fas fa-edit mr-2 text-yellow-500"></i>Edit Room</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-6 bg-gray-50">
                <form id="editRoomForm">
                    <input type="hidden" id="editRoomId">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <label for="editRoomName" class="block text-xs font-bold text-gray-500 uppercase mb-2">Room Name</label>
                            <input type="text" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 transition-all outline-none" id="editRoomName" required>
                        </div>
                        <div>
                            <label for="editRoomPrice" class="block text-xs font-bold text-gray-500 uppercase mb-2">Price per Night</label>
                            <div class="relative">
                                <span class="absolute left-4 top-3 text-gray-400">$</span>
                                <input type="number" class="w-full pl-8 pr-4 py-3 rounded-xl border border-gray-200 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 transition-all outline-none" id="editRoomPrice" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="editRoomImage" class="block text-xs font-bold text-gray-500 uppercase mb-2">Image Filename</label>
                        <input type="text" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 transition-all outline-none" id="editRoomImage" placeholder="e.g. room.jpg">
                    </div>
                    <div class="mb-4">
                        <label for="editRoomDescription" class="block text-xs font-bold text-gray-500 uppercase mb-2">Description</label>
                        <textarea class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 transition-all outline-none" id="editRoomDescription" rows="4"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-t border-gray-100 bg-white px-6 py-4">
                <button type="button" class="px-5 py-2.5 rounded-xl text-gray-600 font-bold hover:bg-gray-100 transition-colors" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="px-5 py-2.5 rounded-xl bg-gray-900 text-white font-bold hover:bg-gray-800 transition-colors shadow-lg shadow-gray-900/20" id="updateRoom">Update Room</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Edit room
        document.querySelectorAll('.edit-room').forEach(button => {
            button.addEventListener('click', function() {
                const roomId = this.dataset.roomId;
                const roomName = this.dataset.roomName;
                const roomPrice = this.dataset.roomPrice;
                const roomDescription = this.dataset.roomDescription;
                const roomImage = this.dataset.roomImage;

                document.getElementById('editRoomId').value = roomId;
                document.getElementById('editRoomName').value = roomName;
                document.getElementById('editRoomPrice').value = roomPrice;
                document.getElementById('editRoomDescription').value = roomDescription;
                document.getElementById('editRoomImage').value = roomImage;

                new bootstrap.Modal(document.getElementById('editRoomModal')).show();
            });
        });

        // Delete room
        document.querySelectorAll('.delete-room').forEach(button => {
            button.addEventListener('click', function() {
                const roomId = this.dataset.roomId;
                if (confirm('Are you sure you want to delete this room?')) {
                    deleteRoom(roomId);
                }
            });
        });

        // Save new room
        document.getElementById('saveRoom').addEventListener('click', function() {
            const roomName = document.getElementById('roomName').value;
            const roomPrice = document.getElementById('roomPrice').value;
            const roomDescription = document.getElementById('roomDescription').value;
            const roomImage = document.getElementById('roomImage').value;

            if (roomName && roomPrice) {
                addNewRoom(roomName, roomPrice, roomDescription, roomImage);
            }
        });

        // Update room
        document.getElementById('updateRoom').addEventListener('click', function() {
            const roomId = document.getElementById('editRoomId').value;
            const roomName = document.getElementById('editRoomName').value;
            const roomPrice = document.getElementById('editRoomPrice').value;
            const roomDescription = document.getElementById('editRoomDescription').value;
            const roomImage = document.getElementById('editRoomImage').value;

            if (roomId && roomName && roomPrice) {
                updateRoom(roomId, roomName, roomPrice, roomDescription, roomImage);
            }
        });

        // Helper functions
        async function deleteRoom(roomId) {
            try {
                const response = await fetch('delete_room.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `room_id=${roomId}`
                });

                if (response.ok) {
                    location.reload();
                } else {
                    alert('Error deleting room');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error deleting room');
            }
        }

        async function addNewRoom(roomName, roomPrice, roomDescription, roomImage) {
            try {
                const response = await fetch('add_room.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `room_name=${encodeURIComponent(roomName)}&price=${roomPrice}&description=${encodeURIComponent(roomDescription)}&room_image=${encodeURIComponent(roomImage)}`
                });

                if (response.ok) {
                    location.reload();
                } else {
                    alert('Error adding new room');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error adding new room');
            }
        }

        async function updateRoom(roomId, roomName, roomPrice, roomDescription, roomImage) {
            try {
                const response = await fetch('update_room.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `room_id=${roomId}&room_name=${encodeURIComponent(roomName)}&price=${roomPrice}&description=${encodeURIComponent(roomDescription)}&room_image=${encodeURIComponent(roomImage)}`
                });

                if (response.ok) {
                    location.reload();
                } else {
                    alert('Error updating room');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error updating room');
            }
        }
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        ['addRoomModal', 'editRoomModal'].forEach(function(id) {
            const modalEl = document.getElementById(id);
            if (modalEl && modalEl.parentElement !== document.body) {
                document.body.appendChild(modalEl);
            }
            if (modalEl) {
                modalEl.addEventListener('hidden.bs.modal', function() {
                    const video = modalEl.querySelector('video');
                    if (video) {
                        video.pause();
                        video.currentTime = 0;
                    }
                    document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    try {
                        modalEl.setAttribute('aria-hidden', 'true');
                        modalEl.removeAttribute('aria-modal');
                    } catch (e) {}
                });
            }
        });
    });
</script>

</main>
</div>
</div>

<?php include('../footer.php'); ?>