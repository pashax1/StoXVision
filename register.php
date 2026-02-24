<?php
include "config/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $password);

    if ($stmt->execute()) {
        header("Location: login.php");
        exit();
    } else {
        $error = "Email already exists!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Register - StoXVision</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-bg">

<div class="auth-box">
<h2>Create Account</h2>

<?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>

<form method="POST">
<input type="text" name="name" placeholder="Full Name" required>
<input type="email" name="email" placeholder="Email" required>
<input type="password" name="password" placeholder="Password" required>
<button type="submit">Register</button>
</form>

<p>Already have account? <a href="login.php">Login</a></p>
</div>

</body>
</html>