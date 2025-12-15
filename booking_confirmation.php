<?php
session_start();
include('header.php');

// Redirect if no success message (prevents direct access)
if (!isset($_SESSION['success'])) {
    header('Location: view_rooms.php');
    exit;
}

$success_message = $_SESSION['success'];
unset($_SESSION['success']); // Clear the message after displaying
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                    </div>
                    
                    <h2 class="card-title mb-4">Booking Confirmed!</h2>
                    
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                    
                    <p class="mb-4">Thank you for choosing our hotel. We look forward to welcoming you!</p>
                    
                    <div class="d-grid gap-3 d-sm-flex justify-content-sm-center">
                        <a href="view_bookings.php" class="btn btn-primary">
                            <i class="fas fa-list me-2"></i>View Your Bookings
                        </a>
                        <a href="view_rooms.php" class="btn btn-outline-primary">
                            <i class="fas fa-hotel me-2"></i>Browse More Rooms
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?> 