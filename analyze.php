<?php
$api_key = "X9Z985T648Z0Z750";

if(isset($_POST['symbol'])) {

    $symbol = strtoupper(trim($_POST['symbol']));

    $api_url = "https://www.alphavantage.co/query?function=TIME_SERIES_DAILY&symbol=$symbol&apikey=$api_key";

    $response = file_get_contents($api_url);
    $data = json_decode($response, true);

    if(isset($data["Time Series (Daily)"])) {

        $time_series = $data["Time Series (Daily)"];
        $dates = [];
        $closes = [];

        $count = 0;
        foreach($time_series as $date => $values) {
            if($count == 7) break; // get only last 7 days
            $dates[] = $date;
            $closes[] = $values["4. close"];
            $count++;
}

            $dates = array_reverse($dates);
            $closes = array_reverse($closes);

            // Calculate 5-day Moving Average
$ma = array_sum(array_slice($closes, -5)) / 5;

            $recommendation = "HOLD";

if(count($closes) >= 3) {
    $last = $closes[count($closes)-1];
    $prev1 = $closes[count($closes)-2];
    $prev2 = $closes[count($closes)-3];

    if($last > $prev1 && $prev1 > $prev2) {
        $recommendation = "BUY 📈";
    } elseif($last < $prev1 && $prev1 < $prev2) {
        $recommendation = "SELL 📉";
    }
}
        $latest_date = array_key_first($time_series);
        $latest_data = $time_series[$latest_date];

        $open = $latest_data["1. open"];
        $high = $latest_data["2. high"];
        $low = $latest_data["3. low"];
        $close = $latest_data["4. close"];
        $volume = $latest_data["5. volume"];

        // Simple RSI Calculation
$gains = 0;
$losses = 0;

for($i=1; $i<count($closes); $i++){
    $change = $closes[$i] - $closes[$i-1];
    if($change > 0){
        $gains += $change;
    } else {
        $losses += abs($change);
    }
}

$avg_gain = $gains / count($closes);
$avg_loss = $losses / count($closes);

if($avg_loss == 0){
    $rsi = 100;
} else {
    $rs = $avg_gain / $avg_loss;
    $rsi = 100 - (100 / (1 + $rs));
}

    } else {
        die("Invalid stock symbol or API limit reached.");
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>StoXVision Analysis</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background: #0f2027;
            color: white;
            font-family: Arial;
            text-align: center;
            padding: 40px;
        }
        .card {
            background: rgba(255,255,255,0.1);
            padding: 20px;
            border-radius: 15px;
            display: inline-block;
            width: 400px;
        }
        h2 {
            color: #00ffcc;
        }
        .back-btn {
            margin-top: 20px;
            padding: 10px 20px;
            background: #00ffcc;
            color: black;
            text-decoration: none;
            border-radius: 8px;
        }
    </style>
</head>
<body>

<div class="card">
    <h2>StoXVision Report</h2>
    <h3><?php echo $symbol; ?></h3>
    <p><strong>Date:</strong> <?php echo $latest_date; ?></p>
    <p>Open: <?php echo $open; ?></p>
    <p>High: <?php echo $high; ?></p>
    <p>Low: <?php echo $low; ?></p>
    <p>Close: <?php echo $close; ?></p>
    <p>Volume: <?php echo $volume; ?></p>
    <canvas id="stockChart"></canvas>
    <p><strong>AI Recommendation:</strong> <?php echo $recommendation; ?></p>
    
    <p><strong>5-Day Moving Average:</strong> <?php echo round($ma,2); ?></p>
    <p><strong>RSI:</strong> <?php echo round($rsi,2); ?></p>
    <a href="dashboard.php" class="back-btn">Analyze Another</a>
</div>

<script>
const ctx = document.getElementById('stockChart').getContext('2d');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($dates); ?>,
        datasets: [{
            label: 'Closing Price',
            data: <?php echo json_encode($closes); ?>,
            borderColor: '#00ffcc',
            backgroundColor: 'rgba(0,255,204,0.2)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { labels: { color: 'white' } }
        },
        scales: {
            x: { ticks: { color: 'white' } },
            y: { ticks: { color: 'white' } }
        }
    }
});
</script>

</body>
</html>