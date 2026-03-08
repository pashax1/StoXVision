<?php
include "config/db.php";
include "includes/auth_check.php";
require_once "src/bootstrap.php";

use StoXVision\Services\MarketDataService;
use StoXVision\Services\ReportService;
use StoXVision\Services\NewsService;
use StoXVision\Data\Stocks;

global $config;
$api_key = $config['api_key'];

// Initialize variables so later references never throw undefined warnings
$symbol_raw   = '';
$symbol       = '';
$display_name = '';
$report       = null;
$apiError     = null;
$is_in_watchlist = false;
$dates  = [];
$closes = [];
$latest_price    = 0;
$price_change    = 0;
$price_change_pct = 0;

if(isset($_POST['symbol'])) {
    $symbol_raw = strtoupper(trim($_POST['symbol']));
    
    // Strict validation: Alphanumeric, dots, and hyphens only
    if (!preg_match("/^[A-Z0-9\.\-]+$/", $symbol_raw)) {
        header("Location: dashboard.php?error=invalid_symbol");
        exit();
    }
    // Smart resolution: name/alias → NSE ticker (e.g. "Infosys" → "INFY.NS")
    $symbol = Stocks::resolve($symbol_raw);
    // Display name = clean ticker without exchange suffix
    $display_name = preg_replace('/\.(NS|BO)$/i', '', $symbol);

    $marketDataService = new MarketDataService($api_key);
    $reportService = new ReportService();
    $newsService = new NewsService($api_key);

    try {
        $raw_data = $marketDataService->getDailyTimeSeries($symbol);
        $indicators = $marketDataService->calculateIndicators($raw_data["Time Series (Daily)"]);
        
        // Fetch news sentiment
        $news_data = $newsService->getNewsSentiment($symbol);
        
        $report = $reportService->generateReport($indicators, $symbol, $news_data);

        // Chart data
        $dates = $indicators['dates'];
        $closes = $indicators['closes'];
        $latest_price = $indicators['latest_price'];
        $price_change = $indicators['price_change'];
        $price_change_pct = $indicators['price_change_pct'];

        // Watchlist check (symbol is already fully resolved e.g. INFY.NS)
        $is_in_watchlist = false;
        $res_wCheck = $conn->query("SHOW TABLES LIKE 'watchlist'");
        if($res_wCheck->num_rows > 0) {
            $stmt_w = $conn->prepare("SELECT id FROM watchlist WHERE user_id = ? AND symbol = ?");
            $stmt_w->bind_param("is", $_SESSION["user_id"], $symbol);
            $stmt_w->execute();
            $is_in_watchlist = ($stmt_w->get_result()->num_rows > 0);
        }

    } catch (\Exception $e) {
        $apiError = $e->getMessage();
    }
} else {
    header("Location: dashboard.php");
    exit();
}


$pageTitle = "Analysis: " . ($display_name ?? $symbol_raw) . " | StoXVision AI";
$currentPage = "analysis";
$extraHead = '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';

include "includes/header.php";
?>

<a href="dashboard.php" style="color:var(--text-secondary); text-decoration:none; display:inline-flex; align-items:center; gap:8px; margin-bottom: 20px;">
    <i class="fas fa-arrow-left"></i> Back to Dashboard
</a>

<?php if (isset($apiError)): ?>
    <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; padding: 40px; border-radius: 24px; text-align: center;">
        <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: #ef4444; margin-bottom: 20px;"></i>
        <h2>Something Went Wrong</h2>
        <p><?php echo $apiError; ?></p>
        <br>
        <a href="dashboard.php" class="btn btn-primary" style="display: inline-block;">Try Another Symbol</a>
    </div>
<?php else: ?>
    <div class="analysis-container">
        <div class="analysis-header">
            <div class="stock-info">
                <span class="symbol-badge"><?php echo $symbol; ?></span>
                <h1><?php echo htmlspecialchars($display_name ?? $symbol_raw); ?> &mdash; Professional Report</h1>
                <div class="live-price">
                    <span class="price">₹ <?php echo number_format($latest_price, 2); ?></span>
                    <span class="change <?php echo ($price_change >= 0) ? 'up' : 'down'; ?>">
                        <?php echo ($price_change >= 0) ? '+' : ''; ?><?php echo number_format($price_change, 2); ?> 
                        (<?php echo number_format($price_change_pct, 2); ?>%)
                    </span>
                </div>
            </div>
            <div class="header-actions">
                <button id="watchlistBtn" class="btn btn-outline" onclick="toggleWatchlist('<?php echo $symbol; ?>')">
                    <i class="<?php echo $is_in_watchlist ? 'fas' : 'far'; ?> fa-bookmark"></i> 
                    <span><?php echo $is_in_watchlist ? 'In Watchlist' : 'Add to Watchlist'; ?></span>
                </button>
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-file-pdf"></i> Export Report
                </button>
            </div>
        </div>

        <div class="report-grid">
            <!-- Market Overview -->
            <div class="report-section glass">
                <h3><i class="fas fa-globe"></i> Market Overview</h3>
                <div class="report-item">
                    <label>Current Trend</label>
                    <span class="badge <?php echo $report['market_overview']['trend_class']; ?>">
                        <?php echo $report['market_overview']['current_trend']; ?>
                    </span>
                </div>
                <div class="report-item">
                    <label>Momentum Strength</label>
                    <span><?php echo $report['market_overview']['momentum_strength']; ?></span>
                </div>
                <div class="report-item">
                    <label>Volume Behavior</label>
                    <span><?php echo $report['market_overview']['volume_behavior']; ?></span>
                </div>
            </div>

            <!-- Technical Analysis -->
            <div class="report-section glass">
                <h3><i class="fas fa-chart-line"></i> Technical Analysis</h3>
                <div class="technical-stats">
                    <div class="t-stat">
                        <label>RSI (14)</label>
                        <span class="value"><?php echo $report['technical_analysis']['rsi_value']; ?></span>
                        <small><?php echo $report['technical_analysis']['rsi_status']; ?></small>
                    </div>
                    <div class="t-stat">
                        <label>MACD Trend</label>
                        <span class="value" style="font-size: 0.9rem;"><?php echo $report['technical_analysis']['macd_trend']; ?></span>
                    </div>
                </div>
                <p class="ema-status"><?php echo $report['technical_analysis']['ema_20_vs_50']; ?></p>
                <div class="levels">
                    <div class="level">
                        <label>Support</label>
                        <span class="val-s">₹ <?php echo $report['technical_analysis']['support']; ?></span>
                    </div>
                    <div class="level">
                        <label>Resistance</label>
                        <span class="val-r">₹ <?php echo $report['technical_analysis']['resistance']; ?></span>
                    </div>
                </div>
            </div>

            <!-- Main Chart Area -->
            <div class="report-section chart-section glass">
                <div class="chart-header">
                    <h3><i class="fas fa-chart-area"></i> Price Action</h3>
                </div>
                <div class="chart-container">
                    <canvas id="stockChart"></canvas>
                </div>
            </div>

            <!-- Sentiment Insight -->
            <div class="report-section glass">
                <h3><i class="fas fa-newspaper"></i> Sentiment Insight</h3>
                <div class="report-item">
                    <label>News Sentiment</label>
                    <span class="p-dir <?php echo strtolower($report['sentiment_insight']['news_sentiment']); ?>">
                        <?php echo $report['sentiment_insight']['news_sentiment']; ?>
                    </span>
                </div>
                <div class="report-item">
                    <label>Overall Market Mood</label>
                    <span><?php echo $report['sentiment_insight']['overall_market_mood']; ?></span>
                </div>
                <!-- Mini News Feed -->
                <div class="mini-news">
                    <?php if(!empty($report['sentiment_insight']['news_articles'])): ?>
                        <?php foreach($report['sentiment_insight']['news_articles'] as $article): ?>
                            <a href="<?php echo $article['url']; ?>" target="_blank" class="news-link">
                                <?php echo substr($article['title'], 0, 60); ?>...
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="font-size: 0.8rem; color: var(--text-secondary);">Using simulated sentiment data (API Limit reached or no news found).</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- AI Prediction -->
            <div class="report-section glass prediction-box">
                <div class="prediction-header">
                    <h3><i class="fas fa-robot"></i> AI Prediction</h3>
                    <div class="confidence">
                        <span>Confidence</span>
                        <div class="conf-bar" style="--conf: <?php echo $report['ai_prediction']['confidence']; ?>%"></div>
                        <span class="conf-val"><?php echo $report['ai_prediction']['confidence']; ?>%</span>
                    </div>
                </div>
                <div class="predict-grid">
                    <div class="predict-item">
                        <label>Predicted Direction</label>
                        <span class="p-dir <?php echo strtolower($report['ai_prediction']['predicted_direction']); ?>">
                            <?php echo $report['ai_prediction']['predicted_direction']; ?>
                        </span>
                    </div>
                    <div class="predict-item">
                        <label>Short-term (1-3d)</label>
                        <span><?php echo $report['ai_prediction']['short_term']; ?></span>
                    </div>
                    <div class="predict-item">
                        <label>Risk Level</label>
                        <span class="risk-<?php echo strtolower($report['ai_prediction']['risk_level']); ?>">
                            <?php echo $report['ai_prediction']['risk_level']; ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Smart Trade Plan -->
            <div class="report-section glass trade-plan">
                <h3><i class="fas fa-bullseye"></i> Smart Trade Plan</h3>
                <div class="trade-grid">
                    <div class="trade-item">
                        <label>Entry Zone</label>
                        <span><?php echo htmlspecialchars($report['trade_plan']['entry_zone']); ?></span>
                    </div>
                    <div class="trade-item">
                        <label>Target</label>
                        <span class="target">₹ <?php echo $report['trade_plan']['target']; ?></span>
                    </div>
                    <div class="trade-item">
                        <label>Stop Loss</label>
                        <span class="stop">₹ <?php echo $report['trade_plan']['stop_loss']; ?></span>
                    </div>
                    <div class="trade-item recomendation">
                        <label>Recommendation</label>
                        <span class="rect-badge"><?php echo $report['trade_plan']['recommendation']; ?></span>
                    </div>
                </div>
            </div>
            <!-- Portfolio Advice -->
            <div class="report-section glass portfolio-advice">
                <h3><i class="fas fa-wallet"></i> Portfolio Strategy</h3>
                <div class="advice-grid">
                    <div class="advice-item">
                        <label>If Holding</label>
                        <span><?php echo $report['portfolio_advice']['if_holding']; ?></span>
                    </div>
                    <div class="advice-item">
                        <label>If Not Holding</label>
                        <span><?php echo $report['portfolio_advice']['if_not_holding']; ?></span>
                    </div>
                    <div class="advice-item">
                        <label>Suggested Allocation</label>
                        <span class="allocation-val"><?php echo $report['portfolio_advice']['allocation']; ?> of portfolio</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    <?php if (!empty($dates) && !empty($closes)): ?>
    const ctx = document.getElementById('stockChart').getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(14, 165, 233, 0.4)');
    gradient.addColorStop(1, 'rgba(14, 165, 233, 0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($dates); ?>,
            datasets: [{
                label: 'Price',
                data: <?php echo json_encode($closes); ?>,
                borderColor: '#0ea5e9',
                borderWidth: 3,
                backgroundColor: gradient,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#0ea5e9'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#94a3b8' } },
                y: { grid: { color: 'rgba(255, 255, 255, 0.05)' }, ticks: { color: '#94a3b8' } }
            }
        }
    });

    async function toggleWatchlist(symbol) {
        const btn = document.getElementById('watchlistBtn');
        const icon = btn.querySelector('i');
        const label = btn.querySelector('span');
        
        try {
            const formData = new FormData();
            formData.append('symbol', symbol);
            const res = await fetch('api/toggle_watchlist.php', { method: 'POST', body: formData });
            const data = await res.json();
            
            if (data.status === 'success') {
                if (data.action === 'added') {
                    icon.className = 'fas fa-bookmark';
                    label.innerText = 'In Watchlist';
                } else {
                    icon.className = 'far fa-bookmark';
                    label.innerText = 'Add to Watchlist';
                }
            }
        } catch (e) {
            console.error(e);
        }
    }
    <?php endif; ?>
    </script>
<?php endif; ?>

<?php include "includes/footer.php"; ?>
