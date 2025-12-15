<?php
header('Content-Type: application/json');
session_start();
include 'db.php';

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$room_id = isset($_REQUEST['room_id']) ? (int)$_REQUEST['room_id'] : 0;
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

if (!$room_id) {
    echo json_encode(['error' => 'Invalid Room ID']);
    exit;
}

if ($action === 'get_interactions') {
    // Get Likes Count
    $likeQ = mysqli_query($conn, "SELECT COUNT(*) as count FROM room_likes WHERE room_id = $room_id");
    $likes = mysqli_fetch_assoc($likeQ)['count'];

    // Check if current user liked
    $userLiked = false;
    if ($user_id) {
        $checkLike = mysqli_query($conn, "SELECT id FROM room_likes WHERE room_id = $room_id AND user_id = $user_id");
        if (mysqli_num_rows($checkLike) > 0) $userLiked = true;
    }

    // Get Reviews
    $reviews = [];
    $revQ = mysqli_query($conn, "
        SELECT rr.*, u.username 
        FROM room_reviews rr 
        JOIN users u ON rr.user_id = u.id 
        WHERE rr.room_id = $room_id 
        ORDER BY rr.created_at DESC
    ");

    while ($row = mysqli_fetch_assoc($revQ)) {
        // Format date
        $row['date_formatted'] = date('M j, Y', strtotime($row['created_at']));
        $reviews[] = $row;
    }

    echo json_encode([
        'likes' => $likes,
        'user_liked' => $userLiked,
        'reviews' => $reviews,
        'user_logged_in' => ($user_id > 0)
    ]);
    exit;
}

if ($action === 'toggle_like') {
    if (!$user_id) {
        http_response_code(401);
        echo json_encode(['error' => 'Login required']);
        exit;
    }

    $check = mysqli_query($conn, "SELECT id FROM room_likes WHERE room_id = $room_id AND user_id = $user_id");
    if (mysqli_num_rows($check) > 0) {
        // Unlike
        mysqli_query($conn, "DELETE FROM room_likes WHERE room_id = $room_id AND user_id = $user_id");
        $liked = false;
    } else {
        // Like
        mysqli_query($conn, "INSERT INTO room_likes (room_id, user_id) VALUES ($room_id, $user_id)");
        $liked = true;
    }

    $countQ = mysqli_query($conn, "SELECT COUNT(*) as count FROM room_likes WHERE room_id = $room_id");
    $newCount = mysqli_fetch_assoc($countQ)['count'];

    echo json_encode(['success' => true, 'liked' => $liked, 'new_count' => $newCount]);
    exit;
}

if ($action === 'post_review') {
    if (!$user_id) {
        http_response_code(401);
        echo json_encode(['error' => 'Login required']);
        exit;
    }

    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 5;
    $comment = isset($_POST['comment']) ? mysqli_real_escape_string($conn, trim($_POST['comment'])) : '';

    if (empty($comment)) {
        echo json_encode(['error' => 'Comment cannot be empty']);
        exit;
    }

    $sql = "INSERT INTO room_reviews (room_id, user_id, rating, comment) VALUES ($room_id, $user_id, $rating, '$comment')";
    if (mysqli_query($conn, $sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Database error']);
    }
    exit;
}

echo json_encode(['error' => 'Invalid Action']);
