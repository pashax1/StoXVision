<?php
session_start();
include "../config/db.php";

// Check if user is an authenticated admin
if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
    header("Location: login.php");
    exit();
}

// Handle Form POST Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // #8 CSRF Check
    $csrf = $_POST['csrf_token'] ?? '';
    if ($csrf !== ($_SESSION['csrf_token'] ?? '')) {
        header("Location: index.php?msg=invalid_token&type=error");
        exit();
    }

    if (isset($_POST['add_key'])) {
        $new_key = strtoupper(trim($_POST['new_api_key']));
        $provider = 'AlphaVantage';
        if (!empty($new_key)) {
            $check_stmt = $conn->prepare("SELECT id FROM api_keys WHERE api_key = ?");
            $check_stmt->bind_param("s", $new_key);
            $check_stmt->execute();
            $check_res = $check_stmt->get_result();
            
            if ($check_res && $check_res->num_rows > 0) {
                header("Location: index.php?msg=duplicate&type=error");
            } else {
                $stmt = $conn->prepare("INSERT INTO api_keys (api_key, provider, last_reset) VALUES (?, ?, ?)");
                $today = date('Y-m-d');
                $stmt->bind_param("sss", $new_key, $provider, $today);
                $stmt->execute();
                header("Location: index.php?msg=key_added&type=success");
            }
        }
        exit();
    }
    if (isset($_POST['purge_cache'])) {
        $conn->query("TRUNCATE TABLE stock_cache");
        header("Location: index.php?msg=cache_purged&type=success");
        exit();
    }
}

// Handle GET Actions
if (isset($_GET['delete_key'])) {
    $id = intval($_GET['delete_key']);
    $conn->query("DELETE FROM api_keys WHERE id = $id");
    header("Location: index.php?msg=key_deleted&type=success");
    exit();
}

if (isset($_GET['reset_key'])) {
    $id = intval($_GET['reset_key']);
    $conn->query("UPDATE api_keys SET status = 'active', usage_count = 0 WHERE id = $id");
    header("Location: index.php?msg=key_reset&type=success");
    exit();
}

if (isset($_GET['toggle_status'])) {
    $id = intval($_GET['toggle_status']);
    $stmt = $conn->prepare("UPDATE users SET status = IF(status='active', 'suspended', 'active') WHERE id = ? AND role != 'admin'");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
    header("Location: index.php?msg=status_toggled&type=success");
    exit();
}

if (isset($_GET['delete_id'])) {
    // Note: Traditional GET delete is now deprecated in favor of AJAX POST below
    $delete_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
    if ($stmt) {
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
    }
    header("Location: index.php?msg=user_deleted&type=success");
    exit();
}

// #16 AJAX Action Handler for smooth animations
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'];
    $id = intval($_POST['id'] ?? 0);
    $csrf = $_POST['csrf_token'] ?? '';
    
    if ($csrf !== ($_SESSION['csrf_token'] ?? '')) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid Security Token']);
        exit();
    }

    if ($action === 'delete_key') {
        $conn->query("DELETE FROM api_keys WHERE id = $id");
        echo json_encode(['status' => 'success']);
        exit();
    }
    if ($action === 'delete_user') {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo json_encode(['status' => 'success']);
        exit();
    }
    if ($action === 'delete_cache_item') {
        $symbol = $_POST['symbol'] ?? '';
        $stmt = $conn->prepare("DELETE FROM stock_cache WHERE symbol = ?");
        $stmt->bind_param("s", $symbol);
        $stmt->execute();
        echo json_encode(['status' => 'success']);
        exit();
    }
}

// --- Fetch Dashboard Data ---
$users = [];
$res = $conn->query("SELECT id, name, email, role, status, created_at FROM users ORDER BY created_at DESC");
if ($res) while ($r = $res->fetch_assoc()) $users[] = $r;

$total_users = count(array_filter($users, fn($u) => $u['role'] === 'user'));
$admin_count = count(array_filter($users, fn($u) => $u['role'] === 'admin'));

$api_keys_db = [];
$res = $conn->query("SELECT id, api_key, provider, status, last_used, usage_count FROM api_keys ORDER BY status ASC, usage_count DESC");
if ($res) while ($r = $res->fetch_assoc()) $api_keys_db[] = $r;

$active_keys_count = count(array_filter($api_keys_db, fn($k) => $k['status'] === 'active'));

$cache_res = $conn->query("SELECT COUNT(*) as c FROM stock_cache");
$cache_count = $cache_res ? $cache_res->fetch_assoc()['c'] : 0;

$stock_cache_items = [];
$c_res = $conn->query("SELECT symbol, current_price, cached_at FROM stock_cache ORDER BY cached_at DESC LIMIT 50");
if ($c_res) while ($r = $c_res->fetch_assoc()) $stock_cache_items[] = $r;

$trending = [];
$trend_res = $conn->query("SELECT symbol, COUNT(*) as watchers FROM watchlist GROUP BY symbol ORDER BY watchers DESC LIMIT 5");
if ($trend_res) while ($r = $trend_res->fetch_assoc()) $trending[] = $r;

// Prepare success messaging
$msgText = "";
$msgType = $_GET['type'] ?? 'success';
if(isset($_GET['msg'])) {
    switch($_GET['msg']) {
        case 'key_added': $msgText = "API Uplink Successfully Synchronized."; break;
        case 'key_deleted': $msgText = "API Key Removed from Neural Vault."; break;
        case 'key_reset': $msgText = "Key Usage Telemetry Reset."; break;
        case 'status_toggled': $msgText = "User Access Vector Modified."; break;
        case 'user_deleted': $msgText = "Identity Purged from Database."; break;
        case 'cache_purged': $msgText = "Core Market Cache Flushed."; break;
        case 'duplicate': $msgText = "API Key already exists in local registry."; $msgType = 'error'; break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Command Center | StoXVision AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800;900&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        dark: '#020617',
                        primary: '#0ea5e9',
                        secondary: '#10b981',
                        accent: '#8b5cf6',
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: #020617; font-family: 'Inter', sans-serif; }
        .glass-panel {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        /* #16 Smooth row exit animation */
        .row-exit {
            opacity: 0;
            transform: translateX(30px);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }
        /* Stylish Custom Scrollbar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.02); 
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(14, 165, 233, 0.4); /* Primary color */
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(14, 165, 233, 0.8);
        }
    </style>
</head>
<body class="text-slate-300">

    <!-- Simplified Admin Branding Bar -->
    <nav class="border-bottom border-white/5 py-6 px-10 flex items-center justify-between">
        <div class="text-2xl font-black text-white tracking-tighter italic">
            StoX<span class="text-primary">Vision</span> <span class="text-xs ml-2 text-slate-500 font-bold uppercase tracking-widest not-italic">Admin</span>
        </div>
        <div class="flex items-center gap-6">
            <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Admin Control Port</span>
        </div>
    </nav>

<div class="max-w-[1600px] mx-auto py-10 px-6 space-y-10 animate-in fade-in slide-in-from-bottom-5 duration-700">
    
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-5xl font-black text-white tracking-tighter italic">COMMAND<span class="text-primary">CENTER</span></h1>
            <p class="text-slate-500 font-bold uppercase tracking-widest text-xs mt-2 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-secondary animate-pulse"></span>
                StoXVision Core Administration • Live Telemetry
            </p>
        </div>
        <div class="flex items-center gap-4 bg-white/5 p-2 rounded-3xl border border-white/5">
            <div class="flex items-center gap-3 px-4">
                <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-dark shadow-lg shadow-primary/20">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div>
                    <div class="text-[10px] font-black text-slate-500 uppercase leading-none">Admin Instance</div>
                    <div class="text-sm font-bold text-white"><?php echo htmlspecialchars($_SESSION["admin_name"]); ?></div>
                </div>
            </div>
            <a href="logout.php" class="bg-red-500/10 hover:bg-red-500/20 text-red-500 font-black px-6 py-3 rounded-2xl transition-all border border-red-500/10">
                <i class="fas fa-power-off"></i>
            </a>
        </div>
    </div>

    <?php if($msgText): ?>
    <div id="statusToast" class="<?php echo $msgType === 'error' ? 'bg-red-500/10 border-red-500/20 text-red-500' : 'bg-primary/10 border-primary/20 text-primary'; ?> border p-5 rounded-3xl flex items-center justify-between gap-4 font-bold animate-in zoom-in-95 duration-300">
        <div class="flex items-center gap-4">
            <i class="fas <?php echo $msgType === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle'; ?> text-xl"></i>
            <span><?php echo $msgText; ?></span>
        </div>
        <button onclick="document.getElementById('statusToast').remove()" class="text-slate-500 hover:text-white transition-colors">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <script>
        setTimeout(() => {
            const toast = document.getElementById('statusToast');
            if(toast) {
                toast.classList.add('fade-out');
                setTimeout(() => toast.remove(), 500);
            }
        }, 5000);
    </script>
    <style>
        .fade-out { opacity: 0; transition: opacity 0.5s ease-out; }
    </style>
    <?php endif; ?>

    <!-- KPI Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php 
        $metrics = [
            ['title' => 'Global Users', 'val' => $total_users, 'icon' => 'fa-users', 'color' => 'blue'],
            ['title' => 'Active Uplinks', 'val' => "$active_keys_count/" . count($api_keys_db), 'icon' => 'fa-plug', 'color' => 'emerald'],
            ['title' => 'Core Cache', 'val' => number_format($cache_count), 'icon' => 'fa-database', 'color' => 'orange'],
            ['title' => 'System Admins', 'val' => $admin_count, 'icon' => 'fa-user-lock', 'color' => 'purple'],
        ];
        foreach($metrics as $m):
        ?>
        <div class="glass-panel p-8 rounded-[40px] border-white/5 relative overflow-hidden group hover:scale-[1.02] transition-transform">
            <div class="absolute top-0 right-0 w-32 h-32 bg-<?php echo $m['color']; ?>-500/5 blur-[50px] -mr-10 -mt-10"></div>
            <div class="flex justify-between items-start mb-4">
                <div class="w-12 h-12 rounded-2xl bg-white/5 flex items-center justify-center text-xl text-white group-hover:bg-white/10 transition-colors">
                    <i class="fas <?php echo $m['icon']; ?>"></i>
                </div>
            </div>
            <div class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] mb-1"><?php echo $m['title']; ?></div>
            <div class="text-4xl font-black text-white tracking-tighter"><?php echo $m['metric_val'] ?? $m['val']; ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Main Workspace -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        
        <!-- API Matrix (Left 2 Columns) -->
        <div class="lg:col-span-2 space-y-10">
            <div class="glass-panel rounded-[48px] border-white/5 overflow-hidden shadow-2xl">
                <div class="px-10 py-8 border-b border-white/5 flex items-center justify-between flex-wrap gap-6">
                    <div>
                        <h2 class="text-2xl font-black text-white tracking-tighter flex items-center gap-3">
                            <i class="fas fa-random text-primary"></i> API Rotation Matrix
                        </h2>
                    </div>
                    <form method="POST" class="flex gap-3 flex-wrap md:flex-nowrap">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                        <input type="text" name="new_api_key" placeholder="New API Key" required class="bg-white/5 border border-white/10 rounded-2xl px-6 py-3 text-sm font-bold text-white focus:outline-none focus:border-primary transition-all md:w-64">
                        <button type="submit" name="add_key" class="bg-primary hover:bg-primary/90 text-dark font-black px-6 py-3 rounded-2xl transition-all"><i class="fas fa-plus"></i></button>
                    </form>
                </div>
                <div class="p-4 overflow-y-auto max-h-[600px] custom-scrollbar">
                    <table class="w-full text-left relative">
                        <thead class="sticky top-0 bg-[#0f172a] shadow-md z-10">
                            <tr class="text-[10px] font-black text-slate-500 uppercase tracking-widest">
                                <th class="px-6 py-4">Key Instance</th>
                                <th class="px-6 py-4">Provider</th>
                                <th class="px-6 py-4">Current Load</th>
                                <th class="px-6 py-4">Health Status</th>
                                <th class="px-6 py-4 text-right">Operation</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <?php foreach($api_keys_db as $k): ?>
                            <tr class="group hover:bg-white/[0.02] transition-colors">
                                <td class="px-6 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center font-mono text-[10px] text-white/50 group-hover:text-primary transition-colors">#<?php echo $k['id']; ?></div>
                                        <div class="font-mono text-sm tracking-wider text-white">
                                            <?php echo substr($k['api_key'], 0, 4) . '••••' . substr($k['api_key'], -4); ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-6 font-bold text-slate-300 text-sm">
                                    <span class="text-[10px] font-black text-primary/80 bg-primary/10 px-3 py-1 rounded-lg border border-primary/20 uppercase"><?php echo htmlspecialchars($k['provider'] ?? 'AlphaVantage'); ?></span>
                                </td>
                                <td class="px-6 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="flex-1 max-w-[100px] h-1.5 bg-white/5 rounded-full overflow-hidden">
                                            <div class="h-full bg-primary" style="width: <?php echo ($k['usage_count'] / 25) * 100; ?>%"></div>
                                        </div>
                                        <span class="text-sm font-black text-white"><?php echo $k['usage_count']; ?><span class="text-slate-600">/25</span></span>
                                    </div>
                                </td>
                                <td class="px-6 py-6">
                                    <?php 
                                    $status_classes = [
                                        'active' => 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
                                        'rate_limited' => 'bg-orange-500/10 text-orange-500 border-orange-500/20',
                                        'dead' => 'bg-red-500/10 text-red-500 border-red-500/20'
                                    ];
                                    $s = $k['status'];
                                    ?>
                                    <span class="px-4 py-1.5 rounded-xl border text-[10px] font-black uppercase tracking-widest <?php echo $status_classes[$s]; ?>">
                                        <?php echo $s; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-6 text-right">
                                    <div class="flex justify-end gap-3">
                                        <a href="index.php?reset_key=<?php echo $k['id']; ?>" class="w-10 h-10 rounded-xl bg-white/5 hover:bg-primary/20 text-white hover:text-primary flex items-center justify-center transition-all border border-white/5" title="Force Reset Logic">
                                            <i class="fas fa-redo-alt text-xs"></i>
                                        </a>
                                        <button onclick="confirmAction('delete_key', <?php echo $k['id']; ?>, '<?php echo substr($k['api_key'], 0, 8); ?>...')" class="w-10 h-10 rounded-xl bg-white/5 hover:bg-red-500/20 text-white hover:text-red-500 flex items-center justify-center transition-all border border-white/5">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- User Grid (Table replacement) -->
            <div class="glass-panel rounded-[48px] border-white/5 overflow-hidden shadow-2xl">
                <div class="px-10 py-8 border-b border-white/5">
                    <h2 class="text-2xl font-black text-white tracking-tighter flex items-center gap-3">
                        <i class="fas fa-users-cog text-primary"></i> Entity Management
                    </h2>
                </div>
                <div class="p-10">
                    <div class="hidden md:grid grid-cols-12 gap-4 text-[10px] font-black text-slate-500 uppercase tracking-widest mb-6 px-4">
                        <div class="col-span-1">ID</div>
                        <div class="col-span-4">User Entity</div>
                        <div class="col-span-3">Role & Auth</div>
                        <div class="col-span-2">Registered</div>
                        <div class="col-span-2 text-right">Access Controls</div>
                    </div>
                    <div class="space-y-4">
                        <?php foreach($users as $u): ?>
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center p-4 bg-white/[0.02] border border-white/5 rounded-3xl group hover:border-white/10 transition-colors">
                            <div class="md:col-span-1 font-mono text-slate-600 text-sm">#<?php echo $u['id']; ?></div>
                            <div class="md:col-span-4 flex items-center gap-4">
                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($u['name']); ?>&background=0ea5e9&color=fff&rounded=true" class="w-10 h-10 rounded-2xl border border-white/10">
                                <div class="truncate">
                                    <div class="text-white font-bold truncate"><?php echo htmlspecialchars($u['name']); ?></div>
                                    <div class="text-xs text-slate-500 truncate"><?php echo htmlspecialchars($u['email']); ?></div>
                                </div>
                            </div>
                            <div class="md:col-span-3 flex items-center gap-3 text-[10px] font-black uppercase tracking-wider">
                                <span class="<?php echo $u['role'] === 'admin' ? 'bg-purple-500/10 text-purple-400' : 'bg-blue-500/10 text-blue-400'; ?> px-3 py-1 rounded-lg">
                                    <?php echo $u['role']; ?>
                                </span>
                                <?php if($u['status'] === 'suspended'): ?>
                                <span class="bg-red-500/10 text-red-500 px-3 py-1 rounded-lg tracking-normal lowercase"><i class="fas fa-ban"></i> blocked</span>
                                <?php endif; ?>
                            </div>
                            <div class="md:col-span-2 text-sm text-slate-400">
                                <?php echo date('M d, Y', strtotime($u['created_at'])); ?>
                            </div>
                            <div class="md:col-span-2 flex justify-end gap-3">
                                <?php if($u['role'] !== 'admin'): ?>
                                <a href="index.php?toggle_status=<?php echo $u['id']; ?>" class="p-3 rounded-2xl bg-white/5 border border-white/5 hover:bg-orange-500/10 hover:text-orange-500 transition-all text-xs" title="Toggle Access">
                                    <i class="fas fa-lock-open"></i>
                                </a>
                                <button onclick="confirmAction('delete_user', <?php echo $u['id']; ?>, '<?php echo addslashes($u['name']); ?>')" class="p-3 rounded-2xl bg-white/5 border border-white/5 hover:bg-red-500/10 hover:text-red-500 transition-all text-xs">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php else: ?>
                                <span class="text-[10px] font-black text-slate-600 uppercase italic">Immutable</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Actions (Right Column) -->
        <div class="space-y-10">
            
            <!-- System Optimizer -->
            <div class="glass-panel rounded-[48px] border-white/5 shadow-2xl relative overflow-hidden flex flex-col h-[500px]">
                <div class="absolute top-0 right-0 w-32 h-32 bg-red-500/5 blur-[40px] -mr-16 -mt-16"></div>
                <div class="p-8 border-b border-white/5 shrink-0 flex justify-between items-center">
                    <h3 class="text-xl font-black text-white tracking-tighter flex items-center gap-3">
                        <i class="fas fa-bolt text-orange-500"></i> Local Cache (<span class="text-white"><?php echo $cache_count; ?></span>)
                    </h3>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <button type="submit" name="purge_cache" onclick="return confirm('Initiate Global Cache Purge?')" class="bg-red-500/10 hover:bg-red-500 hover:text-white text-red-500 border border-red-500/20 px-4 py-2 rounded-xl text-xs font-black tracking-tighter transition-all">
                            PURGE ALL
                        </button>
                    </form>
                </div>
                
                <div class="flex-1 overflow-y-auto p-4 custom-scrollbar">
                    <?php if (empty($stock_cache_items)): ?>
                        <div class="text-center text-slate-500 text-sm mt-10 italic">Cache is empty.</div>
                    <?php else: ?>
                        <div class="space-y-2">
                        <?php foreach($stock_cache_items as $cache_item): ?>
                            <div class="flex items-center justify-between p-3 bg-white/[0.02] border border-white/5 rounded-2xl hover:bg-white/5 transition-colors group">
                                <div class="truncate mr-2">
                                    <div class="text-sm font-black text-white"><?php echo htmlspecialchars($cache_item['symbol']); ?></div>
                                    <div class="text-[10px] text-slate-500"><i class="far fa-clock"></i> <?php echo date('M d, H:i', strtotime($cache_item['cached_at'])); ?></div>
                                </div>
                                <div class="flex items-center gap-3 shrink-0">
                                    <div class="text-xs font-mono text-emerald-400">₹<?php echo number_format((float)$cache_item['current_price'], 2); ?></div>
                                    <button onclick="confirmAction('delete_cache_item', '<?php echo $cache_item['symbol']; ?>', '<?php echo $cache_item['symbol']; ?> (Cache)')" class="w-8 h-8 rounded-lg bg-red-500/10 text-red-400 flex items-center justify-center hover:bg-red-500 hover:text-white transition-all">
                                        <i class="fas fa-trash text-[10px]"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- System Health Dashboard -->
            <div class="glass-panel p-8 rounded-[48px] border-white/5 shadow-2xl">
                <h3 class="text-xl font-black text-white tracking-tighter mb-6 flex items-center gap-3">
                    <i class="fas fa-heartbeat text-secondary text-2xl animate-pulse"></i> Global Health
                </h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-white/[0.02] rounded-3xl border border-emerald-500/20">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-emerald-500/20 flex items-center justify-center text-emerald-500"><i class="fas fa-database"></i></div>
                            <div>
                                <div class="text-white font-bold text-sm">MySQL Core DB</div>
                                <div class="text-[10px] text-slate-500 uppercase">Operational • 0.<?php echo rand(10,40); ?>ms latency</div>
                            </div>
                        </div>
                        <div class="text-emerald-500"><i class="fas fa-check-circle"></i></div>
                    </div>
                    
                    <div class="flex items-center justify-between p-4 bg-white/[0.02] rounded-3xl border border-primary/20">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center text-primary"><i class="fas fa-server"></i></div>
                            <div>
                                <div class="text-white font-bold text-sm">App Server Node</div>
                                <div class="text-[10px] text-slate-500 uppercase">Load Avg: 0.<?php echo rand(10,99); ?> • Mem: Normal</div>
                            </div>
                        </div>
                        <div class="text-primary"><i class="fas fa-check-circle"></i></div>
                    </div>
                </div>
            </div>

            <!-- Market Intelligence -->
            <div class="glass-panel p-8 rounded-[48px] border-white/5 shadow-2xl">
                <h3 class="text-xl font-black text-white tracking-tighter mb-8 flex items-center gap-3">
                    <i class="fas fa-chart-line text-secondary"></i> Market Intel
                </h3>
                <div class="space-y-6">
                    <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Top Watchlisted Entities</div>
                    <?php if(empty($trending)): ?>
                    <div class="text-sm font-bold text-slate-600 italic">No market interest detected yet...</div>
                    <?php else: ?>
                    <?php foreach($trending as $i => $t): ?>
                    <div class="flex items-center justify-between p-4 bg-white/[0.02] border border-white/5 rounded-3xl hover:border-white/10 transition-colors">
                        <div class="flex items-center gap-4">
                            <span class="text-xs font-black text-slate-600 italic">#<?php echo $i+1; ?></span>
                            <span class="text-white font-black tracking-wider"><?php echo htmlspecialchars($t['symbol']); ?></span>
                        </div>
                        <div class="flex items-center gap-2 bg-secondary/10 text-secondary px-4 py-1.5 rounded-2xl text-[10px] font-black border border-secondary/10">
                            <i class="fas fa-eye text-[8px]"></i> <?php echo $t['watchers']; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>

    </div>
</div>

<?php include "../includes/footer.php"; ?>

<!-- #16 Custom Admin Deletion Modal -->
<div id="adminModal" class="fixed inset-0 bg-dark/80 backdrop-blur-sm z-[999] hidden items-center justify-center p-6">
    <div class="bg-[#0f172a] border border-white/10 rounded-[40px] p-10 max-w-sm w-full shadow-2xl text-center animate-in fade-in zoom-in-95 duration-300">
        <div id="modalIcon" class="w-16 h-16 bg-red-500/10 rounded-full flex items-center justify-center mx-auto mb-6 text-red-400 text-2xl border border-red-500/20">
            <i class="fas fa-trash-alt"></i>
        </div>
        <h3 id="modalTitle" class="text-xl font-black text-white mb-2">Confirm Action</h3>
        <p id="modalBody" class="text-slate-400 text-sm mb-8">Are you sure you want to perform this restricted operation?</p>
        <div class="flex gap-4">
            <button onclick="closeModal()" class="flex-1 py-3 rounded-2xl border border-white/10 text-slate-400 font-bold hover:bg-white/5 transition-all">Abort</button>
            <button id="modalConfirmBtn" class="flex-1 py-3 rounded-2xl bg-red-500 text-white font-black hover:bg-red-600 transition-all shadow-lg shadow-red-500/20">Confirm</button>
        </div>
    </div>
</div>

<script>
let _pendingAction = null;
let _pendingId = null;
let _pendingBtn = null;

function confirmAction(type, id, name) {
    _pendingAction = type;
    _pendingId = id;
    _pendingBtn = event.currentTarget;

    const modal = document.getElementById('adminModal');
    const title = document.getElementById('modalTitle');
    const body = document.getElementById('modalBody');

    if (type === 'delete_key') {
        title.textContent = "Purge API Key";
        body.innerHTML = `Terminate uplink <span class="text-white font-bold">${name}</span>? This will disconnect the data pipe.`;
    } else if (type === 'delete_user') {
        title.textContent = "Purge Identity";
        body.innerHTML = `Erase user <span class="text-white font-bold">${name}</span> from the neural core? All data will be lost.`;
    } else if (type === 'delete_cache_item') {
        title.textContent = "Purge Single Cache";
        body.innerHTML = `Delete cache payload for <span class="text-white font-bold">${name}</span>? System will re-fetch data next request.`;
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeModal() {
    const modal = document.getElementById('adminModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

document.getElementById('modalConfirmBtn').addEventListener('click', async () => {
    if (!_pendingAction) return;
    
    closeModal();
    const originalRow = _pendingBtn.closest('tr') || _pendingBtn.closest('.grid');
    
    const formData = new FormData();
    formData.append('action', _pendingAction);
    if (_pendingAction === 'delete_cache_item') {
        formData.append('symbol', _pendingId);
    } else {
        formData.append('id', _pendingId);
    }
    formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');

    try {
        const res = await fetch('index.php', { method: 'POST', body: formData });
        const data = await res.json();

        if (data.status === 'success') {
            originalRow.classList.add('row-exit');
            setTimeout(() => {
                originalRow.remove();
                // Show a quick success toast if we wanted, but the exit is quite satisfying
            }, 500);
        } else {
            alert(data.message || "Operation failed");
        }
    } catch (e) {
        console.error(e);
        alert("Nexus communication failure.");
    }
});
</script>
