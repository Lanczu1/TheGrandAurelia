<?php include 'header.php'; ?>

<!-- Hero Section -->
<div class="relative h-screen min-h-[600px] flex items-center justify-center overflow-hidden">
    <!-- Background Image with Parallax Effect -->
    <div class="absolute inset-0 z-0">
        <img src="images/background.jpg" alt="Luxury Hotel" class="w-full h-full object-cover transform scale-105 animate-slow-zoom">
        <div class="absolute inset-0 bg-gradient-to-b from-black/70 via-black/40 to-gray-900"></div>
    </div>

    <!-- Hero Content -->
    <div class="relative z-10 text-center px-4 max-w-5xl mx-auto space-y-8 animate-fade-in-up">
        <div class="space-y-4">
            <h2 class="text-yellow-500 font-medium tracking-[0.2em] text-sm md:text-base uppercase animate-slide-down">Welcome to The Grand Aurelia</h2>
            <h1 class="text-5xl md:text-7xl lg:text-8xl font-bold text-white tracking-tight leading-tight">
                Experience <span class="italic font-serif text-yellow-500">Luxury</span> <br> & Comfort
            </h1>
            <p class="text-gray-300 text-lg md:text-xl max-w-2xl mx-auto font-light leading-relaxed">
                Discover the perfect blend of elegance and relaxation at our prestigious destination. Where every detail is curated for your ultimate comfort.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center pt-8">
            <a href="add_booking.php" class="px-8 py-4 bg-yellow-600 hover:bg-yellow-700 text-white rounded-full font-semibold tracking-wide transition-all duration-300 shadow-lg hover:shadow-yellow-500/30 transform hover:-translate-y-1">
                Book Your Stay
            </a>
            <a href="view_rooms.php" class="px-8 py-4 bg-white/10 hover:bg-white/20 backdrop-blur-md text-white border border-white/30 rounded-full font-semibold tracking-wide transition-all duration-300 transform hover:-translate-y-1">
                View Suites
            </a>
        </div>
    </div>

    <!-- Scroll Indicator -->
    <div class="absolute bottom-10 left-1/2 transform -translate-x-1/2 animate-bounce">
        <i class="fas fa-chevron-down text-white/50 text-2xl"></i>
    </div>
</div>

<!-- Features Section -->
<section class="py-20 bg-gray-900 text-white relative overflow-hidden">
    <div class="absolute top-0 right-0 w-64 h-64 bg-yellow-500/10 rounded-full blur-3xl -mr-32 -mt-32"></div>
    <div class="absolute bottom-0 left-0 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl -ml-48 -mb-48"></div>

    <div class="container mx-auto px-4 relative z-10">
        <div class="text-center mb-16 space-y-4">
            <h3 class="text-yellow-500 font-medium tracking-widest text-sm uppercase">Why Choose Us</h3>
            <h2 class="text-3xl md:text-4xl font-bold">World Class Amenities</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Feature 1 -->
            <div class="group p-8 rounded-2xl bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 hover:bg-gray-800 hover:border-yellow-500/30 transition-all duration-300 hover:-translate-y-2">
                <div class="w-14 h-14 rounded-full bg-gray-700/50 flex items-center justify-center mb-6 group-hover:bg-yellow-500/20 group-hover:text-yellow-500 transition-colors duration-300">
                    <i class="fas fa-wifi text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-3">High-Speed Wi-Fi</h3>
                <p class="text-gray-400 text-sm leading-relaxed">Stay seamlessly connected with our complimentary premium internet access throughout the property.</p>
            </div>

            <!-- Feature 2 -->
            <div class="group p-8 rounded-2xl bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 hover:bg-gray-800 hover:border-yellow-500/30 transition-all duration-300 hover:-translate-y-2">
                <div class="w-14 h-14 rounded-full bg-gray-700/50 flex items-center justify-center mb-6 group-hover:bg-yellow-500/20 group-hover:text-yellow-500 transition-colors duration-300">
                    <i class="fas fa-parking text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-3">Valet Parking</h3>
                <p class="text-gray-400 text-sm leading-relaxed">Secure and convenient parking services available 24/7 for all our valued guests.</p>
            </div>

            <!-- Feature 3 -->
            <div class="group p-8 rounded-2xl bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 hover:bg-gray-800 hover:border-yellow-500/30 transition-all duration-300 hover:-translate-y-2">
                <div class="w-14 h-14 rounded-full bg-gray-700/50 flex items-center justify-center mb-6 group-hover:bg-yellow-500/20 group-hover:text-yellow-500 transition-colors duration-300">
                    <i class="fas fa-utensils text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-3">Gourmet Dining</h3>
                <p class="text-gray-400 text-sm leading-relaxed">Indulge in exquisite culinary masterpieces at our award-winning fine dining restaurants.</p>
            </div>

            <!-- Feature 4 -->
            <div class="group p-8 rounded-2xl bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 hover:bg-gray-800 hover:border-yellow-500/30 transition-all duration-300 hover:-translate-y-2">
                <div class="w-14 h-14 rounded-full bg-gray-700/50 flex items-center justify-center mb-6 group-hover:bg-yellow-500/20 group-hover:text-yellow-500 transition-colors duration-300">
                    <i class="fas fa-spa text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-3">Luxury Spa</h3>
                <p class="text-gray-400 text-sm leading-relaxed">Rejuvenate your senses with our world-class treatments and wellness facilities.</p>
            </div>
        </div>
    </div>
</section>

<!-- Featured Rooms Section -->
<section class="py-24 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row justify-between items-end mb-16 gap-6">
            <div class="space-y-4">
                <h3 class="text-yellow-600 font-medium tracking-widest text-sm uppercase">Accommodations</h3>
                <h2 class="text-4xl md:text-5xl font-bold text-gray-900">Our Luxurious Rooms</h2>
            </div>
            <a href="view_rooms.php" class="group flex items-center gap-2 text-gray-900 font-semibold hover:text-yellow-600 transition-colors">
                View All Rooms
                <i class="fas fa-arrow-right transform group-hover:translate-x-1 transition-transform"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <!-- Room 1 -->
            <div class="group bg-white rounded-3xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-500 hover:-translate-y-2">
                <div class="relative h-72 overflow-hidden">
                    <div class="absolute top-4 right-4 z-10 bg-white/90 backdrop-blur text-gray-900 font-bold px-4 py-2 rounded-full shadow-lg">
                        $1,500<span class="text-sm font-normal text-gray-600">/night</span>
                    </div>
                    <img src="images/penthouse.jpg" alt="Penthouse" class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </div>
                <div class="p-8">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Penthouse Suite</h3>
                    <p class="text-gray-600 mb-6 line-clamp-2">Experience the pinnacle of luxury with breathtaking city views and exclusive amenities.</p>
                    <div class="flex items-center gap-4 text-sm text-gray-500 mb-6 border-t border-gray-100 pt-6">
                        <span class="flex items-center gap-2"><i class="fas fa-bed text-yellow-600"></i> King Bed</span>
                        <span class="flex items-center gap-2"><i class="fas fa-ruler-combined text-yellow-600"></i> 75 m²</span>
                        <span class="flex items-center gap-2"><i class="fas fa-mountain text-yellow-600"></i> City View</span>
                    </div>
                    <a href="view_rooms.php" class="block w-full text-center py-3 border-2 border-gray-900 text-gray-900 font-semibold rounded-xl group-hover:bg-gray-900 group-hover:text-white transition-all duration-300">
                        View Details
                    </a>
                </div>
            </div>

            <!-- Room 2 -->
            <div class="group bg-white rounded-3xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-500 hover:-translate-y-2">
                <div class="relative h-72 overflow-hidden">
                    <div class="absolute top-4 right-4 z-10 bg-white/90 backdrop-blur text-gray-900 font-bold px-4 py-2 rounded-full shadow-lg">
                        $350<span class="text-sm font-normal text-gray-600">/night</span>
                    </div>
                    <img src="images/bridal.jpg" alt="Bridal Suite" class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </div>
                <div class="p-8">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Bridal Suite</h3>
                    <p class="text-gray-600 mb-6 line-clamp-2">Celebrate love in our elegantly designed Bridal Suite with romantic touches.</p>
                    <div class="flex items-center gap-4 text-sm text-gray-500 mb-6 border-t border-gray-100 pt-6">
                        <span class="flex items-center gap-2"><i class="fas fa-bed text-yellow-600"></i> Queen Bed</span>
                        <span class="flex items-center gap-2"><i class="fas fa-ruler-combined text-yellow-600"></i> 55 m²</span>
                        <span class="flex items-center gap-2"><i class="fas fa-glass-cheers text-yellow-600"></i> Lounge</span>
                    </div>
                    <a href="view_rooms.php" class="block w-full text-center py-3 border-2 border-gray-900 text-gray-900 font-semibold rounded-xl group-hover:bg-gray-900 group-hover:text-white transition-all duration-300">
                        View Details
                    </a>
                </div>
            </div>

            <!-- Room 3 -->
            <div class="group bg-white rounded-3xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-500 hover:-translate-y-2">
                <div class="relative h-72 overflow-hidden">
                    <div class="absolute top-4 right-4 z-10 bg-white/90 backdrop-blur text-gray-900 font-bold px-4 py-2 rounded-full shadow-lg">
                        $400<span class="text-sm font-normal text-gray-600">/night</span>
                    </div>
                    <img src="images/honeymoon.jpg" alt="Honeymoon Suite" class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </div>
                <div class="p-8">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Honeymoon Suite</h3>
                    <p class="text-gray-600 mb-6 line-clamp-2">Private balcony and jacuzzi for the ultimate romantic getaway.</p>
                    <div class="flex items-center gap-4 text-sm text-gray-500 mb-6 border-t border-gray-100 pt-6">
                        <span class="flex items-center gap-2"><i class="fas fa-bed text-yellow-600"></i> King Bed</span>
                        <span class="flex items-center gap-2"><i class="fas fa-ruler-combined text-yellow-600"></i> 60 m²</span>
                        <span class="flex items-center gap-2"><i class="fas fa-hot-tub text-yellow-600"></i> Jacuzzi</span>
                    </div>
                    <a href="view_rooms.php" class="block w-full text-center py-3 border-2 border-gray-900 text-gray-900 font-semibold rounded-xl group-hover:bg-gray-900 group-hover:text-white transition-all duration-300">
                        View Details
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Experience Section (Alternate Grid) -->
<section class="py-20 bg-gray-900 text-white">
    <div class="container mx-auto px-4">
        <!-- Dining -->
        <div class="flex flex-col lg:flex-row items-center gap-12 mb-20">
            <div class="lg:w-1/2 relative group">
                <div class="absolute -inset-2 bg-yellow-500/20 rounded-2xl blur-lg opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                <img src="images/dining.jpg" alt="Fine Dining" class="relative rounded-2xl shadow-2xl w-full object-cover h-[400px] transform transition-transform duration-500 group-hover:scale-[1.01]">
            </div>
            <div class="lg:w-1/2 space-y-6">
                <h3 class="text-yellow-500 font-medium tracking-widest text-sm uppercase">Culinary Excellence</h3>
                <h2 class="text-4xl font-bold">Exquisite Dining Experiences</h2>
                <p class="text-gray-400 text-lg leading-relaxed">
                    Savor the authentic flavors prepared by our world-renowned chefs. Whether it's a romantic dinner or a casual brunch, our restaurants offer an ambiance that perfectly complements the exquisite cuisine.
                </p>
                <ul class="space-y-3 text-gray-300">
                    <li class="flex items-center gap-3"><i class="fas fa-check text-yellow-500"></i> Farm-to-table ingredients</li>
                    <li class="flex items-center gap-3"><i class="fas fa-check text-yellow-500"></i> Award-winning wine list</li>
                    <li class="flex items-center gap-3"><i class="fas fa-check text-yellow-500"></i> Private dining options</li>
                </ul>
                <a href="dining.php" class="custom-btn-hover inline-block px-8 py-3 mt-4 border border-white text-white rounded-full hover:bg-white transition-all duration-300">
                    Explore Dining
                </a>
            </div>
        </div>

        <!-- Spa -->
        <div class="flex flex-col lg:flex-row-reverse items-center gap-12">
            <div class="lg:w-1/2 relative group">
                <div class="absolute -inset-2 bg-blue-500/20 rounded-2xl blur-lg opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                <img src="images/spa.jpg" alt="Aurelia Spa" class="relative rounded-2xl shadow-2xl w-full object-cover h-[400px] transform transition-transform duration-500 group-hover:scale-[1.01]">
            </div>
            <div class="lg:w-1/2 space-y-6 text-left lg:text-right flex flex-col items-start lg:items-end">
                <h3 class="text-yellow-500 font-medium tracking-widest text-sm uppercase">Wellness & Serenity</h3>
                <h2 class="text-4xl font-bold">Rejuvenate Your Senses</h2>
                <p class="text-gray-400 text-lg leading-relaxed">
                    Escape to a sanctuary of peace in our premium spa. From therapeutic massages to revitalizing facials, every treatment is designed to restore balance to your body and mind.
                </p>
                <div class="flex gap-4">
                    <span class="px-4 py-2 bg-gray-800 rounded-lg text-sm text-gray-300">Massage Therapy</span>
                    <span class="px-4 py-2 bg-gray-800 rounded-lg text-sm text-gray-300">Hydrotherapy</span>
                    <span class="px-4 py-2 bg-gray-800 rounded-lg text-sm text-gray-300">Yoga</span>
                </div>
                <a href="spa.php" class="custom-btn-hover inline-block px-8 py-3 mt-4 border border-white text-white rounded-full hover:bg-white transition-all duration-300">
                    Discover Spa
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-24 bg-gray-50 from-gray-50 to-white bg-gradient-to-b">
    <div class="container mx-auto px-4 text-center">
        <h3 class="text-yellow-600 font-medium tracking-widest text-sm uppercase mb-4">Guest Reviews</h3>
        <h2 class="text-4xl font-bold text-gray-900 mb-16">What Our Guests Say</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Review 1 -->
            <div class="bg-white p-8 rounded-2xl shadow-lg border border-gray-100 hover:shadow-xl transition-shadow duration-300 relative">
                <div class="absolute -top-6 left-1/2 transform -translate-x-1/2 w-12 h-12 bg-yellow-500 flex items-center justify-center rounded-full text-white text-xl">
                    <i class="fas fa-quote-left"></i>
                </div>
                <div class="flex justify-center gap-1 text-yellow-400 mb-6 mt-4">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p class="text-gray-600 italic mb-6">"Amazing experience! The staff was incredibly helpful and the room exceeded our expectations in every way."</p>
                <div class="border-t border-gray-100 pt-6">
                    <div class="font-bold text-gray-900">Kurt Umali</div>
                    <div class="text-sm text-gray-500">Business Traveler</div>
                </div>
            </div>

            <!-- Review 2 -->
            <div class="bg-white p-8 rounded-2xl shadow-lg border border-gray-100 hover:shadow-xl transition-shadow duration-300 relative transform md:-translate-y-4">
                <div class="absolute -top-6 left-1/2 transform -translate-x-1/2 w-12 h-12 bg-yellow-500 flex items-center justify-center rounded-full text-white text-xl">
                    <i class="fas fa-quote-left"></i>
                </div>
                <div class="flex justify-center gap-1 text-yellow-400 mb-6 mt-4">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p class="text-gray-600 italic mb-6">"Perfect location, luxurious rooms, and outstanding service. The dining experience was absolute perfection."</p>
                <div class="border-t border-gray-100 pt-6">
                    <div class="font-bold text-gray-900">Kart Mendoza</div>
                    <div class="text-sm text-gray-500">Vacation Guest</div>
                </div>
            </div>

            <!-- Review 3 -->
            <div class="bg-white p-8 rounded-2xl shadow-lg border border-gray-100 hover:shadow-xl transition-shadow duration-300 relative">
                <div class="absolute -top-6 left-1/2 transform -translate-x-1/2 w-12 h-12 bg-yellow-500 flex items-center justify-center rounded-full text-white text-xl">
                    <i class="fas fa-quote-left"></i>
                </div>
                <div class="flex justify-center gap-1 text-yellow-400 mb-6 mt-4">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p class="text-gray-600 italic mb-6">"The attention to detail and customer service made our stay truly memorable. The spa is a must-try!"</p>
                <div class="border-t border-gray-100 pt-6">
                    <div class="font-bold text-gray-900">Ahron Valenzuela</div>
                    <div class="text-sm text-gray-500">Honeymoon Couple</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Banner -->
<section class="py-20 relative overflow-hidden flex items-center justify-center">
    <div class="absolute inset-0">
        <img src="images/background.jpg" alt="Background" class="w-full h-full object-cover filter brightness-[0.3]">
    </div>
    <div class="relative z-10 text-center px-4 max-w-4xl mx-auto">
        <h2 class="text-4xl md:text-5xl font-bold text-white mb-6">Ready for an Unforgettable Stay?</h2>
        <p class="text-gray-300 text-lg mb-10">Book your luxury escape today and experience the Grand Aurelia difference.</p>
        <a href="add_booking.php" class="inline-block px-10 py-4 bg-yellow-600 hover:bg-yellow-700 text-white rounded-full font-bold text-lg shadow-lg hover:shadow-yellow-500/50 transition-all duration-300 transform hover:-translate-y-1">
            Book Now
        </a>
    </div>
</section>

<style>
    @keyframes slow-zoom {
        0% {
            transform: scale(1);
        }

        100% {
            transform: scale(1.1);
        }
    }

    .animate-slow-zoom {
        animation: slow-zoom 20s infinite alternate;
    }

    @keyframes slide-down {
        from {
            transform: translateY(-20px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .animate-slide-down {
        animation: slide-down 1s ease-out forwards;
    }

    @keyframes fade-in-up {
        from {
            transform: translateY(20px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .animate-fade-in-up {
        animation: fade-in-up 1s ease-out forwards 0.5s;
        opacity: 0;
    }

    .custom-btn-hover:hover {
        color: #000000 !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.body.classList.add('loaded');
    });
</script>

<?php include 'footer.php'; ?>