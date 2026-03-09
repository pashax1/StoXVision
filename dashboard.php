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

// Check if watchlist table exists (simple check for robustness)
$watchlist_preview = [];
$res = $conn->query("SHOW TABLES LIKE 'watchlist'");
if($res->num_rows > 0) {
    $stmt_w = $conn->prepare("SELECT symbol FROM watchlist WHERE user_id = ? ORDER BY added_at DESC LIMIT 3");
    $stmt_w->bind_param("i", $user_id);
    $stmt_w->execute();
    $result_w = $stmt_w->get_result();
    $watchlist_preview = $result_w->fetch_all(MYSQLI_ASSOC);
}

// Mock data (or Fetch from Cache)
$nifty_price = "23,145.40";
$nifty_change = "+0.85%";
$sensex_price = "75,987.15";
$sensex_change = "+0.72%";

include "includes/header.php";
?>

<div style="margin-bottom: 40px;">
    <h1 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 5px;">Welcome back, <?php echo htmlspecialchars($user['name']); ?>!</h1>
    <p style="color: var(--text-secondary);">Here's what's happening in the Indian markets today.</p>
</div>

<!-- Market Overview Cards -->
<div class="market-overview">
    <a href="index_stocks.php?type=nifty50" class="market-card up" style="border-left: 4px solid var(--secondary); text-decoration: none; display: block; transition: transform 0.2s ease;">
        <div style="font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 5px; font-weight: 700; letter-spacing: 1px;">NIFTY 50</div>
        <div style="display: flex; justify-content: space-between; align-items: flex-end;">
            <h3 style="margin:0;"><?php echo $nifty_price; ?></h3>
            <div class="price-change" style="color: var(--secondary); font-weight: 800;">
                <i class="fas fa-caret-up"></i> <?php echo $nifty_change; ?>
            </div>
        </div>
    </a>
    <a href="index_stocks.php?type=sensex" class="market-card up" style="border-left: 4px solid var(--secondary); text-decoration: none; display: block; transition: transform 0.2s ease;">
        <div style="font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 5px; font-weight: 700; letter-spacing: 1px;">SENSEX</div>
        <div style="display: flex; justify-content: space-between; align-items: flex-end;">
            <h3 style="margin:0;"><?php echo $sensex_price; ?></h3>
            <div class="price-change" style="color: var(--secondary); font-weight: 800;">
                <i class="fas fa-caret-up"></i> <?php echo $sensex_change; ?>
            </div>
        </div>
    </a>
    <div class="market-card" style="border-left: 4px solid var(--accent);">
        <div style="font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 5px; font-weight: 700; letter-spacing: 1px;">TOP SECTOR</div>
        <h3 style="margin:0;">ENERGY</h3>
        <div style="color: var(--accent); font-size: 0.8rem; font-weight: 800; margin-top: 5px;">
            <i class="fas fa-chart-line"></i> BULLISH BIAS
        </div>
    </div>
</div>

<!-- Search Section -->
<div class="search-wrapper">
    <h2><i class="fas fa-magnifying-glass-chart" style="color:var(--primary); margin-right:10px;"></i>Analyze Any Stock</h2>
    <p>Type a company name or ticker — we'll find it for you.</p>

    <form class="search-form" method="POST" action="analyze.php" id="stockSearchForm" autocomplete="off">
        <input type="hidden" name="symbol" id="symbolHidden">
        <div class="autocomplete-wrapper">
            <input type="text" id="stockSearchInput" placeholder="E.g. Infosys, Reliance, HDFC Bank, TCS...">
            <div class="autocomplete-dropdown" id="autocompleteDropdown"></div>
        </div>
        <button type="submit" class="btn btn-primary" id="analyzeBtn">
            <i class="fas fa-wand-magic-sparkles"></i> Analyze Now
        </button>
    </form>

    <div class="search-hints">
        <span><i class="fas fa-circle-check" style="color:var(--secondary);"></i> Type any name: "Tata", "HDFC", "Wipro"</span>
        <span><i class="fas fa-circle-check" style="color:var(--secondary);"></i> Or paste a ticker: "TCS.NS", "SBIN.BO"</span>
        <span><i class="fas fa-circle-check" style="color:var(--secondary);"></i> 150+ Indian stocks supported</span>
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
        dropdown.classList.remove('open');

        if (q.length < 1) return;

        debounceTimer = setTimeout(() => {
            fetch(`api/search_stocks.php?q=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(results => {
                    dropdown.innerHTML = '';
                    if (!results.length) {
                        // Show fallback — try as raw ticker
                        const el = document.createElement('div');
                        el.className = 'ac-item ac-fallback';
                        el.innerHTML = `<span class="ac-name">Search for "<b>${q}</b>"</span><span class="ac-sector">Custom Symbol</span>`;
                        el.addEventListener('click', () => {
                            hidden.value = q;
                            input.value  = q.toUpperCase();
                            dropdown.classList.remove('open');
                        });
                        dropdown.appendChild(el);
                        dropdown.classList.add('open');
                        return;
                    }

                    results.forEach(stock => {
                        const el = document.createElement('div');
                        el.className = 'ac-item';
                        el.innerHTML = `
                            <div class="ac-left">
                                <span class="ac-ticker">${stock.symbol.replace('.NS','').replace('.BO','')}</span>
                                <span class="ac-name">${stock.name}</span>
                            </div>
                            <span class="ac-sector">${stock.sector}</span>`;
                        el.addEventListener('click', () => {
                            hidden.value = stock.symbol;
                            input.value  = stock.name + ' (' + stock.symbol + ')';
                            dropdown.classList.remove('open');
                            form.submit();
                        });
                        dropdown.appendChild(el);
                    });
                    dropdown.classList.add('open');
                })
                .catch(() => {});
        }, 200);
    });

    // Allow free-type submit — resolve on server side
    form.addEventListener('submit', function(e) {
        let val = input.value.trim();
        if (!hidden.value) {
            hidden.value = val;
        }
        
        // Strict alphanumeric + dot + hyphen check
        if (!/^[a-zA-Z0-9\.\-]+$/.test(hidden.value)) {
            alert("Invalid symbol. Please use only letters, numbers, dots, and hyphens.");
            e.preventDefault();
            return;
        }

        if (!hidden.value) { e.preventDefault(); }
    });

    // Close dropdown on outside click
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.autocomplete-wrapper')) {
            dropdown.classList.remove('open');
        }
    });

    // Hover effect for index cards
    document.querySelectorAll('.market-card').forEach(card => {
        card.addEventListener('mouseenter', () => card.style.transform = 'translateY(-3px)');
        card.addEventListener('mouseleave', () => card.style.transform = 'translateY(0)');
    });
})();
</script>

<div class="content-grid">
    <div style="background: var(--glass); border: 1px solid var(--glass-border); padding: 30px; border-radius: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0;">Quick Watchlist</h3>
            <a href="portfolio.php" style="color: var(--primary); font-size: 0.85rem; text-decoration: none;">View All</a>
        </div>
        
        <?php if (empty($watchlist_preview)): ?>
            <p style="color: var(--text-secondary); font-size: 0.9rem;">Your watchlist is currently empty. Bookmark stocks in your analysis to see them here.</p>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <?php foreach ($watchlist_preview as $item): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; border-radius: 12px; background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border);">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-chart-line" style="color: var(--primary); font-size: 0.8rem;"></i>
                            <span style="font-weight: 600; font-size: 0.9rem;"><?php echo $item['symbol']; ?></span>
                        </div>
                        <form method="POST" action="analyze.php" style="margin: 0;">
                            <input type="hidden" name="symbol" value="<?php echo $item['symbol']; ?>">
                            <button type="submit" class="btn btn-outline" style="width: auto; padding: 4px 10px; font-size: 0.75rem;">Analyze</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div style="background: var(--glass); border: 1px solid var(--glass-border); padding: 30px; border-radius: 24px;">
        <h3 style="margin-bottom: 20px;">Market Insights</h3>
        <div style="display: flex; gap: 15px; margin-bottom: 20px;">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(14, 165, 233, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-rocket"></i>
            </div>
            <div>
                <h4 style="margin: 0 0 5px 0;">Growth Momentum</h4>
                <p style="color: var(--text-secondary); font-size: 0.85rem;">Indian indices hit new life-time highs as global sentiments improve.</p>
            </div>
        </div>
        <div style="display: flex; gap: 15px;">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(245, 158, 11, 0.1); color: var(--accent); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-lightbulb"></i>
            </div>
            <div>
                <h4 style="margin: 0 0 5px 0;">Strategy Alert</h4>
                <p style="color: var(--text-secondary); font-size: 0.85rem;">Identify mid-cap stocks with high RSI crossovers for short-term breakouts.</p>
            </div>
        </div>
    </div>
</div>

<?php 
include "includes/footer.php";
?>