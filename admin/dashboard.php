<?php
session_start();

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
}
?>

<!DOCTYPE html>
<html>

<head>
<title>Admin Dashboard</title>
<link rel="stylesheet" href="style.css">
</head>

<body>

<h1>StoXVision Admin Panel</h1>

<p>Welcome <?php echo $_SESSION['admin']; ?></p>

<div class="menu">

<a href="#">Manage Stocks</a>
<a href="#">Add News</a>
<a href="#">API Settings</a>
<a href="logout.php">Logout</a>

</div>

</body>
</html>