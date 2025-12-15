<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
include('header.php');

$user_id = $_SESSION['user_id'];
?>

<div class="min-h-screen bg-gray-50 py-12">
    <div class="container mx-auto px-4 max-w-4xl">

        <div class="mb-8 flex items-center justify-between">
            <h1 class="text-3xl font-bold font-serif text-gray-900">Account Settings</h1>
            <a href="profile.php" class="text-gray-500 hover:text-gray-900 font-medium flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Back to Profile
            </a>
        </div>

        <div class="grid grid-cols-1 gap-8">

            <!-- Notification Area -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl flex items-center gap-3">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($_SESSION['error']);
                    unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded-xl flex items-center gap-3">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($_SESSION['success']);
                    unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <!-- Change Password Section -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-lock text-yellow-600"></i> Security
                    </h2>
                    <p class="text-gray-500 text-sm mt-1">Update your password to keep your account secure.</p>
                </div>
                <div class="p-6">
                    <form action="process_password_change.php" method="POST" class="space-y-4 max-w-lg">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Current Password</label>
                            <input type="password" name="current_password" required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-black focus:ring-1 focus:ring-black transition-colors">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">New Password</label>
                            <input type="password" name="new_password" required minlength="8"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-black focus:ring-1 focus:ring-black transition-colors">
                            <p class="text-xs text-gray-500 mt-1">Must be at least 8 characters</p>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Confirm New Password</label>
                            <input type="password" name="confirm_password" required minlength="8"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-black focus:ring-1 focus:ring-black transition-colors">
                        </div>
                        <div class="pt-2">
                            <button type="submit" class="bg-black text-white px-6 py-3 rounded-xl font-bold hover:bg-gray-800 transition-colors shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Delete Account Section -->
            <div class="bg-red-50 rounded-2xl shadow-sm border border-red-100 overflow-hidden">
                <div class="p-6">
                    <h2 class="text-xl font-bold text-red-700 flex items-center gap-2">
                        <i class="fas fa-exclamation-triangle"></i> Danger Zone
                    </h2>
                    <p class="text-red-600/80 text-sm mt-1">Once you delete your account, there is no going back. Please be certain.</p>

                    <div class="mt-6 flex items-center justify-between flex-wrap gap-4">
                        <div>
                            <p class="font-bold text-gray-900">Delete Account</p>
                            <p class="text-sm text-gray-500">Permanently delete your account and all data.</p>
                        </div>
                        <button onclick="document.getElementById('deleteAccountModal').classList.remove('hidden')"
                            class="bg-white border-2 border-red-200 text-red-600 px-6 py-2.5 rounded-xl font-bold hover:bg-red-50 transition-colors">
                            Delete Account
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteAccountModal" class="fixed inset-0 bg-black/80 z-50 hidden flex items-center justify-center backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 transform transition-all scale-100 border-t-8 border-red-600">
        <h3 class="text-2xl font-bold text-gray-900 mb-4">Are you absolutely sure?</h3>
        <p class="text-gray-600 mb-6">
            This action cannot be undone. This will permanently delete your account, booking history, and profile data from our servers.
        </p>

        <form action="process_delete_account.php" method="POST">
            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-2">Please type "DELETE" to confirm</label>
                <input type="text" name="confirmation" required pattern="DELETE" placeholder="DELETE"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:border-red-500 focus:ring-1 focus:ring-red-500 transition-colors">
            </div>

            <div class="flex gap-4">
                <button type="button" onclick="document.getElementById('deleteAccountModal').classList.add('hidden')"
                    class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition-colors">
                    Cancel
                </button>
                <button type="submit"
                    class="flex-1 px-4 py-3 bg-red-600 text-white font-bold rounded-xl hover:bg-red-700 transition-colors shadow-lg shadow-red-500/30">
                    Delete Account
                </button>
            </div>
        </form>
    </div>
</div>