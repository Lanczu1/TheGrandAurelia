<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('header.php');

// Prefill name/email for logged-in users
$prefill_name = '';
$prefill_email = '';
if (isset($_SESSION['user_id'])) {
    include_once('db.php');
    $uid = (int)$_SESSION['user_id'];
    $uqr = mysqli_query($conn, "SELECT username, email FROM users WHERE id = $uid LIMIT 1");
    if ($uqr && mysqli_num_rows($uqr) > 0) {
        $urow = mysqli_fetch_assoc($uqr);
        $prefill_name = !empty($urow['username']) ? $urow['username'] : '';
        $prefill_email = !empty($urow['email']) ? $urow['email'] : '';
    }
}

// Get error/success messages
$error_message = isset($_SESSION['error']) ? $_SESSION['error'] : '';
$success_message = isset($_SESSION['success']) ? $_SESSION['success'] : '';

// Clear messages after retrieving
unset($_SESSION['error']);
unset($_SESSION['success']);

// Spa treatments array
$spa_treatments = [
    [
        'name' => 'Swedish Massage',
        'description' => 'A gentle full-body massage that is perfect for relaxation and easing muscle tension.',
        'duration' => '60 mins',
        'price' => '$120',
        'image' => 'images/swedish.jpg'
    ],
    [
        'name' => 'Deep Tissue Massage',
        'description' => 'A therapeutic massage focused on realigning deeper layers of muscles and connective tissue.',
        'duration' => '60 mins',
        'price' => '$140',
        'image' => 'images/deep tissue.jpg'
    ],
    [
        'name' => 'Hot Stone Massage',
        'description' => 'Smooth, heated stones are placed on specific parts of your body to melt away tension.',
        'duration' => '90 mins',
        'price' => '$160',
        'image' => 'images/hotstone.jpg'
    ],
    [
        'name' => 'Aromatherapy Massage',
        'description' => 'Essential oils are used to enhance psychological and physical well-being.',
        'duration' => '75 mins',
        'price' => '$150',
        'image' => 'images/Aromatic.jpg'
    ]
];

// Facial treatments
$facial_treatments = [
    [
        'name' => 'Hydrating Facial',
        'description' => 'Restore moisture and radiance to dry, dehydrated skin with this nourishing facial.',
        'duration' => '60 mins',
        'price' => '$130',
        'image' => 'images/hydrating.jpg'
    ],
    [
        'name' => 'Anti-Aging Facial',
        'description' => 'Combat signs of aging with this rejuvenating facial that stimulates collagen production.',
        'duration' => '75 mins',
        'price' => '$150',
        'image' => 'images/antiaging.jpg'
    ]
];
?>

<style>
    .parallax-hero {
        background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.6)), url('images/spa.jpg');
        background-attachment: fixed;
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
    }

    .text-gold {
        color: #d4af37;
        background: linear-gradient(45deg, #d4af37, #f3e5ab, #d4af37);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        text-shadow: 0px 2px 4px rgba(0, 0, 0, 0.3);
    }

    .glass-panel {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .card-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .reveal {
        opacity: 0;
        transform: translateY(30px);
        transition: all 0.8s ease-out;
    }

    .reveal.active {
        opacity: 1;
        transform: translateY(0);
    }

    .custom-btn-hover:hover {
        color: #000000 !important;
    }

    .custom-btn-yellow-hover:hover {
        background-color: #eab308 !important; /* Yellow-500 */
        color: #000000 !important;
    }
</style>

<!-- Hero Section -->
<section class="parallax-hero h-[80vh] flex items-center justify-center relative overflow-hidden">
    <div class="absolute inset-0 bg-black/30"></div>
    <div class="text-center relative z-10 p-6 reveal active delay-100">
        <span class="block text-yellow-500 tracking-[0.3em] uppercase text-sm font-bold mb-4">Serenity & Balance</span>
        <h1 class="text-6xl md:text-8xl font-serif text-white font-bold mb-6 drop-shadow-2xl">
            A Sanctuary of <span class="italic text-gold">Wellness</span>
        </h1>
        <p class="text-gray-200 text-lg md:text-xl max-w-2xl mx-auto font-light leading-relaxed mb-10">
            Escape the ordinary and embark on a journey of relaxation. Restore your mind, body, and spirit in our world-class spa facilities.
        </p>
        <button onclick="document.getElementById('spa-reserve').scrollIntoView({behavior: 'smooth'})"
            class="inline-block px-8 py-4 bg-yellow-600 hover:bg-yellow-700 text-white rounded-full font-semibold tracking-wide transition-all duration-300 shadow-lg hover:shadow-yellow-500/30 transform hover:-translate-y-1">
            Book Appointment
        </button>
    </div>

    <div class="absolute bottom-10 left-1/2 transform -translate-x-1/2 animate-bounce cursor-pointer">
        <a href="#intro" class="text-white opacity-70 hover:opacity-100 transition-opacity">
            <i class="fas fa-chevron-down text-2xl"></i>
        </a>
    </div>
</section>

<!-- Intro & Philosophy -->
<section id="intro" class="py-24 bg-white relative">
    <div class="container mx-auto px-4 max-w-6xl">
        <div class="flex flex-col md:flex-row items-center gap-16 reveal">
            <div class="w-full md:w-1/2 relative">
                <div class="absolute -top-4 -left-4 w-20 h-20 bg-yellow-100 rounded-full z-0"></div>
                <div class="absolute -bottom-4 -right-4 w-32 h-32 bg-yellow-50 rounded-full z-0"></div>
                <img src="images/spa.jpg" alt="Spa Interior" class="relative z-10 rounded-3xl shadow-2xl object-cover w-full h-[500px]">
            </div>
            <div class="w-full md:w-1/2">
                <span class="text-yellow-600 font-bold tracking-widest uppercase text-sm">Our Philosophy</span>
                <h2 class="text-4xl md:text-5xl font-serif mt-4 mb-6 text-gray-900">Holistic Harmony</h2>
                <div class="w-20 h-1 bg-yellow-500 mb-8"></div>
                <p class="text-gray-600 text-lg leading-relaxed mb-6 font-light">
                    At The Grand Aurelia Spa, we believe in the power of holistic wellness. Our carefully crafted treatments combine ancient techniques with modern luxury to provide an experience that nurtures both body and soul.
                </p>
                <div class="grid grid-cols-2 gap-6 mt-8">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-600">
                            <i class="fas fa-leaf text-xl"></i>
                        </div>
                        <span class="font-medium text-gray-800">Organic Products</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-600">
                            <i class="fas fa-user-md text-xl"></i>
                        </div>
                        <span class="font-medium text-gray-800">Expert Therapists</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Facilities Grid -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4 max-w-7xl">
        <div class="text-center mb-16 reveal">
            <h2 class="text-4xl font-serif text-gray-900">World-Class Facilities</h2>
            <p class="text-gray-500 mt-3 font-light">Complimentary access for all hotel guests</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white p-8 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 reveal">
                <i class="fas fa-swimming-pool text-4xl text-yellow-500 mb-6"></i>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Indoor Pool</h3>
                <p class="text-gray-500 text-sm">Temperature-controlled lap pool with hydrotherapy jets for ultimate relaxation.</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 reveal delay-100">
                <i class="fas fa-hot-tub text-4xl text-yellow-500 mb-6"></i>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Jacuzzi</h3>
                <p class="text-gray-500 text-sm">Luxurious hot tubs featuring panoramic skyline views.</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 reveal delay-200">
                <i class="fas fa-wind text-4xl text-yellow-500 mb-6"></i>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Sauna & Steam</h3>
                <p class="text-gray-500 text-sm">Traditional Finnish sauna and aromatic steam rooms to detoxify.</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 reveal delay-300">
                <i class="fas fa-dumbbell text-4xl text-yellow-500 mb-6"></i>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Fitness Center</h3>
                <p class="text-gray-500 text-sm">State-of-the-art gym equipment open 24 hours for your convenience.</p>
            </div>
        </div>
    </div>
</section>

<!-- Treatments Section -->
<section class="py-24 bg-white">
    <div class="container mx-auto px-4 max-w-6xl">
        <div class="text-center mb-16 reveal">
            <span class="text-yellow-600 font-bold tracking-widest uppercase text-sm">Our Menu</span>
            <h2 class="text-4xl md:text-5xl font-serif mt-3 text-gray-900">Signature Treatments</h2>
        </div>

        <!-- Massages -->
        <h3 class="text-2xl font-serif text-gray-800 mb-8 border-l-4 border-yellow-500 pl-4 reveal">Massage Therapy</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-16">
            <?php foreach ($spa_treatments as $treatment): ?>
                <div class="flex flex-col md:flex-row bg-white rounded-2xl overflow-hidden shadow-lg border border-gray-100 card-hover reveal">
                    <div class="md:w-2/5 h-64 md:h-auto overflow-hidden">
                        <img src="<?php echo $treatment['image']; ?>" alt="<?php echo $treatment['name']; ?>" class="w-full h-full object-cover transform hover:scale-110 transition-transform duration-700">
                    </div>
                    <div class="p-8 md:w-3/5 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="text-xl font-bold text-gray-900 font-serif"><?php echo $treatment['name']; ?></h4>
                                <span class="text-yellow-600 font-bold text-lg"><?php echo $treatment['price']; ?></span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-400 mb-4">
                                <i class="far fa-clock"></i> <?php echo $treatment['duration']; ?>
                            </div>
                            <p class="text-gray-600 text-sm"><?php echo $treatment['description']; ?></p>
                        </div>
                        <button onclick="bookTreatment('<?php echo $treatment['name']; ?>')"
                            class="mt-6 w-full py-3 border border-gray-200 text-gray-900 rounded-lg hover:bg-gray-900 hover:text-white transition-all text-sm font-bold uppercase tracking-wider">
                            Book This
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Facials -->
        <h3 class="text-2xl font-serif text-gray-800 mb-8 border-l-4 border-yellow-500 pl-4 reveal">Facial Treatments</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <?php foreach ($facial_treatments as $facial): ?>
                <div class="flex flex-col md:flex-row bg-white rounded-2xl overflow-hidden shadow-lg border border-gray-100 card-hover reveal">
                    <div class="md:w-2/5 h-64 md:h-auto overflow-hidden">
                        <img src="<?php echo $facial['image']; ?>" alt="<?php echo $facial['name']; ?>" class="w-full h-full object-cover transform hover:scale-110 transition-transform duration-700">
                    </div>
                    <div class="p-8 md:w-3/5 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="text-xl font-bold text-gray-900 font-serif"><?php echo $facial['name']; ?></h4>
                                <span class="text-yellow-600 font-bold text-lg"><?php echo $facial['price']; ?></span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-400 mb-4">
                                <i class="far fa-clock"></i> <?php echo $facial['duration']; ?>
                            </div>
                            <p class="text-gray-600 text-sm"><?php echo $facial['description']; ?></p>
                        </div>
                        <button onclick="bookTreatment('<?php echo $facial['name']; ?>')"
                            class="mt-6 w-full py-3 border border-gray-200 text-gray-900 rounded-lg hover:bg-gray-900 hover:text-white transition-all text-sm font-bold uppercase tracking-wider">
                            Book This
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Packages Section -->
<section class="py-24 bg-gray-900 text-white relative overflow-hidden">
    <div class="absolute inset-0 bg-[url('images/spa.jpg')] bg-cover bg-center opacity-10"></div>
    <div class="container mx-auto px-4 max-w-6xl relative z-10">
        <div class="text-center mb-16 reveal">
            <h2 class="text-4xl md:text-5xl font-serif mb-6 text-gold">Luxury Packages</h2>
            <p class="text-gray-300 max-w-2xl mx-auto text-lg font-light">
                Comprehensive experiences designed for ultimate relaxation and rejuvenation.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Pkg 1 -->
            <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-3xl p-8 hover:bg-gray-800 transition-all card-hover reveal">
                <div class="text-center border-b border-gray-700 pb-6 mb-6">
                    <h3 class="text-2xl font-serif font-bold text-white mb-2">Relaxation</h3>
                    <div class="text-gold text-3xl font-bold">$250</div>
                    <span class="text-gray-400 text-sm">3 Hours • Per Person</span>
                </div>
                <ul class="space-y-4 mb-8 text-gray-300 text-sm">
                    <li class="flex items-center gap-3"><i class="fas fa-check text-yellow-500"></i> 60-min Swedish Massage</li>
                    <li class="flex items-center gap-3"><i class="fas fa-check text-yellow-500"></i> Hydrating Facial</li>
                    <li class="flex items-center gap-3"><i class="fas fa-check text-yellow-500"></i> Aromatherapy Scrub</li>
                    <li class="flex items-center gap-3"><i class="fas fa-check text-yellow-500"></i> Spa Lunch</li>
                </ul>
                <button onclick="bookTreatment('Relaxation Package')" class="custom-btn-yellow-hover w-full py-3 bg-white text-gray-900 rounded-full font-bold transition-colors">Select Package</button>
            </div>

            <!-- Pkg 2 (Featured) -->
            <div class="bg-yellow-600 rounded-3xl p-8 transform md:-translate-y-4 shadow-2xl relative card-hover reveal delay-100">
                <div class="absolute top-0 right-0 bg-white text-yellow-800 text-xs font-bold px-3 py-1 rounded-bl-lg rounded-tr-lg">POPULAR</div>
                <div class="text-center border-b border-yellow-500 pb-6 mb-6">
                    <h3 class="text-2xl font-serif font-bold text-white mb-2">Renewal</h3>
                    <div class="text-white text-4xl font-bold">$350</div>
                    <span class="text-yellow-100 text-sm">4 Hours • Per Person</span>
                </div>
                <ul class="space-y-4 mb-8 text-white text-sm">
                    <li class="flex items-center gap-3"><i class="fas fa-star text-white"></i> 90-min Hot Stone Massage</li>
                    <li class="flex items-center gap-3"><i class="fas fa-star text-white"></i> Anti-Aging Facial</li>
                    <li class="flex items-center gap-3"><i class="fas fa-star text-white"></i> Body Exfoliation</li>
                    <li class="flex items-center gap-3"><i class="fas fa-star text-white"></i> Champagne Lunch</li>
                </ul>
                <button onclick="bookTreatment('Renewal Package')" class="custom-btn-hover w-full py-3 bg-gray-900 text-white rounded-full font-bold hover:bg-white transition-colors shadow-lg">Select Package</button>
            </div>

            <!-- Pkg 3 -->
            <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-3xl p-8 hover:bg-gray-800 transition-all card-hover reveal delay-200">
                <div class="text-center border-b border-gray-700 pb-6 mb-6">
                    <h3 class="text-2xl font-serif font-bold text-white mb-2">Couples Retreat</h3>
                    <div class="text-gold text-3xl font-bold">$450</div>
                    <span class="text-gray-400 text-sm">3 Hours • Per Couple</span>
                </div>
                <ul class="space-y-4 mb-8 text-gray-300 text-sm">
                    <li class="flex items-center gap-3"><i class="fas fa-check text-yellow-500"></i> Couples Massage</li>
                    <li class="flex items-center gap-3"><i class="fas fa-check text-yellow-500"></i> Private Jacuzzi</li>
                    <li class="flex items-center gap-3"><i class="fas fa-check text-yellow-500"></i> Mini Facials</li>
                    <li class="flex items-center gap-3"><i class="fas fa-check text-yellow-500"></i> Strawberries & Wine</li>
                </ul>
                <button onclick="bookTreatment('Couples Retreat')" class="custom-btn-yellow-hover w-full py-3 bg-white text-gray-900 rounded-full font-bold transition-colors">Select Package</button>
            </div>
        </div>
    </div>
</section>

<!-- Reservation Form Section -->
<section id="spa-reserve" class="py-24 bg-gray-100">
    <div class="container mx-auto px-4 max-w-4xl">
        <div class="glass-panel p-8 md:p-12 rounded-3xl shadow-2xl reveal">
            <div class="text-center mb-10">
                <h2 class="text-3xl font-serif text-gray-900 font-bold">Book Your Appointment</h2>
                <p class="text-gray-500 mt-2">Reserve your moment of tranquility</p>
            </div>

            <?php if ($error_message): ?>
                <div class="bg-red-50 text-red-700 p-4 rounded-lg mb-6 flex items-center gap-3 border border-red-200">
                    <i class="fas fa-exclamation-circle text-xl"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="bg-green-50 text-green-700 p-4 rounded-lg mb-6 flex items-center gap-3 border border-green-200">
                    <i class="fas fa-check-circle text-xl"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <form action="process_spa_reservation.php" method="POST" id="spaForm" class="space-y-6">
                <!-- User Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wide ml-1">Full Name</label>
                        <div class="relative">
                            <i class="fas fa-user absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="text" name="name" required value="<?php echo htmlspecialchars($prefill_name); ?>"
                                class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 outline-none transition-all">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wide ml-1">Email Address</label>
                        <div class="relative">
                            <i class="fas fa-envelope absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="email" name="email" required value="<?php echo htmlspecialchars($prefill_email); ?>"
                                class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 outline-none transition-all">
                        </div>
                    </div>
                </div>

                <!-- Treatment & Date -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wide ml-1">Treatment / Package</label>
                        <div class="relative">
                            <i class="fas fa-spa absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <select id="treatment-select" name="treatment" required
                                class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 outline-none transition-all appearance-none cursor-pointer">
                                <option value="">Select Experience</option>
                                <optgroup label="Massages">
                                    <?php foreach ($spa_treatments as $t) echo '<option value="' . htmlspecialchars($t['name']) . '">' . htmlspecialchars($t['name']) . '</option>'; ?>
                                </optgroup>
                                <optgroup label="Facials">
                                    <?php foreach ($facial_treatments as $f) echo '<option value="' . htmlspecialchars($f['name']) . '">' . htmlspecialchars($f['name']) . '</option>'; ?>
                                </optgroup>
                                <optgroup label="Packages">
                                    <option value="Relaxation Package">Relaxation Package</option>
                                    <option value="Renewal Package">Renewal Package</option>
                                    <option value="Couples Retreat">Couples Retreat</option>
                                </optgroup>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wide ml-1">Date</label>
                        <input type="date" id="date" name="date" required min="<?php echo date('Y-m-d'); ?>"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 outline-none transition-all cursor-pointer">
                    </div>
                </div>

                <!-- Time & Guests -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wide ml-1">Time</label>
                        <div class="relative">
                            <i class="fas fa-clock absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <select name="time" required class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 outline-none transition-all cursor-pointer">
                                <option value="">Select Time</option>
                                <?php
                                for ($h = 9; $h <= 20; $h++) {
                                    $timeDisplay = date("g:00 A", strtotime("$h:00"));
                                    echo "<option value='$h:00'>$timeDisplay</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wide ml-1">Number of Guests</label>
                        <select name="guests" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 outline-none transition-all">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?> Person<?php echo $i > 1 ? 's' : ''; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wide ml-1">Special Requests</label>
                    <textarea name="special_requests" rows="3" placeholder="Any health conditions, allergies, or therapist preferences..."
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 outline-none transition-all"></textarea>
                </div>

                <div class="pt-4 text-center">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <button type="submit" class="w-full md:w-auto px-10 py-4 bg-yellow-600 hover:bg-yellow-700 text-white rounded-full font-bold text-lg shadow-lg hover:shadow-yellow-500/50 transition-all duration-300 transform hover:-translate-y-1">
                            Confirm Booking
                        </button>
                    <?php else: ?>
                        <a href="login.php?redirect=spa.php&notify=please_login" class="inline-block w-full md:w-auto px-10 py-4 bg-yellow-600 hover:bg-yellow-700 text-white rounded-full font-bold text-lg shadow-lg hover:shadow-yellow-500/50 transition-all duration-300 transform hover:-translate-y-1">
                            Login to Book
                        </a>
                        <p class="mt-4 text-sm text-gray-500">You must be logged in to book an appointment.</p>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
    // Reveal Animation
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
            }
        });
    }, {
        threshold: 0.1
    });

    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

    // Date Validation
    const spaDate = document.getElementById('date');
    if (spaDate) {
        spaDate.min = new Date().toISOString().split('T')[0];
    }

    // Scroll and Fill Function
    function bookTreatment(treatmentName) {
        const formSection = document.getElementById('spa-reserve');
        const select = document.getElementById('treatment-select');

        if (select && treatmentName) {
            select.value = treatmentName;
        }

        formSection.scrollIntoView({
            behavior: 'smooth'
        });
    }

    // Auto-scroll to form on error
    <?php if ($error_message): ?>
        window.location.hash = 'spa-reserve';
    <?php endif; ?>
</script>

<?php include('footer.php'); ?>