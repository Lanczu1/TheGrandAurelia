<?php
session_start();
include('header.php');

// Redirect if no success message (prevents direct access)
if (!isset($_SESSION['success'])) {
    header('Location: dining.php');
    exit;
}

$success_message = $_SESSION['success'];
unset($_SESSION['success']); // Clear the message after displaying
?>

<style>
    .confirmation-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }
    
    .confirmation-icon {
        font-size: 5rem;
        color: #28a745;
        margin-bottom: 1.5rem;
        animation: scaleIn 0.5s ease-out;
    }
    
    @keyframes scaleIn {
        from {
            transform: scale(0);
            opacity: 0;
        }
        to {
            transform: scale(1);
            opacity: 1;
        }
    }
    
    .confirmation-header {
        background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
        padding: 2rem;
        text-align: center;
        color: #333;
    }
    
    .confirmation-body {
        padding: 3rem;
    }
    
    .btn-confirmation {
        background-color: #1a1a1a;
        border: none;
        color: white;
        padding: 12px 30px;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s;
    }
    
    .btn-confirmation:hover {
        background-color: #333;
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        color: white;
    }
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card confirmation-card">
                <div class="confirmation-header">
                    <div class="confirmation-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h2 class="mb-0">Reservation Request Submitted!</h2>
                </div>
                
                <div class="confirmation-body text-center">
                    <div class="alert alert-success mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                    
                    <p class="lead mb-4">Thank you for choosing The Grand Aurelia for your dining experience. Our team will review your reservation request and confirm it shortly.</p>
                    
                    <div class="mb-4">
                        <h5><i class="fas fa-envelope me-2 text-primary"></i>What's Next?</h5>
                        <p class="text-muted">You will receive a confirmation email at the address you provided. Our team will contact you if any adjustments are needed.</p>
                    </div>
                    
                    <div class="d-grid gap-3 d-sm-flex justify-content-sm-center">
                        <a href="dining.php" class="btn btn-confirmation">
                            <i class="fas fa-utensils me-2"></i>Back to Dining
                        </a>
                        <a href="index.php" class="btn btn-outline-dark">
                            <i class="fas fa-home me-2"></i>Return Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>

