include "config/db.php";
include "includes/auth_check.php";
require_once "src/bootstrap.php";

use StoXVision\Services\MarketDataService;
use StoXVision\Data\Stocks;

global $config, $conn;

$symbol_raw = strtoupper($_GET["symbol"] ?? '');
if (empty($symbol_raw)) {
    header("Location: dashboard.php");
    exit();
}

// Smart resolution
$symbol = Stocks::resolve($symbol_raw);
$apiKey = $config['api_key'];

$marketDataService = new MarketDataService($apiKey);

try {
    $data = $marketDataService->getDailyTimeSeries($symbol);
    $indicators = $marketDataService->calculateIndicators($data["Time Series (Daily)"]);
    
    $currentPrice = $indicators['latest_price'];
    $prediction = ($indicators['price_change_pct'] > 0) ? "BULLISH" : "BEARISH";
    $confidence = 70; // Simplified for this legacy view

    // Save Search History
    $userId = $_SESSION["user_id"];
    $stmt2 = $conn->prepare("INSERT INTO search_history (user_id, symbol) VALUES (?, ?)");
    if ($stmt2) {
        $stmt2->bind_param("is", $userId, $symbol);
        $stmt2->execute();
        $stmt2->close();
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
<title><?php echo $symbol; ?> - StoXVision</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="dashboard-container">
    <h2><?php echo $symbol; ?></h2>
    <p>Current Price: ₹ <?php echo $currentPrice; ?></p>
    <h3>AI Prediction: <?php echo $prediction; ?></h3>
    <p>Confidence: <?php echo $confidence; ?>%</p>

    <br>
    <a href="dashboard.php">Back</a>
</div>

</body>
</html>