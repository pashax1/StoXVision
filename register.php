<?php
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
        $error = "All fields are required.";
    } elseif (!preg_match("/^[a-zA-Z0-9\s]+$/", $name)) {
        $error = "Full Name can only contain letters, numbers, and spaces.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sss", $name, $email, $password);

            if ($stmt->execute()) {
                $_SESSION["registered"] = true;
                header("Location: login.php");
                exit();
            } else {
                $error = "This email is already associated with an account.";
            }
        } else {
            $error = "Database error. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | StoXVision AI</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #020617;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            font-family: 'Outfit', sans-serif;
            padding: 40px 20px;
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
    <div class="auth-subtitle">Join the future of market analysis</div>

    <?php if(isset($error)): ?>
        <div class="error-msg">
            <i class="fas fa-circle-exclamation"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name" placeholder="John Doe" required autofocus pattern="[a-zA-Z0-9\s]+" title="Only alphanumeric characters and spaces allowed">
        </div>

        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="john@example.com" required>
        </div>
        
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="••••••••" required minlength="6">
        </div>

        <button type="submit" class="btn-auth">
            Create Account <i class="fas fa-user-plus"></i>
        </button>
    </form>

    <div class="auth-footer">
        Already have an account? <a href="login.php">Log In Here</a>
    </div>
</div>

</body>
</html>
