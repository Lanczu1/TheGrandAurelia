<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get the current page path
$current_path = $_SERVER['PHP_SELF'];
$current_page = basename($current_path);
$is_admin_page = strpos($current_path, '/admin/') !== false;
$admin_prefix = $is_admin_page ? '' : 'admin/';

// Check for unread notifications if user is logged in
$unread_notifications = 0;
if (isset($_SESSION['user_id']) && !$is_admin_page) {
    include_once('db.php');
    $user_id = $_SESSION['user_id'];

    // Try central notifications table first; fall back to legacy per-table counts if table doesn't exist
    $check_table = @mysqli_query($conn, "SHOW TABLES LIKE 'notifications'");
    $table_exists = ($check_table && mysqli_num_rows($check_table) > 0);

    if ($table_exists) {
        // Use central notifications table
        $q = "SELECT COUNT(*) as count FROM notifications WHERE user_id = $user_id AND seen = 0";
        $res = @mysqli_query($conn, $q);
        if ($res) {
            $unread_notifications = (int)(mysqli_fetch_assoc($res)['count'] ?? 0);
        }
    } else {
        // Fall back to legacy per-table counts
        $room_q = "SELECT COUNT(*) as count FROM bookings 
                   WHERE user_id = $user_id 
                   AND notification_seen = 0
                   AND (status = 'approved' OR status = 'rejected')";
        $room_res = @mysqli_query($conn, $room_q);
        $room_count = ($room_res) ? (int)(mysqli_fetch_assoc($room_res)['count'] ?? 0) : 0;

        $dining_q = "SELECT COUNT(*) as count FROM dining_reservations 
                     WHERE user_id = $user_id 
                     AND notification_seen = 0
                     AND (status = 'confirmed' OR status = 'cancelled')";
        $dining_res = @mysqli_query($conn, $dining_q);
        $dining_count = ($dining_res) ? (int)(mysqli_fetch_assoc($dining_res)['count'] ?? 0) : 0;

        $spa_q = "SELECT COUNT(*) as count FROM spa_bookings 
                  WHERE user_id = $user_id 
                  AND notification_seen = 0
                  AND (status = 'confirmed' OR status = 'cancelled')";
        $spa_res = @mysqli_query($conn, $spa_q);
        $spa_count = ($spa_res) ? (int)(mysqli_fetch_assoc($spa_res)['count'] ?? 0) : 0;

        $unread_notifications = $room_count + $dining_count + $spa_count;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Grand Aurelia</title>
    <link rel="icon" type="image/png" href="<?php echo $is_admin_page ? '../' : ''; ?>images/logo.png">
    <!-- Bootstrap CSS (for other components) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Tailwind CSS -->
    <!-- Tailwind CSS (Local) -->
    <link href="<?php echo $is_admin_page ? '../' : ''; ?>dist/output.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo $is_admin_page ? '../' : ''; ?>styles.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body class="font-poppins">

    <!-- Navigation Bar (Tailwind CSS) -->
    <nav class="bg-gradient-to-r from-gray-900 via-gray-800 to-gray-900 text-white sticky top-0 z-50 shadow-2xl">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-20">
                <!-- Logo and Brand -->
                <a href="<?php echo $is_admin_page ? '../' : ''; ?>index.php"
                    class="flex items-center space-x-3 group transition-transform duration-300 hover:scale-105">
                    <img src="<?php echo $is_admin_page ? '../' : ''; ?>images/logo.png"
                        alt="Hotel Logo"
                        class="h-12 w-12 rounded-full object-cover transition-all duration-500 ease-in-out group-hover:scale-110 group-hover:shadow-[0_0_15px_rgba(234,179,8,0.5)]">
                    <span class="text-2xl font-bold tracking-wide bg-gradient-to-r from-white to-gray-300 bg-clip-text text-transparent">
                        The Grand Aurelia
                    </span>
                </a>

                <!-- Desktop Navigation -->
                <div class="hidden lg:flex items-center space-x-1">
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <!-- Admin Navigation -->
                        <a href="<?php echo $admin_prefix; ?>dashboard.php"
                            class="px-4 py-2.5 rounded-lg font-semibold text-sm tracking-wide transition-all duration-300 flex items-center gap-2 
                                  hover:bg-white/10 hover:-translate-y-0.5 hover:shadow-lg
                                  <?php echo ($current_page === 'dashboard.php') ? 'bg-white/15 shadow-md' : ''; ?>">
                            <i class="fas fa-tachometer-alt text-base"></i>
                            <span>Dashboard</span>
                        </a>

                        <!-- Bookings Dropdown -->
                        <div class="relative group">
                            <button id="bookingsDropdownBtn" class="px-4 py-2.5 rounded-lg font-semibold text-sm tracking-wide transition-all duration-300 flex items-center gap-2 hover:bg-white/10">
                                <i class="fas fa-calendar-check text-base"></i>
                                <span>Bookings</span>
                                <i class="fas fa-chevron-down text-xs transition-transform duration-300 group-hover:rotate-180"></i>
                            </button>
                            <div id="bookingsDropdownMenu" class="absolute left-0 mt-2 w-56 bg-white rounded-xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform group-hover:translate-y-1 z-[100]">
                                <a href="<?php echo $admin_prefix; ?>bookings.php"
                                    class="flex items-center px-5 py-3 text-gray-800 hover:bg-gradient-to-r hover:from-gray-50 hover:to-blue-50 hover:pl-6 transition-all duration-200 rounded-t-xl">
                                    <i class="fas fa-hotel mr-3 text-blue-600"></i>
                                    <span class="font-medium text-gray-800">Room Bookings</span>
                                </a>
                                <a href="<?php echo $admin_prefix; ?>dining_bookings.php"
                                    class="flex items-center px-5 py-3 text-gray-800 hover:bg-gradient-to-r hover:from-gray-50 hover:to-blue-50 hover:pl-6 transition-all duration-200">
                                    <i class="fas fa-utensils mr-3 text-blue-600"></i>
                                    <span class="font-medium text-gray-800">Dining Reservations</span>
                                </a>
                                <a href="<?php echo $admin_prefix; ?>spa_bookings.php"
                                    class="flex items-center px-5 py-3 text-gray-800 hover:bg-gradient-to-r hover:from-gray-50 hover:to-blue-50 hover:pl-6 transition-all duration-200 rounded-b-xl">
                                    <i class="fas fa-spa mr-3 text-blue-600"></i>
                                    <span class="font-medium text-gray-800">Spa Bookings</span>
                                </a>
                            </div>
                        </div>

                        <a href="<?php echo $admin_prefix; ?>rooms.php"
                            class="px-4 py-2.5 rounded-lg font-semibold text-sm tracking-wide transition-all duration-300 flex items-center gap-2 
                                  hover:bg-white/10 hover:-translate-y-0.5 hover:shadow-lg
                                  <?php echo ($current_page === 'rooms.php') ? 'bg-white/15 shadow-md' : ''; ?>">
                            <i class="fas fa-door-open text-base"></i>
                            <span>Rooms</span>
                        </a>
                    <?php else: ?>
                        <!-- Regular User Navigation -->
                        <a href="<?php echo $is_admin_page ? '../' : ''; ?>index.php"
                            class="px-4 py-2.5 rounded-lg font-semibold text-sm tracking-wide transition-all duration-300
                                  hover:bg-white/10 hover:-translate-y-0.5 hover:shadow-lg
                                  <?php echo ($current_page === 'index.php') ? 'bg-white/15 shadow-md' : ''; ?>">
                            Home
                        </a>
                        <a href="<?php echo $is_admin_page ? '../' : ''; ?>view_rooms.php"
                            class="px-4 py-2.5 rounded-lg font-semibold text-sm tracking-wide transition-all duration-300
                                  hover:bg-white/10 hover:-translate-y-0.5 hover:shadow-lg
                                  <?php echo ($current_page === 'view_rooms.php') ? 'bg-white/15 shadow-md' : ''; ?>">
                            Rooms & Suites
                        </a>
                        <a href="<?php echo $is_admin_page ? '../' : ''; ?>dining.php"
                            class="px-4 py-2.5 rounded-lg font-semibold text-sm tracking-wide transition-all duration-300
                                  hover:bg-white/10 hover:-translate-y-0.5 hover:shadow-lg
                                  <?php echo ($current_page === 'dining.php') ? 'bg-white/15 shadow-md' : ''; ?>">
                            Dining
                        </a>
                        <a href="<?php echo $is_admin_page ? '../' : ''; ?>spa.php"
                            class="px-4 py-2.5 rounded-lg font-semibold text-sm tracking-wide transition-all duration-300
                                  hover:bg-white/10 hover:-translate-y-0.5 hover:shadow-lg
                                  <?php echo ($current_page === 'spa.php') ? 'bg-white/15 shadow-md' : ''; ?>">
                            Spa & Wellness
                        </a>

                    <?php endif; ?>
                </div>

                <!-- User Account Section -->
                <div class="hidden lg:flex items-center gap-3">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- User Dropdown -->
                        <div class="relative group">
                            <button id="userDropdownBtn" class="relative flex items-center gap-2 px-3 py-2 border-2 border-white/30 rounded-full font-semibold text-sm tracking-wide
                                         transition-all duration-300 hover:bg-white/10 hover:border-white/50 hover:-translate-y-0.5 hover:shadow-xl focus:outline-none pl-2">

                                <?php
                                $header_profile_img = 'profileimg/' . $_SESSION['user_id'] . '.jpg';
                                $has_header_img = file_exists($header_profile_img);
                                $user_initial = strtoupper(substr($_SESSION['username'], 0, 1));
                                ?>

                                <?php if ($has_header_img): ?>
                                    <img src="<?php echo $header_profile_img . '?' . time(); ?>" alt="Profile" class="w-8 h-8 rounded-full object-cover border border-white/50">
                                <?php else: ?>
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-yellow-400 to-yellow-600 flex items-center justify-center text-white font-bold border border-white/50 shadow-sm">
                                        <?php echo $user_initial; ?>
                                    </div>
                                <?php endif; ?>

                                <span class="pl-1"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                    <span class="bg-gradient-to-r from-red-500 to-red-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded shadow-md ml-1">Admin</span>
                                <?php endif; ?>
                                <?php if (isset($unread_notifications) && $unread_notifications > 0): ?>
                                    <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white shadow-lg animate-pulse border border-white">
                                        <?php echo $unread_notifications; ?>
                                    </span>
                                <?php endif; ?>
                                <i class="fas fa-chevron-down text-xs transition-transform duration-300 group-hover:rotate-180 ml-1"></i>
                            </button>
                            <div id="userDropdownMenu" class="absolute right-0 mt-2 w-64 bg-gray-900 text-white rounded-xl shadow-2xl opacity-0 invisible 
                                        transition-all duration-300 transform translate-y-2 group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 z-50 border border-gray-700 overflow-hidden">
                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                    <a href="<?php echo $admin_prefix; ?>dashboard.php"
                                        class="flex items-center px-5 py-3.5 hover:bg-white/10 transition-all duration-200">
                                        <div class="w-8 h-8 rounded-lg bg-blue-500/20 flex items-center justify-center mr-3 text-blue-400 group-hover:bg-blue-500/30 group-hover:text-blue-300 transition-colors">
                                            <i class="fas fa-tachometer-alt"></i>
                                        </div>
                                        <div>
                                            <span class="font-semibold block text-sm">Dashboard</span>
                                            <span class="text-xs text-gray-400">Admin Control Panel</span>
                                        </div>
                                    </a>
                                <?php else: ?>
                                    <div class="px-5 py-3 border-b border-gray-700 bg-gray-800/50">
                                        <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Account</p>
                                    </div>
                                    <a href="<?php echo $is_admin_page ? '../' : ''; ?>profile.php"
                                        class="flex items-center px-5 py-3.5 hover:bg-white/10 transition-all duration-200 group/item">
                                        <i class="fas fa-user-circle mr-3 text-purple-400 group-hover/item:text-purple-300 transition-colors w-5 text-center"></i>
                                        <span class="font-medium text-sm">My Profile</span>
                                    </a>
                                    <a href="<?php echo $is_admin_page ? '../' : ''; ?>view_bookings.php"
                                        class="flex items-center px-5 py-3.5 hover:bg-white/10 transition-all duration-200 group/item">
                                        <i class="fas fa-list mr-3 text-blue-400 group-hover/item:text-blue-300 transition-colors w-5 text-center"></i>
                                        <span class="font-medium text-sm">My Bookings</span>
                                    </a>
                                    <a href="<?php echo $is_admin_page ? '../' : ''; ?>notifications.php"
                                        class="flex items-center justify-between px-5 py-3.5 hover:bg-white/10 transition-all duration-200 group/item">
                                        <div class="flex items-center">
                                            <i class="fas fa-bell mr-3 text-yellow-400 group-hover/item:text-yellow-300 transition-colors w-5 text-center"></i>
                                            <span class="font-medium text-sm">Notifications</span>
                                        </div>
                                        <?php if ($unread_notifications > 0): ?>
                                            <span class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-lg"><?php echo $unread_notifications; ?></span>
                                        <?php endif; ?>
                                    </a>
                                <?php endif; ?>
                                <div class="border-t border-gray-700 my-1"></div>
                                <a href="<?php echo $is_admin_page ? '../' : ''; ?>logout.php"
                                    class="flex items-center px-5 py-3.5 hover:bg-red-500/10 hover:text-red-400 text-red-500 transition-all duration-200 group/logout">
                                    <i class="fas fa-sign-out-alt mr-3 w-5 text-center group-hover/logout:transform group-hover/logout:translate-x-1 transition-transform"></i>
                                    <span class="font-medium text-sm">Logout</span>
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo $is_admin_page ? '../' : ''; ?>login.php"
                            class="px-5 py-2.5 border-2 border-white/30 rounded-lg font-semibold text-sm tracking-wide flex items-center gap-2
                                  transition-all duration-300 hover:bg-white hover:text-gray-900 hover:-translate-y-0.5 hover:shadow-xl">
                            <i class="fas fa-sign-in-alt"></i>
                            <span>Login</span>
                        </a>
                    <?php endif; ?>

                    <?php if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'): ?>
                        <a href="<?php echo $is_admin_page ? '../' : ''; ?>add_booking.php"
                            class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg font-bold text-sm tracking-wide flex items-center gap-2
                                  transition-all duration-300 hover:from-blue-700 hover:to-blue-800 hover:-translate-y-0.5 hover:shadow-2xl shadow-blue-500/50">
                            <i class="fas fa-calendar-check"></i>
                            <span>Book Now</span>
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Mobile Menu Button -->
                <button id="mobileMenuBtn" class="lg:hidden text-white p-2 hover:bg-white/10 rounded-lg transition-all duration-300">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>

            <!-- Mobile Navigation -->
            <div id="mobileMenu" class="lg:hidden hidden pb-4">
                <div class="flex flex-col space-y-1 pt-2">
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <a href="<?php echo $admin_prefix; ?>dashboard.php"
                            class="px-4 py-3 rounded-lg hover:bg-white/10 transition-all duration-200 flex items-center gap-3 font-medium">
                            <i class="fas fa-tachometer-alt"></i>Dashboard
                        </a>
                        <a href="<?php echo $admin_prefix; ?>bookings.php"
                            class="px-4 py-3 rounded-lg hover:bg-white/10 transition-all duration-200 flex items-center gap-3 font-medium">
                            <i class="fas fa-hotel"></i>Room Bookings
                        </a>
                        <a href="<?php echo $admin_prefix; ?>dining_bookings.php"
                            class="px-4 py-3 rounded-lg hover:bg-white/10 transition-all duration-200 flex items-center gap-3 font-medium">
                            <i class="fas fa-utensils"></i>Dining Reservations
                        </a>
                        <a href="<?php echo $admin_prefix; ?>spa_bookings.php"
                            class="px-4 py-3 rounded-lg hover:bg-white/10 transition-all duration-200 flex items-center gap-3 font-medium">
                            <i class="fas fa-spa"></i>Spa Bookings
                        </a>
                        <a href="<?php echo $admin_prefix; ?>rooms.php"
                            class="px-4 py-3 rounded-lg hover:bg-white/10 transition-all duration-200 flex items-center gap-3 font-medium">
                            <i class="fas fa-door-open"></i>Rooms
                        </a>
                    <?php else: ?>
                        <a href="<?php echo $is_admin_page ? '../' : ''; ?>index.php"
                            class="px-4 py-3 rounded-lg hover:bg-white/10 transition-all duration-200 font-medium">Home</a>
                        <a href="<?php echo $is_admin_page ? '../' : ''; ?>view_rooms.php"
                            class="px-4 py-3 rounded-lg hover:bg-white/10 transition-all duration-200 font-medium">Rooms & Suites</a>
                        <a href="<?php echo $is_admin_page ? '../' : ''; ?>dining.php"
                            class="px-4 py-3 rounded-lg hover:bg-white/10 transition-all duration-200 font-medium">Dining</a>
                        <a href="<?php echo $is_admin_page ? '../' : ''; ?>spa.php"
                            class="px-4 py-3 rounded-lg hover:bg-white/10 transition-all duration-200 font-medium">Spa & Wellness</a>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="<?php echo $is_admin_page ? '../' : ''; ?>notifications.php"
                                class="px-4 py-3 rounded-lg hover:bg-white/10 transition-all duration-200 flex items-center justify-between font-medium">
                                <span><i class="fas fa-bell mr-2"></i>Notifications</span>
                                <?php if ($unread_notifications > 0): ?>
                                    <span class="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full"><?php echo $unread_notifications; ?></span>
                                <?php endif; ?>
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <hr class="border-white/20 my-2">
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <a href="<?php echo $admin_prefix; ?>dashboard.php"
                                class="px-4 py-3 rounded-lg hover:bg-white/10 transition-all duration-200 flex items-center gap-3 font-medium">
                                <i class="fas fa-tachometer-alt"></i>Admin Dashboard
                            </a>
                        <?php else: ?>
                            <a href="<?php echo $is_admin_page ? '../' : ''; ?>profile.php"
                                class="px-4 py-3 rounded-lg hover:bg-white/10 transition-all duration-200 flex items-center gap-3 font-medium">
                                <i class="fas fa-user-circle"></i>My Profile
                            </a>
                            <a href="<?php echo $is_admin_page ? '../' : ''; ?>view_bookings.php"
                                class="px-4 py-3 rounded-lg hover:bg-white/10 transition-all duration-200 flex items-center gap-3 font-medium">
                                <i class="fas fa-list"></i>My Bookings
                            </a>
                        <?php endif; ?>
                        <a href="<?php echo $is_admin_page ? '../' : ''; ?>logout.php"
                            class="px-4 py-3 rounded-lg hover:bg-red-600 transition-all duration-200 flex items-center gap-3 font-medium text-red-300">
                            <i class="fas fa-sign-out-alt"></i>Logout
                        </a>
                    <?php else: ?>
                        <a href="<?php echo $is_admin_page ? '../' : ''; ?>login.php"
                            class="mx-4 my-2 px-4 py-3 border-2 border-white/30 rounded-lg hover:bg-white hover:text-gray-900 transition-all duration-200 text-center font-semibold">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login
                        </a>
                    <?php endif; ?>

                    <?php if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'): ?>
                        <a href="<?php echo $is_admin_page ? '../' : ''; ?>add_booking.php"
                            class="mx-4 my-2 px-4 py-3 bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-200 text-center font-bold shadow-lg">
                            <i class="fas fa-calendar-check mr-2"></i>Book Now
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <script>
        // Mobile menu toggle
        document.getElementById('mobileMenuBtn')?.addEventListener('click', function() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        });

        // User Dropdown Toggle (for click support)
        const userDropdownBtn = document.getElementById('userDropdownBtn');
        const userDropdownMenu = document.getElementById('userDropdownMenu');

        if (userDropdownBtn && userDropdownMenu) {
            userDropdownBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                userDropdownMenu.classList.toggle('invisible');
                userDropdownMenu.classList.toggle('opacity-0');
                userDropdownMenu.classList.toggle('translate-y-2');
                userDropdownMenu.classList.toggle('translate-y-0');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!userDropdownBtn.contains(e.target) && !userDropdownMenu.contains(e.target)) {
                    userDropdownMenu.classList.add('invisible', 'opacity-0', 'translate-y-2');
                    userDropdownMenu.classList.remove('translate-y-0');
                }
            });
        }

        // Bookings Dropdown Toggle (for click support)
        const bookingsDropdownBtn = document.getElementById('bookingsDropdownBtn');
        const bookingsDropdownMenu = document.getElementById('bookingsDropdownMenu');

        if (bookingsDropdownBtn && bookingsDropdownMenu) {
            bookingsDropdownBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                bookingsDropdownMenu.classList.toggle('invisible');
                bookingsDropdownMenu.classList.toggle('opacity-0');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!bookingsDropdownBtn.contains(e.target) && !bookingsDropdownMenu.contains(e.target)) {
                    bookingsDropdownMenu.classList.add('invisible', 'opacity-0');
                }
            });
        }
    </script>

    <!-- Main content wrapper -->
    <main class="flex-grow-1 page-fade">