<?php
$api_key = "X9Z985T648Z0Z750";

function getIndexData($symbol, $api_key) {
    $url = "https://www.alphavantage.co/query?function=GLOBAL_QUOTE&symbol=$symbol&apikey=$api_key";
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    if(isset($data["Global Quote"])) {
        return $data["Global Quote"];
    }
    return null;
}

$nifty = getIndexData("^NSEI", $api_key);
$sensex = getIndexData("^BSESN", $api_key);

include "config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Dashboard - StoXVision</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div style="background:#111;padding:15px;border-radius:10px;margin-bottom:20px;">
    <h3 style="color:#00ffcc;">Market Overview</h3>

    <?php if($nifty): ?>
        <p>NIFTY: <?php echo $nifty["05. price"]; ?></p>
    <?php endif; ?>

    <?php if($sensex): ?>
        <p>SENSEX: <?php echo $sensex["05. price"]; ?></p>
    <?php endif; ?>
</div>

<div class="dashboard-container">
    <h1>StoXVision Dashboard</h1>
    <a href="logout.php" class="logout-btn">Logout</a>

    <div class="search-box">
        <form method="POST" action="analyze.php">
    <input type="text" name="symbol" placeholder="Enter Stock Symbol (e.g. TCS.NSE)" required>
    <button type="submit">Analyze</button>
</form>
    </div>
</div>

</body>
</html>