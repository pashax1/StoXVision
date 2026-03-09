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
    <a href="dashboard.php" class="nav-logo">StoXVision</a>
    <div class="nav-links">
        <a href="dashboard.php" class="<?php echo ($currentPage == 'dashboard') ? 'active' : ''; ?>"><i class="fas fa-th-large"></i> Dashboard</a>
        <a href="portfolio.php" class="<?php echo ($currentPage == 'portfolio') ? 'active' : ''; ?>"><i class="fas fa-briefcase"></i> Portfolio</a>
        <a href="ai_mode.php" class="<?php echo ($currentPage == 'ai_mode') ? 'active' : ''; ?>"><i class="fas fa-robot"></i> AI Mode</a>
        
        <div class="nav-profile">
            <a href="profile.php" class="profile-trigger <?php echo ($currentPage == 'profile') ? 'active' : ''; ?>">
                <?php 
                $profile_pic = $_SESSION['profile_pic'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['user_name'] ?? 'User') . '&background=0ea5e9&color=fff';
                ?>
                <img src="<?php echo $profile_pic; ?>" alt="Profile" class="nav-avatar">
                <span>Profile</span>
            </a>
        </div>
        
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i></a>
    </div>
</nav>
<div class="dashboard-container">
