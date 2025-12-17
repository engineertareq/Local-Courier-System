<?php
require 'admin/db.php';

// Fetch Active Packages
$stmt = $pdo->query("SELECT * FROM delivery_packages WHERE status = 'Active' ORDER BY price_inside_dhaka ASC");
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper function to render cards (Public Version)
function renderPublicCard($pkg, $location, $index) {
    $price = ($location === 'inside') ? $pkg['price_inside_dhaka'] : $pkg['price_outside_dhaka'];
    $location_label = ($location === 'inside') ? 'Inside Dhaka' : 'Outside Dhaka';
    
    // Aesthetic Logic (Cycle colors)
    $style_index = $index % 3;
    $colors = [
        0 => ['border' => '#6c5ce7', 'bg' => '#a29bfe', 'light' => '#e0dcfc'], // Purple
        1 => ['border' => '#0984e3', 'bg' => '#74b9ff', 'light' => '#dfe6e9'], // Blue
        2 => ['border' => '#00b894', 'bg' => '#55efc4', 'light' => '#dff9fb']  // Green
    ];
    $theme = $colors[$style_index];
    
    // Highlight "Best Value" (Middle item usually)
    $is_popular = ($style_index === 1); 
    ?>
    
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="pricing-card h-100 position-relative" style="border-top: 5px solid <?= $theme['border'] ?>;">
            <?php if($is_popular): ?>
                <span class="badge bg-danger position-absolute top-0 end-0 m-3 px-3 py-2 rounded-pill">Best Value</span>
            <?php endif; ?>

            <div class="card-body p-4 text-center">
                <div class="icon-wrapper mb-3 mx-auto" style="background-color: <?= $theme['light'] ?>; color: <?= $theme['border'] ?>;">
                    <iconify-icon icon="solar:box-bold-duotone" width="32"></iconify-icon>
                </div>
                
                <h5 class="fw-bold mb-1"><?= htmlspecialchars($pkg['package_name']) ?></h5>
                <p class="text-muted small"><?= $location_label ?></p>
                
                <div class="price-tag my-4">
                    <h2 class="display-5 fw-bold" style="color: <?= $theme['border'] ?>;">
                        ৳<?= number_format($price) ?>
                        <span class="fs-6 text-muted fw-normal">/ kg</span>
                    </h2>
                </div>

                <ul class="list-unstyled text-start mx-auto" style="max-width: 250px;">
                    <li class="mb-3 d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:clock-circle-bold" class="text-success"></iconify-icon>
                        Delivery: <strong><?= htmlspecialchars($pkg['delivery_time']) ?></strong>
                    </li>
                    <li class="mb-3 d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:map-point-bold" class="text-primary"></iconify-icon>
                        Real-time Tracking
                    </li>
                    <li class="mb-3 d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:smartphone-bold" class="text-primary"></iconify-icon>
                        SMS Notifications
                    </li>
                    <li class="mb-3 d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:box-minimalistic-bold" class="text-primary"></iconify-icon>
                        Secure Handling
                    </li>
                </ul>

                <a href="login.php" class="btn btn-outline-primary w-100 rounded-pill mt-3 py-2 fw-bold" style="border-color: <?= $theme['border'] ?>; color: <?= $theme['border'] ?>;">
                    Ship Now
                </a>
            </div>
        </div>
    </div>
    <?php
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pricing Rates | Desh Courier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>

    <style>
        :root {
            --primary-color: #4834d4;
            --text-dark: #2d3436;
        }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f5f7fa; }
        
        /* Navbar (Same as index.php) */
        .navbar { background: rgba(255, 255, 255, 0.95); box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .nav-link { font-weight: 500; color: var(--text-dark); margin: 0 10px; }
        .nav-link:hover, .nav-link.active { color: var(--primary-color); }
        .btn-custom { background: var(--primary-color); color: white; padding: 8px 25px; border-radius: 8px; border: none; }
        
        /* Hero */
        .page-hero {
            background: linear-gradient(135deg, #4834d4 0%, #686de0 100%);
            padding: 100px 0 80px;
            color: white;
            text-align: center;
            margin-bottom: 50px;
        }

        /* Pricing Cards */
        .pricing-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
        }
        .pricing-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .icon-wrapper {
            width: 70px; height: 70px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
        }
        
        /* Toggle Tabs */
        .pricing-nav {
            background: #fff;
            padding: 5px;
            border-radius: 50px;
            display: inline-flex;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 40px;
        }
        .pricing-nav .nav-link {
            border-radius: 50px;
            padding: 10px 30px;
            color: #636e72;
            border: none;
            font-weight: 600;
        }
        .pricing-nav .nav-link.active {
            background-color: var(--primary-color);
            color: white;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold fs-4 text-dark" href="index.php">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <iconify-icon icon="solar:box-bold-duotone"></iconify-icon>
                </div>
                Desh Courier
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link active" href="public_pricing.php">Pricing</a></li>
                    <li class="nav-item"><a class="nav-link" href="public_track.php">Track</a></li>
                    <li class="nav-item ms-lg-3"><a href="login.php" class="btn btn-outline-primary me-2">Log In</a></li>
                    <li class="nav-item"><a href="signup.php" class="btn btn-custom">Sign Up</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="page-hero">
        <div class="container">
            <h1 class="fw-bold mb-3">Pricing</h1>
            <p class="opacity-75 fs-5">Choose the delivery plan that fits your needs. No hidden charges.</p>
        </div>
    </section>

    <div class="container pb-5">
        
        <div class="text-center">
            <ul class="nav nav-pills pricing-nav" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="pills-inside-tab" data-bs-toggle="pill" data-bs-target="#pills-inside" type="button">Inside Dhaka</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-outside-tab" data-bs-toggle="pill" data-bs-target="#pills-outside" type="button">Outside Dhaka</button>
                </li>
            </ul>
        </div>

        <div class="tab-content" id="pills-tabContent">
            
            <div class="tab-pane fade show active" id="pills-inside" role="tabpanel">
                <div class="row justify-content-center">
                    <?php 
                    if(count($packages) > 0) {
                        foreach($packages as $index => $pkg) {
                            renderPublicCard($pkg, 'inside', $index);
                        }
                    } else {
                        echo '<div class="text-center py-5 text-muted">No packages available.</div>';
                    }
                    ?>
                </div>
            </div>

            <div class="tab-pane fade" id="pills-outside" role="tabpanel">
                <div class="row justify-content-center">
                    <?php 
                    if(count($packages) > 0) {
                        foreach($packages as $index => $pkg) {
                            renderPublicCard($pkg, 'outside', $index);
                        }
                    } else {
                        echo '<div class="text-center py-5 text-muted">No packages available.</div>';
                    }
                    ?>
                </div>
            </div>

        </div>
    </div>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0 small">&copy; 2025 Desh Courier Logistics. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>