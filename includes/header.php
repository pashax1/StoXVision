<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : "StoXVision AI"; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php if(isset($extraHead)) echo $extraHead; ?>
</head>
<body>

<nav class="navbar">
    <a href="dashboard.php" class="nav-logo">StoXVision <span style="color:var(--text-primary);">AI</span></a>
    <div class="nav-links">
        <a href="dashboard.php" class="<?php echo ($currentPage == 'dashboard') ? 'active' : ''; ?>">Dashboard</a>
        <a href="portfolio.php" class="<?php echo ($currentPage == 'portfolio') ? 'active' : ''; ?>">Portfolio</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</nav>
<div class="dashboard-container">
