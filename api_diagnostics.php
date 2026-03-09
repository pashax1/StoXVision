<?php
include "config/db.php";
include "includes/auth_check.php";

$pageTitle = "API Diagnostics | StoXVision";
$currentPage = "diagnostics";

global $config;
$keys = $config['api_keys'];
$finnhub_key = $config['finnhub_key'] ?? null;
$results = [];
$finnhub_result = null;

if (isset($_POST['check_all'])) {
    // 1. Check Alpha Vantage Keys
    foreach ($keys as $idx => $key) {
        $url = "https://www.alphavantage.co/query?function=GLOBAL_QUOTE&symbol=IBM&apikey=$key";
        $response = @file_get_contents($url);
        $data = json_decode($response, true);
        
        $status = "Unknown";
        $color = "#94a3b8"; // Gray
        
        if (!$response) {
            $status = "Connection Failed";
            $color = "#ef4444"; // Red
        } elseif (isset($data["Note"])) {
            $status = "Limit Reached";
            $color = "#f59e0b"; // Orange
        } elseif (isset($data["Global Quote"]) && !empty($data["Global Quote"])) {
            $status = "Working";
            $color = "#10b981"; // Green
        } else {
            $status = "Error: " . (isset($data["Information"]) ? "Info/Limit" : "Invalid Response");
            $color = "#ef4444";
        }
        
        $results[] = [
            'index' => $idx + 1,
            'key' => substr($key, 0, 4) . "...",
            'status' => $status,
            'color' => $color
        ];
    }

    // 2. Check Finnhub Key
    if ($finnhub_key) {
        $url = "https://finnhub.io/api/v1/quote?symbol=IBM&token=$finnhub_key";
        $response = @file_get_contents($url);
        $data = json_decode($response, true);

        if ($response && isset($data['c']) && $data['c'] > 0) {
            $finnhub_result = ['status' => 'Working', 'color' => '#10b981'];
        } else {
            $finnhub_result = ['status' => 'Error/Limit', 'color' => '#ef4444'];
        }
    }
}

include "includes/header.php";
?>

<div style="margin-bottom: 40px;">
    <a href="dashboard.php" style="color:var(--text-secondary); text-decoration:none; display:inline-flex; align-items:center; gap:8px; margin-bottom: 20px;">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
    <h1>API Health Diagnostics</h1>
    <p style="color: var(--text-secondary);">Check the real-time status of all your Alpha Vantage API keys.</p>
</div>

<div class="glass" style="padding: 30px; border-radius: 24px; border: 1px solid var(--glass-border);">
    <form method="POST" style="margin-bottom: 30px; text-align: center;">
        <button type="submit" name="check_all" class="btn btn-primary">
            <i class="fas fa-heart-pulse"></i> Scan All Providers Now
        </button>
    </form>

    <?php if ($finnhub_result): ?>
        <div style="margin-bottom: 30px; padding: 20px; border-radius: 16px; background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.2);">
            <h3 style="margin: 0 0 15px 0; font-size: 1rem;"><i class="fas fa-shield-halved"></i> Backup Provider: Finnhub</h3>
            <div style="display: inline-flex; align-items: center; gap: 8px; padding: 4px 12px; border-radius: 20px; background: <?php echo $finnhub_result['color']; ?>22; color: <?php echo $finnhub_result['color']; ?>; font-size: 0.85rem; font-weight: 700;">
                <i class="fas fa-circle" style="font-size: 0.5rem;"></i> <?php echo $finnhub_result['status']; ?>
            </div>
            <p style="margin: 10px 0 0 0; font-size: 0.8rem; color: var(--text-secondary);">Used automatically if all Alpha Vantage keys are exhausted for the day.</p>
        </div>
    <?php endif; ?>

    <?php if (!empty($results)): ?>
        <h3 style="margin: 0 0 15px 0; font-size: 1rem;"><i class="fas fa-key"></i> Alpha Vantage Cluster (<?php echo count($keys); ?> Keys)</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">
            <?php foreach ($results as $res): ?>
                <div style="padding: 20px; border-radius: 16px; background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); text-align: center;">
                    <div style="font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 8px;">KEY #<?php echo $res['index']; ?></div>
                    <div style="font-family: monospace; font-size: 1.1rem; margin-bottom: 15px;"><?php echo $res['key']; ?></div>
                    <div style="display: inline-flex; align-items: center; gap: 8px; padding: 4px 12px; border-radius: 20px; background: <?php echo $res['color']; ?>22; color: <?php echo $res['color']; ?>; font-size: 0.85rem; font-weight: 700;">
                        <i class="fas fa-circle" style="font-size: 0.5rem;"></i> <?php echo $res['status']; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="text-align: center; color: var(--text-secondary); padding: 40px;">
            <i class="fas fa-info-circle" style="font-size: 2rem; margin-bottom: 15px; display: block;"></i>
            Click the button above to verify your 10 API keys.
        </div>
    <?php endif; ?>
</div>

<div style="margin-top: 30px; padding: 20px; border-radius: 16px; background: rgba(14, 165, 233, 0.1); border: 1px solid rgba(14, 165, 233, 0.2); color: var(--primary);">
    <i class="fas fa-lightbulb"></i> <strong>Pro Tip:</strong> StoXVision automatically skips keys that have reached their limit. Caching also minimizes the hits used per search.
</div>

<?php include "includes/footer.php"; ?>
