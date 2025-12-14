<?php
session_start();
require 'db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // 1. Find User
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // 2. Verify Password
    if ($user && password_verify($password, $user['password_hash'])) {
        // Set Session Variables
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['phone'] = $user['phone']; // Important for finding their parcels

        // Redirect based on role
        if ($user['role'] == 'admin' || $user['role'] == 'staff') {
            header("Location: create_parcel.php"); // Or admin_dashboard.php
        } else {
            header("Location: customer_dashboard.php");
        }
        exit;
    } else {
        $message = "<div class='alert alert-danger'>Incorrect email or password.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="height: 100vh;">

    <div class="card shadow p-4" style="width: 100%; max-width: 400px;">
        <h3 class="text-center mb-3">Login</h3>
        <?= $message ?>
        
        <form method="POST">
            <div class="mb-3">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Sign In</button>
        </form>
        <p class="text-center mt-3">
            No account? <a href="register.php">Register here</a>
        </p>
    </div>

</body>
</html>