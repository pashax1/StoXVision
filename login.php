<?php
include "config/db.php";

if(isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = strtolower(trim($_POST["email"]));
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["user_name"] = $user["name"];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "Email not found.";
    }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | StoXVision AI</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #020617;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: 'Outfit', sans-serif;
        }
        .auth-container {
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            padding: 50px;
            border-radius: 32px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 40px 80px -20px rgba(0,0,0,0.5);
            text-align: center;
            animation: fadeIn 0.8s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .auth-logo {
            font-size: 2.5rem;
            font-weight: 900;
            background: linear-gradient(90deg, #38bdf8, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 5px;
        }
        .auth-subtitle {
            color: #94a3b8;
            margin-bottom: 40px;
            font-size: 1rem;
        }
        .form-group {
            text-align: left;
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            color: #cbd5e1;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 8px;
            margin-left: 5px;
        }
        input {
            width: 100%;
            padding: 16px 20px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 14px;
            color: #fff;
            font-size: 1rem;
            transition: all 0.3s;
        }
        input:focus {
            border-color: #38bdf8;
            background: rgba(255, 255, 255, 0.06);
            outline: none;
            box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.1);
        }
        .btn-auth {
            width: 100%;
            padding: 16px;
            background: #38bdf8;
            border: none;
            border-radius: 14px;
            color: #000;
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }
        .btn-auth:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(56, 189, 248, 0.4);
        }
        .auth-footer {
            margin-top: 30px;
            color: #64748b;
            font-size: 0.95rem;
        }
        .auth-footer a {
            color: #38bdf8;
            text-decoration: none;
            font-weight: 600;
        }
        .error-msg {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
            padding: 12px;
            border-radius: 12px;
            font-size: 0.9rem;
            margin-bottom: 25px;
            border: 1px solid rgba(239, 68, 68, 0.2);
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>
</head>
<body>

<div class="auth-container">
    <div class="auth-logo">StoXVision</div>
    <div class="auth-subtitle">Sign in to your trading portal</div>

    <?php if(isset($error)): ?>
        <div class="error-msg">
            <i class="fas fa-circle-exclamation"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="name@company.com" required autofocus>
        </div>
        
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn-auth">
            Sign In <i class="fas fa-arrow-right"></i>
        </button>
    </form>

    <div class="auth-footer">
        New to the platform? <a href="register.php">Create an account</a>
    </div>
</div>

</body>
</html>