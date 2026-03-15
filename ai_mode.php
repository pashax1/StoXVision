<?php
include "config/db.php";
include "includes/auth_check.php";

$pageTitle = "Neural Engine | StoXVision AI";
$currentPage = "ai_mode";

include "includes/header.php";
?>

<div class="flex flex-col items-center justify-center py-32 text-center space-y-10 animate-in fade-in slide-in-from-bottom-5 duration-700">
    <div class="relative">
        <div class="absolute inset-0 bg-primary/20 blur-[60px] rounded-full animate-pulse"></div>
        <div class="relative w-32 h-32 bg-white/5 border border-white/10 rounded-[40px] flex items-center justify-center text-6xl text-primary shadow-2xl">
            <i class="fas fa-brain animate-bounce"></i>
        </div>
    </div>

    <div class="space-y-4">
        <h1 class="text-6xl font-black text-white tracking-tighter italic">NEURAL ENGINE <span class="text-primary">OFFLINE</span></h1>
        <p class="max-w-xl mx-auto text-slate-500 font-bold text-lg leading-relaxed uppercase tracking-widest">
            Calibrating advanced deep-learning models for sentiment synthesis. Interface unavailable during synaptic reset.
        </p>
    </div>

    <div class="flex items-center gap-4 px-8 py-4 bg-white/[0.02] border border-white/5 rounded-full">
        <span class="relative flex h-3 w-3">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-secondary opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3 w-3 bg-secondary"></span>
        </span>
        <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em]">Synaptic Calibration In Progress</span>
    </div>

    <div class="pt-10">
        <a href="dashboard.php" class="inline-flex items-center gap-3 px-10 py-5 bg-white text-dark font-black rounded-2xl hover:scale-105 active:scale-95 transition-all shadow-2xl">
            RETURN TO COMMAND CENTER <i class="fas fa-arrow-right"></i>
        </a>
    </div>
</div>

<?php include "includes/footer.php"; ?>
