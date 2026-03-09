<?php
include "../config/db.php";
include "../includes/auth_check.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

if (!isset($_FILES['profile_pic'])) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$file = $_FILES['profile_pic'];
$allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
$max_size = 2 * 1024 * 1024; // 2MB

if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and WebP allowed.']);
    exit;
}

if ($file['size'] > $max_size) {
    echo json_encode(['success' => false, 'message' => 'File too large. Max size is 2MB.']);
    exit;
}

$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = "profile_" . $user_id . "_" . time() . "." . $ext;
$target_dir = "../uploads/profiles/";
$target_path = $target_dir . $filename;
$db_path = "uploads/profiles/" . $filename;

if (move_uploaded_file($file['tmp_name'], $target_path)) {
    // Update Database
    $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
    $stmt->bind_param("si", $db_path, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['profile_pic'] = $db_path;
        echo json_encode(['success' => true, 'path' => $db_path]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database update failed.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save file.']);
}
?>
