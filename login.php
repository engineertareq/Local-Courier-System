<?php
session_start();
require 'admin/db.php';

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: admin/index.php"); // Change to your dashboard file
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        // Fetch user from database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // 1. Verify Password
            if (password_verify($password, $user['password_hash'])) {
                
                // 2. Check Account Status
                if ($user['status'] === 'Inactive') {
                    $error = "Your account is deactivated. Please contact admin.";
                } elseif ($user['status'] === 'Pending') {
                    $error = "Your account is pending approval.";
                } else {
                    // 3. Login Success - Set Session Variables
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['profile_image'] = $user['profile_image'];

                    // Redirect based on role (Optional customization)
                    if ($user['role'] === 'customer') {
                        header("Location: parcel-track.php");
                    } else {
                        header("Location: admin/index.php");
                    }
                    exit();
                }
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | Dashboard</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    
    <style>
        /* Mimicking your dashboard theme styles */
        body { background-color: #f5f7fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .radius-12 { border-radius: 12px !important; }
        .radius-8 { border-radius: 8px !important; }
        .text-primary-light { color: #4834d4; } /* Adjust to your specific primary color */
        .text-secondary-light { color: #6c757d; }
        .bg-hover-primary-100:hover { background-color: #e0e7ff; }
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
                    <iconify-icon icon="solar:user-circle-bold-duotone" class="text-4xl" style="font-size: 32px; color: #4834d4;"></iconify-icon>
                </div>
                <h4 class="fw-bold mb-1">Welcome Back!</h4>
                <p class="text-secondary-light text-sm">Sign in to your account to continue</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger d-flex align-items-center gap-2 p-2 text-sm radius-8 mb-3" role="alert">
                    <iconify-icon icon="solar:danger-circle-bold-duotone" class="text-lg"></iconify-icon>
                    <?= $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold text-primary-light text-sm">Email Address</label>
                    <div class="position-relative">
                        <input type="email" name="email" class="form-control radius-8 ps-5 py-2" id="email" placeholder="Enter your email" required>
                        <span class="position-absolute start-0 top-50 translate-middle-y ms-3 text-secondary-light">
                            <iconify-icon icon="solar:letter-linear" class="text-xl"></iconify-icon>
                        </span>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold text-primary-light text-sm">Password</label>
                    <div class="position-relative">
                        <input type="password" name="password" class="form-control radius-8 ps-5 py-2" id="password" placeholder="Enter your password" required>
                        <span class="position-absolute start-0 top-50 translate-middle-y ms-3 text-secondary-light">
                            <iconify-icon icon="solar:lock-password-linear" class="text-xl"></iconify-icon>
                        </span>
                        <span class="position-absolute end-0 top-50 translate-middle-y me-3 cursor-pointer text-secondary-light toggle-password" style="cursor: pointer;">
                            <iconify-icon icon="solar:eye-closed-linear" id="eyeIcon" class="text-xl"></iconify-icon>
                        </span>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="rememberMe">
                        <label class="form-check-label text-secondary-light text-sm" for="rememberMe">Remember me</label>
                    </div>
                    <a href="forgot_password.php" class="text-primary-light text-sm fw-medium text-decoration-none">Forgot Password?</a>
                </div>

                <button type="submit" class="btn btn-primary w-100 radius-8 py-2 fw-medium text-md">
                    Sign In
                </button>

                <div class="text-center mt-4 text-sm text-secondary-light">
                    Don't have an account? 
                    <a href="signup.php" class="text-primary-light fw-semibold text-decoration-none">Sign Up</a>
                </div>

            </form>
        </div>
    </div>

    <script>
        const togglePassword = document.querySelector('.toggle-password');
        const passwordInput = document.querySelector('#password');
        const eyeIcon = document.querySelector('#eyeIcon');

        togglePassword.addEventListener('click', function () {
            // Toggle the type attribute
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle the eye icon
            if (type === 'password') {
                eyeIcon.setAttribute('icon', 'solar:eye-closed-linear');
            } else {
                eyeIcon.setAttribute('icon', 'solar:eye-bold');
            }
        });
    </script>

</body>
</html>