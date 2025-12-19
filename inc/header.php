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
                    <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#services">Services</a></li>
                    
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