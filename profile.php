<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Require login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

include('header.php');
include('db.php');

$user_id = $_SESSION['user_id'];

// specific user details
$user_query = "SELECT username, email, created_at, role, is_verified FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$stmt->close();

// Get counts for dashboard stats
$stats = [
    'rooms' => 0,
    'dining' => 0,
    'spa' => 0
];

// Room Bookings Count
$room_q = "SELECT COUNT(*) as count FROM bookings WHERE user_id = ?";
$stmt = $conn->prepare($room_q);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stats['rooms'] = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

// Dining Count
$dining_q = "SELECT COUNT(*) as count FROM dining_reservations WHERE user_id = ?";
$stmt = $conn->prepare($dining_q);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stats['dining'] = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

// Spa Count
$spa_q = "SELECT COUNT(*) as count FROM spa_bookings WHERE user_id = ?";
$stmt = $conn->prepare($spa_q);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stats['spa'] = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

// Fetch Recent Activity (Limit 5 combined)
$recent_activities = [];

// 1. Rooms
$stmt = $conn->prepare("SELECT b.id, b.check_in_date as date, 'room' as type, b.status, r.room_name as title 
                        FROM bookings b 
                        JOIN rooms r ON b.room_id = r.id 
                        WHERE b.user_id = ? 
                        ORDER BY b.check_in_date DESC LIMIT 5");
$stmt->bind_param("i", $user_id);
if ($stmt->execute()) {
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $recent_activities[] = $row;
    }
}
$stmt->close();

// 2. Dining
$stmt = $conn->prepare("SELECT id, reservation_date as date, reservation_time, 'dining' as type, status, venue as title 
                        FROM dining_reservations 
                        WHERE user_id = ? 
                        ORDER BY reservation_date DESC LIMIT 5");
$stmt->bind_param("i", $user_id);
if ($stmt->execute()) {
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $recent_activities[] = $row;
    }
}
$stmt->close();

// 3. Spa
$stmt = $conn->prepare("SELECT id, spa_date as date, spa_time, 'spa' as type, status, treatment as title 
                        FROM spa_bookings 
                        WHERE user_id = ? 
                        ORDER BY spa_date DESC LIMIT 5");
$stmt->bind_param("i", $user_id);
if ($stmt->execute()) {
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $recent_activities[] = $row;
    }
}
$stmt->close();

// Sort by Date Descending
usort($recent_activities, function ($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

// Keep top 5
$recent_activities = array_slice($recent_activities, 0, 5);


?>

<div class="min-h-screen bg-gray-50 py-12">
    <div class="container mx-auto px-4 max-w-5xl">

        <!-- Profile Header -->
        <div class="bg-white rounded-3xl shadow-xl overflow-hidden mb-8">
            <div class="bg-gray-900 h-32 relative">
                <div class="absolute inset-0 bg-gradient-to-r from-yellow-600/20 to-gray-900/50"></div>
            </div>
            <div class="px-8 pb-8 relative">
                <div class="-mt-16 flex flex-col md:flex-row items-center md:items-start gap-6 text-center md:text-left">
                    <div class="relative group">
                        <div class="w-32 h-32 rounded-full border-4 border-white bg-gray-200 flex items-center justify-center text-gray-400 text-4xl shadow-lg relative bg-white shrink-0 overflow-hidden">
                            <?php
                            $profile_img = 'profileimg/' . $user_id . '.jpg';
                            if (file_exists($profile_img)):
                            ?>
                                <img src="<?php echo $profile_img . '?' . time(); ?>" alt="Profile" class="w-full h-full object-cover">
                            <?php else: ?>
                                <span class="font-bold text-5xl text-gray-300"><?php echo strtoupper(substr($user['username'], 0, 1)); ?></span>
                            <?php endif; ?>
                            <!-- Edit Overlay -->
                            <div class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer" onclick="document.getElementById('profile_upload').click()">
                                <i class="fas fa-camera text-white text-2xl"></i>
                            </div>
                        </div>
                        <?php if ($user['is_verified']): ?>
                            <div class="absolute bottom-1 right-1 w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm border-2 border-white z-10" title="Verified Account">
                                <i class="fas fa-check"></i>
                            </div>
                        <?php endif; ?>

                        <!-- Visible Edit Button (Top Left) -->
                        <button onclick="document.getElementById('profile_upload').click()" class="absolute top-0 left-0 w-8 h-8 bg-gray-900 text-white rounded-full flex items-center justify-center border-2 border-white shadow-md hover:bg-gray-700 transition-colors z-20" title="Change Profile Picture">
                            <i class="fas fa-camera text-xs"></i>
                        </button>

                        <!-- Hidden Upload Form -->
                        <form id="profile_form" action="upload_profile.php" method="POST" enctype="multipart/form-data" class="hidden">
                            <input type="file" name="profile_image" id="profile_upload" accept="image/jpeg,image/png,image/webp" onchange="document.getElementById('profile_form').submit()">
                        </form>

                        <!-- Remove Button (Top Right) - Only show if image exists -->
                        <?php if (file_exists('profileimg/' . $user_id . '.jpg')): ?>
                            <button onclick="document.getElementById('removeProfileModal').classList.remove('hidden')" class="absolute top-0 right-0 w-8 h-8 bg-red-600 text-white rounded-full flex items-center justify-center border-2 border-white shadow-md hover:bg-red-700 transition-colors z-20" title="Remove Profile Picture">
                                <i class="fas fa-trash-alt text-xs"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                    <div class="flex-grow pb-2 md:mt-20">
                        <div class="flex flex-col md:items-start items-center">
                            <h1 class="text-4xl font-bold font-serif text-gray-900 mb-3 flex items-center gap-3">
                                <?php echo htmlspecialchars($user['username']); ?>
                                <?php if (isset($user['role']) && $user['role'] === 'admin'): ?>
                                    <span class="bg-red-50 text-red-600 text-xs px-3 py-1 rounded-full font-sans font-bold uppercase tracking-wider border border-red-100">Admin</span>
                                <?php endif; ?>
                            </h1>
                            <div class="flex flex-wrap justify-center md:justify-start gap-3 text-gray-600">
                                <div class="flex items-center gap-2 bg-gray-50 px-4 py-2 rounded-xl border border-gray-100 shadow-sm transition-colors hover:bg-white hover:shadow-md">
                                    <i class="fas fa-envelope text-yellow-600"></i>
                                    <span class="font-medium"><?php echo htmlspecialchars($user['email']); ?></span>
                                </div>
                                <div class="flex items-center gap-2 bg-gray-50 px-4 py-2 rounded-xl border border-gray-100 shadow-sm transition-colors hover:bg-white hover:shadow-md">
                                    <i class="fas fa-calendar-alt text-yellow-600"></i>
                                    <span class="font-medium text-sm">Joined <?php echo date('F Y', strtotime($user['created_at'])); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="pb-2 md:mt-20">
                        <a href="logout.php" class="inline-flex items-center gap-2 px-6 py-2.5 bg-red-50 text-red-600 rounded-xl font-bold hover:bg-red-100 transition-colors">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Sidebar Navigation -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Quick Stats -->
                <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                    <h3 class="font-bold text-gray-900 mb-4 font-serif">Your Activity</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                                    <i class="fas fa-bed"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-600">Room Stays</span>
                            </div>
                            <span class="font-bold text-gray-900"><?php echo $stats['rooms']; ?></span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-yellow-100 text-yellow-600 flex items-center justify-center">
                                    <i class="fas fa-utensils"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-600">Dining</span>
                            </div>
                            <span class="font-bold text-gray-900"><?php echo $stats['dining']; ?></span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                                    <i class="fas fa-spa"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-600">Spa Visits</span>
                            </div>
                            <span class="font-bold text-gray-900"><?php echo $stats['spa']; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Account Actions -->
                <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                    <h3 class="font-bold text-gray-900 mb-4 font-serif">Account</h3>
                    <div class="space-y-2">
                        <a href="view_bookings.php" class="block w-full text-left px-4 py-3 rounded-xl hover:bg-gray-50 text-gray-700 font-medium transition-colors flex justify-between items-center group">
                            <span><i class="fas fa-calendar-check mr-3 text-gray-400 group-hover:text-yellow-600"></i>My Bookings</span>
                            <i class="fas fa-chevron-right text-xs text-gray-300 group-hover:text-gray-500"></i>
                        </a>
                        <a href="settings.php" class="block w-full text-left px-4 py-3 rounded-xl hover:bg-gray-50 text-gray-700 font-medium transition-colors flex justify-between items-center group">
                            <span><i class="fas fa-cog mr-3 text-gray-400 group-hover:text-yellow-600"></i>Settings</span>
                            <i class="fas fa-chevron-right text-xs text-gray-300 group-hover:text-gray-500"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="lg:col-span-2">
                <!-- Recent Bookings Teaser -->
                <div class="bg-white rounded-2xl shadow-lg p-8 border border-gray-100 mb-8">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold font-serif text-gray-900">Recent Activity</h2>
                        <a href="view_bookings.php" class="text-sm font-bold text-yellow-600 hover:text-yellow-700">View All</a>
                    </div>

                    <?php if (!empty($recent_activities)): ?>
                        <div class="space-y-4">
                            <?php foreach ($recent_activities as $activity): ?>
                                <?php
                                // Prepare display data
                                $icon = 'fa-circle';
                                $bg_color = 'bg-gray-100';
                                $text_color = 'text-gray-600';
                                $subtext = '';

                                if ($activity['type'] == 'room') {
                                    $icon = 'fa-bed';
                                    $bg_color = 'bg-blue-100';
                                    $text_color = 'text-blue-600';
                                    $subtext = 'Check-in: ' . date('M d, Y', strtotime($activity['date']));
                                } elseif ($activity['type'] == 'dining') {
                                    $icon = 'fa-utensils';
                                    $bg_color = 'bg-yellow-100';
                                    $text_color = 'text-yellow-600';
                                    $venue_names = [
                                        'restaurant' => 'The Aurelia Restaurant',
                                        'lounge' => 'The Skyline Lounge',
                                        'grand_room' => 'The Grand Room',
                                        'wine_cellar' => 'The Wine Cellar'
                                    ];
                                    $title_display = isset($venue_names[$activity['title']]) ? $venue_names[$activity['title']] : ucfirst(str_replace('_', ' ', $activity['title']));
                                    $activity['title'] = $title_display; // Override title for display
                                    $subtext = 'Reserved: ' . date('M d, Y', strtotime($activity['date']));
                                    if (isset($activity['reservation_time'])) $subtext .= ' at ' . date('g:i A', strtotime($activity['reservation_time']));
                                } elseif ($activity['type'] == 'spa') {
                                    $icon = 'fa-spa';
                                    $bg_color = 'bg-emerald-100';
                                    $text_color = 'text-emerald-600';
                                    $subtext = 'Appointment: ' . date('M d, Y', strtotime($activity['date']));
                                    if (isset($activity['spa_time'])) $subtext .= ' at ' . date('g:i A', strtotime($activity['spa_time']));
                                }

                                // Status colors
                                $status_colors = [
                                    'pending' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                    'confirmed' => 'bg-green-100 text-green-700 border-green-200',
                                    'approved' => 'bg-green-100 text-green-700 border-green-200',
                                    'cancelled' => 'bg-gray-100 text-gray-500 border-gray-200',
                                    'rejected' => 'bg-red-100 text-red-700 border-red-200'
                                ];
                                $s_key = strtolower($activity['status']);
                                $status_class = isset($status_colors[$s_key]) ? $status_colors[$s_key] : 'bg-gray-50 text-gray-600 border-gray-200';
                                ?>
                                <div class="flex items-center p-4 bg-white border border-gray-100 rounded-xl hover:shadow-md transition-shadow group">
                                    <div class="w-12 h-12 rounded-full <?php echo $bg_color . ' ' . $text_color; ?> flex items-center justify-center mr-4 shrink-0 group-hover:scale-110 transition-transform">
                                        <i class="fas <?php echo $icon; ?>"></i>
                                    </div>
                                    <div class="flex-grow min-w-0">
                                        <h4 class="font-bold text-gray-900 truncate"><?php echo htmlspecialchars($activity['title']); ?></h4>
                                        <p class="text-xs md:text-sm text-gray-500 font-medium"><?php echo $subtext; ?></p>
                                    </div>
                                    <div class="ml-4 shrink-0">
                                        <span class="px-3 py-1 rounded-full text-[10px] md:text-xs font-bold uppercase border <?php echo $status_class; ?>">
                                            <?php echo htmlspecialchars($activity['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-12 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                            <i class="fas fa-ghost text-4xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500 font-medium">No recent activity found.</p>
                            <div class="flex justify-center gap-3 mt-6">
                                <a href="view_rooms.php" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition-colors shadow-sm">
                                    Book
                                </a>
                                <a href="dining.php" class="px-5 py-2.5 bg-yellow-600 text-white font-bold rounded-xl hover:bg-yellow-700 transition-colors shadow-lg hover:shadow-yellow-500/30">
                                    Reserve
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Update Info Form -->
                <!-- Placeholder for future functionality, kept simple for now -->
                <div class="bg-white rounded-2xl shadow-lg p-8 border border-gray-100">
                    <h2 class="text-2xl font-bold font-serif text-gray-900 mb-6">Personal Details</h2>
                    <form class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Username</label>
                                <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled
                                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-500 cursor-not-allowed">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Email Address</label>
                                <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled
                                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-500 cursor-not-allowed">
                            </div>
                        </div>
                        <div class="pt-4 border-t border-gray-100 flex justify-end">
                            <button type="button" class="text-sm font-bold text-gray-400 cursor-not-allowed" disabled>Edit functionality coming soon</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Remove Profile Picture Modal -->
<div id="removeProfileModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 transform transition-all scale-100">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4 text-red-600">
                <i class="fas fa-trash-alt text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Remove Profile Picture?</h3>
            <p class="text-gray-500 text-sm">Are you sure you want to remove your profile picture? This action cannot be undone.</p>
        </div>
        <div class="flex gap-3">
            <button onclick="document.getElementById('removeProfileModal').classList.add('hidden')" class="flex-1 px-4 py-2.5 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition-colors">
                Cancel
            </button>
            <form action="remove_profile_pic.php" method="POST" class="flex-1">
                <button type="submit" class="w-full px-4 py-2.5 bg-red-600 text-white font-bold rounded-xl hover:bg-red-700 transition-colors shadow-lg shadow-red-500/30">
                    Yes, Remove
                </button>
            </form>
        </div>
    </div>
</div>