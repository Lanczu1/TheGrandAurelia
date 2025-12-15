<?php
session_start();
include('header.php');
include('security.php');

// If user is already logged in, redirect to home page
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$csrf_token = generate_csrf_token();
$email = isset($_SESSION['reset_email']) ? $_SESSION['reset_email'] : '';
?>

<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8 relative font-sans">
    <!-- Decorative Background Elements -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
        <div class="absolute top-[-10%] right-[-5%] w-96 h-96 bg-yellow-500/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-[-10%] left-[-5%] w-[500px] h-[500px] bg-blue-500/5 rounded-full blur-3xl"></div>
    </div>

    <div class="max-w-[1100px] w-full bg-white rounded-[2.5rem] shadow-2xl overflow-hidden flex flex-col md:flex-row relative z-10 min-h-[600px]">

        <!-- Left Side: Form -->
        <div class="w-full md:w-1/2 p-10 md:p-14 lg:p-16 flex flex-col justify-center bg-white relative">
            <div class="mb-10">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-10 h-10 rounded-full bg-yellow-500 flex items-center justify-center text-white font-bold text-xl">
                        GA
                    </div>
                    <span class="font-bold text-xl tracking-tight text-gray-900">Grand Aurelia</span>
                </div>
                <h1 class="font-serif text-3xl md:text-4xl font-bold text-gray-900 leading-tight mb-4">
                    Reset <span class="text-yellow-600">Password</span>
                </h1>
                <p class="text-gray-500">Enter the code sent to your email and your new password.</p>
            </div>

            <!-- Alerts -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="mb-6 bg-red-50 text-red-600 px-4 py-3 rounded-xl text-sm border border-red-100 flex items-center gap-3 animate-pulse">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php
                    echo htmlspecialchars($_SESSION['error']);
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="mb-6 bg-green-50 text-green-600 px-4 py-3 rounded-xl text-sm border border-green-100 flex items-center gap-3">
                    <i class="fas fa-check-circle"></i>
                    <?php
                    echo htmlspecialchars($_SESSION['success']);
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <form class="space-y-6" action="process_reset_password.php" method="POST" id="resetPasswordForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <!-- We need the email to identify the user, preferably hidden if it's in session, or ask for it if not -->
                <?php if ($email): ?>
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                    <div class="text-sm text-gray-500 mb-2">Resetting password for: <strong><?php echo htmlspecialchars($email); ?></strong></div>
                <?php else: ?>
                    <div class="group">
                        <label for="email" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 ml-1">Email Address</label>
                        <div class="relative">
                            <input type="email" id="email" name="email" class="block w-full px-5 py-4 bg-gray-50 border-2 border-gray-100 rounded-2xl text-gray-900 placeholder-transparent focus:outline-none focus:bg-white focus:border-yellow-500 focus:ring-0 transition-all duration-300 peer font-medium" placeholder="Email Address" required>
                            <div class="absolute inset-y-0 right-0 pr-5 flex items-center pointer-events-none text-gray-400 peer-focus:text-yellow-500 transition-colors">
                                <i class="fas fa-envelope"></i>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="space-y-5">
                    <!-- Reset Code -->
                    <div class="group">
                        <label for="code" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 ml-1">Reset Code</label>
                        <div class="relative">
                            <input type="text"
                                id="code"
                                name="code"
                                class="block w-full px-5 py-4 bg-gray-50 border-2 border-gray-100 rounded-2xl text-gray-900 placeholder-transparent focus:outline-none focus:bg-white focus:border-yellow-500 focus:ring-0 transition-all duration-300 peer font-medium tracking-widest text-lg"
                                placeholder="Reset Code"
                                required
                                maxlength="6">
                            <div class="absolute inset-y-0 right-0 pr-5 flex items-center pointer-events-none text-gray-400 peer-focus:text-yellow-500 transition-colors">
                                <i class="fas fa-key"></i>
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-red-500 hidden font-medium ml-1" id="codeError"></p>
                    </div>

                    <!-- New Password -->
                    <div class="group">
                        <label for="password" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 ml-1">New Password</label>
                        <div class="relative">
                            <input type="password"
                                id="password"
                                name="password"
                                class="block w-full px-5 py-4 bg-gray-50 border-2 border-gray-100 rounded-2xl text-gray-900 placeholder-transparent focus:outline-none focus:bg-white focus:border-yellow-500 focus:ring-0 transition-all duration-300 peer font-medium"
                                placeholder="New Password"
                                required
                                minlength="8">
                            <div class="absolute inset-y-0 right-0 pr-5 flex items-center pointer-events-none text-gray-400 peer-focus:text-yellow-500 transition-colors">
                                <i class="fas fa-lock"></i>
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-red-500 hidden font-medium ml-1" id="passwordError"></p>
                    </div>

                    <!-- Confirm Password -->
                    <div class="group">
                        <label for="confirm_password" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 ml-1">Confirm New Password</label>
                        <div class="relative">
                            <input type="password"
                                id="confirm_password"
                                name="confirm_password"
                                class="block w-full px-5 py-4 bg-gray-50 border-2 border-gray-100 rounded-2xl text-gray-900 placeholder-transparent focus:outline-none focus:bg-white focus:border-yellow-500 focus:ring-0 transition-all duration-300 peer font-medium"
                                placeholder="Confirm New Password"
                                required>
                            <div class="absolute inset-y-0 right-0 pr-5 flex items-center pointer-events-none text-gray-400 peer-focus:text-yellow-500 transition-colors">
                                <i class="fas fa-lock"></i>
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-red-500 hidden font-medium ml-1" id="confirmPasswordError"></p>
                    </div>
                </div>

                <div class="pt-6 flex flex-col gap-4">
                    <button type="submit" class="w-full py-4 px-6 bg-gray-900 hover:bg-gray-800 text-white font-bold rounded-2xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300 text-sm tracking-wide uppercase">
                        Reset Password
                    </button>
                    <a href="login.php" class="text-center font-bold text-gray-500 hover:text-gray-800 transition-colors text-sm">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Login
                    </a>
                </div>
            </form>
        </div>

        <!-- Right Side: Image -->
        <div class="hidden md:block w-1/2 relative bg-gray-900 overflow-hidden">
            <img src="images/penthouse.jpg" alt="Luxury Hotel" class="absolute inset-0 w-full h-full object-cover opacity-90 transition-transform duration-[20s] hover:scale-110" style="filter: grayscale(20%) sepia(10%);">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-900/40 via-transparent to-yellow-900/40 mix-blend-overlay"></div>
            <div class="absolute inset-0 bg-black/20"></div>

            <div class="absolute bottom-12 left-12 right-12 z-20">
                <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/20 shadow-xl transform transition-all hover:bg-white/20">
                    <i class="fas fa-shield-alt text-3xl text-yellow-400 mb-4"></i>
                    <h3 class="text-white font-serif text-xl font-bold mb-2">Create New Password</h3>
                    <p class="text-white font-serif text-lg leading-relaxed italic opacity-95">"Secure your account with a strong, unique password."</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        'use strict';
        const form = document.getElementById('resetPasswordForm');

        function showError(input, message) {
            const errorElement = document.getElementById(input.id + 'Error');
            errorElement.textContent = message;
            errorElement.classList.remove('hidden');
            input.classList.add('border-red-500', 'focus:border-red-500');
            input.classList.remove('border-gray-100', 'focus:border-yellow-500');
        }

        function clearError(input) {
            const errorElement = document.getElementById(input.id + 'Error');
            errorElement.classList.add('hidden');
            input.classList.remove('border-red-500', 'focus:border-red-500');
            input.classList.add('border-gray-100', 'focus:border-yellow-500');
        }

        form.addEventListener('submit', function(e) {
            let isValid = true;
            const codeInput = document.getElementById('code');
            const passwordInput = document.getElementById('password');
            const confirmInput = document.getElementById('confirm_password');

            // Validate Code
            if (!codeInput.value.trim()) {
                isValid = false;
                showError(codeInput, 'Reset code is required');
            } else if (codeInput.value.trim().length !== 6) {
                isValid = false;
                showError(codeInput, 'Code must be 6 digits');
            } else {
                clearError(codeInput);
            }

            // Validate Password
            if (!passwordInput.value) {
                isValid = false;
                showError(passwordInput, 'Password is required');
            } else if (passwordInput.value.length < 8) {
                isValid = false;
                showError(passwordInput, 'Password must be at least 8 characters');
            } else {
                clearError(passwordInput);
            }

            // Validate Confirm Password
            if (passwordInput.value !== confirmInput.value) {
                isValid = false;
                showError(confirmInput, 'Passwords do not match');
            } else {
                clearError(confirmInput);
            }

            if (!isValid) {
                e.preventDefault();
            }
        });

        // Clear errors on input
        ['code', 'password', 'confirm_password'].forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.addEventListener('input', () => clearError(input));
            }
        });
    })();
</script>