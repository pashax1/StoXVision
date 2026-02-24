<?php
session_start();

$host = "localhost";
$user = "root";
$password = "";
$database = "stoxvision";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}
?>