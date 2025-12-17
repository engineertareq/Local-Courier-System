<?php
session_start();
require 'db.php';

// --- 1. ACCESS CONTROL ---
// Ensure only authorized roles can access
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    // header("Location: login.php"); 
    // exit(); 
}

$message = "";

// --- 2. FORM SUBMISSION LOGIC ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $trk = $_POST['tracking_number'];
    $new_status = $_POST['status'];
    $location = $_POST['location'];
    $description = $_POST['description'];

    // Get Parcel ID based on Tracking Number
    $stmt = $pdo->prepare("SELECT parcel_id FROM parcels WHERE tracking_number = ?");
    $stmt->execute([$trk]);
    $parcel = $stmt->fetch();

    if ($parcel) {
        $parcel_id = $parcel['parcel_id'];

        try {
            $pdo->beginTransaction();

            // 1. Update Main Status in `parcels` table
            $stmtUpdate = $pdo->prepare("UPDATE parcels SET current_status = ? WHERE parcel_id = ?");
            $stmtUpdate->execute([$new_status, $parcel_id]);

            // 2. Add History Log in `parcel_history` table
            $stmtLog = $pdo->prepare("INSERT INTO parcel_history (parcel_id, status, description, location) VALUES (?, ?, ?, ?)");
            $stmtLog->execute([$parcel_id, ucwords(str_replace('_', ' ', $new_status)), $description, $location]);

            $pdo->commit();
            $message = "<div class='alert alert-success'>Status Updated Successfully!</div>";
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Invalid Tracking Number</div>";
    }
}

include 'inc/header.php';
?>

<div class="dashboard-main-body">

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <h6 class="fw-semibold mb-0">Update Delivery Status</h6>
        <ul class="d-flex align-items-center gap-2">
            <li class="fw-medium">
                <a href="index.html" class="d-flex align-items-center gap-1 hover-text-primary">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                    Dashboard
                </a>
            </li>
            <li>-</li>
            <li class="fw-medium">Update Status</li>
        </ul>
    </div>

    <div class="card h-100 p-0 radius-12">
        <div class="card-body p-24">
            <div class="row justify-content-center">
                <div class="col-xxl-6 col-xl-8 col-lg-10">
                    <div class="card border">
                        <div class="card-body">
                            
                            <?php echo $message; ?>

                            <h6 class="text-md text-primary-light mb-16">Update Info</h6>

                            <form method="POST" action="">
                                
                                <div class="mb-20">
                                    <label for="tracking_number" class="form-label fw-semibold text-primary-light text-sm mb-8">Tracking Number <span class="text-danger-600">*</span></label>
                                    <div class="position-relative">
                                        <input type="text" class="form-control radius-8 ps-40" id="tracking_number" name="tracking_number" placeholder="TRK-XXXXXX" required>
                                        <span class="position-absolute start-0 top-50 translate-middle-y ms-16 text-secondary-light">
                                            <iconify-icon icon="solar:box-minimalistic-outline" class="text-lg"></iconify-icon>
                                        </span>
                                    </div>
                                </div>

                                <div class="mb-20">
                                    <label for="status" class="form-label fw-semibold text-primary-light text-sm mb-8">New Status <span class="text-danger-600">*</span></label>
                                    <select class="form-control radius-8 form-select" id="status" name="status" required>
                                        <option value="picked_up">Picked Up</option>
                                        <option value="in_transit">In Transit</option>
                                        <option value="out_for_delivery">Out for Delivery</option>
                                        <option value="delivered">Delivered</option>
                                        <option value="failed">Delivery Failed</option>
                                    </select>
                                </div>

                                <div class="mb-20">
                                    <label for="location" class="form-label fw-semibold text-primary-light text-sm mb-8">Current Location <span class="text-danger-600">*</span></label>
                                    <div class="position-relative">
                                        <input type="text" class="form-control radius-8 ps-40" id="location" name="location" placeholder="e.g. Dhaka Hub" required>
                                        <span class="position-absolute start-0 top-50 translate-middle-y ms-16 text-secondary-light">
                                            <iconify-icon icon="solar:map-point-outline" class="text-lg"></iconify-icon>
                                        </span>
                                    </div>
                                </div>

                                <div class="mb-20">
                                    <label for="description" class="form-label fw-semibold text-primary-light text-sm mb-8">Description / Note</label>
                                    <textarea name="description" class="form-control radius-8" id="description" placeholder="e.g. Package arrived at facility..."></textarea>
                                </div>

                                <div class="d-flex align-items-center justify-content-center gap-3">
                                    <a href="index.html" class="border border-danger-600 bg-hover-danger-200 text-danger-600 text-md px-56 py-11 radius-8 text-decoration-none"> 
                                        Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary border border-primary-600 text-md px-56 py-12 radius-8"> 
                                        Update Status
                                    </button>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc/footer.php'; ?>