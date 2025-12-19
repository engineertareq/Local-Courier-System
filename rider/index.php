<?php
require 'db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// 1. SECURITY CHECK
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['full_name'] ?? 'Rider';
$message = "";

// 2. FETCH DATA
try {
    $stmt = $pdo->prepare("SELECT * FROM couriers WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $courier = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$courier) {
        die("<div class='p-4'><div class='alert alert-danger'>Access Denied. User ID $user_id is not a registered courier.</div></div>");
    }

    // Get Active Tasks Count
    $stmtTask = $pdo->prepare("SELECT COUNT(*) FROM parcels WHERE assigned_courier_id = ? AND current_status NOT IN ('delivered', 'cancelled', 'returned')");
    $stmtTask->execute([$courier['courier_id']]);
    $active_tasks = $stmtTask->fetchColumn();

} catch (Exception $e) {
    die("DB Error: " . $e->getMessage());
}

// 3. HANDLE FORM SUBMISSION
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_status = $_POST['status'];
    $lat = $_POST['latitude'];
    $lng = $_POST['longitude'];

    $sql = "UPDATE couriers SET status = ?, current_latitude = ?, current_longitude = ? WHERE courier_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$new_status, $lat, $lng, $courier['courier_id']]);
    
    // Refresh local data
    $courier['status'] = $new_status;
    $courier['current_latitude'] = $lat;
    $courier['current_longitude'] = $lng;
    
    $message = "<div class='alert alert-success bg-success-100 text-success-600 border-0 radius-8 mb-24 py-12 px-24 d-flex align-items-center gap-2'>
                    <iconify-icon icon='solar:check-circle-bold' class='text-xl'></iconify-icon>
                    <div class='fw-semibold text-sm'>Status & Location Updated Successfully!</div>
                </div>";
}
?>

<?php include "inc/header.php"?>

<div class="dashboard-main-body">

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <h6 class="fw-semibold mb-0">Rider Dashboard</h6>
        <ul class="d-flex align-items-center gap-2">
            <li class="fw-medium">
                <a href="#" class="d-flex align-items-center gap-1 hover-text-primary">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                    Home
                </a>
            </li>
            <li>-</li>
            <li class="fw-medium">Overview</li>
        </ul>
    </div>

    <?= $message ?>

    <div class="row gy-4">
        <div class="col-lg-8">
            
            <div class="card bg-primary-600 text-white mb-24 border-0 radius-12 overflow-hidden" style="background: linear-gradient(45deg, #4154f1, #2effff);">
                <div class="card-body p-24 d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h4 class="fw-bold mb-2 text-white">Hello, <?= htmlspecialchars(explode(' ', $user_name)[0]) ?>! 🚴</h4>
                        <p class="mb-0 text-white-50">Ready to deliver some happiness today?</p>
                        
                        <div class="mt-3 d-inline-flex align-items-center gap-2 bg-white bg-opacity-10 px-12 py-6 radius-8 border border-white border-opacity-25">
                            <iconify-icon icon="solar:bus-bold" class="text-white text-lg"></iconify-icon> 
                            <span class="text-sm fw-medium"><?= ucfirst($courier['vehicle_type']) ?> &bull; <?= $courier['vehicle_plate_number'] ?></span>
                        </div>
                    </div>
                    <div class="d-none d-sm-block opacity-50">
                        <iconify-icon icon="solar:bicycling-bold-duotone" style="font-size: 80px;" class="text-white"></iconify-icon>
                    </div>
                </div>
            </div>

            <div class="row gy-4 mb-24">
                <div class="col-sm-6">
                    <div class="card h-100 radius-12 border-0 shadow-none bg-base">
                        <div class="card-body p-24 d-flex align-items-center gap-3">
                            <div class="w-50-px h-50-px d-flex justify-content-center align-items-center bg-warning-50 text-warning-600 rounded-circle">
                                <iconify-icon icon="solar:box-bold-duotone" class="text-2xl"></iconify-icon>
                            </div>
                            <div>
                                <p class="fw-medium text-secondary-light mb-1">Pending Delivery</p>
                                <h6 class="fw-bold mb-0 text-primary-light"><?= $active_tasks ?> Parcels</h6>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6">
                    <div class="card h-100 radius-12 border-0 shadow-none bg-base">
                        <div class="card-body p-24 d-flex align-items-center gap-3">
                            <div class="w-50-px h-50-px d-flex justify-content-center align-items-center bg-info-50 text-info-600 rounded-circle">
                                <iconify-icon icon="solar:wifi-router-round-bold-duotone" class="text-2xl"></iconify-icon>
                            </div>
                            <div>
                                <p class="fw-medium text-secondary-light mb-1">Current Status</p>
                                <?php 
                                    $statusColor = 'text-secondary-light';
                                    if($courier['status'] == 'available') $statusColor = 'text-success-600';
                                    if($courier['status'] == 'busy') $statusColor = 'text-danger-600';
                                ?>
                                <h6 class="fw-bold mb-0 <?= $statusColor ?> text-capitalize"><?= $courier['status'] ?></h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <a href="rider_tasks.php" class="card h-100 radius-12 border border-primary-100 hover-bg-primary-50 transition-2">
                <div class="card-body p-24 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="w-48-px h-48-px bg-primary-600 text-white rounded-circle d-flex justify-content-center align-items-center">
                            <iconify-icon icon="solar:clipboard-list-bold-duotone" class="text-2xl"></iconify-icon>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1 text-primary-light">Manage Delivery List</h6>
                            <p class="text-sm text-secondary-light mb-0">View details and update parcel status</p>
                        </div>
                    </div>
                    <div class="w-32-px h-32-px bg-white text-primary-600 rounded-circle d-flex justify-content-center align-items-center shadow-sm">
                        <iconify-icon icon="solar:arrow-right-linear" class="text-xl"></iconify-icon>
                    </div>
                </div>
            </a>

        </div>

        <div class="col-lg-4">
            <div class="card h-100 p-0 radius-12">
                <div class="card-header border-bottom bg-base py-16 px-24">
                    <h6 class="text-lg fw-semibold mb-0">Control Panel</h6>
                </div>
                <div class="card-body p-24">
                    <form method="POST" id="statusForm">
                        
                        <div class="mb-20">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Availability Status</label>
                            <select name="status" class="form-select form-control radius-8 h-40-px">
                                <option value="available" <?= $courier['status'] == 'available' ? 'selected' : '' ?>>🟢 Available</option>
                                <option value="busy" <?= $courier['status'] == 'busy' ? 'selected' : '' ?>>🔴 Busy</option>
                                <option value="offline" <?= $courier['status'] == 'offline' ? 'selected' : '' ?>>⚫ Offline</option>
                            </select>
                        </div>

                        <input type="hidden" name="latitude" id="lat" value="<?= $courier['current_latitude'] ?>">
                        <input type="hidden" name="longitude" id="lng" value="<?= $courier['current_longitude'] ?>">

                        <div class="mb-24">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Location Sync</label>
                            <button type="button" class="btn btn-outline-primary w-100 radius-8 d-flex justify-content-center align-items-center gap-2" id="gpsBtn" onclick="getLocation()">
                                <iconify-icon icon="solar:map-point-wave-bold" class="text-xl"></iconify-icon>
                                <span id="gpsText">Fetch Current GPS</span>
                            </button>
                            <div id="locSuccess" class="text-success-600 text-sm fw-medium mt-8 text-center" style="display:none;">
                                <iconify-icon icon="solar:check-circle-bold" class="align-middle me-1"></iconify-icon> Coordinates Ready
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary-600 w-100 radius-8 py-12">
                            Update Status
                        </button>
                    </form>

                    <div class="mt-24 pt-24 border-top text-center">
                        <a href="logout.php" class="text-danger-600 fw-semibold hover-text-danger-700 d-inline-flex align-items-center gap-2">
                            <iconify-icon icon="solar:logout-linear" class="text-xl"></iconify-icon> Sign Out
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function getLocation() {
    const btn = document.getElementById("gpsBtn");
    const txt = document.getElementById("gpsText");
    const successMsg = document.getElementById("locSuccess");

    if (navigator.geolocation) {
        btn.classList.add('disabled');
        txt.innerHTML = "Locating...";
        
        navigator.geolocation.getCurrentPosition(
            (position) => {
                document.getElementById("lat").value = position.coords.latitude;
                document.getElementById("lng").value = position.coords.longitude;
                
                txt.innerHTML = "GPS Synced";
                btn.classList.remove('btn-outline-primary');
                btn.classList.add('btn-success-600', 'text-white');
                btn.classList.remove('disabled');
                
                successMsg.style.display = "block";
            },
            (error) => {
                alert("GPS Error: " + error.message);
                btn.classList.remove('disabled');
                txt.innerHTML = "Retry GPS";
            }
        );
    } else { 
        alert("Geolocation not supported");
    }
}
</script>

<?php include "inc/footer.php" ?>