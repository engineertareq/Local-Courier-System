<?php
require 'db.php';

$message = "";

// --- 1. Fetch Eligible Candidates ---
try {
    // FIXED: Removed 'phone_number' from this query to prevent the crash
    $sql = "SELECT user_id, full_name, email 
            FROM users 
            WHERE user_id NOT IN (SELECT user_id FROM couriers) 
            ORDER BY full_name ASC";
            
    $stmt = $pdo->query($sql);
    $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $candidates = [];
    $message = "<div class='alert alert-danger'>Database Error: " . $e->getMessage() . "</div>";
}

// --- 2. Handle Form Submission ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    $vehicle_type = $_POST['vehicle_type'];
    $plate_number = $_POST['vehicle_plate_number'];
    $status = $_POST['status'];
    // Default location to 0 if empty
    $lat = !empty($_POST['current_latitude']) ? $_POST['current_latitude'] : 0.000000;
    $lng = !empty($_POST['current_longitude']) ? $_POST['current_longitude'] : 0.000000;

    if (empty($user_id)) {
        $message = "<div class='alert alert-warning'>Please select a user first.</div>";
    } else {
        try {
            $sql = "INSERT INTO couriers (user_id, vehicle_type, vehicle_plate_number, status, current_latitude, current_longitude) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id, $vehicle_type, $plate_number, $status, $lat, $lng]);

            $message = "<div class='alert alert-success alert-dismissible fade show'>
                            Success! Courier has been registered.
                            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                        </div>";
            
            // Refresh candidates list
            $stmt = $pdo->query("SELECT user_id, full_name, email FROM users WHERE user_id NOT IN (SELECT user_id FROM couriers) ORDER BY full_name ASC");
            $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            $message = "<div class='alert alert-danger alert-dismissible fade show'>Error: " . $e->getMessage() . "<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
        }
    }
}
?>

<?php include "inc/header.php"?>

<style>
    .icon-field { position: relative; }
    .icon-field .icon {
        position: absolute;
        top: 50%;
        left: 16px;
        transform: translateY(-50%);
        font-size: 1.2rem;
        color: #6c757d;
        pointer-events: none;
        z-index: 5;
    }
    .icon-field .form-control,
    .icon-field .form-select {
        padding-left: 45px !important;
    }
</style>

<div class="dashboard-main-body">

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <h6 class="fw-semibold mb-0">Courier Management</h6>
        <ul class="d-flex align-items-center gap-2">
            <li class="fw-medium"><a href="index.html" class="hover-text-primary">Dashboard</a></li>
            <li>-</li>
            <li class="fw-medium">Add Courier</li>
        </ul>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Register New Courier</h5>
                </div>
                <div class="card-body">
                    
                    <?= $message ?>

                    <form method="POST">
                        <div class="row gy-3">
                            
                            <div class="col-md-6">
                                <label class="form-label">Select User Account <span class="text-danger">*</span></label>
                                <div class="icon-field">
                                    <span class="icon"><iconify-icon icon="solar:user-circle-bold-duotone"></iconify-icon></span>
                                    <select name="user_id" class="form-select" required>
                                        <option value="" disabled selected>Select a candidate...</option>
                                        <?php if(count($candidates) > 0): ?>
                                            <?php foreach ($candidates as $user): ?>
                                                <option value="<?= $user['user_id'] ?>">
                                                    <?= htmlspecialchars($user['full_name']) ?> (<?= htmlspecialchars($user['email']) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <option value="" disabled>No eligible users found</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <div class="form-text">Select a user to upgrade to Rider status.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Vehicle Type <span class="text-danger">*</span></label>
                                <div class="icon-field">
                                    <span class="icon"><iconify-icon icon="solar:bus-bold-duotone"></iconify-icon></span>
                                    <select name="vehicle_type" class="form-select" required>
                                        <option value="Bike" selected>Motorcycle / Bike</option>
                                        <option value="Bicycle">Bicycle</option>
                                        <option value="Van">Delivery Van</option>
                                        <option value="Truck">Truck</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Vehicle Plate Number <span class="text-danger">*</span></label>
                                <div class="icon-field">
                                    <span class="icon"><iconify-icon icon="solar:card-recive-bold-duotone"></iconify-icon></span>
                                    <input type="text" name="vehicle_plate_number" class="form-control" placeholder="EX: DHA-METRO-HA-1234" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Initial Status</label>
                                <div class="icon-field">
                                    <span class="icon"><iconify-icon icon="solar:flag-bold-duotone"></iconify-icon></span>
                                    <select name="status" class="form-select" required>
                                        <option value="active" selected>Active (Ready)</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="busy">Busy (On Delivery)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-12"><hr class="my-2"></div>

                            <div class="col-md-6">
                                <label class="form-label">Latitude</label>
                                <div class="icon-field">
                                    <span class="icon"><iconify-icon icon="solar:map-point-wave-bold-duotone"></iconify-icon></span>
                                    <input type="text" name="current_latitude" class="form-control" placeholder="0.000000">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Longitude</label>
                                <div class="icon-field">
                                    <span class="icon"><iconify-icon icon="solar:map-point-wave-bold-duotone"></iconify-icon></span>
                                    <input type="text" name="current_longitude" class="form-control" placeholder="0.000000">
                                </div>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary-600 px-4 w-100">Register Courier</button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "inc/footer.php" ?>