<?php
session_start();
include "../config/db.php";

if(isset($_SESSION["admin_logged_in"]) && $_SESSION["admin_logged_in"] === true) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_id = trim($_POST["login_id"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE (email = ? OR name = ?) AND role = 'admin'");
    if ($stmt) {
        $stmt->bind_param("ss", $login_id, $login_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows >= 1) {
            $authenticated = false;
            while ($user = $result->fetch_assoc()) {
                if (password_verify($password, $user["password"])) {
                    $authenticated = true;
                    $_SESSION["admin_logged_in"] = true;
                    $_SESSION["admin_name"] = $user["name"];
                    header("Location: index.php");
                    exit();
                }
            }
            if (!$authenticated) {
                $error = "Incorrect password.";
            }
        } else {
            $error = "Admin account not found.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | StoXVision AI</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            font-family: 'Inter', sans-serif;
            color: #f8fafc;
        }
    </style>
</head>
<body class="auth-bg">

<div class="auth-container" style="border-top: 4px solid var(--accent);">
    <div class="auth-logo">StoXVision <span style="color:var(--accent);">Admin</span></div>
    <div class="auth-subtitle">System Administration Portal</div>

    <?php if(isset($error)): ?>
        <div class="error-msg" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Admin Username or Email</label>
            <input type="text" name="login_id" placeholder="" required autofocus autocomplete="username">
        </div>

        <div class="form-group">
            <label>Password</label>
            <div class="password-wrapper">
                <input type="password" name="password" id="loginPassword" placeholder="••••••••" required autocomplete="current-password">
                <i class="fas fa-eye password-toggle" id="toggleLoginPassword"></i>
            </div>
        </div>

        <button type="submit" class="btn-auth" style="background: var(--accent); box-shadow: 0 4px 15px rgba(139, 92, 246, 0.4);">
            Secure Sign In <i class="fas fa-shield-alt"></i>
        </button>
    </form>
    
    <div class="auth-footer" style="margin-top: 20px; font-size: 0.85rem; color: var(--text-secondary);">
        Restricted Access. Authorized Personnel Only.
    </div>
</div>

<script>
document.getElementById('toggleLoginPassword').addEventListener('click', function() {
    const pwd = document.getElementById('loginPassword');
    if (pwd.type === 'password') {
        pwd.type = 'text';
        this.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        pwd.type = 'password';
        this.classList.replace('fa-eye-slash', 'fa-eye');
    }
});
</script>

</body>
</html>