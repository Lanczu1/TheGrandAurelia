<?php
include('db.php');

// Check if a room ID is passed
if (isset($_GET['id'])) {
    $room_id = $_GET['id'];

    // Fetch the current room details
    $result = mysqli_query($conn, "SELECT * FROM rooms WHERE id = $room_id");
    $room = mysqli_fetch_assoc($result);
} else {
    echo "Invalid room ID.";
    exit;
}

// Handle room update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_room'])) {
    $room_name = $_POST['room_name'];
    $price = $_POST['price'];

    $update_sql = "UPDATE rooms SET room_name = '$room_name', price = '$price' WHERE id = $room_id";

    if (mysqli_query($conn, $update_sql)) {
        echo "<div class='alert alert-success'>Room updated successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error updating room: " . mysqli_error($conn) . "</div>";
    }
}
?>

<?php include('header.php'); ?>

<div class="container">
    <h2>Edit Room</h2>

    <form action="edit_room.php?id=<?php echo $room_id; ?>" method="POST">
        <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">

        <!-- Room Name -->
        <div class="form-group">
            <label for="room_name">Room Name:</label>
            <input type="text" name="room_name" id="room_name" class="form-control" value="<?php echo $room['room_name']; ?>" required>
        </div>

        <!-- Price -->
        <div class="form-group">
            <label for="price">Price per Night:</label>
            <input type="number" name="price" id="price" class="form-control" value="<?php echo $room['price']; ?>" required>
        </div>

        <!-- Update Button -->
        <button type="submit" name="update_room" class="btn btn-primary">Update Room</button>
    </form>
</div>

<?php include('footer.php'); ?>
