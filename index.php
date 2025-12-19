<?php include_once 'inc/header.php' ?>


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

<?php include_once 'inc/footer.php' ?>