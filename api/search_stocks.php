<?php
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Require login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once __DIR__ . '/../src/bootstrap.php';
use StoXVision\Data\Stocks;

$query = trim($_GET['q'] ?? '');
// Clean query: alphanumeric and spaces only
$query = preg_replace("/[^a-zA-Z0-9\s]/", "", $query);

if (strlen($query) < 1) {
    echo json_encode([]);
    exit();
}

$results = Stocks::search($query, 8);

echo json_encode($results);
