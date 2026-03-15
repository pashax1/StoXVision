<?php
include "config/db.php";
include "includes/auth_check.php";

$pageTitle = "My Portfolio | StoXVision AI";
$currentPage = "portfolio";

$user_id = $_SESSION["user_id"];

// Fetch watchlist items
$watchlist = [];
$resCheck = $conn->query("SHOW TABLES LIKE 'watchlist'");
if($resCheck && $resCheck->num_rows > 0) {
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

<div class="mb-12 flex flex-col md:flex-row justify-between items-start md:items-end gap-6 animate-in fade-in slide-in-from-bottom-5 duration-700">
    <div>
        <h1 class="text-4xl md:text-5xl font-black text-white tracking-tighter mb-2">My Portfolio</h1>
        <p class="text-slate-400 text-lg">Your curated collection of market intelligence.</p>
    </div>
    <a href="dashboard.php" class="bg-primary hover:bg-primary/90 text-dark font-black px-8 py-4 rounded-2xl transition-all flex items-center gap-3 shadow-lg shadow-primary/20">
        <i class="fas fa-plus"></i> Add New Stock
    </a>
</div>

<?php if (empty($watchlist)): ?>
    <div class="glass-panel p-20 rounded-[48px] text-center border-dashed border-white/10">
        <div class="w-24 h-24 bg-white/5 rounded-full flex items-center justify-center mx-auto mb-8 text-slate-600">
            <i class="fas fa-chart-pie text-4xl"></i>
        </div>
        <h3 class="text-2xl font-black text-white mb-4">Your portfolio is silent</h3>
        <p class="text-slate-500 max-w-md mx-auto mb-8 italic">Start analyzing stocks and bookmark them to keep track of their neural predictions here.</p>
        <a href="dashboard.php" class="inline-block bg-white/5 text-white font-bold px-10 py-4 rounded-2xl hover:bg-white/10 transition-colors border border-white/5">Explore Markets</a>
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($watchlist as $item): ?>
            <div class="glass-panel p-8 rounded-[36px] flex flex-col justify-between group hover:border-primary/30 transition-all transform hover:-translate-y-2 relative overflow-hidden">
                <div class="absolute -top-6 -right-6 text-6xl text-white/5 transform rotate-12 group-hover:scale-110 transition-transform">
                    <i class="fas fa-bookmark"></i>
                </div>
                
                <div class="relative z-10">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <span class="text-[10px] font-black text-primary uppercase tracking-widest mb-1 italic">Bookmarked Asset</span>
                            <h3 class="text-3xl font-black text-white tracking-tighter"><?php echo str_replace([".BSE", ".NS"], "", $item['symbol']); ?></h3>
                            <code class="text-xs text-slate-500 font-mono mt-1 block tracking-wider uppercase"><?php echo htmlspecialchars($item['symbol']); ?></code>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2 text-xs text-slate-500 mb-8 pb-8 border-b border-white/5">
                        <i class="far fa-clock"></i>
                        <span>Added on <?php echo date("M d, Y", strtotime($item['added_at'])); ?></span>
                    </div>
                </div>

                <div class="flex gap-3">
                    <form method="POST" action="analyze.php" class="flex-grow m-0">
                        <input type="hidden" name="symbol" value="<?php echo $item['symbol']; ?>">
                        <button type="submit" class="w-full bg-white/5 hover:bg-primary hover:text-dark text-white font-black py-4 rounded-2xl transition-all flex items-center justify-center gap-2 border border-white/5 hover:border-primary">
                            <i class="fas fa-bolt text-xs"></i> Analyze
                        </button>
                    </form>
                    <button onclick="removeStock('<?php echo $item['symbol']; ?>', this)" 
                        class="w-14 h-14 rounded-2xl bg-red-500/10 text-red-400 flex items-center justify-center hover:bg-red-500 hover:text-white transition-all border border-red-500/20 active:scale-90">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
    async function removeStock(symbol, btn) {
        if(!confirm('Permanently remove ' + symbol + ' from your portfolio?')) return;
        
        btn.disabled = true;
        try {
            const formData = new FormData();
            formData.append('symbol', symbol);
            const res = await fetch('api/toggle_watchlist.php', { method: 'POST', body: formData });
            const data = await res.json();
            
            if (data.status === 'success') {
                const card = btn.closest('.glass-panel');
                card.style.opacity = '0';
                card.style.transform = 'scale(0.9)';
                setTimeout(() => {
                    card.remove();
                    if(document.querySelectorAll('.glass-panel').length == 0) location.reload();
                }, 300);
            }
        } catch (e) {
            console.error(e);
            btn.disabled = false;
        }
    }
    </script>
<?php endif; ?>

<?php include "includes/footer.php"; ?>
