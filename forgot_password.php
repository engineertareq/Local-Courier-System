<?php
session_start();
require 'admin/db.php';

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    // Check if email exists
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Generate a unique token
        $token = bin2hex(random_bytes(50));
        
        // Update database with token (Expires in 1 hour)
        $sql = "UPDATE users SET reset_token = ?, reset_token_expire = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = ?";
        $stmtUpdate = $pdo->prepare($sql);
        
        if ($stmtUpdate->execute([$token, $email])) {
            // NOTE: In a real app, send this link via email using PHPMailer
            // For now, we simulate success
            $resetLink = "http://yourwebsite.com/reset_password.php?token=" . $token;
            
            $message = "A reset link has been sent to your email address.";
            $message_type = "success";
        }
    } else {
        // Security: Don't reveal if email doesn't exist, just show generic success or generic error.
        // But for development, we might want to know:
        $message = "If that email exists, we have sent a reset link.";
        $message_type = "info";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <style>
        body { background-color: #f5f7fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .radius-12 { border-radius: 12px !important; }
        .radius-8 { border-radius: 8px !important; }
        .text-primary-light { color: #4834d4; }
        .auth-card { width: 100%; max-width: 450px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        .btn-primary { background-color: #4834d4; border-color: #4834d4; }
        .btn-primary:hover { background-color: #3c2bb6; border-color: #3c2bb6; }
        .form-control:focus { box-shadow: none; border-color: #4834d4; }
    </style>
</head>
<body>

    <div class="card auth-card radius-12 p-4">
        <div class="card-body">
            
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle w-64-px h-64-px mb-3" style="width: 64px; height: 64px;">
                    <iconify-icon icon="solar:shield-keyhole-bold-duotone" class="text-4xl" style="font-size: 32px; color: #4834d4;"></iconify-icon>
                </div>
                <h4 class="fw-bold mb-1">Forgot Password?</h4>
                <p class="text-secondary opacity-75 text-sm">Enter your email and we'll send you instructions to reset your password.</p>
            </div>

            <?php if (!empty($message)): ?>
                <div class="alert alert-<?= $message_type ?> d-flex align-items-center gap-2 p-2 text-sm radius-8 mb-3">
                    <iconify-icon icon="solar:info-circle-bold" class="text-lg"></iconify-icon>
                    <?= $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                
                <div class="mb-4">
                    <label class="form-label fw-semibold text-primary-light text-sm">Email Address</label>
                    <div class="position-relative">
                        <input type="email" name="email" class="form-control radius-8 ps-5 py-2" placeholder="name@example.com" required>
                        <span class="position-absolute start-0 top-50 translate-middle-y ms-3 text-secondary">
                            <iconify-icon icon="solar:letter-linear" class="text-xl"></iconify-icon>
                        </span>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 radius-8 py-2 fw-medium text-md">
                    Send Reset Link
                </button>

                <div class="text-center mt-4 text-sm">
                    <a href="login.php" class="text-secondary fw-semibold text-decoration-none d-flex align-items-center justify-content-center gap-1">
                        <iconify-icon icon="solar:arrow-left-linear"></iconify-icon> Back to Sign In
                    </a>
                </div>

            </form>
        </div>
    </div>
</body>
</html>