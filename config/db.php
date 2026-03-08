<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// DATABASE CREDENTIALS
$host     = "localhost";
$user     = "root";
$password = "";
$database = "stoxvision";

// GLOBAL CONFIGURATION
$config = [
    'api_key' => 'X9Z985T648Z0Z750', // Alpha Vantage Free Key
    'app_name' => 'StoXVision AI',
    'cache_time' => 3600, // 1 hour
];

// Disable mysqli reporting for clean error handling
mysqli_report(MYSQLI_REPORT_OFF);

// Create connection WITHOUT selecting a database first
$conn = @new mysqli($host, $user, $password);

if ($conn->connect_error) {
    die("
    <div style='font-family:monospace; background:#1e0000; color:#ff6b6b; padding:30px; border-radius:12px; margin:40px auto; max-width:700px;'>
        <h2>&#9888; Database Connection Failed</h2>
        <p><b>Error:</b> " . htmlspecialchars($conn->connect_error) . "</p>
        <p>Check your MySQL server is running in XAMPP Control Panel.</p>
    </div>");
}

// Create the database if it doesn't exist
$conn->query("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

// Now select the database
if (!$conn->select_db($database)) {
    die("
    <div style='font-family:monospace; background:#1e0000; color:#ff6b6b; padding:30px; border-radius:12px; margin:40px auto; max-width:700px;'>
        <h2>&#9888; Could Not Select Database</h2>
        <p>Could not select database '<b>{$database}</b>'.</p>
    </div>");
}

$conn->set_charset("utf8mb4");

// Automatically create tables if they don't exist
$tables = [
    "users" => "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "watchlist" => "CREATE TABLE IF NOT EXISTS watchlist (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        symbol VARCHAR(20) NOT NULL,
        added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_user_symbol (user_id, symbol),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    "stock_cache" => "CREATE TABLE IF NOT EXISTS stock_cache (
        id INT AUTO_INCREMENT PRIMARY KEY,
        symbol VARCHAR(20) NOT NULL,
        current_price DECIMAL(15, 2),
        prediction VARCHAR(20),
        confidence INT,
        cached_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )"
];

foreach ($tables as $name => $sql) {
    if (!$conn->query($sql)) {
        // Log error and continue, don't crash the whole app if one table fails unless it's critical
        error_log("StoXVision: Failed to create table $name: " . $conn->error);
    }
}
?>