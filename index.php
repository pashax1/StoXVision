<?php
include "config/db.php";

if(isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StoXVision | Next-Gen AI Market Analysis</title>
    <meta name="description" content="AI-powered stock analysis for the Indian Market. Real-time data, predictive modeling, and premium interface.">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;900&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Style -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        :root {
            --primary: #38bdf8;
            --primary-rgb: 56, 189, 248;
            --secondary: #10b981;
            --secondary-rgb: 16, 185, 129;
            --bg-dark: #020617;
            --purple-glow: rgba(139, 92, 246, 0.15);
        }

        body.landing {
            background: var(--bg-dark);
            overflow-x: hidden;
        }

        .gradient-sphere {
            position: fixed;
            width: 1000px;
            height: 1000px;
            border-radius: 50%;
            background: radial-gradient(circle, var(--purple-glow) 0%, transparent 70%);
            z-index: -1;
            top: -500px;
            right: -300px;
            pointer-events: none;
        }

        .hero-section {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 80px 20px;
            position: relative;
        }

        .hero-badge {
            background: rgba(56, 189, 248, 0.1);
            border: 1px solid rgba(56, 189, 248, 0.2);
            padding: 8px 16px;
            border-radius: 100px;
            color: var(--primary);
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 30px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            backdrop-filter: blur(10px);
            animation: fadeInUp 0.8s ease-out;
        }

        .hero-title {
            font-size: clamp(3rem, 10vw, 6rem);
            font-weight: 900;
            text-align: center;
            line-height: 1.1;
            margin-bottom: 25px;
            background: linear-gradient(180deg, #fff 0%, rgba(255,255,255,0.7) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: fadeInUp 1s ease-out 0.2s backwards;
        }

        .hero-title span {
            background: linear-gradient(90deg, #38bdf8, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-desc {
            max-width: 700px;
            text-align: center;
            font-size: clamp(1.1rem, 2vw, 1.4rem);
            color: #94a3b8;
            margin-bottom: 40px;
            line-height: 1.6;
            animation: fadeInUp 1s ease-out 0.4s backwards;
        }

        .hero-actions {
            display: flex;
            gap: 20px;
            animation: fadeInUp 1s ease-out 0.6s backwards;
        }

        .btn-premium {
            padding: 18px 40px;
            border-radius: 14px;
            font-size: 1.1rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-primary-glass {
            background: var(--primary);
            color: #000;
            box-shadow: 0 20px 40px -10px rgba(56, 189, 248, 0.5);
        }

        .btn-primary-glass:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 25px 50px -10px rgba(56, 189, 248, 0.7);
        }

        .btn-secondary-glass {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            backdrop-filter: blur(10px);
        }

        .btn-secondary-glass:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-5px);
        }

        .market-visual {
            margin-top: 80px;
            width: 100%;
            max-width: 1200px;
            height: 400px;
            background: linear-gradient(180deg, rgba(56, 189, 248, 0.05) 0%, transparent 100%);
            border: 1px solid rgba(56, 189, 248, 0.1);
            border-radius: 30px;
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            animation: fadeInUp 1.2s ease-out 0.8s backwards;
        }

        .visual-placeholder {
            color: rgba(255, 255, 255, 0.1);
            font-size: 5rem;
            font-weight: 900;
            letter-spacing: 10px;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Floating particles */
        .particles span {
            position: absolute;
            background: var(--primary);
            border-radius: 50%;
            filter: blur(2px);
            opacity: 0.3;
            animation: float 20s infinite linear;
        }

        @keyframes float {
            0% { transform: translateY(0) translateX(0); }
            50% { transform: translateY(-100px) translateX(100px); }
            100% { transform: translateY(0) translateX(0); }
        }
    </style>
</head>

<body class="landing">

<div class="gradient-sphere"></div>

<div class="particles">
    <span style="width: 10px; height: 10px; top: 20%; left: 10%; animation-delay: 0s;"></span>
    <span style="width: 15px; height: 15px; top: 60%; left: 80%; animation-delay: 2s;"></span>
    <span style="width: 8px; height: 8px; top: 10%; left: 70%; animation-delay: 4s;"></span>
    <span style="width: 12px; height: 12px; top: 80%; left: 20%; animation-delay: 6s;"></span>
</div>

<main class="hero-section">
    <div class="hero-badge">
        <i class="fas fa-bolt"></i> Powered by Advanced Neural Networks
    </div>
    
    <h1 class="hero-title">
        The Future of <br><span>Stock Analysis</span>
    </h1>
    
    <p class="hero-desc">
        Unleash high-precision analysis for the Indian Market. StoXVision combines real-time data with AI-driven predictive modeling to give you the ultimate trading edge.
    </p>

    <div class="hero-actions">
        <?php if(isset($_SESSION["user_id"])): ?>
            <a href="dashboard.php" class="btn-premium btn-primary-glass">
                Back to Dashboard <i class="fas fa-arrow-right"></i>
            </a>
        <?php else: ?>
            <a href="register.php" class="btn-premium btn-primary-glass">
                Get Started Free <i class="fas fa-user-plus"></i>
            </a>
            <a href="login.php" class="btn-premium btn-secondary-glass">
                Sign In <i class="fas fa-sign-in-alt"></i>
            </a>
        <?php endif; ?>
    </div>

    <div class="market-visual">
        <div class="visual-placeholder">STOXVision</div>
        <!-- In a real world this would be a Lottie or interactive chart -->
    </div>
</main>

<footer style="padding: 40px; text-align: center; color: #475569; font-size: 0.9rem;">
    &copy; <?php echo date("Y"); ?> StoXVision AI. Precision Engineering for Market Mastery.
</footer>

</body>
</html>