<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// DATABASE CREDENTIALS (REPLACE WITH YOUR OWN)
$host     = "localhost";
$user     = "root";
$password = "";
$database = "stoxvision";

// GLOBAL CONFIGURATION
$config = [
    'api_key' => 'YOUR_ALPHA_VANTAGE_KEY_HERE', 
    'app_name' => 'StoXVision AI',
    'cache_time' => 3600, // 1 hour
];

// Disable mysqli reporting for clean error handling
mysqli_report(MYSQLI_REPORT_OFF);

// Create connection WITHOUT selecting a database first
$conn = @new mysqli($host, $user, $password);

if ($conn->connect_error) {
    die("Database Connection Failed. Check your MySQL server.");
}

// Create the database if it doesn't exist
$conn->query("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

// Select the database
$conn->select_db($database);
$conn->set_charset("utf8mb4");

// Tables will be automatically created on first run
?>
