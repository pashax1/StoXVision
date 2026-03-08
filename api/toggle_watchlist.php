<?php
include "../config/db.php";

if (!isset($_SESSION["user_id"]) || !isset($_POST["symbol"])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

$user_id = $_SESSION["user_id"];
$symbol = strtoupper(trim($_POST["symbol"]));

// Validate symbol format (alphanumeric, dots, hyphens)
if (!preg_match("/^[A-Z0-9\.\-]+$/", $symbol)) {
    echo json_encode(["status" => "error", "message" => "Invalid symbol format"]);
    exit();
}

// Check if already in watchlist
$stmt = $conn->prepare("SELECT id FROM watchlist WHERE user_id = ? AND symbol = ?");
if (!$stmt) {
    echo json_encode(["status" => "error", "message" => "Database error"]);
    exit();
}
$stmt->bind_param("is", $user_id, $symbol);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Remove if exists
    $stmt = $conn->prepare("DELETE FROM watchlist WHERE user_id = ? AND symbol = ?");
    if ($stmt) {
        $stmt->bind_param("is", $user_id, $symbol);
        if($stmt->execute()) {
            echo json_encode(["status" => "success", "action" => "removed"]);
        }
    }
} else {
    // Add if does not exist
    $stmt = $conn->prepare("INSERT INTO watchlist (user_id, symbol) VALUES (?, ?)");
    if ($stmt) {
        $stmt->bind_param("is", $user_id, $symbol);
        if($stmt->execute()) {
            echo json_encode(["status" => "success", "action" => "added"]);
        }
    }
}
?>
