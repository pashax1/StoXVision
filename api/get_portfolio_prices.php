<?php
header('Content-Type: application/json');
include '../config/db.php';

// Auth check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// CSRF check (#8)
$incoming_csrf = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
if ($incoming_csrf !== ($_SESSION['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit();
}

$user_id = (int)$_SESSION['user_id'];

// Fetch all watchlist symbols for this user with cached prices
$prices = [];
try {
    $stmt = $conn->prepare("
        SELECT w.symbol, sc.current_price, sc.cached_at
        FROM watchlist w
        LEFT JOIN stock_cache sc ON sc.symbol = w.symbol
        WHERE w.user_id = ?
    ");
    if ($stmt) {
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $prices[$row['symbol']] = [
                'price'     => $row['current_price'] ? number_format((float)$row['current_price'], 2) : null,
                'cached_at' => $row['cached_at'],
            ];
        }
        $stmt->close();
    }
} catch (Exception $e) {
    // Silently return empty on error
}

echo json_encode($prices);
