<?php
session_start();
require 'admin/db.php';

$message = "";
$message_type = ""; // danger or success

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = htmlspecialchars($_POST['full_name']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic Validation
    if (empty($full_name) || empty($email) || empty($password)) {
        $message = "All fields are required.";
        $message_type = "danger";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
        $message_type = "danger";
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $message = "Email is already registered.";
            $message_type = "danger";
        } else {
            // Insert New User
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'customer'; // Default role
            $status = 'Active'; // Default status

            try {
                $sql = "INSERT INTO users (full_name, email, password_hash, role, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
                $stmtInsert = $pdo->prepare($sql);
                $stmtInsert->execute([$full_name, $email, $hashed_password, $role, $status]);

                $message = "Account created successfully! <a href='login.php' class='fw-bold text-success'>Login here</a>";
                $message_type = "success";
            } catch (PDOException $e) {
                $message = "Database Error: " . $e->getMessage();
                $message_type = "danger";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <style>
        body { background-color: #f5f7fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
        .radius-12 { border-radius: 12px !important; }
        .radius-8 { border-radius: 8px !important; }
        .text-primary-light { color: #4834d4; }
        .auth-card { width: 100%; max-width: 500px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        .btn-primary { background-color: #4834d4; border-color: #4834d4; }
        .btn-primary:hover { background-color: #3c2bb6; border-color: #3c2bb6; }
        .form-control:focus { box-shadow: none; border-color: #4834d4; }
    </style>
</head>
<body>

    <div class="card auth-card radius-12 p-4">
        <div class="card-body">
            
            <div class="text-center mb-4">
                <h4 class="fw-bold mb-1">Create Account</h4>
                <p class="text-secondary opacity-75 text-sm">Join us today! It takes less than a minute.</p>
            </div>

            <?php if (!empty($message)): ?>
                <div class="alert alert-<?= $message_type ?> d-flex align-items-center gap-2 p-2 text-sm radius-8 mb-3">
                    <iconify-icon icon="solar:info-circle-bold" class="text-lg"></iconify-icon>
                    <?= $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                
                <div class="mb-3">
                    <label class="form-label fw-semibold text-primary-light text-sm">Full Name</label>
                    <div class="position-relative">
                        <input type="text" name="full_name" class="form-control radius-8 ps-5 py-2" placeholder="John Doe" required>
                        <span class="position-absolute start-0 top-50 translate-middle-y ms-3 text-secondary">
                            <iconify-icon icon="solar:user-linear" class="text-xl"></iconify-icon>
                        </span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-primary-light text-sm">Email Address</label>
                    <div class="position-relative">
                        <input type="email" name="email" class="form-control radius-8 ps-5 py-2" placeholder="name@example.com" required>
                        <span class="position-absolute start-0 top-50 translate-middle-y ms-3 text-secondary">
                            <iconify-icon icon="solar:letter-linear" class="text-xl"></iconify-icon>
                        </span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold text-primary-light text-sm">Password</label>
                        <div class="position-relative">
                            <input type="password" name="password" class="form-control radius-8 ps-5 py-2" placeholder="******" required>
                            <span class="position-absolute start-0 top-50 translate-middle-y ms-3 text-secondary">
                                <iconify-icon icon="solar:lock-password-linear" class="text-xl"></iconify-icon>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold text-primary-light text-sm">Confirm Password</label>
                        <div class="position-relative">
                            <input type="password" name="confirm_password" class="form-control radius-8 ps-5 py-2" placeholder="******" required>
                            <span class="position-absolute start-0 top-50 translate-middle-y ms-3 text-secondary">
                                <iconify-icon icon="solar:lock-password-linear" class="text-xl"></iconify-icon>
                            </span>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 radius-8 py-2 fw-medium text-md mt-2">
                    Sign Up
                </button>

                <div class="text-center mt-4 text-sm text-secondary">
                    Already have an account? 
                    <a href="login.php" class="text-primary-light fw-semibold text-decoration-none">Sign In</a>
                </div>

            </form>
        </div>
    </div>
</body>
</html>