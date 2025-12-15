<?php
// Admin sidebar - include this in admin pages for consistent navigation
$current_admin_page = basename($_SERVER['PHP_SELF']);
?>

<div class="flex min-h-screen bg-gray-50 font-sans">
    <!-- Sidebar -->
    <aside class="w-64 bg-gray-900 text-white flex-shrink-0 hidden lg:block shadow-2xl relative z-10">
        <div class="p-6">
            <h1 class="text-2xl font-bold font-serif text-white tracking-wide">
                Admin<span class="text-yellow-600">Panel</span>
            </h1>
        </div>
        
        <nav class="mt-2">
            <div class="px-4 mb-2 text-xs font-bold text-gray-500 uppercase tracking-wider">Main</div>
            <a href="dashboard.php" class="group flex items-center px-6 py-3 text-sm font-medium transition-colors <?php echo $current_admin_page === 'dashboard.php' ? 'bg-yellow-600 text-white border-r-4 border-yellow-400' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                <i class="fas fa-tachometer-alt w-6 text-center mr-3 <?php echo $current_admin_page === 'dashboard.php' ? 'text-white' : 'text-gray-500 group-hover:text-yellow-500'; ?>"></i>
                Dashboard
            </a>
            
            <div class="px-4 mt-8 mb-2 text-xs font-bold text-gray-500 uppercase tracking-wider">Bookings & Reservations</div>
            <a href="bookings.php" class="group flex items-center px-6 py-3 text-sm font-medium transition-colors <?php echo $current_admin_page === 'bookings.php' ? 'bg-yellow-600 text-white border-r-4 border-yellow-400' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                <i class="fas fa-hotel w-6 text-center mr-3 <?php echo $current_admin_page === 'bookings.php' ? 'text-white' : 'text-gray-500 group-hover:text-yellow-500'; ?>"></i>
                Room Bookings
            </a>
            <a href="dining_bookings.php" class="group flex items-center px-6 py-3 text-sm font-medium transition-colors <?php echo $current_admin_page === 'dining_bookings.php' ? 'bg-yellow-600 text-white border-r-4 border-yellow-400' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                <i class="fas fa-utensils w-6 text-center mr-3 <?php echo $current_admin_page === 'dining_bookings.php' ? 'text-white' : 'text-gray-500 group-hover:text-yellow-500'; ?>"></i>
                Dining Reservations
            </a>
            <a href="spa_bookings.php" class="group flex items-center px-6 py-3 text-sm font-medium transition-colors <?php echo $current_admin_page === 'spa_bookings.php' ? 'bg-yellow-600 text-white border-r-4 border-yellow-400' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                <i class="fas fa-spa w-6 text-center mr-3 <?php echo $current_admin_page === 'spa_bookings.php' ? 'text-white' : 'text-gray-500 group-hover:text-yellow-500'; ?>"></i>
                Spa Bookings
            </a>
            
            <div class="px-4 mt-8 mb-2 text-xs font-bold text-gray-500 uppercase tracking-wider">Management</div>
            <a href="rooms.php" class="group flex items-center px-6 py-3 text-sm font-medium transition-colors <?php echo $current_admin_page === 'rooms.php' ? 'bg-yellow-600 text-white border-r-4 border-yellow-400' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                <i class="fas fa-door-open w-6 text-center mr-3 <?php echo $current_admin_page === 'rooms.php' ? 'text-white' : 'text-gray-500 group-hover:text-yellow-500'; ?>"></i>
                Manage Rooms
            </a>
            <a href="add_room.php" class="group flex items-center px-6 py-3 text-sm font-medium transition-colors <?php echo $current_admin_page === 'add_room.php' ? 'bg-yellow-600 text-white border-r-4 border-yellow-400' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                <i class="fas fa-plus-circle w-6 text-center mr-3 <?php echo $current_admin_page === 'add_room.php' ? 'text-white' : 'text-gray-500 group-hover:text-yellow-500'; ?>"></i>
                Add Room
            </a>
        </nav>

        <div class="absolute bottom-0 w-full p-6">
            <a href="../logout.php" class="flex items-center text-gray-400 hover:text-white transition-colors">
                <i class="fas fa-sign-out-alt mr-3"></i> Logout
            </a>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <!-- Optional Topbar for Mobile -->
        <header class="bg-white shadow-sm lg:hidden h-16 flex items-center justify-between px-4 z-20">
            <span class="font-serif font-bold text-xl text-gray-900">Admin Panel</span>
            <button class="text-gray-500 focus:outline-none">
                <i class="fas fa-bars text-2xl"></i>
            </button>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6 md:p-8">
