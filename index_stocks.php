<?php
include "config/db.php";
include "includes/auth_check.php";
require_once "src/bootstrap.php";

use StoXVision\Data\Stocks;

$type = $_GET['type'] ?? 'nifty50';
$stocks = [];
$pageTitle = "";
$indexName = "";

if ($type === 'nifty50') {
    $stocks = Stocks::getNifty50();
    $indexName = "Nifty 50";
} elseif ($type === 'sensex') {
    $stocks = Stocks::getSensex();
    $indexName = "SENSEX";
} else {
    header("Location: dashboard.php");
    exit();
}

$pageTitle = $indexName . " Stocks | StoXVision";
$currentPage = "markets";

include "includes/header.php";
?>

<div style="margin-bottom: 40px;">
    <a href="dashboard.php" style="color:var(--text-secondary); text-decoration:none; display:inline-flex; align-items:center; gap:8px; margin-bottom: 20px;">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
    <h1 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 5px;"><?php echo $indexName; ?> Constituents</h1>
    <p style="color: var(--text-secondary);">Browse and analyze the most powerful companies in the <?php echo $indexName; ?> index.</p>
</div>

<div class="index-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
    <?php foreach ($stocks as $stock): ?>
        <div class="stock-card">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <span class="sector-label"><?php echo htmlspecialchars($stock['sector']); ?></span>
                    <h3><?php echo htmlspecialchars($stock['name']); ?></h3>
                    <code class="symbol-code"><?php echo htmlspecialchars($stock['symbol']); ?></code>
                </div>
                <div class="card-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
            <form method="POST" action="analyze.php" class="btn-analyze">
                <input type="hidden" name="symbol" value="<?php echo $stock['symbol']; ?>">
                <button type="submit" class="btn btn-primary btn-sm" style="width: 100%; justify-content: center;">
                    <i class="fas fa-wand-magic-sparkles"></i> Analyze
                </button>
            </form>
        </div>
    <?php endforeach; ?>
</div>

<?php 
include "includes/footer.php";
?>
