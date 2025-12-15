<?php
session_start();
include('db.php');

header('Content-Type: application/json');

// Initialize conversation if not set
if (!isset($_SESSION['chat_state'])) {
    $_SESSION['chat_state'] = 'START';
    $_SESSION['chat_data'] = [];
}

// Get user input
$data = json_decode(file_get_contents('php://input'), true);
$input = isset($data['message']) ? trim($data['message']) : '';
$action = isset($data['action']) ? $data['action'] : '';

// Helper to send response
function send_response($message, $options = []) {
    echo json_encode(['message' => $message, 'options' => $options]);
    exit;
}

// Handle reset
if ($action === 'reset') {
    $_SESSION['chat_state'] = 'AWAIT_CHOICE';
    $_SESSION['chat_data'] = [];
    send_response("Welcome to Grand Aurelia Concierge! How can I assist you today?", ['Book a Room', 'Room Prices', 'Hotel Info', 'View Policies']);
}

// State Machine
switch ($_SESSION['chat_state']) {
    case 'START':
        $_SESSION['chat_state'] = 'AWAIT_CHOICE';
        send_response("Welcome to Grand Aurelia Concierge! How can I assist you today?", ['Book a Room', 'Room Prices', 'Hotel Info', 'View Policies']);
        break;

    case 'AWAIT_CHOICE':
        $choice = strtolower($input);
        
        // BOOKING
        if (strpos($choice, 'book') !== false || strpos($choice, 'reserv') !== false) {
            $_SESSION['chat_state'] = 'AWAIT_CHECKIN_DATE';
            send_response("Excellent! Let's get you booked. Please select your check-in date (YYYY-MM-DD), or type 'Cancel' to return to the main menu.");
        
        // POLICIES
        } elseif (strpos($choice, 'polic') !== false || strpos($choice, 'term') !== false) {
             send_response("Here are our key policies:\n<br>• <strong>Check-in:</strong> 2:00 PM\n<br>• <strong>Check-out:</strong> 12:00 PM\n<br>• <strong>Cancellation:</strong> Free cancellation up to 24 hours before standard check-in time.\n<br>• <strong>Payment:</strong> We accept all major credit cards.\n\nWould you like to book a room now?", ['Book a Room', 'Back to Menu']);
        
        // HELP/GUIDES
        } elseif (strpos($choice, 'help') !== false || strpos($choice, 'guide') !== false) {
             send_response("How to use our website:\n<br>• <strong>Browse Rooms:</strong> Visit the 'Rooms & Suites' page to see detailed amenities and photos.\n<br>• <strong>My Bookings:</strong> Log in to view your past and upcoming reservations.\n<br>• <strong>Profile:</strong> Update your details in the account section.\n\nNeed more help? Call the front desk.", ['Book a Room', 'Back to Menu']);
        
        // HOTEL INFO
        } elseif (strpos($choice, 'about') !== false || strpos($choice, 'info') !== false || strpos($choice, 'hotel') !== false || strpos($choice, 'desc') !== false) {
             send_response("<strong>Grand Aurelia</strong> represents the pinnacle of luxury living. <br><br>Located in the heart of the city with breathtaking ocean and skyline views, we offer world-class amenities including:\n<br>• High-speed fiber internet\n<br>• 24/7 Concierge & Room Service\n<br>• Premium Spa & Wellness Center\n<br>• Exclusive Rooftop Lounge", ['Book a Room', 'Room Prices', 'Back to Menu']);
        
        // PRICES
        } elseif (strpos($choice, 'price') !== false || strpos($choice, 'cost') !== false || strpos($choice, 'rate') !== false || strpos($choice, 'budget') !== false) {
             $p_res = mysqli_query($conn, "SELECT MIN(price) as min_p, MAX(price) as max_p FROM rooms");
             $p_row = mysqli_fetch_assoc($p_res);
             $min = $p_row['min_p'];
             $max = $p_row['max_p'];
             send_response("Our rooms cater to various needs:\n<br>• <strong>Standard Rooms:</strong> Starting at $$min/night\n<br>• <strong>Luxury Suites:</strong> Up to $$max/night\n<br><br>We also offer hourly rates for flexible stays. Would you like to check availability?", ['Book a Room', 'View Rooms', 'Back to Menu']);

        // MENU / BACK
        } elseif (strpos($choice, 'menu') !== false || strpos($choice, 'back') !== false) {
             send_response("Main Menu: How can I help you?", ['Book a Room', 'Room Prices', 'Hotel Info', 'View Policies']);
        
        } else {
             send_response("I can help you with bookings, pricing, hotel info, or policies. Please select an option.", ['Book a Room', 'Room Prices', 'Hotel Info', 'View Policies']);
        }
        break;

    case 'AWAIT_CHECKIN_DATE':
        if (in_array(strtolower($input), ['cancel', 'return', 'back', 'main menu'])) {
            $_SESSION['chat_state'] = 'AWAIT_CHOICE';
            $_SESSION['chat_data'] = [];
            send_response("Booking cancelled. How can I help you?", ['Book a Room', 'Room Prices', 'Hotel Info', 'View Policies']);
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $input)) {
             if (strtotime($input) < strtotime(date('Y-m-d'))) {
                 send_response("Please select a date from today onwards.", ['Cancel']);
             } else {
                 $_SESSION['chat_data']['check_in_date'] = $input;
                 $_SESSION['chat_state'] = 'AWAIT_CHECKIN_TIME';
                 // Offer broad range of times
                 $times = ['09:00 AM', '11:00 AM', '01:00 PM', '02:00 PM', '03:00 PM', '06:00 PM', '08:00 PM', 'Cancel'];
                 send_response("Got it. What time will you be checking in? (Select below or type any time like 10:30 AM)", $times);
             }
        } else {
            send_response("Invalid date format. Please use YYYY-MM-DD (e.g., 2025-12-25) or type 'Cancel'.", ['Cancel']);
        }
        break;

    case 'AWAIT_CHECKIN_TIME':
        if (in_array(strtolower($input), ['cancel', 'return', 'back', 'main menu'])) {
            $_SESSION['chat_state'] = 'AWAIT_CHOICE';
            $_SESSION['chat_data'] = [];
            send_response("Booking cancelled. How can I help you?", ['Book a Room', 'Room Prices', 'Hotel Info', 'View Policies']);
        }
        // ... (rest of case)

    case 'AWAIT_CHECKOUT_DATE':
        if (in_array(strtolower($input), ['cancel', 'return', 'back', 'main menu'])) {
            $_SESSION['chat_state'] = 'AWAIT_CHOICE';
            $_SESSION['chat_data'] = [];
            send_response("Booking cancelled. How can I help you?", ['Book a Room', 'Room Prices', 'Hotel Info', 'View Policies']);
        }
        // ... (rest of case)

    case 'AWAIT_CHECKOUT_TIME':
        if (in_array(strtolower($input), ['cancel', 'return', 'back', 'main menu'])) {
            $_SESSION['chat_state'] = 'AWAIT_CHOICE';
            $_SESSION['chat_data'] = [];
            send_response("Booking cancelled. How can I help you?", ['Book a Room', 'Room Prices', 'Hotel Info', 'View Policies']);
        }
        // ... (rest of case)

    case 'AWAIT_GUESTS':
        if (in_array(strtolower($input), ['cancel', 'return', 'back', 'main menu'])) {
            $_SESSION['chat_state'] = 'AWAIT_CHOICE';
            $_SESSION['chat_data'] = [];
            send_response("Booking cancelled. How can I help you?", ['Book a Room', 'Room Prices', 'Hotel Info', 'View Policies']);
        }
        // ... (rest of case)

    case 'AWAIT_ROOM':
        if (in_array(strtolower($input), ['cancel', 'return', 'back', 'main menu'])) {
            $_SESSION['chat_state'] = 'AWAIT_CHOICE';
            $_SESSION['chat_data'] = [];
            send_response("Booking cancelled. How can I help you?", ['Book a Room', 'Room Prices', 'Hotel Info', 'View Policies']);
        }
        $room_id = null;
        $room_name = null;
        $price = 0;
        
        // Search room
        $result = mysqli_query($conn, "SELECT * FROM rooms");
        while($row = mysqli_fetch_assoc($result)) {
            if ($input == $row['id'] || stripos($row['room_name'], $input) !== false || stripos($input, $row['room_name']) !== false) {
                $room_id = $row['id'];
                $room_name = $row['room_name'];
                $price = $row['price'];
                $hourly_price = isset($row['price_per_hour']) ? $row['price_per_hour'] : ceil($price * 0.15);
                break;
            }
        }
        
        if ($room_id) {
            $_SESSION['chat_data']['room_id'] = $room_id;
            
            $check_in = $_SESSION['chat_data']['check_in'];
            $check_out = $_SESSION['chat_data']['check_out'];
            
            // Availability Check (20 min buffer)
            $query = "SELECT id FROM bookings 
                      WHERE room_id = $room_id 
                      AND status IN ('approved', 'pending')
                      AND (
                          ('$check_in' < DATE_ADD(check_out_date, INTERVAL 20 MINUTE) AND 
                           DATE_ADD('$check_out', INTERVAL 20 MINUTE) > check_in_date)
                      )";
            
            $avail = mysqli_query($conn, $query);
            if (mysqli_num_rows($avail) > 0) {
                 send_response("Sorry, $room_name is already booked for these dates/times. Please choose another room.");
            } else {
                // Calculate Price Logic (Hybrid)
                $diff_seconds = strtotime($check_out) - strtotime($check_in);
                $diff_hours = $diff_seconds / 3600;
                
                $total_price = 0;
                if ($diff_hours < 24) {
                     $hours = max(1, ceil($diff_hours));
                     $total_price = $hours * $hourly_price;
                } else {
                     $nights = floor($diff_hours / 24);
                     $remaining_hours = ceil(fmod($diff_hours, 24));
                     
                     if ($remaining_hours > 0) {
                         $night_price = $nights * $price;
                         $overage = $remaining_hours * $hourly_price;
                         if ($overage >= $price) {
                             $total_price = ($nights + 1) * $price;
                         } else {
                             $total_price = $night_price + $overage;
                         }
                     } else {
                         $total_price = max(1, $nights) * $price;
                     }
                }
                
                 $_SESSION['chat_data']['total_price'] = $total_price;
                 
                 $_SESSION['chat_state'] = 'AWAIT_CONFIRM';
                 send_response("Room is available! Total price for approx " . round($diff_hours, 1) . " hours is $$total_price. Do you want to confirm this booking? (Yes/No)", ['Yes', 'No']);
            }
            
        } else {
             send_response("Room not found. Please select from the list.");
        }
        break;

    case 'AWAIT_CONFIRM':
        if (strtolower($input) === 'yes') {
            if (!isset($_SESSION['user_id'])) {
                 send_response("You need to be logged in to confirm. Please log in.");
            } else {
                $user_id = $_SESSION['user_id'];
                $room_id = $_SESSION['chat_data']['room_id'];
                // Get customer name from users table
                $u_res = mysqli_query($conn, "SELECT username FROM users WHERE id = $user_id");
                $u_row = mysqli_fetch_assoc($u_res);
                $customer_name = $u_row['username'];

                $check_in = $_SESSION['chat_data']['check_in'];
                $check_out = $_SESSION['chat_data']['check_out'];
                $total = $_SESSION['chat_data']['total_price'];
                
                $sql = "INSERT INTO bookings (user_id, room_id, customer_name, check_in_date, check_out_date, total_price, status, created_at) 
                        VALUES ($user_id, $room_id, '$customer_name', '$check_in', '$check_out', $total, 'pending', NOW())";
                
                if (mysqli_query($conn, $sql)) {
                    $_SESSION['chat_state'] = 'START';
                    $_SESSION['chat_data'] = [];
                    send_response("Booking Confirmed! Your Reference ID is " . mysqli_insert_id($conn) . ". Thank you for choosing Grand Aurelia!");
                } else {
                    send_response("Error saving booking. Please try again.");
                }
            }
        } elseif (strtolower($input) === 'no') {
            $_SESSION['chat_state'] = 'START';
            $_SESSION['chat_data'] = [];
            send_response("Booking cancelled. How else can I help?");
        } else {
            send_response("Please answer Yes or No.", ['Yes', 'No']);
        }
        break;
}
?>
