<?php
ob_start();
session_start();

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to edit a reservation.";
    header('Location: login.php');
    exit;
}

include('db.php');
include('header.php');

$user_id = (int)$_SESSION['user_id'];

if (!isset($_GET['id']) || !isset($_GET['type'])) {
    $_SESSION['error'] = 'Invalid request.';
    header('Location: view_bookings.php');
    exit;
}

$id = (int)$_GET['id'];
$type = $_GET['type'];

$editable = false;
$record = null;

if ($type === 'dining') {
    $res = mysqli_query($conn, "SELECT * FROM dining_reservations WHERE id = $id AND user_id = $user_id");
    if ($res && mysqli_num_rows($res) > 0) {
        $record = mysqli_fetch_assoc($res);
        $editable = true;
    }
} elseif ($type === 'spa') {
    $res = mysqli_query($conn, "SELECT * FROM spa_bookings WHERE id = $id AND user_id = $user_id");
    if ($res && mysqli_num_rows($res) > 0) {
        $record = mysqli_fetch_assoc($res);
        $editable = true;
    }
} else {
    $_SESSION['error'] = 'Unsupported reservation type for editing.';
    header('Location: view_bookings.php');
    exit;
}

if (!$editable || !$record) {
    $_SESSION['error'] = 'Reservation not found or permission denied.';
    header('Location: view_bookings.php');
    exit;
}

// Only allow editing when status is pending
if (strtolower($record['status']) !== 'pending') {
    $_SESSION['error'] = 'Only pending reservations can be edited.';
    header('Location: view_bookings.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($type === 'dining') {
        $customer_name = mysqli_real_escape_string($conn, trim($_POST['customer_name']));
        $reservation_date = mysqli_real_escape_string($conn, $_POST['reservation_date']);
        $reservation_time = mysqli_real_escape_string($conn, $_POST['reservation_time']);
        $number_of_guests = (int)$_POST['number_of_guests'];
        $venue = mysqli_real_escape_string($conn, trim($_POST['venue']));
        $special_requests = mysqli_real_escape_string($conn, trim($_POST['special_requests']));

        // Basic validation
        $errors = [];
        if (empty($customer_name)) $errors[] = 'Name is required.';
        if (empty($reservation_date)) $errors[] = 'Date is required.';
        if (empty($reservation_time)) $errors[] = 'Time is required.';
        if ($number_of_guests < 1 || $number_of_guests > 50) $errors[] = 'Guests must be between 1 and 50.';

        if (!empty($reservation_date) && $reservation_date < date('Y-m-d')) $errors[] = 'Date cannot be in the past.';

        if (count($errors) > 0) {
            $_SESSION['error'] = implode(' ', $errors);
            header("Location: edit_reservation.php?id=$id&type=dining");
            exit;
        }

        $sql = "UPDATE dining_reservations SET customer_name = '$customer_name', reservation_date = '$reservation_date', reservation_time = '$reservation_time', number_of_guests = $number_of_guests, venue = '$venue', special_requests = " . (!empty($special_requests) ? "'" . $special_requests . "'" : "NULL") . ", status = 'pending' WHERE id = $id AND user_id = $user_id";

        if (!mysqli_query($conn, $sql)) {
            $_SESSION['error'] = 'Failed to update reservation: ' . mysqli_error($conn);
            header("Location: edit_reservation.php?id=$id&type=dining");
            exit;
        }

        $_SESSION['success'] = 'Your dining reservation has been updated and will be reviewed.';
        header('Location: view_bookings.php');
        exit;

    } elseif ($type === 'spa') {
        $customer_name = mysqli_real_escape_string($conn, trim($_POST['customer_name']));
        $email = mysqli_real_escape_string($conn, trim($_POST['email']));
        $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
        $treatment = mysqli_real_escape_string($conn, trim($_POST['treatment']));
        $spa_date = mysqli_real_escape_string($conn, $_POST['spa_date']);
        $spa_time = mysqli_real_escape_string($conn, $_POST['spa_time']);
        $guests = (int)$_POST['guests'];
        $special_requests = mysqli_real_escape_string($conn, trim($_POST['special_requests']));

        $errors = [];
        if (empty($customer_name)) $errors[] = 'Name is required.';
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
        if (empty($treatment)) $errors[] = 'Please select a treatment.';
        if (empty($spa_date)) $errors[] = 'Date is required.';
        if (empty($spa_time)) $errors[] = 'Time is required.';
        if ($guests < 1 || $guests > 10) $errors[] = 'Guests must be between 1 and 10.';
        if (!empty($spa_date) && $spa_date < date('Y-m-d')) $errors[] = 'Date cannot be in the past.';

        if (count($errors) > 0) {
            $_SESSION['error'] = implode(' ', $errors);
            header("Location: edit_reservation.php?id=$id&type=spa");
            exit;
        }

        $sql = "UPDATE spa_bookings SET customer_name = '$customer_name', email = '$email', phone = " . (!empty($phone) ? "'" . $phone . "'" : "NULL") . ", treatment = '$treatment', spa_date = '$spa_date', spa_time = '$spa_time', guests = $guests, special_requests = " . (!empty($special_requests) ? "'" . $special_requests . "'" : "NULL") . ", status = 'pending' WHERE id = $id AND user_id = $user_id";

        if (!mysqli_query($conn, $sql)) {
            $_SESSION['error'] = 'Failed to update booking: ' . mysqli_error($conn);
            header("Location: edit_reservation.php?id=$id&type=spa");
            exit;
        }

        $_SESSION['success'] = 'Your spa booking has been updated and will be reviewed.';
        header('Location: view_bookings.php');
        exit;
    }
}

// Render form
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body">
                    <h3 class="mb-4">Edit Reservation</h3>
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
                    <?php endif; ?>
                    <?php if ($type === 'dining'): ?>
                        <form method="POST" action="edit_reservation.php?id=<?php echo $id; ?>&type=dining">
                            <div class="mb-3">
                                <label class="form-label">Your Name</label>
                                <input type="text" name="customer_name" class="form-control" value="<?php echo htmlspecialchars($record['customer_name']); ?>" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date</label>
                                    <input type="date" name="reservation_date" class="form-control" value="<?php echo htmlspecialchars($record['reservation_date']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Time</label>
                                    <input type="time" name="reservation_time" class="form-control" value="<?php echo htmlspecialchars($record['reservation_time']); ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Guests</label>
                                <input type="number" name="number_of_guests" class="form-control" min="1" max="50" value="<?php echo htmlspecialchars($record['number_of_guests']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Venue</label>
                                <input type="text" name="venue" class="form-control" value="<?php echo htmlspecialchars($record['venue']); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Special Requests</label>
                                <textarea name="special_requests" class="form-control"><?php echo htmlspecialchars($record['special_requests']); ?></textarea>
                            </div>
                            <div class="d-flex justify-content-between">
                                <a href="view_bookings.php" class="btn btn-secondary">Back</a>
                                <button class="btn btn-primary" type="submit">Update Reservation</button>
                            </div>
                        </form>
                    <?php else: /* spa */ ?>
                        <form method="POST" action="edit_reservation.php?id=<?php echo $id; ?>&type=spa">
                            <div class="mb-3">
                                <label class="form-label">Your Name</label>
                                <input type="text" name="customer_name" class="form-control" value="<?php echo htmlspecialchars($record['customer_name']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($record['email']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($record['phone']); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Treatment</label>
                                <input type="text" name="treatment" class="form-control" value="<?php echo htmlspecialchars($record['treatment']); ?>" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date</label>
                                    <input type="date" name="spa_date" class="form-control" value="<?php echo htmlspecialchars($record['spa_date']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Time</label>
                                    <input type="time" name="spa_time" class="form-control" value="<?php echo htmlspecialchars($record['spa_time']); ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Guests</label>
                                <input type="number" name="guests" class="form-control" min="1" max="10" value="<?php echo htmlspecialchars($record['guests']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Special Requests</label>
                                <textarea name="special_requests" class="form-control"><?php echo htmlspecialchars($record['special_requests']); ?></textarea>
                            </div>
                            <div class="d-flex justify-content-between">
                                <a href="view_bookings.php" class="btn btn-secondary">Back</a>
                                <button class="btn btn-primary" type="submit">Update Booking</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>
