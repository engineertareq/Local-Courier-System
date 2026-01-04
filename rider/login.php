<?php
require 'db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (password_verify($password, $user['password_hash'])) { 
                
       
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role']; 
                $stmtCourier = $pdo->prepare("SELECT * FROM couriers WHERE user_id = ?");
                $stmtCourier->execute([$user['user_id']]);
                
                if ($stmtCourier->fetch()) {
                    header("Location: index.php");
                } else {
                    header("Location: index.html"); 
                }
                exit;

            } else {
                $message = "<div class='alert alert-danger'>Incorrect password.</div>";
            }
        } else {
            $message = "<div class='alert alert-danger'>User not found.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Courier System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <style>
        body { background-color: #f4f6f8; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { width: 100%; max-width: 400px; border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

    <div class="card login-card p-4">
        <div class="card-body">
            <div class="text-center mb-4">
                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:64px; height:64px; font-size:32px;">
                    <iconify-icon icon="solar:box-bold-duotone"></iconify-icon>
                </div>
                <h4 class="fw-bold">Courier Login</h4>
            </div>

            <?= $message ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="Enter email" required>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Login</button>
            </form>
        </div>
    </div>

</body>
</html>