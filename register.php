<?php
session_start();
include "config/db.php";

if(isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $email = strtolower(trim($_POST["email"]));
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    if (empty($name) || empty($email) || empty($_POST["password"])) {
        $error = "All vectors required for profile generation.";
    } elseif (!preg_match("/^[a-zA-Z]+$/", $name)) {
        $error = "Name must be characters only (A-Z) for system registration.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid uplink address (Email format incorrect).";
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("sss", $name, $email, $password);
                if ($stmt->execute()) {
                    $_SESSION["registered"] = true;
                    header("Location: login.php");
                    exit();
                } else {
                    $error = "Registration failed. Database link unstable.";
                }
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                $error = "Identity already exists in StoXVision vault.";
            } else {
                $error = "Critical error during profile synthesis.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Synthesize | StoXVision AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap" rel="stylesheet">
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
                    },
                }
            }
        }
    </script>
    <style>
        body { background-color: #020617; }
        .glass-card {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-6 overflow-hidden">
    
    <!-- Background Blobs -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-[-10%] right-[-10%] w-[40%] h-[40%] bg-primary/10 blur-[120px] rounded-full"></div>
        <div class="absolute bottom-[-10%] left-[-10%] w-[40%] h-[40%] bg-emerald-500/10 blur-[120px] rounded-full"></div>
    </div>

    <div class="w-full max-w-md relative animate-in fade-in slide-in-from-bottom-5 duration-700">
        <div class="glass-card p-10 rounded-[48px] shadow-2xl">
            
            <div class="text-center mb-10">
                <h1 class="text-4xl font-black text-white tracking-tighter italic mb-2">StoX<span class="text-primary">Vision</span></h1>
                <p class="text-slate-500 font-bold uppercase tracking-widest text-[10px]">Neural Profile Synthesis</p>
            </div>

            <?php if(isset($error)): ?>
            <div class="mb-6 bg-red-500/10 border border-red-500/20 p-4 rounded-2xl text-red-500 text-xs font-bold flex items-center gap-3 animate-pulse">
                <i class="fas fa-microchip"></i>
                <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3">Full Identity</label>
                    <div class="relative group">
                        <i class="fas fa-user-tag absolute left-5 top-1/2 -translate-y-1/2 text-slate-600 group-focus-within:text-primary transition-colors"></i>
                        <input type="text" id="regName" name="name" placeholder="Full Name" required autofocus
                               pattern="[A-Za-z ]+" title="Alphabets and spaces only"
                               oninput="this.value=this.value.replace(/[^a-zA-Z ]/g,'')"
                               class="w-full bg-white/5 border border-white/10 rounded-2xl pl-12 pr-6 py-4 text-white font-bold focus:outline-none focus:border-primary transition-all">
                    </div>
                    <p class="mt-2 text-[9px] text-slate-600 font-bold uppercase tracking-widest pl-1">Letters and spaces only &mdash; no numbers or symbols</p>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3">Uplink Address</label>
                    <div class="relative group">
                        <i class="fas fa-envelope absolute left-5 top-1/2 -translate-y-1/2 text-slate-600 group-focus-within:text-primary transition-colors"></i>
                        <input type="email" name="email" placeholder="Email Address" required autocomplete="username"
                               class="w-full bg-white/5 border border-white/10 rounded-2xl pl-12 pr-6 py-4 text-white font-bold focus:outline-none focus:border-primary transition-all">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3">Encryption Key</label>
                    <div class="relative group">
                        <i class="fas fa-shield-cat absolute left-5 top-1/2 -translate-y-1/2 text-slate-600 group-focus-within:text-primary transition-colors"></i>
                        <input type="password" name="password" id="regPassword" placeholder="••••••••" required minlength="6" autocomplete="new-password"
                               class="w-full bg-white/5 border border-white/10 rounded-2xl pl-12 pr-12 py-4 text-white font-bold focus:outline-none focus:border-primary transition-all">
                        <button type="button" id="toggleRegPassword" class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-600 hover:text-white transition-colors">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-600 text-dark font-black py-4 rounded-2xl tracking-tighter text-xl transition-all shadow-lg shadow-emerald-500/20 flex items-center justify-center gap-3 group">
                    SYNTHESIZE <i class="fas fa-dna text-sm group-hover:rotate-180 transition-transform duration-700"></i>
                </button>
            </form>

            <div class="mt-8 pt-8 border-t border-white/5 text-center">
                <p class="text-slate-500 text-xs font-bold">ALREADY REGISTERED? <a href="login.php" class="text-primary hover:underline ml-2 uppercase">Log In</a></p>
            </div>
        </div>
        
        <div class="mt-8 text-center text-slate-600 text-[10px] font-black uppercase tracking-[0.3em] opacity-50">
            &copy; 2026 StoXVision • Neural Engine v4.0.2
        </div>
    </div>

    <script>
        // Name field: block non-alpha characters (belt-and-suspenders beyond oninput attr)
        const regName = document.getElementById('regName');
        if (regName) {
            regName.addEventListener('keypress', function(e) {
                if (!/[a-zA-Z ]/.test(e.key)) {
                    e.preventDefault();
                }
            });
            regName.addEventListener('paste', function(e) {
                e.preventDefault();
                const text = (e.clipboardData || window.clipboardData).getData('text');
                const cleaned = text.replace(/[^a-zA-Z ]/g, '');
                const start = this.selectionStart;
                const end = this.selectionEnd;
                this.value = this.value.slice(0, start) + cleaned + this.value.slice(end);
                this.setSelectionRange(start + cleaned.length, start + cleaned.length);
            });
        }

        // Password toggle
        document.getElementById('toggleRegPassword').addEventListener('click', function() {
            const pwd = document.getElementById('regPassword');
            const icon = this.querySelector('i');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                pwd.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    </script>
</body>
</html>
