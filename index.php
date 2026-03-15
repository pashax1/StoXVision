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
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        outfit: ['Outfit', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style>
        body { background-color: #020617; }
        .text-glow {
            text-shadow: 0 0 30px rgba(14, 165, 233, 0.3);
        }
        .glass-nav {
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
    </style>
</head>

<body class="font-sans text-slate-300 selection:bg-primary selection:text-dark overflow-x-hidden">

    <!-- Global Background Elements -->
    <div class="fixed inset-0 pointer-events-none z-[-1] overflow-hidden">
        <div class="absolute top-[-20%] left-[-10%] w-[60%] h-[60%] bg-primary/10 blur-[150px] rounded-full"></div>
        <div class="absolute bottom-[-20%] right-[-10%] w-[60%] h-[60%] bg-accent/10 blur-[150px] rounded-full"></div>
    </div>

    <!-- Navigation -->
    <nav class="glass-nav fixed top-0 left-0 right-0 z-50">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <div class="text-2xl font-black text-white tracking-tighter italic">
                StoX<span class="text-primary">Vision</span>
            </div>
            <div class="flex items-center gap-8">
                <a href="login.php" class="text-sm font-bold text-slate-400 hover:text-white transition-colors uppercase tracking-widest">Sign In</a>
                <a href="register.php" class="bg-white text-dark font-black px-6 py-2.5 rounded-xl hover:scale-105 transition-transform">GET STARTED</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <main class="relative pt-32 pb-20 px-6">
        <div class="max-w-7xl mx-auto text-center space-y-10">
            
            <div class="inline-flex items-center gap-3 px-6 py-2 bg-primary/10 border border-primary/20 rounded-full text-primary text-xs font-black tracking-[0.2em] uppercase animate-in fade-in slide-in-from-top-4 duration-1000">
                <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span>
                Neural Market Engine v4.0 Active
            </div>

            <h1 class="text-6xl md:text-8xl lg:text-9xl font-black text-white tracking-tighter italic leading-[0.9] animate-in fade-in slide-in-from-bottom-8 duration-700 delay-150">
                THE FUTURE <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-br from-primary via-primary to-accent text-glow">OF ANALYSIS</span>
            </h1>

            <p class="max-w-3xl mx-auto text-lg md:text-xl text-slate-400 font-bold leading-relaxed animate-in fade-in slide-in-from-bottom-8 duration-700 delay-300">
                StoXVision merges real-time financial telemetry with advanced neural modeling to provide 
                institutional-grade intelligence for the Indian equity markets.
            </p>


            <!-- Dashboard Preview Visual -->
            <div class="relative mt-24 max-w-5xl mx-auto group animate-in fade-in slide-in-from-bottom-12 duration-1000 delay-700">
                <div class="absolute -inset-4 bg-gradient-to-br from-primary/30 to-accent/30 blur-[60px] opacity-20 group-hover:opacity-40 transition-opacity"></div>
                <div class="relative bg-dark/60 backdrop-blur-3xl border border-white/5 rounded-[48px] overflow-hidden aspect-video shadow-2xl">
                    <!-- Symbolic Dashboard Interface -->
                    <div class="absolute inset-0 p-8 flex flex-col">
                        <div class="flex items-center justify-between mb-8">
                            <div class="flex gap-4">
                                <div class="w-12 h-4 bg-white/10 rounded-full"></div>
                                <div class="w-20 h-4 bg-white/10 rounded-full"></div>
                            </div>
                            <div class="w-8 h-8 rounded-full bg-white/10"></div>
                        </div>
                        <div class="flex-1 grid grid-cols-3 gap-6">
                            <div class="bg-white/5 rounded-3xl p-6 border border-white/5">
                                <div class="w-1/2 h-2 bg-primary/20 rounded-full mb-4"></div>
                                <div class="w-full h-8 bg-white/5 rounded-xl"></div>
                            </div>
                            <div class="bg-white/5 rounded-3xl p-6 border border-white/5">
                                <div class="w-1/2 h-2 bg-secondary/20 rounded-full mb-4"></div>
                                <div class="w-full h-8 bg-white/5 rounded-xl"></div>
                            </div>
                            <div class="bg-white/5 rounded-3xl p-6 border border-white/5">
                                <div class="w-1/2 h-2 bg-accent/20 rounded-full mb-4"></div>
                                <div class="w-full h-8 bg-white/5 rounded-xl"></div>
                            </div>
                        </div>
                        <div class="mt-6 flex-1 bg-white/[0.02] rounded-[32px] border border-white/5 flex items-center justify-center">
                            <i class="fas fa-chart-line text-8xl text-white/10"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Specs Section -->
    <section class="py-32 px-6">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-10">
            <div class="p-10 bg-white/[0.02] border border-white/5 rounded-[40px] hover:border-primary/20 transition-colors">
                <div class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center text-2xl text-primary mb-8">
                    <i class="fas fa-microchip"></i>
                </div>
                <h3 class="text-2xl font-black text-white italic mb-4">AI ANALYTICS</h3>
                <p class="font-bold text-slate-500 leading-relaxed uppercase text-xs tracking-widest">
                    Proprietary algorithmic processing of real-time market data flows.
                </p>
            </div>
            <div class="p-10 bg-white/[0.02] border border-white/5 rounded-[40px] hover:border-secondary/20 transition-colors">
                <div class="w-16 h-16 bg-secondary/10 rounded-2xl flex items-center justify-center text-2xl text-secondary mb-8">
                    <i class="fas fa-bolt"></i>
                </div>
                <h3 class="text-2xl font-black text-white italic mb-4">REAL-TIME</h3>
                <p class="font-bold text-slate-500 leading-relaxed uppercase text-xs tracking-widest">
                    Ultra-low latency telemetry from the Indian Exchanges.
                </p>
            </div>
            <div class="p-10 bg-white/[0.02] border border-white/5 rounded-[40px] hover:border-accent/20 transition-colors">
                <div class="w-16 h-16 bg-accent/10 rounded-2xl flex items-center justify-center text-2xl text-accent mb-8">
                    <i class="fas fa-dna"></i>
                </div>
                <h3 class="text-2xl font-black text-white italic mb-4">NEURAL SYNC</h3>
                <p class="font-bold text-slate-500 leading-relaxed uppercase text-xs tracking-widest">
                    Personalized portfolio intelligence that evolves with your trading style.
                </p>
            </div>
        </div>
    </section>

    <footer class="py-20 border-t border-white/5 text-center mt-20">
        <div class="text-3xl font-black text-white tracking-tighter italic mb-8">
            StoX<span class="text-primary">Vision</span>
        </div>
        <p class="text-slate-600 text-[10px] font-black uppercase tracking-[0.5em] mb-4">
            PRECISION ENGINEERING FOR MARKET MASTERY
        </p>
        <p class="text-slate-600 text-[10px]">
            &copy; <?php echo date("Y"); ?> StoXVision AI. All Systems Operational.
        </p>
    </footer>

</body>
</html>