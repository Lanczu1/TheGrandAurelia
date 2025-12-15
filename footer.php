</main>

<!-- Footer Section -->
<footer class="bg-gray-900 text-gray-300 pt-20 pb-10 mt-auto relative overflow-hidden">
    <!-- Background Decor -->
    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-yellow-500 to-transparent opacity-50"></div>

    <div class="container mx-auto px-4 max-w-7xl relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-16">
            <!-- Brand Column -->
            <div class="space-y-6">
                <a href="index.php" class="block">
                    <h3 class="text-2xl font-serif font-bold text-white tracking-wider">GRAND AURELIA</h3>
                    <p class="text-yellow-600 text-sm tracking-[0.3em] uppercase mt-1">Hotel & Resort</p>
                </a>
                <p class="text-gray-400 leading-relaxed text-sm">
                    Experience the epitome of luxury and comfort in the heart of Manila. Your sanctuary of elegance awaits.
                </p>
                <div class="flex gap-4">
                    <a href="#" class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center text-gray-400 hover:bg-white hover:text-gray-900 transition-all duration-300 shadow-lg hover:shadow-yellow-500/20">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center text-gray-400 hover:bg-white hover:text-gray-900 transition-all duration-300 shadow-lg hover:shadow-yellow-500/20">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center text-gray-400 hover:bg-white hover:text-gray-900 transition-all duration-300 shadow-lg hover:shadow-yellow-500/20">
                        <i class="fab fa-twitter"></i>
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="lg:pl-8">
                <h4 class="text-white font-bold mb-6 text-lg">Explore</h4>
                <ul class="space-y-4 text-sm">
                    <li><a href="index.php" class="hover:text-yellow-500 transition-colors flex items-center gap-2"><i class="fas fa-chevron-right text-xs text-gray-600"></i> Home</a></li>
                    <li><a href="view_rooms.php" class="hover:text-yellow-500 transition-colors flex items-center gap-2"><i class="fas fa-chevron-right text-xs text-gray-600"></i> Rooms & Suites</a></li>
                    <li><a href="dining.php" class="hover:text-yellow-500 transition-colors flex items-center gap-2"><i class="fas fa-chevron-right text-xs text-gray-600"></i> Dining</a></li>
                    <li><a href="spa.php" class="hover:text-yellow-500 transition-colors flex items-center gap-2"><i class="fas fa-chevron-right text-xs text-gray-600"></i> Spa & Wellness</a></li>
                    <li><a href="contact.php" class="hover:text-yellow-500 transition-colors flex items-center gap-2"><i class="fas fa-chevron-right text-xs text-gray-600"></i> Contact Us</a></li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div>
                <h4 class="text-white font-bold mb-6 text-lg">Contact Us</h4>
                <ul class="space-y-6 text-sm">
                    <li class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-lg bg-gray-800 flex-shrink-0 flex items-center justify-center text-yellow-500">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <span class="text-gray-400 leading-relaxed">Roxas Boulevard, Manila,<br>Philippines 1000</span>
                    </li>
                    <li class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-lg bg-gray-800 flex-shrink-0 flex items-center justify-center text-yellow-500">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <span class="text-gray-400">+63 960 467 7200</span>
                    </li>
                    <li class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-lg bg-gray-800 flex-shrink-0 flex items-center justify-center text-yellow-500">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <span class="text-gray-400 break-all">TheGrandAurelia@gmail.com</span>
                    </li>
                </ul>
            </div>

            <!-- Newsletter -->
            <div>
                <h4 class="text-white font-bold mb-6 text-lg">Newsletter</h4>
                <p class="text-gray-400 text-sm mb-4">Subscribe to receive exclusive offers and news.</p>
                <form class="space-y-3">
                    <div class="relative">
                        <input type="email" placeholder="Your Email Address" class="w-full bg-gray-800 text-white px-4 py-3 rounded-xl border border-gray-700 focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500 outline-none transition-all placeholder-gray-500 text-sm">
                        <button type="button" class="absolute right-2 top-1/2 transform -translate-y-1/2 w-8 h-8 rounded-lg bg-yellow-600 text-white flex items-center justify-center hover:bg-yellow-500 transition-colors">
                            <i class="fas fa-paper-plane text-xs"></i>
                        </button>
                    </div>
                    <p class="text-xs text-gray-600">By subscribing, you agree to our Privacy Policy.</p>
                </form>
            </div>
        </div>

        <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-gray-500">
            <p>&copy; <?php echo date('Y'); ?> The Grand Aurelia. All rights reserved.</p>
            <div class="flex gap-6">
                <a href="privacy.php" class="hover:text-white transition-colors">Privacy Policy</a>
                <a href="terms.php" class="hover:text-white transition-colors">Terms of Service</a>
                <a href="#" class="hover:text-white transition-colors">Cookie Policy</a>
            </div>
        </div>
    </div>
</footer>

<!-- Modals -->
<?php
// Defaults for modal inputs
$modal_prefill_name = isset($prefill_name) ? $prefill_name : '';
$modal_prefill_email = isset($prefill_email) ? $prefill_email : '';
?>

<!-- Spa Booking Modal (Styled with Tailwind + Bootstrap) -->
<div class="modal fade" id="spaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-2xl shadow-2xl overflow-hidden">
            <div class="modal-header bg-gray-900 border-0 p-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-yellow-500/20 text-yellow-500 flex items-center justify-center">
                        <i class="fas fa-spa text-lg"></i>
                    </div>
                    <h5 class="text-xl font-bold text-white mb-0">Book Treatment</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-8 bg-gray-50">
                <form action="process_spa_reservation.php" method="POST" id="spaBookingForm" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Name</label>
                            <input type="text" name="name" required value="<?php echo htmlspecialchars($modal_prefill_name); ?>"
                                class="w-full px-4 py-3 rounded-lg bg-white border border-gray-200 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 outline-none transition-all text-gray-800 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Email</label>
                            <input type="email" name="email" required value="<?php echo htmlspecialchars($modal_prefill_email); ?>"
                                class="w-full px-4 py-3 rounded-lg bg-white border border-gray-200 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 outline-none transition-all text-gray-800 text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Treatment</label>
                        <select name="treatment" required
                            class="w-full px-4 py-3 rounded-lg bg-white border border-gray-200 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 outline-none transition-all text-gray-800 text-sm appearance-none">
                            <option value="">Select a treatment</option>
                            <?php if (isset($spa_treatments) && is_array($spa_treatments)): ?>
                                <optgroup label="Massage">
                                    <?php foreach ($spa_treatments as $treatment): ?>
                                        <option value="<?php echo htmlspecialchars($treatment['name']); ?>"><?php echo htmlspecialchars($treatment['name']); ?> (<?php echo htmlspecialchars($treatment['duration']); ?>)</option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endif; ?>
                            <optgroup label="Packages">
                                <option value="Relaxation Package">Relaxation Package (3 hrs)</option>
                                <option value="Renewal Package">Renewal Package (4 hrs)</option>
                                <option value="Couples Retreat">Couples Retreat (3 hrs)</option>
                            </optgroup>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Date</label>
                            <input type="date" name="date" required
                                class="w-full px-4 py-3 rounded-lg bg-white border border-gray-200 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 outline-none transition-all text-gray-800 text-sm hover:cursor-pointer">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Time</label>
                            <select name="time" required class="w-full px-4 py-3 rounded-lg bg-white border border-gray-200 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 outline-none transition-all text-gray-800 text-sm">
                                <option value="">Select</option>
                                <option>09:00</option>
                                <option>10:00</option>
                                <option>11:00</option>
                                <option>13:00</option>
                                <option>14:00</option>
                                <option>15:00</option>
                                <option>16:00</option>
                                <option>17:00</option>
                                <option>18:00</option>
                            </select>
                        </div>
                        <div class="col-span-2 lg:col-span-1">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Guests</label>
                            <select name="guests" required class="w-full px-4 py-3 rounded-lg bg-white border border-gray-200 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 outline-none transition-all text-gray-800 text-sm">
                                <option value="1">1 Person</option>
                                <option value="2">2 People</option>
                                <option value="3">3 People</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Special Requests</label>
                        <textarea name="special_requests" rows="2" placeholder="Any preferences?" class="w-full px-4 py-3 rounded-lg bg-white border border-gray-200 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 outline-none transition-all text-gray-800 text-sm"></textarea>
                    </div>
                </form>
            </div>

            <div class="modal-footer bg-gray-50 border-t border-gray-100 p-6 flex justify-between">
                <button type="button" class="px-6 py-2.5 rounded-xl border border-gray-300 text-gray-600 font-medium hover:bg-gray-100 transition-colors" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="spaBookingForm" class="px-8 py-2.5 rounded-xl bg-gray-900 text-white font-bold hover:bg-yellow-600 transition-colors shadow-lg hover:shadow-yellow-500/30">
                    Confirm Booking
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS (Essential for Modals) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Chat Widget -->
<?php if (!isset($is_admin_page) || !$is_admin_page) {
    include 'chat_widget.php';
} ?>

</body>

</html>