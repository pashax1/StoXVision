<?php
$currentPage = $currentPage ?? '';
$header_base = (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) ? '../' : './';
$logo_link = (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) ? 'index.php' : 'dashboard.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : "StoXVision AI"; ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#38bdf8',
                        secondary: '#10b981',
                        accent: '#f59e0b',
                        dark: '#020617',
                        glass: 'rgba(255, 255, 255, 0.03)',
                        'glass-border': 'rgba(255, 255, 255, 0.1)',
                    }
                }
            }
        }
    </script>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;900&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS Fallback -->
    <link rel="stylesheet" href="<?php echo $header_base; ?>assets/css/style.css">
    
    <?php if(isset($extraHead)) echo $extraHead; ?>
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #020617; color: #f8fafc; }
        h1, h2, h3, h4, .nav-logo { font-family: 'Outfit', sans-serif; }
        .glass-panel { background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.05); backdrop-filter: blur(12px); }
    </style>
</head>
<body class="bg-dark text-slate-200">

<nav class="sticky top-0 z-50 glass-panel border-b border-white/5 px-6 py-4">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
        <a href="<?php echo $header_base . $logo_link; ?>" class="text-2xl font-black tracking-tighter text-white flex items-center gap-2">
            <i class="fas fa-eye text-primary"></i> StoXVision
        </a>
        
        <div class="hidden md:flex items-center gap-8">
            <a href="<?php echo $header_base; ?>dashboard.php" class="flex items-center gap-2 text-sm font-semibold transition-colors <?php echo ($currentPage == 'dashboard') ? 'text-primary' : 'text-slate-400 hover:text-white'; ?>">
                <i class="fas fa-th-large"></i> Dashboard
            </a>
            <a href="<?php echo $header_base; ?>portfolio.php" class="flex items-center gap-2 text-sm font-semibold transition-colors <?php echo ($currentPage == 'portfolio') ? 'text-primary' : 'text-slate-400 hover:text-white'; ?>">
                <i class="fas fa-briefcase"></i> Portfolio
            </a>
            <a href="<?php echo $header_base; ?>ai_mode.php" class="flex items-center gap-2 text-sm font-semibold transition-colors <?php echo ($currentPage == 'ai_mode') ? 'text-primary' : 'text-slate-400 hover:text-white'; ?>">
                <i class="fas fa-robot"></i> AI Mode
            </a>
        </div>
        
        <div class="flex items-center gap-6">
            <a href="<?php echo $header_base; ?>profile.php" class="flex items-center gap-3 group">
                <div class="text-right hidden sm:block">
                    <div class="text-xs font-bold text-white group-hover:text-primary transition-colors"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></div>
                    <div class="text-[10px] text-slate-500 uppercase tracking-widest">Active</div>
                </div>
                <?php 
                $profile_pic = $_SESSION['profile_pic'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['user_name'] ?? 'User') . '&background=0ea5e9&color=fff';
                ?>
                <img src="<?php echo $profile_pic; ?>" alt="Profile" class="w-10 h-10 rounded-xl border-2 border-white/10 group-hover:border-primary/50 transition-all">
            </a>
            
            <a href="<?php echo $header_base; ?>logout.php" class="text-slate-500 hover:text-red-400 transition-colors">
                <i class="fas fa-sign-out-alt text-lg"></i>
            </a>
        </div>
    </div>
</nav>

<div class="max-w-7xl mx-auto px-6 py-10 min-h-screen">
