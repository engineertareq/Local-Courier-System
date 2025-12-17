<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desh Courier | Global Logistics Solution</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>

    <style>
        :root {
            --primary-color: #4834d4;
            --secondary-color: #686de0;
            --accent-color: #f0f3ff;
            --text-dark: #2d3436;
        }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; overflow-x: hidden; }
        
        /* Navbar */
        .navbar { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .nav-link { font-weight: 500; color: var(--text-dark); margin: 0 10px; }
        .nav-link:hover, .nav-link.active { color: var(--primary-color); }
        .btn-custom { background: var(--primary-color); color: white; padding: 10px 25px; border-radius: 8px; border: none; transition: 0.3s; }
        .btn-custom:hover { background: #3c2bb6; color: white; transform: translateY(-2px); }
        .btn-outline-custom { border: 2px solid var(--primary-color); color: var(--primary-color); padding: 8px 25px; border-radius: 8px; font-weight: 600; }
        .btn-outline-custom:hover { background: var(--primary-color); color: white; }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            padding: 120px 0 160px;
            color: white;
            clip-path: polygon(0 0, 100% 0, 100% 85%, 0 100%);
        }
        .hero-title { font-size: 3.5rem; font-weight: 800; line-height: 1.2; }
        
        /* Updated Tracking Box Styles */
        .tracking-box {
            background: white;
            padding: 10px;
            border-radius: 12px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
            display: flex;
            gap: 10px;
            max-width: 700px;
            flex-wrap: wrap;
        }
        .tracking-box input { 
            border: 1px solid #eee;
            padding: 15px; 
            font-size: 1rem; 
            flex: 1;
            outline: none; 
            border-radius: 8px;
            min-width: 200px;
        }
        .tracking-box input:focus { border-color: var(--primary-color); }
        .tracking-box button { 
            background: var(--text-dark); 
            color: white; 
            padding: 10px 30px; 
            border-radius: 8px; 
            border: none; 
            font-weight: 600;
            white-space: nowrap;
        }
        .tracking-box button:hover { background: #000; }

        /* Features */
        .feature-card {
            background: white;
            padding: 40px 30px;
            border-radius: 16px;
            border: 1px solid #eee;
            transition: 0.3s;
            height: 100%;
        }
        .feature-card:hover { transform: translateY(-10px); box-shadow: 0 10px 30px rgba(0,0,0,0.08); border-color: transparent; }
        .icon-box {
            width: 70px; height: 70px;
            background: var(--accent-color);
            color: var(--primary-color);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 25px;
            font-size: 32px;
        }

        /* Steps */
        .step-number {
            font-size: 4rem; font-weight: 900; color: rgba(72, 52, 212, 0.1);
            position: absolute; top: -20px; right: 20px;
        }

        /* Footer */
        .footer { background: #1e1e2d; color: #aab2bd; padding: 60px 0 20px; }
        .footer h5 { color: white; margin-bottom: 20px; }
        .footer a { color: #aab2bd; text-decoration: none; transition: 0.3s; }
        .footer a:hover { color: white; }
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
                    <li class="nav-item"><a class="nav-link active" href="#">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
                    
                    <li class="nav-item"><a class="nav-link" href="pricing.php">Pricing</a></li>
                    
                    <li class="nav-item"><a class="nav-link" href="parcel-track.php">Track</a></li>
                    <li class="nav-item ms-lg-3">
                        <a href="login.php" class="btn btn-outline-custom me-2">Log In</a>
                    </li>
                    <li class="nav-item">
                        <a href="signup.php" class="btn btn-custom">Sign Up</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="hero-section d-flex align-items-center">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <span class="badge bg-white text-primary px-3 py-2 rounded-pill mb-3 fw-bold text-uppercase ls-1">#1 Logistics Partner</span>
                    <h1 class="hero-title mb-4">Fastest & Secure Delivery Worldwide</h1>
                    <p class="lead mb-5 opacity-75">Reliable shipping solutions for your business. We ensure your package arrives safely and on time, every time.</p>
                    
                    <form action="parcel-track.php" method="GET" class="tracking-box">
                        <input type="text" name="tracking_number" placeholder="Tracking ID (e.g. TRK-123)" required>
                        <input type="text" name="phone_number" placeholder="Receiver Phone" required>
                        <button type="submit">Track</button>
                    </form>
                    
                    <p class="mt-3 text-white-50 small"><iconify-icon icon="solar:info-circle-outline"></iconify-icon> Enter Tracking ID & Phone for security.</p>
                </div>
                <div class="col-lg-6 text-center">
                    <img src="https://img.freepik.com/free-vector/delivery-staff-driving-motorcycle-shopping-online_1150-34989.jpg?w=900" alt="Delivery Illustration" class="img-fluid rounded-4 shadow-lg" style="border: 10px solid rgba(255,255,255,0.2);">
                </div>
            </div>
        </div>
    </section>

    <section id="services" class="py-5 bg-light">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h6 class="text-primary fw-bold text-uppercase">Our Services</h6>
                <h2 class="fw-bold display-6">Why Choose Desh Courier?</h2>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="icon-box">
                            <iconify-icon icon="solar:truck-fast-bold-duotone"></iconify-icon>
                        </div>
                        <h4>Fast Delivery</h4>
                        <p class="text-secondary">Optimized routes and real-time logistics management ensure your packages arrive faster than ever.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="icon-box">
                            <iconify-icon icon="solar:shield-check-bold-duotone"></iconify-icon>
                        </div>
                        <h4>Secure Shipping</h4>
                        <p class="text-secondary">Top-tier security protocols and insurance options to keep your valuable goods safe during transit.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="icon-box">
                            <iconify-icon icon="solar:globe-bold-duotone"></iconify-icon>
                        </div>
                        <h4>Global Reach</h4>
                        <p class="text-secondary">We ship to over 200 countries worldwide with customs support and international tracking.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="fw-bold display-6">How It Works</h2>
                <p class="text-secondary">Simple steps to get your package moving.</p>
            </div>
            <div class="row text-center g-4">
                <div class="col-md-3 position-relative">
                    <div class="step-number">01</div>
                    <div class="mb-3">
                        <iconify-icon icon="solar:box-outline" class="text-primary fs-1"></iconify-icon>
                    </div>
                    <h5>Book Service</h5>
                    <p class="text-secondary small">Create an account and book a pickup.</p>
                </div>
                <div class="col-md-3 position-relative">
                    <div class="step-number">02</div>
                    <div class="mb-3">
                        <iconify-icon icon="solar:box-minimalistic-outline" class="text-primary fs-1"></iconify-icon>
                    </div>
                    <h5>We Pack & Collect</h5>
                    <p class="text-secondary small">Our team collects and packages your item.</p>
                </div>
                <div class="col-md-3 position-relative">
                    <div class="step-number">03</div>
                    <div class="mb-3">
                        <iconify-icon icon="solar:map-arrow-up-bold-duotone" class="text-primary fs-1"></iconify-icon>
                    </div>
                    <h5>Track Shipment</h5>
                    <p class="text-secondary small">Monitor your parcel's journey in real-time.</p>
                </div>
                <div class="col-md-3 position-relative">
                    <div class="step-number">04</div>
                    <div class="mb-3">
                        <iconify-icon icon="solar:home-smile-bold-duotone" class="text-primary fs-1"></iconify-icon>
                    </div>
                    <h5>Safe Delivery</h5>
                    <p class="text-secondary small">Package delivered safely to the destination.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-primary text-white text-center">
        <div class="container py-4">
            <h2 class="fw-bold mb-3">Ready to ship with us?</h2>
            <p class="mb-4 opacity-75">Create an account today and get 20% off your first international shipment.</p>
            <a href="signup.php" class="btn btn-light text-primary fw-bold px-4 py-2 radius-8">Create Account</a>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h5 class="d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:box-bold-duotone" class="text-primary"></iconify-icon> Desh Courier
                    </h5>
                    <p class="small">Reliable logistics solutions for businesses and individuals worldwide. We make shipping simple.</p>
                </div>
                <div class="col-lg-2">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled d-flex flex-column gap-2">
                        <li><a href="#">Home</a></li>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Services</a></li>
                        
                        <li><a href="public_pricing.php">Pricing</a></li>
                        
                        <li><a href="parcel-track.php">Track Parcel</a></li>
                    </ul>
                </div>
                <div class="col-lg-2">
                    <h5>Support</h5>
                    <ul class="list-unstyled d-flex flex-column gap-2">
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Terms & Conditions</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Contact Us</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h5>Newsletter</h5>
                    <form class="d-flex gap-2">
                        <input type="email" class="form-control bg-dark border-secondary text-white" placeholder="Enter email">
                        <button class="btn btn-primary">Subscribe</button>
                    </form>
                </div>
            </div>
            <hr class="border-secondary my-4">
            <div class="text-center small">
                &copy; 2025 Desh Courier Logistics. All rights reserved.
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>