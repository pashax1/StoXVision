<?php
include "config/db.php";
$res = $conn->query("SELECT id, name, email, role, password FROM users WHERE role = 'admin'");
while($row = $res->fetch_assoc()) {
    print_r($row);
    // Also let's quick check if password_verify works for admin@123
    echo "password_verify('admin@123', ..): " . (password_verify('admin@123', $row['password']) ? 'true' : 'false') . "\n";
}
?>
