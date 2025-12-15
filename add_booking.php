<?php
session_start();
include('header.php');
include('db.php');

// Get the room_id from URL if available
$selected_room_id = isset($_GET['room_id']) ? (int)$_GET['room_id'] : '';

// Get today's date and time for minimum booking date
$today = date('Y-m-d H:i');

// Get error message if any
$error_message = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['error']); // Clear the message after getting it
?>

<!-- Add Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css">

<!-- Custom styles for booking page -->
<style>
    .booking-section {
        padding: 80px 0;
        background: linear-gradient(to bottom, #f8f9fa, #ffffff);
    }

    .booking-card {
        border: none;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .booking-card:hover {
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
    }

    .booking-header {
        background-color: #1a1a1a;
        color: white;
        padding: 25px;
        text-align: center;
        position: relative;
    }

    .booking-header h2 {
        margin: 0;
        font-weight: 700;
        font-size: 2.2rem;
        position: relative;
    }

    .booking-header p {
        margin: 10px 0 0;
        opacity: 0.8;
    }

    .booking-header:after {
        content: "";
        display: block;
        width: 50px;
        height: 4px;
        background-color: #ffd700;
        margin: 15px auto 0;
    }

    .booking-body {
        padding: 40px;
    }

    .form-label {
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
    }

    .form-control,
    .form-select {
        padding: 12px 20px;
        border-radius: 10px;
        border: 1px solid #ddd;
        font-size: 1rem;
        transition: all 0.3s;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #1a1a1a;
        box-shadow: 0 0 0 0.25rem rgba(26, 26, 26, 0.15);
    }

    .date-field {
        position: relative;
    }

    .date-field i {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #666;
        pointer-events: none;
    }

    .btn-book {
        background-color: #1a1a1a;
        border: none;
        padding: 16px 24px;
        border-radius: 10px;
        font-weight: 600;
        letter-spacing: 0.5px;
        transition: all 0.3s;
    }

    .btn-book:hover {
        background-color: #333;
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .booking-info {
        margin-top: 30px;
        border-top: 1px solid #eee;
        padding-top: 30px;
    }

    #total-price {
        background-color: #f8f9fa;
        border-left: 4px solid #1a1a1a;
        border-radius: 8px;
        padding: 20px;
    }

    #price-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1a1a1a;
    }

    .flatpickr-day.flatpickr-disabled,
    .flatpickr-day.flatpickr-blocked {
        background-color: #ffcccb !important;
        color: #333 !important;
        text-decoration: line-through;
        position: relative;
    }

    .flatpickr-day.flatpickr-disabled::after,
    .flatpickr-day.flatpickr-blocked::after {
        content: "✕";
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: #ff0000;
        font-weight: bold;
    }

    .date-info-tooltip {
        display: none;
        position: absolute;
        background: #333;
        color: white;
        padding: 5px;
        border-radius: 3px;
        z-index: 100;
    }

    #availability-message.alert-success {
        background-color: #edf7ed;
        border-color: #c3e6cb;
        color: #155724;
        border-radius: 8px;
    }

    #availability-message.alert-danger {
        background-color: #f8d7da;
        border-color: #f5c6cb;
        color: #721c24;
        border-radius: 8px;
    }
</style>

<div class="booking-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="booking-card">
                    <div class="booking-header">
                        <h2>Book Your Stay</h2>
                        <p>Experience luxury and comfort at our prestigious hotel</p>
                    </div>

                    <div class="booking-body">
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($error_message); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form action="process_booking.php" method="POST" id="bookingForm">
                            <div class="mb-4">
                                <label for="customer_name" class="form-label">Your Name</label>
                                <input type="text"
                                    name="customer_name"
                                    id="customer_name"
                                    class="form-control"
                                    placeholder="Enter your full name"
                                    required>
                            </div>

                            <div class="mb-4">
                                <label for="room_id" class="form-label">Select Room</label>
                                <select name="room_id" id="room_id" class="form-select" required>
                                    <option value="" disabled <?php echo empty($selected_room_id) ? 'selected' : ''; ?>>
                                        Select a room
                                    </option>
                                    <?php
                                    $result = mysqli_query($conn, "SELECT * FROM rooms ORDER BY price ASC");

                                    if (mysqli_num_rows($result) > 0) {
                                        while ($room = mysqli_fetch_assoc($result)) {
                                            $selected = ($selected_room_id == $room['id']) ? 'selected' : '';
                                            echo "<option value='" . $room['id'] . "' " . $selected . ">"
                                                . htmlspecialchars($room['room_name'])
                                                . " - $" . number_format($room['price'], 2)
                                                . " per night</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>

                            <div id="unavailable-dates-container" class="mb-4 d-none">
                                <label class="form-label text-danger" style="color: #dc3545;">Unavailable Dates (Currently Booked)</label>
                                <div id="unavailable-dates-list" class="small text-muted" style="background-color: #f8f9fa; padding: 10px; border-radius: 8px; border-left: 4px solid #dc3545;"></div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="check_in_date" class="form-label">Check-in Date</label>
                                    <div class="date-field">
                                        <input type="text"
                                            name="check_in_date"
                                            id="check_in_date"
                                            class="form-control"
                                            placeholder="Select check-in date"
                                            data-min-date="<?php echo $today; ?>"
                                            data-enable-time="true"
                                            required>
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label for="check_out_date" class="form-label">Check-out Date</label>
                                    <div class="date-field">
                                        <input type="text"
                                            name="check_out_date"
                                            id="check_out_date"
                                            class="form-control"
                                            placeholder="Select check-out date"
                                            data-min-date="<?php echo $today; ?>"
                                            data-enable-time="true"
                                            required>
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                </div>
                            </div>

                            <div id="availability-message" class="alert d-none mb-4"></div>

                            <div class="booking-info">
                                <div id="total-price" class="alert alert-info d-none mb-4">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <strong>Total Price for Your Stay:</strong>
                                        </div>
                                        <div class="col-md-4 text-md-end">
                                            <span id="price-value">$0.00</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-grid mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg btn-book">
                                        <i class="fas fa-calendar-check me-2"></i>Confirm Booking
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('bookingForm');
        const roomSelect = document.getElementById('room_id');
        const checkInDateInput = document.getElementById('check_in_date');
        const checkOutDateInput = document.getElementById('check_out_date');
        const messageDiv = document.getElementById('availability-message');
        const totalPriceDiv = document.getElementById('total-price');
        const priceValueSpan = document.getElementById('price-value');
        const submitButton = form.querySelector('button[type="submit"]');

        // Store room prices
        const roomData = {};
        <?php
        mysqli_data_seek($result, 0);
        while ($room = mysqli_fetch_assoc($result)) {
            // Default hourly if not set? We set it in migration but good to handle safely
            $hourly = $room['price_per_hour'] > 0 ? $room['price_per_hour'] : ceil($room['price'] * 0.15);
            echo "roomData[" . $room['id'] . "] = { nightly: " . $room['price'] . ", hourly: " . $hourly . " };\n";
        }
        ?>

        // Initialize date pickers
        let checkInPicker;
        let checkOutPicker;
        let disabledDates = [];

        function initDatePickers() {
            // Destroy existing pickers if they exist
            if (checkInPicker) {
                checkInPicker.destroy();
            }
            if (checkOutPicker) {
                checkOutPicker.destroy();
            }

            // Check-in date picker
            checkInPicker = flatpickr(checkInDateInput, {
                minDate: "today",
                disable: disabledDates,
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                onChange: function(selectedDates, dateStr) {
                    checkOutPicker.set('minDate', dateStr);
                    calculateTotalPrice();
                    checkAvailability();
                }
            });

            // Check-out date picker
            checkOutPicker = flatpickr(checkOutDateInput, {
                minDate: checkInDateInput.value || "today",
                disable: disabledDates,
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                onChange: function() {
                    calculateTotalPrice();
                    checkAvailability();
                }
            });
        }

        // Load booked dates for a room
        async function loadBookedDates(roomId) {
            if (!roomId) return;

            try {
                const response = await fetch(`get_booked_dates.php?room_id=${roomId}`);
                const data = await response.json();

                disabledDates = [];
                const listContainer = document.getElementById('unavailable-dates-container');
                const listDiv = document.getElementById('unavailable-dates-list');
                let listHtml = '';

                // Process each booking period
                if (data.booked_periods && data.booked_periods.length > 0) {
                    data.booked_periods.forEach(period => {
                        disabledDates.push({
                            from: period.start,
                            to: period.end
                        });
                        // Add to list HTML - Formatting date for readability
                        // Note: Ensure dates are parsed correctly. 
                        const startDate = new Date(period.start);
                        const endDate = new Date(period.end);
                        // Using simplistic formatting; can be improved or use local format
                        const startStr = startDate.toLocaleString(undefined, { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
                        const endStr = endDate.toLocaleString(undefined, { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
                        
                        listHtml += `<div class="mb-1"><i class="fas fa-calendar-times me-2"></i> ${startStr} — ${endStr}</div>`;
                    });
                    
                    listDiv.innerHTML = listHtml;
                    listContainer.classList.remove('d-none');
                } else {
                    listContainer.classList.add('d-none');
                    listDiv.innerHTML = '';
                }

                // Reinitialize the date pickers with new disabled dates
                initDatePickers();

            } catch (error) {
                console.error('Error loading booked dates:', error);
            }
        }

        function calculateTotalPrice() {
            const roomId = roomSelect.value;
            const checkInDate = new Date(checkInDateInput.value);
            const checkOutDate = new Date(checkOutDateInput.value);

            if (!roomId || !checkInDateInput.value || !checkOutDateInput.value) {
                totalPriceDiv.classList.add('d-none');
                return;
            }

            // Calculate duration in milliseconds
            const timeDiff = checkOutDate - checkInDate;
            const diffHours = timeDiff / (1000 * 3600); // exact float hours

            if (diffHours <= 0) {
                 totalPriceDiv.classList.add('d-none');
                 return;
            }

            if (roomData[roomId]) {
                let totalPrice = 0;
                let breakdownHtml = "";
                let rateText = ""; // Keep this for now or remove if unused

                // Helper for currency formatting
                const formatMoney = (amount) => '$' + amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

                if (diffHours < 24) {
                    // Hourly Pricing Logic (Less than 24 hours)
                    const hoursCharged = Math.max(1, Math.ceil(diffHours));
                    const hourlyRate = roomData[roomId].hourly;
                    totalPrice = hoursCharged * hourlyRate;
                    
                    breakdownHtml = `
                        <div class="d-flex justify-content-between mb-2">
                            <span>Duration (${hoursCharged} hrs)</span>
                            <span>${hoursCharged} x ${formatMoney(hourlyRate)}/hr</span>
                        </div>
                        <div class="border-top pt-2 d-flex justify-content-between fw-bold">
                            <span>Total</span>
                            <span>${formatMoney(totalPrice)}</span>
                        </div>
                    `;
                    // fallback/simple text
                    rateText = ` (${hoursCharged} hrs @ ${formatMoney(hourlyRate)}/hr)`;
                } else {
                    // Nightly + Hourly Overage Logic
                    const nights = Math.floor(diffHours / 24);
                    const remainingHours = Math.ceil(diffHours % 24);
                    const nightlyRate = roomData[roomId].nightly;
                    const hourlyRate = roomData[roomId].hourly;
                    
                    if (remainingHours > 0) {
                        const nightPrice = nights * nightlyRate;
                        const hourlyPrice = remainingHours * hourlyRate;
                        
                        // Smart check: if hourly overage costs more than a full night
                        if (hourlyPrice >= nightlyRate) {
                             totalPrice = (nights + 1) * nightlyRate;
                             breakdownHtml = `
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Nights (${nights + 1})</span>
                                    <span>${nights + 1} x ${formatMoney(nightlyRate)}</span>
                                </div>
                                <div class="text-muted small mb-2 fst-italic">
                                    * Hourly overage capped at nightly rate.
                                </div>
                                <div class="border-top pt-2 d-flex justify-content-between fw-bold">
                                    <span>Total</span>
                                    <span>${formatMoney(totalPrice)}</span>
                                </div>
                             `;
                             rateText = ` (${nights + 1} nights @ ${formatMoney(nightlyRate)})`;
                        } else {
                             totalPrice = nightPrice + hourlyPrice;
                             breakdownHtml = `
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Nights (${nights})</span>
                                    <span>${nights} x ${formatMoney(nightlyRate)}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Extra Time (${remainingHours} hrs)</span>
                                    <span>${remainingHours} x ${formatMoney(hourlyRate)}</span>
                                </div>
                                <div class="border-top pt-2 d-flex justify-content-between fw-bold">
                                    <span>Total</span>
                                    <span>${formatMoney(totalPrice)}</span>
                                </div>
                             `;
                             rateText = ` (${nights} nights + ${remainingHours} hrs)`;
                        }
                    } else {
                        // Exact number of nights
                        const nightsToCharge = Math.max(1, nights);
                        totalPrice = nightsToCharge * nightlyRate;
                        breakdownHtml = `
                            <div class="d-flex justify-content-between mb-2">
                                <span>Nights (${nightsToCharge})</span>
                                <span>${nightsToCharge} x ${formatMoney(nightlyRate)}</span>
                            </div>
                            <div class="border-top pt-2 d-flex justify-content-between fw-bold">
                                <span>Total</span>
                                <span>${formatMoney(totalPrice)}</span>
                            </div>
                        `;
                        rateText = ` (${nightsToCharge} nights @ ${formatMoney(nightlyRate)})`;
                    }
                }

                // Update the DOM
                // We'll replace the simple text with our HTML breakdown
                const breakdownContainer = document.getElementById('price-breakdown');
                // If the container doesn't exist yet, we can construct it or just use the priceValueSpan parent
                if (breakdownContainer) {
                    breakdownContainer.innerHTML = breakdownHtml;
                    priceValueSpan.style.display = 'none'; // Hide the old single line
                } else {
                     // Create a container if I removed it or it's not there? 
                     // Actually let's just use the priceValueSpan to hold the breakdown if we want to be simple, 
                     // but the user wants structure.
                     // Better: Retarget the DOM element.
                     const priceContainer = document.getElementById('total-price');
                     // Clear previous simple text
                     priceContainer.innerHTML = `<h5 class="alert-heading mb-3"><i class="fas fa-receipt me-2"></i>Price Breakdown</h5>` + breakdownHtml;
                }

                // priceValueSpan.textContent = '$' + totalPrice.toFixed(2) + rateText; 
                totalPriceDiv.classList.remove('d-none');
            }
        }

        async function checkAvailability() {
            if (!roomSelect.value || !checkInDateInput.value || !checkOutDateInput.value) return;

            try {
                const response = await fetch('check_availability.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `room_id=${roomSelect.value}&check_in_date=${checkInDateInput.value}&check_out_date=${checkOutDateInput.value}`
                });

                const data = await response.json();

                messageDiv.classList.remove('d-none', 'alert-success', 'alert-danger');
                submitButton.disabled = !data.available;

                if (data.available) {
                    messageDiv.classList.add('alert-success');
                    messageDiv.textContent = 'Room is available for the selected dates!';
                } else {
                    messageDiv.classList.add('alert-danger');
                    messageDiv.textContent = 'Sorry, this room is already booked for some or all of the selected dates.';
                }
            } catch (error) {
                console.error('Error checking availability:', error);
            }
        }

        roomSelect.addEventListener('change', function() {
            // Reset date inputs
            checkInDateInput.value = '';
            checkOutDateInput.value = '';

            // Load booked dates for selected room
            loadBookedDates(this.value);

            // This will now be called after date selection
            totalPriceDiv.classList.add('d-none');
            messageDiv.classList.add('d-none');
        });

        // Initialize date pickers on page load
        initDatePickers();

        // Check availability initially if room is pre-selected
        if (roomSelect.value) {
            loadBookedDates(roomSelect.value);
        }
    });
</script>