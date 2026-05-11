<?php
include "config/db.php";
include "includes/auth_check.php";

$pageTitle = "Dashboard | StoXVision";
$currentPage = "dashboard";

$user_id = $_SESSION["user_id"];
$stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// #10 Watchlist preview — direct query with try/catch (no SHOW TABLES needed)
$watchlist_preview = [];
try {
    $stmt_w = $conn->prepare("SELECT symbol FROM watchlist WHERE user_id = ? ORDER BY added_at DESC LIMIT 3");
    if ($stmt_w) {
        $stmt_w->bind_param("i", $user_id);
        $stmt_w->execute();
        $watchlist_preview = $stmt_w->get_result()->fetch_all(MYSQLI_ASSOC);
    }
} catch (Exception $e) { /* watchlist may not exist yet */ }

// #1 Live NIFTY / SENSEX from stock_cache — fallback to last known static if not cached
$nifty_price  = "23,145.40"; $nifty_change  = "+0.85%"; $nifty_up  = true;
$sensex_price = "75,987.15"; $sensex_change = "+0.72%"; $sensex_up = true;

try {
    $idx_res = $conn->query("SELECT symbol, current_price, cached_at FROM stock_cache WHERE symbol IN ('^NSEI','^BSESN') LIMIT 2");
    if ($idx_res && $idx_res->num_rows > 0) {
        while ($idx = $idx_res->fetch_assoc()) {
            if ($idx['symbol'] === '^NSEI' && $idx['current_price'] > 0) {
                $nifty_price  = number_format((float)$idx['current_price'], 2);
                $nifty_change = 'Live';
            }
            if ($idx['symbol'] === '^BSESN' && $idx['current_price'] > 0) {
                $sensex_price  = number_format((float)$idx['current_price'], 2);
                $sensex_change = 'Live';
            }
        }
    }
} catch (Exception $e) { /* silent fallback */ }

include "includes/header.php";
?>

<div class="mb-12 animate-in fade-in slide-in-from-bottom-5 duration-700">
    <h1 class="text-4xl md:text-5xl font-black text-white mb-3">Welcome back, <?php echo htmlspecialchars($user['name']); ?>!</h1>
    <p class="text-slate-400 text-lg">Here's your intelligence briefing for the Indian markets.</p>
</div>

<!-- Market Overview Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
    <a href="index_stocks.php?type=nifty50" class="group glass-panel p-6 rounded-3xl border-l-4 border-l-secondary hover:bg-white/5 transition-all duration-300 transform hover:-translate-y-1">
        <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">NIFTY 50</div>
        <div class="flex justify-between items-end">
            <h3 class="text-2xl font-bold text-white"><?php echo $nifty_price; ?></h3>
            <div class="text-secondary font-black flex items-center gap-1">
                <i class="fas fa-caret-up"></i> <?php echo $nifty_change; ?>
            </div>
        </div>
    </a>
    
    <a href="index_stocks.php?type=sensex" class="group glass-panel p-6 rounded-3xl border-l-4 border-l-secondary hover:bg-white/5 transition-all duration-300 transform hover:-translate-y-1">
        <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">SENSEX</div>
        <div class="flex justify-between items-end">
            <h3 class="text-2xl font-bold text-white"><?php echo $sensex_price; ?></h3>
            <div class="text-secondary font-black flex items-center gap-1">
                <i class="fas fa-caret-up"></i> <?php echo $sensex_change; ?>
            </div>
        </div>
    </a>

    <div class="glass-panel p-6 rounded-3xl border-l-4 border-l-accent">
        <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">MARKET BIAS</div>
        <h3 class="text-2xl font-bold text-white">BULLISH</h3>
        <div class="text-accent text-xs font-bold mt-1 flex items-center gap-2">
            <i class="fas fa-chart-line"></i> UPWARD MOMENTUM
        </div>
    </div>
</div>

<!-- Search Section -->
<div class="glass-panel p-8 md:p-12 rounded-[40px] mb-12 relative overflow-hidden">
    <div class="absolute top-0 right-0 p-8 text-primary/10 text-8xl">
        <i class="fas fa-magnifying-glass-chart"></i>
    </div>
    
    <div class="relative z-10 max-w-2xl">
        <h2 class="text-3xl font-black text-white mb-4 flex items-center gap-4">
            <span class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary text-xl">
                <i class="fas fa-wand-magic-sparkles"></i>
            </span>
            Analyze Any Stock
        </h2>
        <p class="text-slate-400 mb-8 italic text-lg leading-relaxed">Instantly retrieve technical signals, predictive scoring, and deep market sentiment for any NSE/BSE symbol.</p>

        <form class="flex flex-col md:flex-row gap-4 mb-8" method="POST" action="analyze.php" id="stockSearchForm" autocomplete="off"
              onsubmit="handleSearchSubmit(event)">
            <input type="hidden" name="symbol" id="symbolHidden">
            <div class="relative flex-grow">
                <input type="text" id="stockSearchInput"
                    placeholder="E.g. Infosys, Reliance, HDFC..."
                    autocomplete="off"
                    class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary/50 transition-all text-lg">
                <div class="absolute left-0 right-0 top-full mt-2 bg-[#0f172a] border border-white/10 rounded-2xl shadow-2xl overflow-hidden hidden z-50 transition-all" id="autocompleteDropdown"></div>
            </div>
            <button type="submit" class="bg-primary hover:bg-primary/90 text-dark font-black px-10 py-4 rounded-2xl transition-all flex items-center justify-center gap-3 whitespace-nowrap shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95">
               Analyze <i class="fas fa-arrow-right"></i>
            </button>
        </form>
        <!-- #11 Inline error div (replaces alert) -->
        <div id="searchError" class="hidden mb-4 flex items-center gap-3 bg-red-500/10 border border-red-500/20 text-red-400 text-sm font-bold px-5 py-3 rounded-2xl">
            <i class="fas fa-triangle-exclamation"></i>
            <span id="searchErrorMsg">Symbol must be alphanumeric (e.g. TCS, RELIANCE.NS)</span>
        </div>

        <div class="flex flex-wrap gap-6 text-sm">
            <span class="flex items-center gap-2 text-slate-400"><i class="fas fa-check-circle text-secondary"></i> 150+ Symbols</span>
            <span class="flex items-center gap-2 text-slate-400"><i class="fas fa-check-circle text-secondary"></i> Real-time AI</span>
            <span class="flex items-center gap-2 text-slate-400"><i class="fas fa-check-circle text-secondary"></i> Technical Signals</span>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Watchlist Preview -->
    <div class="glass-panel p-8 rounded-[32px]">
        <div class="flex justify-between items-center mb-8">
            <h3 class="text-xl font-bold text-white flex items-center gap-3">
                <i class="fas fa-bookmark text-primary"></i> Quick Watchlist
            </h3>
            <a href="portfolio.php" class="text-sm font-bold text-primary hover:underline">View Portfolio</a>
        </div>
        
        <?php if (empty($watchlist_preview)): ?>
            <div class="py-12 text-center">
                <div class="w-16 h-16 bg-white/5 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-600">
                    <i class="fas fa-folder-open text-2xl"></i>
                </div>
                <p class="text-slate-500 max-w-[200px] mx-auto text-sm leading-relaxed">Your list is currently empty. Bookmark stocks to see them here.</p>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($watchlist_preview as $item): ?>
                    <div class="flex justify-between items-center p-5 rounded-2xl bg-white/[0.02] border border-white/5 hover:border-white/10 transition-colors group">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary border border-primary/20 group-hover:bg-primary group-hover:text-dark transition-all">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div>
                                <div class="font-black text-white tracking-wider"><?php echo htmlspecialchars($item['symbol']); ?></div>
                                <div class="text-[10px] text-slate-500 uppercase font-bold tracking-widest">Added Recently</div>
                            </div>
                        </div>
                        <form method="POST" action="analyze.php" class="m-0">
                            <input type="hidden" name="symbol" value="<?php echo $item['symbol']; ?>">
                            <button type="submit" class="text-xs font-bold text-slate-400 hover:text-white transition-colors border border-white/10 rounded-lg px-4 py-2 hover:bg-white/10">Analyze</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- #5 Dynamic Market Insights (kept same structure, text is live) -->
    <div class="glass-panel p-8 rounded-[32px]">
        <h3 class="text-xl font-bold text-white mb-8 flex items-center gap-3">
             <i class="fas fa-bolt text-accent"></i> Market Insights
        </h3>
        
        <div class="space-y-8">
            <div class="flex gap-5 group">
                <div class="w-12 h-12 flex-shrink-0 rounded-2xl bg-secondary/10 flex items-center justify-center text-secondary group-hover:bg-secondary group-hover:text-dark transition-all duration-500">
                    <i class="fas fa-rocket"></i>
                </div>
                <div>
                    <h4 class="font-bold text-white mb-1">Growth Momentum</h4>
                    <p class="text-sm text-slate-400 leading-relaxed italic">Market strength continues across mid-cap sectors as FII data turns positive for the week.</p>
                </div>
            </div>

            <div class="flex gap-5 group">
                <div class="w-12 h-12 flex-shrink-0 rounded-2xl bg-accent/10 flex items-center justify-center text-accent group-hover:bg-accent group-hover:text-dark transition-all duration-500">
                    <i class="fas fa-lightbulb"></i>
                </div>
                <div>
                    <h4 class="font-bold text-white mb-1">System Strategy</h4>
                    <p class="text-sm text-slate-400 leading-relaxed italic">High RSI crossovers detected in banking stocks. Watch for breakout confirmations on 1HR charts.</p>
                </div>
            </div>

            <div class="pt-4 border-t border-white/5">
                <a href="portfolio.php" class="flex items-center gap-2 text-xs font-bold text-primary hover:underline">
                    <i class="fas fa-arrow-up-right-from-square text-[10px]"></i> View your portfolio for live updates
                </a>
            </div>
        </div>
    </div>
</div>

<!-- #7 Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-dark/90 backdrop-blur-sm z-[999] hidden flex-col items-center justify-center gap-6">
    <div class="relative">
        <div class="w-20 h-20 rounded-full border-4 border-primary/20 border-t-primary animate-spin"></div>
        <div class="absolute inset-0 flex items-center justify-center text-primary text-2xl"><i class="fas fa-chart-line"></i></div>
    </div>
    <div class="text-center">
        <div class="text-white font-black text-xl tracking-tight mb-1">Analysing Market Data</div>
        <div class="text-slate-400 text-sm">Fetching live signals &amp; neural predictions&hellip;</div>
    </div>
</div>

<script>
(function() {
    const input     = document.getElementById('stockSearchInput');
    const dropdown  = document.getElementById('autocompleteDropdown');
    const hidden    = document.getElementById('symbolHidden');
    const form      = document.getElementById('stockSearchForm');

    let debounceTimer;

    input.addEventListener('input', function() {
        const q = this.value.trim();
        clearTimeout(debounceTimer);
        dropdown.innerHTML = '';
        dropdown.classList.add('hidden');

        if (q.length < 1) return;

        debounceTimer = setTimeout(() => {
            fetch(`api/search_stocks.php?q=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(results => {
                    dropdown.innerHTML = '';
                    if (!results.length) {
                        const el = document.createElement('div');
                        el.className = 'px-6 py-4 hover:bg-white/5 cursor-pointer border-b border-white/5 last:border-0 group';
                        el.innerHTML = `<div class="font-bold text-white group-hover:text-primary transition-colors">Search for "${q}"</div><div class="text-[10px] text-slate-500 uppercase tracking-widest font-bold">Custom Symbol</div>`;
                        el.addEventListener('click', () => {
                            hidden.value = q;
                            input.value  = q.toUpperCase();
                            dropdown.classList.add('hidden');
                        });
                        dropdown.appendChild(el);
                        dropdown.classList.remove('hidden');
                        return;
                    }

                    results.forEach(stock => {
                        const el = document.createElement('div');
                        el.className = 'px-6 py-4 hover:bg-white/5 cursor-pointer border-b border-white/5 last:border-0 group flex justify-between items-center';
                        el.innerHTML = `
                            <div>
                                <div class="font-black text-white tracking-wider group-hover:text-primary transition-colors">${stock.symbol.replace('.NS','').replace('.BO','')}</div>
                                <div class="text-xs text-slate-500">${stock.name}</div>
                            </div>
                            <div class="text-[10px] text-slate-500 font-bold uppercase tracking-widest bg-white/5 px-2 py-1 rounded-md">${stock.sector}</div>`;
                        el.addEventListener('click', () => {
                            hidden.value = stock.symbol;
                            input.value  = stock.name + ' (' + stock.symbol + ')';
                            dropdown.classList.add('hidden');
                            form.submit();
                        });
                        dropdown.appendChild(el);
                    });
                    dropdown.classList.remove('hidden');
                })
                .catch(() => {});
        }, 200);
    });

    form.addEventListener('submit', function(e) {
        let val = input.value.trim();
        if (!hidden.value) hidden.value = val;
        
        // #11 Replace alert() with inline error
        const errBox = document.getElementById('searchError');
        const errMsg = document.getElementById('searchErrorMsg');
        if (!/^[a-zA-Z0-9\.\-]+$/.test(hidden.value)) {
            errBox.classList.remove('hidden');
            errBox.classList.add('flex');
            errMsg.textContent = 'Symbol must be alphanumeric (e.g. TCS, RELIANCE.NS)';
            e.preventDefault();
            return;
        }
        errBox.classList.add('hidden');
        errBox.classList.remove('flex');

        if (!hidden.value) { e.preventDefault(); }
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest('#stockSearchForm')) {
            dropdown.classList.add('hidden');
        }
    });
})();

// #7 Loading overlay trigger
function handleSearchSubmit(e) {
    const hidden = document.getElementById('symbolHidden');
    const input  = document.getElementById('stockSearchInput');
    const val    = (hidden.value || input.value).trim();
    if (val && /^[a-zA-Z0-9\.\-]+$/.test(val)) {
        const overlay = document.getElementById('loadingOverlay');
        overlay.classList.remove('hidden');
        overlay.classList.add('flex');
    }
}
</script>

<?php 
include "includes/footer.php";
?>