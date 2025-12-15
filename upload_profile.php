<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Require login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if file is uploaded
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_image'])) {
    $user_id = $_SESSION['user_id'];

    // Use absolute path to ensure correct folder targeting on server
    $upload_dir = __DIR__ . '/profileimg/';

    // Ensure directory exists with correct permissions
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) {
            $_SESSION['error'] = 'Server Error: Failed to create upload directory.';
            header('Location: profile.php');
            exit;
        }
    }

    $file = $_FILES['profile_image'];

    // 1. Check for Upload Errors first
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE   => 'File too large (server limit).',
            UPLOAD_ERR_FORM_SIZE  => 'File too large (form limit).',
            UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded.',
            UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION  => 'File upload stopped by extension.'
        ];
        $msg = isset($error_messages[$file['error']]) ? $error_messages[$file['error']] : 'Unknown upload error.';
        $_SESSION['error'] = $msg;
        header('Location: profile.php');
        exit;
    }

    // 2. Validate File Type (MIME & Extension)
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];

    // Check extension
    if (!in_array($file_ext, $allowed_exts)) {
        $_SESSION['error'] = 'Invalid file type. Only JPG, JPEG, PNG, WEBP allowed.';
        header('Location: profile.php');
        exit;
    }

    // Check actual image content
    $image_info = getimagesize($file['tmp_name']);
    if ($image_info === false) {
        $_SESSION['error'] = 'File is not a valid image.';
        header('Location: profile.php');
        exit;
    }

    // 4. Process Image
    // Check if GD is available for resizing/cropping
    if (extension_loaded('gd')) {
        $source_image = null;
        $mime = $image_info['mime'];

        switch ($mime) {
            case 'image/jpeg':
                $source_image = @imagecreatefromjpeg($file['tmp_name']);
                break;
            case 'image/png':
                $source_image = @imagecreatefrompng($file['tmp_name']);
                break;
            case 'image/webp':
                $source_image = @imagecreatefromwebp($file['tmp_name']);
                break;
        }

        if ($source_image) {
            // Crop and Resize (Square 500x500)
            $width = imagesx($source_image);
            $height = imagesy($source_image);
            $size = min($width, $height);
            $x = ($width - $size) / 2;
            $y = ($height - $size) / 2;

            $square_image = imagecreatetruecolor(500, 500);

            // Handle transparency
            imagealphablending($square_image, false);
            imagesavealpha($square_image, true);

            // Crop center and resize
            imagecopyresampled($square_image, $source_image, 0, 0, $x, $y, 500, 500, $size, $size);

            // Save as JPG
            $destination = $upload_dir . $user_id . '.jpg';
            if (file_exists($destination)) {
                @unlink($destination);
            }

            if (imagejpeg($square_image, $destination, 90)) {
                $_SESSION['success'] = 'Profile updated successfully!';
            } else {
                $_SESSION['error'] = 'Failed to save processed image.';
            }

            imagedestroy($source_image);
            imagedestroy($square_image);
        } else {
            // GD failed to open image -> Fallback to raw copy
            fallback_copy($file['tmp_name'], $upload_dir . $user_id . '.jpg');
        }
    } else {
        // GD Missing -> Fallback to raw copy
        // Note: We rename to .jpg for consistency with frontend, even if it's png/webp.
        // Browsers generally handle "mismatched" headers fine (Sniffing).
        fallback_copy($file['tmp_name'], $upload_dir . $user_id . '.jpg');
    }
} else {
    $_SESSION['error'] = 'Invalid request.';
}

function fallback_copy($source, $dest)
{
    if (file_exists($dest)) {
        @unlink($dest);
    }
    if (move_uploaded_file($source, $dest) || copy($source, $dest)) {
        $_SESSION['success'] = 'Profile updated (Resize skipped: GD missing).';
    } else {
        $_SESSION['error'] = 'Failed to save image (Fallback mode).';
    }
}

header('Location: profile.php');
exit;
