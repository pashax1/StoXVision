<?php
include "config/db.php";
include "includes/auth_check.php";

$pageTitle = "My Portfolio | StoXVision AI";
$currentPage = "portfolio";

$user_id = $_SESSION["user_id"];

// Fetch watchlist items
$watchlist = [];
$resCheck = $conn->query("SHOW TABLES LIKE 'watchlist'");
if($resCheck->num_rows > 0) {
    $stmt = $conn->prepare("SELECT symbol, added_at FROM watchlist WHERE user_id = ? ORDER BY added_at DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $watchlist[] = $row;
    }
}

include "includes/header.php";
?>

<div style="margin-bottom: 40px; display: flex; justify-content: space-between; align-items: flex-end;">
    <div>
        <h1 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 5px;">My Portfolio</h1>
        <p style="color: var(--text-secondary);">Track and monitor your favorite Indian stocks.</p>
    </div>
    <a href="dashboard.php" class="btn btn-primary" style="padding: 10px 20px;">
        <i class="fas fa-plus"></i> Add New Stock
    </a>
</div>

<?php if (empty($watchlist)): ?>
    <div style="background: var(--glass); border: 1px solid var(--glass-border); padding: 60px; border-radius: 24px; text-align: center;">
        <i class="fas fa-folder-open" style="font-size: 3rem; color: var(--glass-border); margin-bottom: 20px;"></i>
        <h3>Your portfolio is empty</h3>
        <p style="color: var(--text-secondary); margin-bottom: 20px;">Start searching and adding stocks to your watchlist for quick tracking.</p>
        <a href="dashboard.php" class="btn btn-outline" style="display: inline-block;">Explore Markets</a>
    </div>
<?php else: ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
        <?php foreach ($watchlist as $item): ?>
            <div class="market-card" style="display: flex; flex-direction: column; gap: 15px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <span style="color: var(--primary); font-size: 0.75rem; font-weight: 600;"><?php echo $item['symbol']; ?></span>
                        <h3 style="margin: 0;"><?php echo str_replace(".NS", "", $item['symbol']); ?></h3>
                    </div>
                    <form method="POST" action="analyze.php" style="margin:0">
                        <input type="hidden" name="symbol" value="<?php echo $item['symbol']; ?>">
                        <button type="submit" class="btn btn-outline" style="width: auto; padding: 5px 12px; font-size: 0.8rem;">
                            Analyze
                        </button>
                    </form>
                </div>
                
                <div style="font-size: 0.8rem; color: var(--text-secondary); border-top: 1px solid var(--glass-border); padding-top: 10px; display: flex; justify-content: space-between;">
                    <span>Added on: <?php echo date("d M", strtotime($item['added_at'])); ?></span>
                    <a href="javascript:void(0)" onclick="removeStock('<?php echo $item['symbol']; ?>', this)" style="color: #ef4444; text-decoration: none;">Remove</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
    async function removeStock(symbol, el) {
        if(!confirm('Remove ' + symbol + ' from watchlist?')) return;
        
        try {
            const formData = new FormData();
            formData.append('symbol', symbol);
            const res = await fetch('api/toggle_watchlist.php', { method: 'POST', body: formData });
            const data = await res.json();
            
            if (data.status === 'success') {
                el.closest('.market-card').remove();
                if(document.querySelectorAll('.market-card').length == 0) location.reload();
            }
        } catch (e) {
            console.error(e);
        }
    }
    </script>
<?php endif; ?>

<?php include "includes/footer.php"; ?>
