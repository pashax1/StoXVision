<?php
include "config/db.php";
include "includes/auth_check.php";

$pageTitle = "AI Mode | StoXVision";
$currentPage = "ai_mode";

include "includes/header.php";
?>

<div style="text-align: center; padding: 100px 20px; animation: fadeIn 0.8s ease-out;">
    <div style="font-size: 5rem; color: var(--primary); margin-bottom: 30px; filter: drop-shadow(0 0 20px var(--primary-glow));">
        <i class="fas fa-robot"></i>
    </div>
    <h1 style="font-size: 3rem; font-weight: 900; margin-bottom: 15px;">Neural Engine Offline</h1>
    <p style="color: var(--text-secondary); font-size: 1.2rem; max-width: 600px; margin: 0 auto 40px;">
        We are currently calibrating the advanced AI models for deep market sentiment analysis. This feature will be available shortly.
    </p>
    
    <div style="display: inline-flex; align-items: center; gap: 15px; background: var(--glass); padding: 15px 30px; border-radius: 50px; border: 1px solid var(--glass-border);">
        <div class="pulse" style="width: 12px; height: 12px; background: var(--secondary); border-radius: 50%; box-shadow: 0 0 10px var(--secondary);"></div>
        <span style="font-weight: 600; font-size: 0.9rem; letter-spacing: 1px;">DEVELOPMENT IN PROGRESS</span>
    </div>
    <br><br>
    <a href="dashboard.php" class="btn btn-primary" style="display: inline-flex; width: auto;">Return to Safety</a>
</div>

<style>
@keyframes pulse {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.5); opacity: 0.5; }
    100% { transform: scale(1); opacity: 1; }
}
.pulse {
    animation: pulse 2s infinite;
}
</style>

<?php include "includes/footer.php"; ?>
