<?php
session_start();

// Check if user has a pending verification
if (!isset($_SESSION['pending_verification_email']) || !isset($_SESSION['pending_verification_user_id'])) {
    header('Location: register.php');
    exit;
}

include('header.php');
include('security.php');

// Generate CSRF token
$csrf_token = generate_csrf_token();

$email = $_SESSION['pending_verification_email'];
?>

<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8 relative font-sans">
    <!-- Decorative Background Elements -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
        <div class="absolute top-[-10%] right-[-5%] w-96 h-96 bg-yellow-500/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-[-10%] left-[-5%] w-[500px] h-[500px] bg-blue-500/5 rounded-full blur-3xl"></div>
    </div>

    <div class="max-w-[1100px] w-full bg-white rounded-[2.5rem] shadow-2xl overflow-hidden flex flex-col md:flex-row relative z-10 min-h-[600px]">

        <!-- Left Side: Form -->
        <div class="w-full md:w-1/2 p-10 md:p-14 flex flex-col justify-center bg-white relative">
            <div class="mb-8 text-center md:text-left">
                <div class="flex items-center gap-3 mb-6 justify-center md:justify-start">
                    <div class="w-10 h-10 rounded-full bg-yellow-500 flex items-center justify-center text-white font-bold text-xl">
                        GA
                    </div>
                    <span class="font-bold text-xl tracking-tight text-gray-900">Grand Aurelia</span>
                </div>
                <h1 class="font-serif text-3xl md:text-4xl font-bold text-gray-900 leading-tight mb-2">
                    Verify Your <span class="text-yellow-600">Identity</span>
                </h1>
                <p class="text-gray-500">We've sent a code to your inbox.</p>
            </div>

            <!-- Email Display -->
            <div class="mb-8 p-4 bg-yellow-50 rounded-xl border border-yellow-100 flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-600 shrink-0">
                    <i class="fas fa-envelope"></i>
                </div>
                <div>
                    <p class="text-xs text-uppercase text-gray-400 font-bold tracking-wider">Sent to</p>
                    <p class="font-medium text-gray-900"><?php echo htmlspecialchars($email); ?></p>
                </div>
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

            <form action="process_verification.php" method="POST" id="verificationForm" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                <div class="group">
                    <label for="verification_code" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 ml-1 text-center md:text-left">Enter 6-Digit Code</label>
                    <div class="relative max-w-sm mx-auto md:mx-0">
                        <input type="text"
                            class="block w-full px-5 py-4 bg-gray-50 border-2 border-gray-100 rounded-2xl text-gray-900 placeholder-gray-300 focus:outline-none focus:bg-white focus:border-yellow-500 focus:ring-0 transition-all duration-300 text-center font-mono text-2xl tracking-[0.5em] font-bold"
                            id="verification_code"
                            name="verification_code"
                            maxlength="6"
                            placeholder="000000"
                            autocomplete="off"
                            required>
                    </div>
                    <p class="text-xs text-red-500 hidden font-medium text-center md:text-left mt-2" id="codeError"></p>
                </div>

                <button type="submit" class="w-full py-4 px-6 bg-gray-900 hover:bg-gray-800 text-white font-bold rounded-2xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300 text-sm tracking-wide uppercase">
                    Verify Email
                </button>
            </form>

            <div class="mt-8 flex flex-col gap-3 text-center text-sm">
                <p class="text-gray-500">
                    Didn't receive the code?
                    <a href="resend_verification.php" class="font-bold text-yellow-600 hover:text-yellow-700 transition-colors">Resend Code</a>
                </p>
                <a href="logout.php" class="text-gray-400 hover:text-gray-600 transition-colors">Cancel and return to home</a>
            </div>
        </div>

        <!-- Right Side: Image -->
        <div class="hidden md:block w-1/2 relative bg-gray-900 overflow-hidden">
            <!-- Use a slightly different image crop or overlay to differentiate but keep theme -->
            <img src="images/penthouse.jpg" alt="Luxury Hotel" class="absolute inset-0 w-full h-full object-cover opacity-90 transition-transform duration-[20s] hover:scale-110" style="transform: scaleX(-1);">
            <div class="absolute inset-0 bg-gradient-to-bl from-blue-900/40 via-transparent to-yellow-900/40 mix-blend-overlay"></div>
            <div class="absolute inset-0 bg-black/20"></div>

            <!-- Abstract Floating Elements -->
            <div class="absolute top-20 right-20 w-48 h-48 bg-white/5 backdrop-blur-3xl rounded-full border border-white/10 shadow-2xl z-10 animate-float"></div>

            <div class="absolute bottom-12 left-12 right-12 z-20 text-center">
                <div class="bg-white/10 backdrop-blur-md rounded-2xl p-8 border border-white/20 shadow-xl">
                    <i class="fas fa-shield-alt text-3xl text-yellow-400 mb-4"></i>
                    <h3 class="text-white font-serif text-xl font-bold mb-2">Secure Access</h3>
                    <p class="text-white/80 text-sm leading-relaxed">Your security is our priority. Verifying your identity helps us keep your account safe.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes float {

        0%,
        100% {
            transform: translateY(-20px);
        }

        50% {
            transform: translateY(0);
        }
    }

    .animate-float {
        animation: float 8s ease-in-out infinite;
    }
</style>

<script>
    (function() {
        'use strict';

        const form = document.getElementById('verificationForm');
        const input = document.getElementById('verification_code');
        const error = document.getElementById('codeError');

        function showError(msg) {
            error.textContent = msg;
            error.classList.remove('hidden');
            input.classList.add('border-red-500');
            input.classList.remove('border-gray-100', 'focus:border-yellow-500');
        }

        function clearError() {
            error.classList.add('hidden');
            input.classList.remove('border-red-500');
            input.classList.add('border-gray-100', 'focus:border-yellow-500');
        }

        input.addEventListener('input', function(e) {
            // Only numbers
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length > 0) clearError();
        });

        form.addEventListener('submit', function(e) {
            const code = input.value.trim();
            if (code.length !== 6) {
                e.preventDefault();
                showError('Please enter a valid 6-digit code');
            }
        });

        input.focus();
    })();
</script>

<?php include('footer.php'); ?>