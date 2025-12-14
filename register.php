<?php
require 'db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // FORCE ROLE TO CUSTOMER
    $role = 'customer'; 

    // 1. Basic Validation
    if ($password !== $confirm_password) {
        $message = "<div class='alert alert-danger'>Passwords do not match!</div>";
    } else {
        // 2. Hash the password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            // 3. Insert into Database
            $sql = "INSERT INTO users (full_name, email, password_hash, phone, role) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$full_name, $email, $password_hash, $phone, $role]);

            $message = "<div class='alert alert-success'>Registration successful! <a href='login.php'>Login here</a></div>";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $message = "<div class='alert alert-danger'>This email is already registered.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
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
    <title>Customer Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card { border: none; border-radius: 15px; }
    </style>
</head>
<body>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="col-md-5">
        <div class="card shadow-lg p-4">
            <h3 class="text-center mb-4 text-primary">Customer Sign Up</h3>
            
            <?= $message ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control" required placeholder="John Doe">
                </div>

                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" required placeholder="name@example.com">
                </div>

                <div class="mb-3">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control" placeholder="+880 1XXX..." required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">Create Account</button>

                <div class="text-center mt-3">
                    Already have an account? <a href="login.php" class="text-decoration-none">Login</a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>