<?php
include "config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$symbol = strtoupper($_GET["symbol"]) . ".NS";
$apiKey = "10NWX8N1H14BXDX8";

$url = "https://www.alphavantage.co/query?function=TIME_SERIES_DAILY&symbol=$symbol&apikey=$apiKey";

$response = file_get_contents($url);
$data = json_decode($response, true);

if (!isset($data["Time Series (Daily)"])) {
    die("Invalid stock symbol or API limit reached.");
}

$prices = [];
foreach ($data["Time Series (Daily)"] as $day) {
    $prices[] = $day["4. close"];
    if (count($prices) == 20) break;
}

$currentPrice = $prices[0];
$average = array_sum($prices) / count($prices);

/* Basic AI Logic */
if ($currentPrice > $average) {
    $prediction = "BULLISH";
    $confidence = 70;
} else {
    $prediction = "BEARISH";
    $confidence = 65;
}

/* Save to Cache */
$stmt = $conn->prepare("INSERT INTO stock_cache (symbol, current_price, prediction, confidence) VALUES (?, ?, ?, ?)");
$stmt->bind_param("sdsi", $symbol, $currentPrice, $prediction, $confidence);
$stmt->execute();

/* Save Search History */
$userId = $_SESSION["user_id"];
$stmt2 = $conn->prepare("INSERT INTO search_history (user_id, symbol) VALUES (?, ?)");
$stmt2->bind_param("is", $userId, $symbol);
$stmt2->execute();
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