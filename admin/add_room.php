<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('Unauthorized');
}

include('../db.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_name = mysqli_real_escape_string($conn, $_POST['room_name']);
    $price = (float)$_POST['price'];
    $description = isset($_POST['description']) ? mysqli_real_escape_string($conn, $_POST['description']) : '';
    $room_image = isset($_POST['room_image']) ? mysqli_real_escape_string($conn, $_POST['room_image']) : '';

    // Validate input
    if (empty($room_name) || $price <= 0) {
        http_response_code(400);
        exit('Invalid input');
    }

    // Insert new room
    $query = "INSERT INTO rooms (room_name, price, description, room_image) 
              VALUES ('$room_name', $price, '$description', '$room_image')";

    if (mysqli_query($conn, $query)) {
        // Set a session success message and redirect to rooms list so both form submit and fetch() callers work
        $_SESSION['success'] = 'Room added successfully.';
        header('Location: rooms.php');
        exit;
    } else {
        http_response_code(500);
        exit('Database error: ' . mysqli_error($conn));
    }
} else {
    // Render the Add Room page for GET requests
    include('../header.php');
    include('sidebar.php');
?>

    <!-- Header -->
    <div class="mb-8">
        <h2 class="text-3xl font-bold font-serif text-gray-900 mb-2">Add New Room</h2>
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="dashboard.php" class="hover:text-yellow-600 transition-colors">Dashboard</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <a href="rooms.php" class="hover:text-yellow-600 transition-colors">Manage Rooms</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <span class="text-gray-900 font-medium">Add Room</span>
        </div>
    </div>

    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-8">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-lg">
                        <div class="flex gap-3">
                            <i class="fas fa-exclamation-circle text-red-500 mt-1"></i>
                            <p class="text-sm text-red-700"><?php echo htmlspecialchars($_SESSION['error']);
                                                            unset($_SESSION['error']); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="add_room.php">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="room_name" class="block text-sm font-bold text-gray-700 mb-2">Room Name</label>
                            <input type="text" name="room_name" id="room_name" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 outline-none transition-all placeholder-gray-300" placeholder="e.g. Deluxe Suite" required>
                        </div>
                        <div>
                            <label for="price" class="block text-sm font-bold text-gray-700 mb-2">Price per Night</label>
                            <div class="relative">
                                <span class="absolute left-4 top-3.5 text-gray-400">$</span>
                                <input type="number" name="price" id="price" class="w-full pl-8 pr-4 py-3 rounded-xl border border-gray-200 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 outline-none transition-all placeholder-gray-300" placeholder="0.00" step="0.01" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label for="room_image" class="block text-sm font-bold text-gray-700 mb-2">Image Filename</label>
                        <input type="text" name="room_image" id="room_image" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 outline-none transition-all placeholder-gray-300" placeholder="e.g. room.jpg">
                        <p class="mt-2 text-xs text-gray-500">Enter the filename of an image present in the <code class="bg-gray-100 px-1 py-0.5 rounded text-gray-700">images</code> folder.</p>
                    </div>

                    <div class="mb-8">
                        <label for="description" class="block text-sm font-bold text-gray-700 mb-2">Description</label>
                        <textarea name="description" id="description" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 outline-none transition-all placeholder-gray-300" rows="6" placeholder="Describe the room details, amenities, and view..."></textarea>
                    </div>

                    <div class="flex items-center justify-between pt-6 border-t border-gray-100">
                        <a href="rooms.php" class="px-6 py-2.5 rounded-xl text-gray-600 font-bold hover:bg-gray-100 transition-colors">
                            Cancel
                        </a>
                        <button type="submit" class="px-8 py-3 bg-gray-900 text-white font-bold rounded-xl shadow-lg shadow-gray-900/20 hover:bg-gray-800 transform hover:-translate-y-0.5 transition-all">
                            Create Room
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    </main>
    </div>
    </div>

<?php include('../footer.php');
    exit;
}
?>