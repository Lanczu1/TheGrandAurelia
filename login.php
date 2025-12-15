<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Support redirect via GET param (sets session redirect target safely)
if (isset($_GET['redirect'])) {
    $raw = $_GET['redirect'];
    $path = parse_url($raw, PHP_URL_PATH);
    $base = basename($path);
    $allowed = ['spa.php', 'dining.php', 'add_booking.php', 'view_rooms.php', 'index.php'];
    if (in_array($base, $allowed)) {
        $_SESSION['redirect_after_login'] = $base;
    }
}

// Optional notification key to display a friendly message after redirect
if (isset($_GET['notify'])) {
    $notify = $_GET['notify'];
    $allowed_notifications = ['please_login'];
    if (in_array($notify, $allowed_notifications)) {
        $_SESSION['error'] = 'Please login to make a booking';
    }
}

// If user is already logged in, redirect to home page
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

include('header.php');
include('security.php');

// Generate CSRF token
$csrf_token = generate_csrf_token();
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
                <h1 class="font-serif text-4xl md:text-5xl font-bold text-gray-900 leading-tight mb-4">
                    The <span class="text-yellow-600">Luxury</span> you<br>deserve to have.
                </h1>
                <p class="text-gray-500">Welcome back! Please login to your account to continue.</p>
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

            <form class="space-y-6" action="process_login.php" method="POST" id="loginForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                <div class="space-y-5">
                    <!-- Username/Email -->
                    <div class="group">
                        <label for="username" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 ml-1">Username or Email</label>
                        <div class="relative">
                            <input type="text"
                                id="username"
                                name="username"
                                class="block w-full px-5 py-4 bg-gray-50 border-2 border-gray-100 rounded-2xl text-gray-900 placeholder-transparent focus:outline-none focus:bg-white focus:border-yellow-500 focus:ring-0 transition-all duration-300 peer font-medium"
                                placeholder="Username"
                                required>
                            <div class="absolute inset-y-0 right-0 pr-5 flex items-center pointer-events-none text-gray-400 peer-focus:text-yellow-500 transition-colors">
                                <i class="fas fa-envelope"></i>
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-red-500 hidden font-medium ml-1" id="usernameError"></p>
                    </div>

                    <!-- Password -->
                    <div class="group">
                        <div class="flex justify-between items-center mb-2 ml-1">
                            <label for="password" class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Password</label>
                            <a href="forgot_password.php" class="text-xs font-bold text-yellow-600 hover:text-yellow-700 transition-colors">Forgot Password?</a>
                        </div>
                        <div class="relative">
                            <input type="password"
                                id="password"
                                name="password"
                                class="block w-full px-5 py-4 bg-gray-50 border-2 border-gray-100 rounded-2xl text-gray-900 placeholder-transparent focus:outline-none focus:bg-white focus:border-yellow-500 focus:ring-0 transition-all duration-300 peer font-medium"
                                placeholder="Password"
                                required>
                            <button type="button" class="absolute inset-y-0 right-0 pr-5 flex items-center text-gray-400 hover:text-gray-600 transition-colors cursor-pointer focus:outline-none" id="passwordToggle">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <p class="mt-2 text-xs text-red-500 hidden font-medium ml-1" id="passwordError"></p>
                    </div>
                </div>

                <div class="pt-2 flex items-center ml-1">
                    <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 text-yellow-600 focus:ring-yellow-500 border-gray-300 rounded cursor-pointer">
                    <label for="remember-me" class="ml-2 block text-sm font-medium text-gray-500 cursor-pointer select-none">Remember me</label>
                </div>

                <div class="pt-6 flex gap-4">
                    <button type="submit" class="flex-1 py-4 px-6 bg-gray-900 hover:bg-gray-800 text-white font-bold rounded-2xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300 text-sm tracking-wide uppercase">
                        Login
                    </button>
                    <a href="register.php" class="flex-1 py-4 px-6 bg-white border-2 border-gray-100 hover:border-gray-200 text-gray-900 font-bold rounded-2xl hover:bg-gray-50 transform hover:-translate-y-0.5 transition-all duration-300 text-center text-sm tracking-wide uppercase flex items-center justify-center">
                        Sign Up
                    </a>
                </div>

                <p class="text-xs text-gray-400 text-center mt-8">
                    By signing up, you agree to our <a href="terms.php" class="underline hover:text-gray-600">Terms</a> and <a href="privacy.php" class="underline hover:text-gray-600">Privacy Policy</a>.
                </p>
            </form>
        </div>

        <!-- Right Side: Image -->
        <div class="hidden md:block w-1/2 relative bg-gray-900 overflow-hidden">
            <img src="images/penthouse.jpg" alt="Luxury Hotel" class="absolute inset-0 w-full h-full object-cover opacity-90 transition-transform duration-[20s] hover:scale-110">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-900/30 via-transparent to-yellow-900/30 mix-blend-overlay"></div>
            <div class="absolute inset-0 bg-black/10"></div>

            <!-- Abstract Floating Elements matching reference vibe but more structured -->
            <div class="absolute top-1/2 right-[-50px] transform -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-white/20 to-white/5 backdrop-blur-3xl rounded-full border border-white/20 shadow-2xl z-10 hidden lg:block animate-float"></div>
            <div class="absolute bottom-32 left-20 w-32 h-32 bg-yellow-400/30 backdrop-blur-xl rounded-full blur-xl animate-float-delayed"></div>

            <div class="absolute bottom-12 left-12 right-12 z-20">
                <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/20 shadow-xl transform transition-all hover:bg-white/20">
                    <div class="flex gap-1 text-yellow-400 mb-2 text-xs">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p class="text-white font-serif text-lg leading-relaxed italic opacity-95">"Experience the pinnacle of comfort and style. A true sanctuary in the city."</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes float {

        0%,
        100% {
            transform: translateY(-50%) translateY(0);
        }

        50% {
            transform: translateY(-50%) translateY(-20px);
        }
    }

    @keyframes float-delayed {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-15px);
        }
    }

    .animate-float {
        animation: float 6s ease-in-out infinite;
    }

    .animate-float-delayed {
        animation: float-delayed 8s ease-in-out infinite;
    }
</style>

<script>
    (function() {
        'use strict';

        const form = document.getElementById('loginForm');
        const passwordToggle = document.getElementById('passwordToggle');
        const passwordInput = document.getElementById('password');

        passwordToggle.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });

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
            const inputs = form.querySelectorAll('input[required]');

            inputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    showError(input, 'This field is required');
                } else {
                    clearError(input);
                }
            });

            if (!isValid) {
                e.preventDefault();
            }
        });

        form.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', () => clearError(input));
        });
    })();
</script>