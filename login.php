<?php
session_start();
include "config/db.php";

if(isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_id = strtolower(trim($_POST["login_id"])); 
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT id, name, password, role, status FROM users WHERE email = ? OR name = ?");
    if ($stmt) {
        $stmt->bind_param("ss", $login_id, $login_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows >= 1) {
            while ($user = $result->fetch_assoc()) {
                if (password_verify($password, $user["password"])) {
                    if (isset($user['status']) && $user['status'] === 'suspended') {
                        $error = "System Access Suspended. Contact Admin.";
                        break;
                    }

                    $_SESSION["user_id"] = $user["id"];
                    $_SESSION["user_name"] = $user["name"];
                    $_SESSION["role"] = $user["role"];

                    header("Location: dashboard.php");
                    exit();
                }
            }
            if (!isset($error)) $error = "Invalid decryption key (Password incorrect).";
        } else {
            $error = "Entity not found in StoXVision database.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uplink | StoXVision AI</title>
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
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-primary/10 blur-[120px] rounded-full"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-accent/10 blur-[120px] rounded-full"></div>
    </div>

    <div class="w-full max-w-md relative animate-in fade-in slide-in-from-bottom-5 duration-700">
        <div class="glass-card p-10 rounded-[48px] shadow-2xl">
            
            <div class="text-center mb-10">
                <h1 class="text-4xl font-black text-white tracking-tighter italic mb-2">StoX<span class="text-primary">Vision</span></h1>
                <p class="text-slate-500 font-bold uppercase tracking-widest text-[10px]">Strategic Intelligence Uplink</p>
            </div>

            <?php if(isset($error)): ?>
            <div class="mb-6 bg-red-500/10 border border-red-500/20 p-4 rounded-2xl text-red-500 text-xs font-bold flex items-center gap-3 animate-pulse">
                <i class="fas fa-satellite-dish"></i>
                <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3">Login Identity</label>
                    <div class="relative group">
                        <i class="fas fa-at absolute left-5 top-1/2 -translate-y-1/2 text-slate-600 group-focus-within:text-primary transition-colors"></i>
                        <input type="text" name="login_id" placeholder="Email or Username" required autofocus autocomplete="username"
                               class="w-full bg-white/5 border border-white/10 rounded-2xl pl-12 pr-6 py-4 text-white font-bold focus:outline-none focus:border-primary transition-all">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3">Security Key</label>
                    <div class="relative group">
                        <i class="fas fa-lock absolute left-5 top-1/2 -translate-y-1/2 text-slate-600 group-focus-within:text-primary transition-colors"></i>
                        <input type="password" name="password" id="loginPassword" placeholder="••••••••" required autocomplete="current-password"
                               class="w-full bg-white/5 border border-white/10 rounded-2xl pl-12 pr-12 py-4 text-white font-bold focus:outline-none focus:border-primary transition-all">
                        <button type="button" id="toggleLoginPassword" class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-600 hover:text-white transition-colors">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="w-full bg-primary hover:bg-primary/90 text-dark font-black py-4 rounded-2xl tracking-tighter text-xl transition-all shadow-lg shadow-primary/20 flex items-center justify-center gap-3 group">
                    AUTHENTICATE <i class="fas fa-chevron-right text-sm group-hover:translate-x-1 transition-transform"></i>
                </button>
            </form>

            <div class="mt-8 pt-8 border-t border-white/5 text-center">
                <p class="text-slate-500 text-xs font-bold">NEW ANALYST? <a href="register.php" class="text-primary hover:underline ml-2 uppercase">Create Profile</a></p>
            </div>
        </div>
        
        <div class="mt-8 text-center text-slate-600 text-[10px] font-black uppercase tracking-[0.3em] opacity-50">
            &copy; 2026 StoXVision • Neural Engine v4.0.2
        </div>
    </div>

    <script>
        document.getElementById('toggleLoginPassword').addEventListener('click', function() {
            const pwd = document.getElementById('loginPassword');
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