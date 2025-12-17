<?php 
session_start();
require 'db.php';

// --- 1. ACCESS CONTROL ---
// Only Admin should be able to add packages
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // header("Location: index.php"); // Uncomment to enforce
}

$message = "";

// --- 2. FORM SUBMISSION ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $package_name = trim($_POST['package_name']);
    $delivery_time = trim($_POST['delivery_time']);
    $price_inside = $_POST['price_inside'];
    $price_outside = $_POST['price_outside'];
    $status = $_POST['status'];

    if (empty($package_name) || empty($delivery_time)) {
        $message = "<div class='alert alert-danger'>Package Name and Time are required.</div>";
    } else {
        try {
            $sql = "INSERT INTO delivery_packages 
                    (package_name, delivery_time, price_inside_dhaka, price_outside_dhaka, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, NOW())";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$package_name, $delivery_time, $price_inside, $price_outside, $status]);

            $message = "<div class='alert alert-success'>Package '<strong>$package_name</strong>' added successfully!</div>";
        } catch (PDOException $e) {
            $message = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
        }
    }
}

include 'inc/header.php';
?>

<div class="dashboard-main-body">

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <h6 class="fw-semibold mb-0">Add New Package</h6>
        <ul class="d-flex align-items-center gap-2">
            <li class="fw-medium">
                <a href="packages.php" class="d-flex align-items-center gap-1 hover-text-primary">
                    <iconify-icon icon="solar:tag-price-bold-duotone" class="icon text-lg"></iconify-icon>
                    Pricing
                </a>
            </li>
            <li>-</li>
            <li class="fw-medium">Add Package</li>
        </ul>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card h-100 p-0 radius-12">
                <div class="card-header border-bottom bg-base py-16 px-24">
                    <h6 class="text-lg fw-semibold mb-0">Package Details</h6>
                </div>
                
                <div class="card-body p-24">
                    
                    <?php echo $message; ?>

                    <form method="POST">
                        <div class="row gy-4">
                            
                            <div class="col-md-12">
                                <label class="form-label fw-semibold text-primary-light text-sm mb-8">Package Name <span class="text-danger-600">*</span></label>
                                <input type="text" name="package_name" class="form-control radius-8" placeholder="e.g. Express Delivery" required>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold text-primary-light text-sm mb-8">Delivery Duration <span class="text-danger-600">*</span></label>
                                <div class="position-relative">
                                    <input type="text" name="delivery_time" class="form-control radius-8 ps-40" placeholder="e.g. 24 Hours / 2-3 Days" required>
                                    <span class="position-absolute start-0 top-50 translate-middle-y ms-16 text-secondary-light">
                                        <iconify-icon icon="solar:clock-circle-linear" class="text-xl"></iconify-icon>
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-primary-light text-sm mb-8">Rate Inside Dhaka (Per KG) <span class="text-danger-600">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-base text-secondary-light border-end-0 radius-8 radius-end-0">৳</span>
                                    <input type="number" step="0.01" name="price_inside" class="form-control radius-8 border-start-0 ps-2" placeholder="60.00" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-primary-light text-sm mb-8">Rate Outside Dhaka (Per KG) <span class="text-danger-600">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-base text-secondary-light border-end-0 radius-8 radius-end-0">৳</span>
                                    <input type="number" step="0.01" name="price_outside" class="form-control radius-8 border-start-0 ps-2" placeholder="120.00" required>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold text-primary-light text-sm mb-8">Status</label>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="form-check d-flex align-items-center gap-2">
                                        <input class="form-check-input" type="radio" name="status" id="statusActive" value="Active" checked>
                                        <label class="form-check-label text-secondary-light" for="statusActive">
                                            Active
                                        </label>
                                    </div>
                                    <div class="form-check d-flex align-items-center gap-2">
                                        <input class="form-check-input" type="radio" name="status" id="statusInactive" value="Inactive">
                                        <label class="form-check-label text-secondary-light" for="statusInactive">
                                            Inactive
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 d-flex align-items-center justify-content-center gap-3 mt-4">
                                <a href="packages.php" class="border border-danger-600 bg-hover-danger-200 text-danger-600 text-md px-56 py-11 radius-8 text-decoration-none"> 
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-primary border border-primary-600 text-md px-56 py-12 radius-8"> 
                                    Save Package
                                </button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

<?php include 'inc/footer.php'; ?>