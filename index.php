<?php
include "config/db.php";

if(isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>StoXVision - AI Powered Indian Market Analysis</title>
<link rel="stylesheet" href="assets/css/style.css">
<script defer src="assets/js/main.js"></script>
</head>
<style>
body {
    margin: 0;
    background: black;
    overflow: hidden;
    color: white;
    font-family: Arial;
}

.floating-container {
    position: absolute;
    width: 100%;
    height: 100vh;
}

.float {
    position: absolute;
    font-size: 24px;
    font-weight: bold;
    color: #00ffcc;
    
    animation: floatAnimation 6s infinite alternate ease-in-out;
}

.float:nth-child(1) { top: 10%; left: 20%; }
.float:nth-child(2) { top: 30%; left: 70%; }
.float:nth-child(3) { top: 70%; left: 40%; }
.float:nth-child(4) { top: 85%; left: 10%; }
.float:nth-child(5) { top: 50%; left: 80%; }

@keyframes floatAnimation {
    from { transform: translateY(0px); }
    to { transform: translateY(-60px); }
}
</style>

<body class="landing">

<div class="floating-container">
    <div class="float">NIFTY</div>
    <div class="float">SENSEX</div>
    <div class="float">TCS</div>
    <div class="float">INFY</div>
    <div class="float">RELIANCE</div>
</div>



<div class="hero">
    <h1 class="logo">StoXVision</h1>
    <p class="tagline">AI Powered Stock & Trend Analysis</p>

    <div class="buttons">
        <a href="login.php" class="btn">Login</a>
        <a href="register.php" class="btn-outline">Register</a>
    </div>

    <div class="quote">
        "Markets reward discipline, not emotion."
    </div>
</div>

</body>
</html>