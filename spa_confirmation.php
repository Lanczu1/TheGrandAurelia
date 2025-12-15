<?php
session_start();
include('header.php');
?>

<div class="container py-5">
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; ?></div>
    <?php elseif (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error']; ?></div>
    <?php else: ?>
        <div class="alert alert-info">No booking found.</div>
    <?php endif; ?>
    <a href="spa.php" class="btn btn-primary mt-3">Back to Spa</a>
</div>

<?php
unset($_SESSION['success'], $_SESSION['error']);
include('footer.php');
?>
