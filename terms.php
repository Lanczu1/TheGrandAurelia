<?php
session_start();
include('header.php');
?>

<div class="bg-gray-50 min-h-screen py-16">
    <div class="container mx-auto px-4 max-w-4xl">
        <h1 class="text-4xl font-serif font-bold text-gray-900 mb-8 text-center">Terms of Service</h1>
        
        <div class="bg-white rounded-3xl shadow-lg p-8 md:p-12 space-y-8 text-gray-600 leading-relaxed text-lg">
            
            <section>
                <h2 class="text-2xl font-bold text-gray-900 mb-4">1. Introduction</h2>
                <p>Welcome to Grand Aurelia. These Terms of Service govern your use of our website and services. By booking a stay or using our facilities, you agree to comply with these terms.</p>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-gray-900 mb-4">2. Reservations & Cancellations</h2>
                <p>All reservations are subject to availability. Cancellations must be made at least 48 hours prior to check-in to avoid a cancellation fee equivalent to one night's stay.</p>
                <ul class="list-disc pl-6 mt-2 space-y-1">
                    <li>Check-in time is from 3:00 PM.</li>
                    <li>Check-out time is until 11:00 AM.</li>
                    <li>Late check-out may be available upon request and subject to fees.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-gray-900 mb-4">3. Guest Responsibilities</h2>
                <p>Guests are expected to treat the property and staff with respect. Any damage to hotel property will be charged to the guest's account. Grand Aurelia is a non-smoking property.</p>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-gray-900 mb-4">4. Payment</h2>
                <p>We accept major credit cards. A valid credit card is required at the time of booking to guarantee your reservation. Full payment may be required upon check-in.</p>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-gray-900 mb-4">5. Liability</h2>
                <p>Grand Aurelia is not liable for loss of valuables or personal items. Please use the in-room safes provided.</p>
            </section>
            
            <section>
                <h2 class="text-2xl font-bold text-gray-900 mb-4">6. Changes to Terms</h2>
                <p>We reserve the right to modify these terms at any time. Continued use of our services constitutes acceptance of the updated terms.</p>
            </section>

            <div class="pt-8 border-t border-gray-100 text-center">
                <p class="text-sm">Last Updated: December 2025</p>
                <a href="index.php" class="inline-block mt-4 text-yellow-600 font-bold hover:underline">Return to Home</a>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>
