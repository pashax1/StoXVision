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

$symbol_raw   = '';
$symbol       = '';
$display_name = '';
$report       = null;
$apiError     = null;
$is_in_watchlist = false;
$dates  = [];
$closes = [];

if(isset($_POST['symbol'])) {
    $symbol_raw = strtoupper(trim($_POST['symbol']));
    
    if (!preg_match("/^[A-Z0-9\.\-]+$/", $symbol_raw)) {
        header("Location: dashboard.php?error=invalid_symbol");
        exit();
    }
    $symbol = Stocks::resolve($symbol_raw);
    $display_name = preg_replace('/\.(NS|BSE|BO)$/i', '', $symbol);

    $marketDataService = new MarketDataService($api_key);
    $reportService = new ReportService();
    $newsService = new NewsService($api_key);

    try {
        $raw_data = $marketDataService->getDailyTimeSeries($symbol);
        $api_key = $config['api_key']; 
        $indicators = $marketDataService->calculateIndicators($raw_data["Time Series (Daily)"]);
        
        $newsService = new NewsService($api_key);
        $news_data = $newsService->getNewsSentiment($symbol);
        
        $report = $reportService->generateReport($indicators, $symbol, $news_data);

        $dates = $indicators['dates'];
        $opens = $indicators['opens'];
        $highs = $indicators['highs'];
        $lows = $indicators['lows'];
        $closes = $indicators['closes'];
        $volumes = $indicators['chart_volumes'];
        file_put_contents('chart_debug.log', json_encode($indicators['dates']));
        $latest_price = $indicators['latest_price'];
        $price_change = $indicators['price_change'];
        $price_change_pct = $indicators['price_change_pct'];

        $is_in_watchlist = false;
        $res_wCheck = $conn->query("SHOW TABLES LIKE 'watchlist'");
        if($res_wCheck && $res_wCheck->num_rows > 0) {
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

$pageTitle = ($display_name ?? $symbol_raw) . " Analysis | StoXVision";
$currentPage = "analysis";

// TradingView Lightweight Charts CDN
$extraHead = '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script><script src="https://cdn.jsdelivr.net/npm/chartjs-chart-financial@0.1.1/dist/chartjs-chart-financial.js"></script><script src="https://cdn.jsdelivr.net/npm/luxon@3.4.3/build/global/luxon.min.js"></script><script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-luxon@1.3.1/dist/chartjs-adapter-luxon.umd.min.js"></script>';

include "includes/header.php";
?>

<!-- Analysis Interface -->
<?php if (isset($apiError)): ?>
    <div class="max-w-xl mx-auto py-12">
        <div class="glass-panel p-12 rounded-[40px] text-center border-red-500/20">
            <div class="w-20 h-20 bg-red-500/10 rounded-full flex items-center justify-center mx-auto mb-6 text-red-500 text-3xl">
                <i class="fas fa-plug-circle-xmark"></i>
            </div>
            <h2 class="text-2xl font-black text-white mb-2">Data Stream Interrupted</h2>
            <p class="text-slate-400 mb-8"><?php echo $apiError; ?></p>
            <div class="flex gap-4 justify-center">
                <form method="POST" action="analyze.php">
                    <input type="hidden" name="symbol" value="<?php echo htmlspecialchars($symbol_raw); ?>">
                    <button type="submit" class="bg-primary text-dark font-bold px-8 py-3 rounded-2xl hover:scale-105 transition-transform">Retry analysis</button>
                </form>
                <a href="dashboard.php" class="bg-white/5 text-white font-bold px-8 py-3 rounded-2xl hover:bg-white/10 transition-colors border border-white/5">Find another</a>
            </div>
        </div>
    </div>
<?php else: ?>

<div class="mb-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6 animate-in fade-in slide-in-from-top-4 duration-700">
    <div>
        <div class="flex items-center gap-3 mb-2">
            <span class="px-3 py-1 bg-primary/10 text-primary text-[10px] font-black tracking-widest uppercase rounded-lg border border-primary/20"><?php echo $symbol; ?></span>
            <span class="px-3 py-1 <?php echo $report['market_overview']['trend_class'] === 'up' ? 'bg-secondary/10 text-secondary border-secondary/20' : 'bg-red-500/10 text-red-400 border-red-500/20'; ?> text-[10px] font-black tracking-widest uppercase rounded-lg border italic">
                <?php echo $report['market_overview']['current_trend']; ?>
            </span>
        </div>
        <h1 class="text-4xl md:text-5xl font-black text-white tracking-tighter"><?php echo htmlspecialchars($display_name); ?></h1>
    </div>
    
    <div class="flex flex-col items-end">
        <div class="text-4xl font-black text-white tracking-tighter">₹<?php echo number_format($latest_price, 2); ?></div>
        <div class="flex items-center gap-2 font-bold <?php echo ($price_change >= 0) ? 'text-secondary' : 'text-red-400'; ?>">
            <i class="fas fa-caret-<?php echo ($price_change >= 0) ? 'up' : 'down'; ?>"></i>
            <span><?php echo ($price_change >= 0) ? '+' : ''; ?><?php echo number_format($price_change, 2); ?> (<?php echo number_format($price_change_pct, 2); ?>%)</span>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
    
    <!-- Left Column: Primary Chart & Indicators -->
    <div class="xl:col-span-2 space-y-8">
        
        <!-- Interactive Chart Card -->
        <div class="glass-panel p-2 rounded-[32px] overflow-hidden border-white/10 shadow-2xl">
            <div class="p-6 flex justify-between items-center">
                <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                    <i class="fas fa-chart-line text-primary"></i> Professional Grade Technicals
                </h3>
                <div class="flex gap-2">
                    <button class="px-4 py-2 bg-white/5 text-white text-xs font-bold rounded-xl border border-white/5 hover:bg-white/10 transition-all active:scale-95">1D</button>
                    <button class="px-4 py-2 bg-primary/20 text-primary text-xs font-bold rounded-xl border border-primary/20">All Time</button>
                </div>
            </div>
            <div id="tradingview_chart" class="w-full rounded-2xl" style="height: 500px; min-height: 500px;"></div>
        </div>

        <!-- Key Indicators Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <?php
            $stats = [
                ['RSI (14)', $report['technical_analysis']['rsi_value'], 'fas fa-gauge-high', 'text-primary'],
                ['EMA 20', '₹'.round($indicators['ema_20'],2), 'fas fa-wave-square', 'text-secondary'],
                ['Support', '₹'.$report['technical_analysis']['support'], 'fas fa-shield-halved', 'text-emerald-400'],
                ['Resistance', '₹'.$report['technical_analysis']['resistance'], 'fas fa-fire-flame-curved', 'text-red-400'],
            ];
            foreach($stats as $s): ?>
            <div class="glass-panel p-6 rounded-3xl border-transparent hover:border-white/10 transition-all group">
                <div class="flex items-center gap-3 mb-2">
                    <i class="<?php echo $s[2]; ?> text-xs <?php echo $s[3]; ?>"></i>
                    <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest"><?php echo $s[0]; ?></span>
                </div>
                <div class="text-xl font-black text-white group-hover:scale-105 transition-transform origin-left"><?php echo $s[1]; ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Right Column: AI Predictions & Fundamentals -->
    <div class="space-y-8">
        
        <!-- AI Alpha Panel -->
        <div class="bg-gradient-to-br from-primary/20 to-blue-600/10 border border-primary/20 p-8 rounded-[40px] relative overflow-hidden group">
            <div class="absolute -top-10 -right-10 text-9xl text-primary/5 transform rotate-12 group-hover:rotate-45 transition-transform duration-1000">
                <i class="fas fa-microchip"></i>
            </div>
            
            <h3 class="text-xl font-black text-white mb-6 flex items-center gap-3">
                <i class="fas fa-brain text-primary"></i> Neural Prediction
            </h3>
            
            <div class="mb-8">
                <div class="flex justify-between items-end mb-2">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Prediction Confidence</span>
                    <span class="text-3xl font-black text-primary"><?php echo $report['ai_prediction']['confidence']; ?>%</span>
                </div>
                <div class="w-full h-3 bg-white/5 rounded-full overflow-hidden border border-white/5">
                    <div class="h-full bg-gradient-to-r from-primary to-blue-400 rounded-full transition-all duration-1000" style="width: <?php echo $report['ai_prediction']['confidence']; ?>%"></div>
                </div>
            </div>

            <div class="p-6 bg-white/5 rounded-3xl border border-white/10 mb-6">
                <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Projected Direction</div>
                <div class="text-3xl font-black <?php echo strtolower($report['ai_prediction']['predicted_direction']) === 'up' ? 'text-secondary' : 'text-red-400'; ?> flex items-center gap-3">
                    <?php echo strtoupper($report['ai_prediction']['predicted_direction']); ?>
                    <i class="fas fa-arrow-trend-<?php echo strtolower($report['ai_prediction']['predicted_direction']) === 'up' ? 'up' : 'down'; ?>"></i>
                </div>
            </div>

            <div class="text-sm text-slate-300 italic leading-relaxed">
                "<?php echo $report['ai_prediction']['short_term']; ?>"
            </div>
        </div>

        <!-- Trading Plan -->
        <div class="glass-panel p-8 rounded-[40px]">
             <h3 class="text-xl font-black text-white mb-8">Strategic Execution</h3>
             
             <div class="space-y-4 mb-8">
                 <div class="flex justify-between items-center p-4 rounded-2xl bg-white/[0.02] border border-white/5">
                     <span class="text-xs font-bold text-slate-500 uppercase">Target</span>
                     <span class="text-lg font-black text-secondary">₹<?php echo $report['trade_plan']['target']; ?></span>
                 </div>
                 <div class="flex justify-between items-center p-4 rounded-2xl bg-white/[0.02] border border-white/5">
                     <span class="text-xs font-bold text-slate-500 uppercase">Stop Loss</span>
                     <span class="text-lg font-black text-red-400">₹<?php echo $report['trade_plan']['stop_loss']; ?></span>
                 </div>
                 <div class="flex justify-between items-center p-4 rounded-2xl bg-white/[0.02] border border-white/5">
                     <span class="text-xs font-bold text-slate-500 uppercase">Entry</span>
                     <span class="text-sm font-bold text-white"><?php echo $report['trade_plan']['entry_zone']; ?></span>
                 </div>
             </div>

             <div class="px-6 py-4 bg-primary text-dark rounded-2xl text-center font-black uppercase tracking-widest text-sm shadow-xl shadow-primary/20">
                 <?php echo $report['trade_plan']['recommendation']; ?>
             </div>
        </div>

        <!-- Watchlist Action -->
        <button id="watchlistBtn" onclick="toggleWatchlist('<?php echo $symbol; ?>')" 
            class="w-full py-5 rounded-[32px] border-2 border-white/10 text-white font-black uppercase tracking-widest text-xs flex items-center justify-center gap-3 hover:bg-white/5 transition-all active:scale-95 group">
            <i class="<?php echo $is_in_watchlist ? 'fas' : 'far'; ?> fa-bookmark text-primary text-sm group-hover:scale-125 transition-transform"></i>
            <span><?php echo $is_in_watchlist ? 'Remove from Portfolio' : 'Add to Portfolio'; ?></span>
        </button>

    </div>
</div>

<!-- News Sentiment Section -->
<div class="mt-12 glass-panel p-10 rounded-[48px] border-white/5">
    <div class="flex items-center gap-4 mb-10">
        <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 flex items-center justify-center text-emerald-400 text-xl border border-emerald-500/20">
            <i class="fas fa-rss"></i>
        </div>
        <div>
            <h3 class="text-2xl font-black text-white">Sentiment Scraper</h3>
            <p class="text-slate-500 text-sm">Aggregated news data and social momentum analysis.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        <div class="space-y-6">
            <?php if(!empty($report['sentiment_insight']['news_articles'])): ?>
                <?php foreach($report['sentiment_insight']['news_articles'] as $article): ?>
                    <a href="<?php echo $article['url']; ?>" target="_blank" class="block p-6 rounded-3xl bg-white/[0.02] border border-white/5 hover:border-primary/30 hover:bg-white/[0.04] transition-all group">
                        <div class="flex justify-between items-start gap-4">
                            <h4 class="font-bold text-white group-hover:text-primary transition-colors leading-relaxed"><?php echo $article['title']; ?></h4>
                            <i class="fas fa-arrow-up-right-from-square text-xs text-slate-600 group-hover:text-primary"></i>
                        </div>
                        <div class="mt-3 text-[10px] text-slate-500 font-black uppercase tracking-widest">Financial Times • 2h ago</div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="p-10 rounded-3xl bg-white/[0.02] border border-dashed border-white/10 text-center">
                    <p class="text-slate-500 italic">No recent news cycles detected for this symbol.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="bg-white/5 p-8 rounded-[40px] border border-white/10 h-fit">
            <h4 class="font-black text-white uppercase tracking-widest text-xs mb-6 text-slate-400">Market Mood Matrix</h4>
            <div class="flex items-center gap-8 mb-8">
                <div class="w-24 h-24 rounded-full border-8 border-secondary flex items-center justify-center text-2xl font-black text-secondary">
                    <?php echo $report['sentiment_insight']['news_sentiment'] === 'Positive' ? 'Bull' : 'Bear'; ?>
                </div>
                <div>
                    <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Overall Tone</div>
                    <div class="text-2xl font-black text-white"><?php echo $report['sentiment_insight']['overall_market_mood']; ?></div>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="p-4 bg-white/5 rounded-2xl border border-white/5">
                    <div class="text-[9px] font-black text-slate-500 uppercase mb-1">Retail Flow</div>
                    <div class="text-sm font-bold text-white italic">High Volatility</div>
                </div>
                <div class="p-4 bg-white/5 rounded-2xl border border-white/5">
                    <div class="text-[9px] font-black text-slate-500 uppercase mb-1">Institutional</div>
                    <div class="text-sm font-bold text-white italic">Accumulation</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    // --- PHP Data ---
    const phpDates  = <?php echo json_encode(array_values($dates)); ?>;
    const phpOpens  = <?php echo json_encode(array_values($indicators['opens'])); ?>;
    const phpHighs  = <?php echo json_encode(array_values($indicators['highs'])); ?>;
    const phpLows   = <?php echo json_encode(array_values($indicators['lows'])); ?>;
    const phpCloses = <?php echo json_encode(array_values($indicators['closes'])); ?>;
    const phpVols   = <?php echo json_encode(array_values($indicators['chart_volumes'])); ?>;

    function buildCharts() {
        const container = document.getElementById('tradingview_chart');
        if (!container) return;

        // Validate we have data
        if (!phpDates || phpDates.length === 0) {
            container.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:#64748b;font-weight:700;">No chart data available.</div>';
            return;
        }

        // Build dataset — Chart.js financial expects {x, o, h, l, c}
        const candleData = phpDates.map((d, i) => ({
            x: new Date(d).getTime(),
            o: parseFloat(phpOpens[i])  || 0,
            h: parseFloat(phpHighs[i])  || 0,
            l: parseFloat(phpLows[i])   || 0,
            c: parseFloat(phpCloses[i]) || 0
        })).filter(d => d.o && d.h && d.l && d.c);

        const volColors = candleData.map(d => d.c >= d.o ? 'rgba(16,185,129,0.5)' : 'rgba(239,68,68,0.5)');
        const volData   = candleData.map((d, i) => ({ x: d.x, y: phpVols[i] || 0 }));

        // Replace container with two stacked canvases
        container.innerHTML = '';
        container.style.display = 'flex';
        container.style.flexDirection = 'column';
        container.style.gap = '4px';

        const candleWrap = document.createElement('div');
        candleWrap.style.cssText = 'flex:1;position:relative;min-height:0;';
        const volWrap = document.createElement('div');
        volWrap.style.cssText = 'height:80px;position:relative;';

        const candleCanvas = document.createElement('canvas');
        const volCanvas    = document.createElement('canvas');
        candleWrap.appendChild(candleCanvas);
        volWrap.appendChild(volCanvas);
        container.appendChild(candleWrap);
        container.appendChild(volWrap);

        const commonScales = {
            x: {
                type: 'time',
                time: { unit: 'day', tooltipFormat: 'dd MMM yyyy' },
                grid: { color: 'rgba(255,255,255,0.05)' },
                ticks: { color: '#64748b', maxRotation: 0, autoSkip: true, maxTicksLimit: 8 }
            }
        };

        // Candlestick chart
        new Chart(candleCanvas, {
            type: 'candlestick',
            data: {
                datasets: [{
                    label: '<?php echo addslashes($display_name); ?>',
                    data: candleData,
                    color: {
                        up:   '#10b981',
                        down: '#ef4444',
                        unchanged: '#94a3b8'
                    },
                    borderColor: {
                        up:   '#10b981',
                        down: '#ef4444',
                        unchanged: '#94a3b8'
                    }
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                const d = ctx.raw;
                                return [
                                    'O: ₹' + d.o.toFixed(2),
                                    'H: ₹' + d.h.toFixed(2),
                                    'L: ₹' + d.l.toFixed(2),
                                    'C: ₹' + d.c.toFixed(2)
                                ];
                            }
                        },
                        backgroundColor: 'rgba(15,23,42,0.9)',
                        borderColor: 'rgba(255,255,255,0.1)',
                        borderWidth: 1,
                        titleColor: '#94a3b8',
                        bodyColor: '#fff',
                        padding: 12,
                        cornerRadius: 12
                    }
                },
                scales: {
                    ...commonScales,
                    y: {
                        position: 'right',
                        grid: { color: 'rgba(255,255,255,0.05)' },
                        ticks: {
                            color: '#64748b',
                            callback: v => '₹' + v.toFixed(0)
                        }
                    }
                }
            }
        });

        // Volume bar chart
        new Chart(volCanvas, {
            type: 'bar',
            data: {
                datasets: [{
                    label: 'Volume',
                    data: volData,
                    backgroundColor: volColors,
                    borderWidth: 0,
                    borderRadius: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                plugins: { legend: { display: false }, tooltip: { enabled: false } },
                scales: {
                    ...commonScales,
                    y: {
                        position: 'right',
                        grid: { display: false },
                        ticks: {
                            color: '#64748b',
                            maxTicksLimit: 3,
                            callback: v => v >= 1e6 ? (v/1e6).toFixed(1)+'M' : v >= 1e3 ? (v/1e3).toFixed(0)+'K' : v
                        }
                    }
                }
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', buildCharts);
    } else {
        buildCharts();
    }
})();

async function toggleWatchlist(symbol) {
    const btn = document.getElementById('watchlistBtn');
    const icon = btn.querySelector('i');
    const label = btn.querySelector('span');
    
    btn.disabled = true;
    try {
        const formData = new FormData();
        formData.append('symbol', symbol);
        const res = await fetch('api/toggle_watchlist.php', { method: 'POST', body: formData });
        const data = await res.json();
        
        if (data.status === 'success') {
            if (data.action === 'added') {
                icon.className = 'fas fa-bookmark text-primary text-sm group-hover:scale-125 transition-transform';
                label.innerText = 'Remove from Portfolio';
            } else {
                icon.className = 'far fa-bookmark text-primary text-sm group-hover:scale-125 transition-transform';
                label.innerText = 'Add to Portfolio';
            }
        }
    } catch (e) {
        console.error(e);
    } finally {
        btn.disabled = false;
    }
}
</script>

<?php endif; ?>

<?php include "includes/footer.php"; ?>
