<?php
include('header.php');
include('db.php');

// Get all rooms with their booking status
$query = "SELECT r.*, 
          (SELECT COUNT(*) FROM bookings b 
           WHERE b.room_id = r.id 
           AND b.status IN ('approved', 'pending')
           AND NOW() >= b.check_in_date 
           AND NOW() < DATE_ADD(b.check_out_date, INTERVAL 20 MINUTE)
           ) as is_booked,
          (SELECT DATE_ADD(MAX(b.check_out_date), INTERVAL 20 MINUTE) FROM bookings b 
           WHERE b.room_id = r.id 
           AND b.status IN ('approved', 'pending')
           AND NOW() >= b.check_in_date 
           AND NOW() < DATE_ADD(b.check_out_date, INTERVAL 20 MINUTE)
           ) as next_available_date
          FROM rooms r";
$result = mysqli_query($conn, $query);

// Store rooms data for JavaScript and display
$rooms_data = [];
while ($room = mysqli_fetch_assoc($result)) {
    // Ensure hourly price is available (fallback if 0)
    $room['hourly_price'] = (isset($room['price_per_hour']) && $room['price_per_hour'] > 0) 
                            ? $room['price_per_hour'] 
                            : ceil($room['price'] * 0.15);
    $rooms_data[] = $room;
}

// Function to format price
function formatPrice($price)
{
    return number_format($price, 2);
}
?>

<!-- Main Content -->
<div class="bg-gray-50 min-h-screen py-16">
    <div class="container mx-auto px-4 max-w-7xl">
        <!-- Header Section -->
        <div class="text-center mb-16 space-y-4">
            <h3 class="text-yellow-600 font-medium tracking-[0.2em] text-sm uppercase">Accommodations</h3>
            <h2 class="text-4xl md:text-5xl font-bold text-gray-900 font-serif">Our Luxury Collection</h2>
            <p class="text-gray-500 max-w-2xl mx-auto text-lg font-light">
                Immerse yourself in sophisticated elegance. Each room is designed to provide an unparalleled experience of comfort and style.
            </p>
            <div class="w-24 h-1 bg-yellow-500 mx-auto mt-6 rounded-full"></div>
        </div>

        <!-- Rooms Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
            <?php foreach ($rooms_data as $room): ?>
                <div class="group bg-white rounded-3xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-500 border border-gray-100 flex flex-col h-full transform hover:-translate-y-2 room-clickable"
                    data-room-id="<?php echo $room['id']; ?>">

                    <!-- Image Carousel Area -->
                    <div class="relative h-72 bg-gray-200 overflow-hidden">
                        <!-- Status Badge -->
                        <?php if ($room['is_booked'] > 0): ?>
                            <div class="absolute top-4 right-4 z-20 bg-red-500/90 backdrop-blur-sm text-white px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider shadow-lg">
                                Unavailable
                            </div>
                        <?php else: ?>
                            <div class="absolute top-4 right-4 z-20 bg-emerald-500/90 backdrop-blur-sm text-white px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider shadow-lg">
                                Available
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($room['room_image'])): ?>
                            <div id="carousel-<?php echo $room['id']; ?>" class="carousel slide h-full" data-bs-ride="carousel">
                                <div class="carousel-indicators mb-2">
                                    <button type="button" data-bs-target="#carousel-<?php echo $room['id']; ?>" data-bs-slide-to="0" class="active"></button>
                                    <?php
                                    // -------- IMAGE SEARCH LOGIC MATCHING ORIGINAL --------
                                    $roomType = strtolower(preg_replace('/\s+/', '', $room['room_name']));
                                    $searchPatterns = [
                                        'penthousesuite' => 'penthouse',
                                        'bridalsuite' => 'bridalsuite',
                                        'honeymoon' => 'honeymoon',
                                        'honeymoonroom' => 'honeymoon',
                                        'honeymoonbedroom' => 'honeymoon',
                                        'honeymoomsuite' => 'honeymoon',
                                        'honeymoonsuite' => 'honeymoon',
                                        'king' => 'kings',
                                        'kingsbed' => 'kings',
                                        'kingbed' => 'kings',
                                        'queen' => 'queen',
                                        'queensbed' => 'queen',
                                        'queenbed' => 'queen',
                                        'singleroom' => 'singleroom',
                                        'doubleroom' => 'doubleroom',
                                        'suiteroom' => 'suiteroom',
                                        'seasideview' => 'seaside'
                                    ];
                                    $normalizedRoomName = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', $room['room_name'])));
                                    if (isset($searchPatterns[$roomType])) {
                                        $searchPattern = $searchPatterns[$roomType];
                                    } elseif (isset($searchPatterns[$normalizedRoomName])) {
                                        $searchPattern = $searchPatterns[$normalizedRoomName];
                                    } else {
                                        $searchPattern = $roomType;
                                        foreach ($searchPatterns as $key => $pattern) {
                                            if (strpos($normalizedRoomName, $key) !== false) {
                                                $searchPattern = $pattern;
                                                break;
                                            }
                                        }
                                    }
                                    $additionalImages = [];
                                    $roomImages = [
                                        'honeymoon' => ['honeymoonbathroom.jpg', 'honeymoonkitchen.jpg', 'honeymoonlivingarea.jpg'],
                                        'penthouse' => ['penthousebathroom.jpg', 'penthousebedroom.jpg', 'penthouse kitchen.jpg'],
                                        'bridalsuite' => ['bridalsuitebathroom.jpg', 'bridalsuitediningroom.jpg', 'bridalsuitebredroom.jpg'],
                                        'kings' => ['kingsbathroom.jpg', 'kingskitchen.jpg'],
                                        'queen' => ['queenbalcony.jpg', 'queenkitchen.jpg'],
                                        'singleroom' => ['singleroombathroom.jpg', 'singleroomkitchen.jpg', 'singleroomworkarea.jpg'],
                                        'doubleroom' => ['doubleroombathroom.jpg', 'doubleroomkitchen.jpg', 'doubleroombalcony.jpg'],
                                        'suiteroom' => ['suiteroombalcony.jpg', 'suiteroomkitchen.jpg'],
                                        'seaside' => ['seasidebathroom.jpg', 'seasidekitchen.jpg']
                                    ];
                                    if (isset($roomImages[$searchPattern])) {
                                        foreach ($roomImages[$searchPattern] as $imageName) {
                                            if (file_exists("images/" . $imageName)) {
                                                $additionalImages[] = "images/" . $imageName;
                                            }
                                        }
                                    } else {
                                        $allImages = glob("images/*.jpg");
                                        foreach ($allImages as $imagePath) {
                                            $imageName = strtolower(basename($imagePath));
                                            if ($imageName != strtolower($room['room_image']) && strpos($imageName, $searchPattern) !== false) {
                                                $additionalImages[] = $imagePath;
                                            }
                                        }
                                    }
                                    if (($searchPattern == 'honeymoon' || $searchPattern == 'kings' || $searchPattern == 'queen') && count($additionalImages) == 0) {
                                        $allImages = glob("images/*.jpg");
                                        foreach ($allImages as $imagePath) {
                                            if (basename($imagePath) != $room['room_image'] && strpos(basename($imagePath), $searchPattern) !== false) {
                                                $additionalImages[] = $imagePath;
                                            }
                                        }
                                    }
                                    if (count($additionalImages) == 0) {
                                        $genericImages = ['bathroom.jpg', 'kitchen.jpg', 'balcony.jpg', 'livingarea.jpg'];
                                        foreach ($genericImages as $generic) {
                                            $possible = glob("images/*" . $generic);
                                            if (!empty($possible)) {
                                                $additionalImages[] = $possible[0];
                                            }
                                        }
                                    }
                                    // Indicators loop
                                    for ($i = 0; $i < count($additionalImages) && $i < 3; $i++) {
                                        echo '<button type="button" data-bs-target="#carousel-' . $room['id'] . '" data-bs-slide-to="' . ($i + 1) . '"></button>';
                                    }
                                    ?>
                                </div>
                                <div class="carousel-inner h-full">
                                    <div class="carousel-item active h-full">
                                        <img src="images/<?php echo htmlspecialchars($room['room_image']); ?>" class="d-block w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700" alt="Main View">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                                    </div>
                                    <?php foreach ($additionalImages as $index => $image): if ($index < 3): ?>
                                            <div class="carousel-item h-full">
                                                <img src="<?php echo $image; ?>" class="d-block w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700" alt="Room View">
                                                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                                            </div>
                                    <?php endif;
                                    endforeach; ?>
                                </div>
                                <button class="carousel-control-prev z-20" type="button" data-bs-target="#carousel-<?php echo $room['id']; ?>" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon transform scale-75" aria-hidden="true"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next z-20" type="button" data-bs-target="#carousel-<?php echo $room['id']; ?>" data-bs-slide="next">
                                    <span class="carousel-control-next-icon transform scale-75" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
                            </div>
                        <?php else: ?>
                            <img src="images/default-room.jpg" class="w-full h-full object-cover" alt="Default Room">
                        <?php endif; ?>
                    </div>

                    <!-- Card Body -->
                    <div class="p-8 flex flex-col flex-grow">
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-2xl font-bold font-serif text-gray-900 group-hover:text-yellow-600 transition-colors">
                                <?php echo htmlspecialchars($room['room_name']); ?>
                            </h3>
                            <div class="text-right">
                                <span class="block text-2xl font-bold text-yellow-600">$<?php echo formatPrice($room['price']); ?></span>
                                <span class="text-xs text-gray-400">/ night</span>
                            </div>
                        </div>

                        <!-- Features Summary -->
                        <div class="flex flex-wrap gap-4 mb-6 text-sm text-gray-500 border-b border-gray-100 pb-6">
                            <?php
                            switch ($room['room_name']) {
                                case 'Penthouse Suite':
                                    echo '<span class="flex items-center gap-2"><i class="fas fa-mountain text-yellow-500"></i> City View</span>';
                                    echo '<span class="flex items-center gap-2"><i class="fas fa-wifi text-yellow-500"></i> Free WiFi</span>';
                                    break;
                                case 'Bridal Suite':
                                    echo '<span class="flex items-center gap-2"><i class="fas fa-bed text-yellow-500"></i> King Bed</span>';
                                    echo '<span class="flex items-center gap-2"><i class="fas fa-glass-cheers text-yellow-500"></i> Champagne</span>';
                                    break;
                                default:
                                    echo '<span class="flex items-center gap-2"><i class="fas fa-bed text-yellow-500"></i> King Bed</span>';
                                    echo '<span class="flex items-center gap-2"><i class="fas fa-wifi text-yellow-500"></i> Free WiFi</span>';
                            }
                            ?>
                        </div>

                        <!-- Description -->
                        <div class="mb-6 relative">
                            <p class="text-gray-500 line-clamp-3 leading-relaxed text-sm">
                                <?php echo htmlspecialchars($room['description']); ?>
                            </p>
                        </div>

                        <!-- Action Area -->
                        <div class="mt-auto space-y-3">
                            <?php if ($room['is_booked'] > 0 && !empty($room['next_available_date'])): ?>
                                <p class="text-center text-xs text-red-500 font-medium">
                                    Next available: <?php echo date('M j, Y g:i A', strtotime($room['next_available_date'])); ?>
                                </p>
                            <?php endif; ?>
                            <a href="add_booking.php?room_id=<?php echo $room['id']; ?>"
                                class="btn-book block w-full py-4 bg-gray-900 text-white text-center rounded-xl font-bold tracking-wide hover:bg-yellow-600 transition-all duration-300 shadow-lg hover:shadow-yellow-500/30 transform hover:-translate-y-1"
                                onclick="event.stopPropagation();">
                                Book Now
                            </a>

                            <button class="btn-view-details w-full py-2 text-sm text-gray-500 hover:text-gray-900 transition-colors font-medium border border-transparent hover:border-gray-200 rounded-lg">
                                Click to view details
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Modal Logic (Hidden) -->
<div class="modal fade" id="roomDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content overflow-hidden border-0 rounded-3xl shadow-2xl">
            <!-- Header with Image Background -->
            <div class="modal-header border-0 p-0 relative h-64 bg-gray-900">
                <button type="button" class="btn-close absolute top-4 right-4 z-50 bg-white opacity-100 rounded-full p-2 shadow-lg hover:bg-gray-100 transition-colors" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="absolute inset-0 z-0" id="modalHeroImageContainer">
                    <!-- Hero Video/Image injected here -->
                </div>
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent z-10 pointer-events-none"></div>
                <div class="absolute bottom-0 left-0 p-8 z-20 text-white w-full flex justify-between items-end">
                    <div>
                        <h2 class="text-3xl font-bold font-serif mb-1" id="modalRoomTitle">Room Name</h2>
                        <div class="flex items-center gap-2">
                            <span class="text-yellow-400 text-xl font-bold" id="modalRoomPrice">$0</span>
                            <span class="text-white/70 text-sm">/ night</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-yellow-400 text-xl font-bold" id="modalRoomHourlyPrice">$0</span>
                            <span class="text-white/70 text-sm">/ hour</span>
                        </div>
                    </div>
                    <button id="modalLikeBtn" onclick="toggleLike()" class="group flex flex-col items-center gap-1 transition-all">
                        <div class="w-12 h-12 rounded-full bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center group-hover:bg-white/20 transition-all">
                            <i class="far fa-heart text-2xl text-white transition-all transform group-hover:scale-110" id="likeIcon"></i>
                        </div>
                        <span class="text-xs font-bold text-white tracking-wide" id="likeCount">0 Likes</span>
                    </button>
                </div>
            </div>

            <div class="modal-body p-8 bg-white">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Main Info -->
                    <div class="lg:col-span-2 space-y-6">
                        <div>
                            <h4 class="text-gray-900 font-bold mb-3 uppercase tracking-wider text-xs">Description</h4>
                            <p class="text-gray-600 leading-relaxed" id="modalRoomDesc">Description goes here...</p>
                        </div>

                        <div>
                            <h4 class="text-gray-900 font-bold mb-4 uppercase tracking-wider text-xs">Room Amenities</h4>
                            <div class="grid grid-cols-2 gap-4" id="modalRoomFeatures">
                                <!-- Features injected here -->
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar / Booking Status -->
                    <div class="lg:col-span-1">
                        <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100 h-full flex flex-col justify-center text-center space-y-4" id="modalBookingStatus">
                            <!-- Status injected here -->
                        </div>
                    </div>
                </div>

                <!-- Reviews Section -->
                <div class="mt-10 pt-10 border-t border-gray-100">
                    <h4 class="text-gray-900 font-bold mb-6 uppercase tracking-wider text-xs flex items-center gap-2">
                        Guest Reviews <span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full text-[10px]" id="reviewCountBadge">0</span>
                    </h4>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                        <!-- Reviews List -->
                        <div class="space-y-6 max-h-[400px] overflow-y-auto pr-4" id="modalReviewsList">
                            <!-- Reviews injected via JS -->
                            <div class="text-center py-10 text-gray-400">Loading reviews...</div>
                        </div>

                        <!-- Add Review Form -->
                        <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100 h-fit">
                            <h5 class="font-bold text-gray-900 mb-4">Write a Review</h5>
                            <form id="reviewForm" onsubmit="submitReview(event)">
                                <input type="hidden" id="reviewRoomId" name="room_id">
                                <div class="mb-4">
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Rating</label>
                                    <div class="flex gap-2 text-2xl text-gray-300">
                                        <i class="fas fa-star cursor-pointer hover:text-yellow-400 transition-colors star-rating" data-value="1" onclick="setRating(1)"></i>
                                        <i class="fas fa-star cursor-pointer hover:text-yellow-400 transition-colors star-rating" data-value="2" onclick="setRating(2)"></i>
                                        <i class="fas fa-star cursor-pointer hover:text-yellow-400 transition-colors star-rating" data-value="3" onclick="setRating(3)"></i>
                                        <i class="fas fa-star cursor-pointer hover:text-yellow-400 transition-colors star-rating" data-value="4" onclick="setRating(4)"></i>
                                        <i class="fas fa-star cursor-pointer hover:text-yellow-400 transition-colors star-rating" data-value="5" onclick="setRating(5)"></i>
                                    </div>
                                    <input type="hidden" name="rating" id="ratingInput" value="5">
                                </div>
                                <div class="mb-4">
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Comment</label>
                                    <textarea name="comment" rows="3" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500 outline-none text-sm" placeholder="Share your experience..."></textarea>
                                </div>
                                <div id="reviewFormActions">
                                    <!-- Button or Login Prompt injected via JS -->
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Custom Scrollbar for Modal */
    .modal-body::-webkit-scrollbar {
        width: 6px;
    }

    .modal-body::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .modal-body::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }

    .modal-body::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>

<script>
    const roomsData = <?php echo json_encode($rooms_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?: '[]'; ?>;
    const roomVideos = {
        penthousesuite: 'Videos/Penthouse.mp4',
        bridalsuite: 'Videos/bridal.mp4',
        honeymoonsuite: 'Videos/honeymoon.mp4',
        doubleroom: 'Videos/double.mp4',
        queenroom: 'Videos/queen.mp4',
        queens: 'Videos/queen.mp4',
        kingroom: 'Videos/king.mp4',
        kings: 'Videos/king.mp4',
        seasideview: 'Videos/seaside.mp4',
        singleroom: 'Videos/single.mp4',
        suiteroom: 'Videos/suite.mp4'
    };
    // Removed Text-to-Speech logic per request to focus on Video Audio

    function getRoomVideoSrc(roomName) {
        if (!roomName) return 'Videos/default.mp4';
        const normalized = roomName.toLowerCase().replace(/[^a-z0-9]/g, '');
        if (roomVideos[normalized]) return roomVideos[normalized];
        const entry = Object.entries(roomVideos).find(([key]) => normalized.includes(key));
        return entry ? entry[1] : 'Videos/default.mp4';
    }

    function getRoomFeatures(roomName) {
        const features = {
            'Penthouse Suite': ['fa-mountain:City View', 'fa-couch:Living Area', 'fa-wifi:Free WiFi', 'fa-tv:Smart TV', 'fa-bath:Luxury Bath', 'fa-utensils:Kitchenette'],
            'Bridal Suite': ['fa-bed:King Bed', 'fa-glass-cheers:Champagne', 'fa-bath:Luxury Bath', 'fa-heart:Romantic', 'fa-wifi:Free WiFi', 'fa-tv:Smart TV'],
            'Honeymoon Suite': ['fa-bed:King Bed', 'fa-heart:Romantic', 'fa-bath:Jacuzzi', 'fa-mountain:Ocean View', 'fa-wifi:Free WiFi'],
            'default': ['fa-bed:Comfortable Bed', 'fa-wifi:Free WiFi', 'fa-tv:Smart TV', 'fa-bath:Private Bath', 'fa-wind:AC', 'fa-coffee:Coffee Maker']
        };
        for (let key in features) {
            if (key !== 'default' && roomName.toLowerCase().includes(key.toLowerCase())) return features[key];
        }
        return features.default;
    }

    function toggleAudio() {
        const video = document.querySelector('#modalHeroImageContainer video');
        if (!video) return;
        video.muted = !video.muted;
        const btn = document.getElementById('audioToggleBtn');
        if (video.muted) {
            btn.innerHTML = '<i class="fas fa-volume-mute"></i>';
            btn.classList.add('bg-red-500', 'text-white');
            btn.classList.remove('bg-white', 'text-gray-900');
        } else {
            btn.innerHTML = '<i class="fas fa-volume-up"></i>';
            btn.classList.remove('bg-red-500', 'text-white');
            btn.classList.add('bg-white', 'text-gray-900');
        }
    }

    // --- Interaction Logic ---
    let currentRoomId = null;
    let isUserLoggedIn = false;

    function fetchInteractions(roomId) {
        currentRoomId = roomId;
        document.getElementById('reviewRoomId').value = roomId;

        fetch(`api_room_interactions.php?action=get_interactions&room_id=${roomId}`)
            .then(res => res.json())
            .then(data => {
                // Update Like UI
                const likeIcon = document.getElementById('likeIcon');
                document.getElementById('likeCount').textContent = `${data.likes} Likes`;
                if (data.user_liked) {
                    likeIcon.classList.remove('far', 'text-white');
                    likeIcon.classList.add('fas', 'text-red-500');
                } else {
                    likeIcon.classList.remove('fas', 'text-red-500');
                    likeIcon.classList.add('far', 'text-white');
                }

                isUserLoggedIn = data.user_logged_in;

                // Render Reviews
                const list = document.getElementById('modalReviewsList');
                document.getElementById('reviewCountBadge').textContent = data.reviews.length;

                if (data.reviews.length === 0) {
                    list.innerHTML = `<div class="text-center py-8 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                        <p class="text-gray-500 text-sm">No reviews yet. Be the first to share your thoughts!</p>
                    </div>`;
                } else {
                    list.innerHTML = data.reviews.map(r => `
                        <div class="border-b border-gray-100 pb-6 last:border-0 last:pb-0">
                            <div class="flex justify-between items-start mb-2">
                                <span class="font-bold text-gray-900">${r.username || 'Guest'}</span>
                                <span class="text-xs text-gray-400">${r.date_formatted}</span>
                            </div>
                            <div class="text-yellow-400 text-xs mb-2">
                                ${Array(5).fill(0).map((_, i) => `<i class="${i < r.rating ? 'fas' : 'far'} fa-star"></i>`).join('')}
                            </div>
                            <p class="text-gray-600 text-sm leading-relaxed">${r.comment}</p>
                        </div>
                    `).join('');
                }

                // Update Form State
                const actionArea = document.getElementById('reviewFormActions');
                if (data.user_logged_in) {
                    actionArea.innerHTML = `
                        <button type="submit" class="w-full py-3 bg-gray-900 text-white rounded-xl font-bold hover:bg-yellow-600 transition-colors">
                            Post Review
                        </button>`;
                } else {
                    actionArea.innerHTML = `
                        <div class="text-center bg-gray-100 p-3 rounded-lg">
                            <p class="text-xs text-gray-500 mb-2">You must be logged in to post a review.</p>
                            <a href="login.php?redirect=view_rooms.php" class="text-sm font-bold text-gray-900 underline hover:text-yellow-600">Login Now</a>
                        </div>`;
                }
            });
    }

    function toggleLike() {
        if (!isUserLoggedIn) {
            alert("Please login to like this room.");
            return;
        }

        // Optimistic UI update
        const likeIcon = document.getElementById('likeIcon');
        const isLiked = likeIcon.classList.contains('fas');

        if (isLiked) {
            likeIcon.classList.remove('fas', 'text-red-500');
            likeIcon.classList.add('far', 'text-white');
        } else {
            likeIcon.classList.remove('far', 'text-white');
            likeIcon.classList.add('fas', 'text-red-500');
        }

        fetch(`api_room_interactions.php?action=toggle_like&room_id=${currentRoomId}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('likeCount').textContent = `${data.new_count} Likes`;
                    // Ensure icon matches server state (idempotent)
                    if (data.liked) {
                        likeIcon.classList.remove('far', 'text-white');
                        likeIcon.classList.add('fas', 'text-red-500');
                    } else {
                        likeIcon.classList.remove('fas', 'text-red-500');
                        likeIcon.classList.add('far', 'text-white');
                    }
                }
            });
    }

    function setRating(val) {
        document.getElementById('ratingInput').value = val;
        document.querySelectorAll('.star-rating').forEach((star, index) => {
            if (index < val) {
                star.classList.add('text-yellow-400');
                star.classList.remove('text-gray-300');
            } else {
                star.classList.remove('text-yellow-400');
                star.classList.add('text-gray-300');
            }
        });
    }

    // Initialize stars to 5 default
    setRating(5);

    function submitReview(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        formData.append('action', 'post_review');

        fetch('api_room_interactions.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Refresh interactions
                    fetchInteractions(currentRoomId);
                    form.reset();
                    setRating(5);
                } else {
                    alert(data.error || 'Error submitting review');
                }
            });
    }

    function showRoomDetails(roomId, modalBS) {
        const room = roomsData.find(r => r.id == roomId);
        if (!room) {
            console.error("Room not found in data. Room ID:", roomId);
            console.log("Available Rooms Data:", roomsData);
            alert("Error: Room details could not be loaded. Please check the console.");
            return;
        }

        document.getElementById('modalRoomTitle').textContent = room.room_name;
        document.getElementById('modalRoomPrice').textContent = '$' + parseFloat(room.price).toFixed(2);
        document.getElementById('modalRoomHourlyPrice').textContent = '$' + parseFloat(room.hourly_price).toFixed(2);

        const videoSrc = getRoomVideoSrc(room.room_name);
        const heroContainer = document.getElementById('modalHeroImageContainer');

        // VIDEO SECTION:
        // We attempt autoplay. Some browsers block unmuted autoplay. 
        // We start UNMUTED (muted=false) because user requested "audio of that video".
        // We provide a fallback if it fails.
        heroContainer.innerHTML = `
        <video class="w-full h-full object-cover" autoplay loop playsinline>
            <source src="${videoSrc}" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <button onclick="toggleAudio()" id="audioToggleBtn" 
            class="absolute bottom-4 right-4 z-30 w-10 h-10 rounded-full flex items-center justify-center shadow-lg transition-transform hover:scale-110 bg-white text-gray-900">
            <i class="fas fa-volume-up"></i>
        </button>
    `;

        // Attempt to play unmuted logic
        const video = heroContainer.querySelector('video');
        video.muted = false; // Intentionally unmuted

        const playPromise = video.play();
        if (playPromise !== undefined) {
            playPromise.then(_ => {
                // Autoplay started unmuted!
                updateAudioButton(false);
            }).catch(error => {
                // Autoplay was prevented. Fallback to muted autoplay.
                console.log("Unmuted autoplay prevented. Muting and playing.");
                video.muted = true;
                video.play();
                updateAudioButton(true);
            });
        }

        document.getElementById('modalRoomDesc').textContent = room.description || 'Experience luxury and comfort in this beautifully appointed room.';

        const features = getRoomFeatures(room.room_name);
        const featureContainer = document.getElementById('modalRoomFeatures');
        featureContainer.innerHTML = features.map(feat => {
            const [icon, text] = feat.split(':');
            return `
            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-yellow-500 shadow-sm">
                    <i class="fas ${icon}"></i>
                </div>
                <span class="text-sm font-medium text-gray-700">${text}</span>
            </div>
        `;
        }).join('');

        const statusContainer = document.getElementById('modalBookingStatus');
        let statusHtml = '';

        if (room.is_booked > 0) {
            statusHtml = `
            <div class="w-12 h-12 bg-red-100 text-red-500 rounded-full flex items-center justify-center mx-auto mb-2">
                <i class="fas fa-calendar-times text-xl"></i>
            </div>
            <h5 class="font-bold text-red-500">Currently Unavailable</h5>
            ${room.next_available_date ? `<p class="text-xs text-gray-500">Available from: ${new Date(room.next_available_date).toLocaleString()}</p>` : ''}
            <a href="add_booking.php?room_id=${room.id}" class="block w-full py-3 bg-gray-900 text-white rounded-xl font-bold hover:bg-gray-800 transition-colors mt-4">
                Check Other Dates
            </a>
        `;
        } else {
            statusHtml = `
            <div class="w-12 h-12 bg-emerald-100 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-2">
                <i class="fas fa-check-circle text-xl"></i>
            </div>
            <h5 class="font-bold text-emerald-600">Available Now</h5>
            <p class="text-xs text-gray-400">Ready for instant confirmation</p>
            <a href="add_booking.php?room_id=${room.id}" class="block w-full py-3 bg-yellow-600 text-white rounded-xl font-bold hover:bg-yellow-700 transition-colors shadow-lg shadow-yellow-500/30 mt-4">
                Book This Room
            </a>
        `;
        }
        statusContainer.innerHTML = statusHtml;

        // Fetch Interactions (Likes/Reviews)
        fetchInteractions(roomId);

        modalBS.show();
    }

    // Helper to sync button state
    function updateAudioButton(isMuted) {
        const btn = document.getElementById('audioToggleBtn');
        if (!btn) return;
        if (isMuted) {
            btn.innerHTML = '<i class="fas fa-volume-mute"></i>';
            btn.classList.add('bg-red-500', 'text-white');
            btn.classList.remove('bg-white', 'text-gray-900');
        } else {
            btn.innerHTML = '<i class="fas fa-volume-up"></i>';
            btn.classList.remove('bg-red-500', 'text-white');
            btn.classList.add('bg-white', 'text-gray-900');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const waitForBS = setInterval(() => {
            if (typeof bootstrap !== 'undefined') {
                clearInterval(waitForBS);
                initModal();
            }
        }, 100);
    });

    function initModal() {
        const modalEl = document.getElementById('roomDetailModal');
        if (modalEl.parentElement !== document.body) document.body.appendChild(modalEl);

        const modalBS = new bootstrap.Modal(modalEl);

        document.querySelectorAll('.room-clickable').forEach(card => {
            card.addEventListener('click', function(e) {
                // Ignore matching specific interactive elements, but ALLOW view-details button
                if (e.target.closest('a') || (e.target.closest('button') && !e.target.closest('.btn-view-details'))) return;

                const roomId = this.getAttribute('data-room-id');
                showRoomDetails(roomId, modalBS);
            });
        });

        modalEl.addEventListener('hidden.bs.modal', () => {
            const video = modalEl.querySelector('video');
            if (video) {
                video.pause();
                video.currentTime = 0;
            }
        });
    }
</script>

<?php include('footer.php'); ?>