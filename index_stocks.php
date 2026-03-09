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
        <div class="stock-card glass" style="padding: 24px; border-radius: 20px; border: 1px solid var(--glass-border); display: flex; flex-direction: column; gap: 15px; transition: transform 0.3s ease, border-color 0.3s ease;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <span style="display: block; font-size: 0.75rem; color: var(--primary); font-weight: 700; margin-bottom: 4px;"><?php echo htmlspecialchars($stock['sector']); ?></span>
                    <h3 style="margin: 0; font-size: 1.25rem;"><?php echo htmlspecialchars($stock['name']); ?></h3>
                    <code style="color: var(--text-secondary); font-size: 0.85rem;"><?php echo htmlspecialchars($stock['symbol']); ?></code>
                </div>
                <div style="width: 40px; height: 40px; border-radius: 12px; background: rgba(56, 189, 248, 0.1); display: flex; align-items: center; justify-content: center; color: var(--primary);">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
            <form method="POST" action="analyze.php" style="margin-top: auto;">
                <input type="hidden" name="symbol" value="<?php echo $stock['symbol']; ?>">
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    <i class="fas fa-wand-magic-sparkles"></i> Analyze
                </button>
            </form>
        </div>
    <?php endforeach; ?>
</div>

<style>
.stock-card:hover {
    transform: translateY(-5px);
    border-color: var(--primary);
    box-shadow: 0 10px 30px -10px rgba(56, 189, 248, 0.2);
}
</style>

<?php 
include "includes/footer.php";
?>
