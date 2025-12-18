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
    
    $message = "<div class='alert alert-success border-0 shadow-sm mb-3 py-2'>
                    <div class='d-flex align-items-center gap-2'>
                        <iconify-icon icon='solar:check-circle-bold' class='fs-5'></iconify-icon>
                        <small class='fw-bold'>Updated Successfully!</small>
                    </div>
                </div>";
}
?>

<?php include "inc/header.php"?>

<style>
    :root {
        --brand-color: #dc3545;
        --brand-hover: #bb2d3b;
        --card-bg: #ffffff;
        --body-bg: #f8f9fa;
    }

    .dashboard-main-body { background-color: var(--body-bg); min-height: 90vh; }

    /* Compact Cards */
    .app-card {
        background: var(--card-bg);
        border: 1px solid #eaeaea;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        transition: transform 0.2s;
    }
    
    /* Stats Box - Compact */
    .stat-box {
        background: #fff;
        border-radius: 12px;
        padding: 15px; /* Reduced padding */
        border: 1px solid #f0f0f0;
        box-shadow: 0 2px 5px rgba(0,0,0,0.01);
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .stat-icon {
        width: 42px; height: 42px; /* Smaller icon box */
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }
    .icon-red { background: #fee2e2; color: #dc2626; }
    .icon-blue { background: #e0e7ff; color: #4338ca; }
    
    /* Stats Text */
    .stat-label { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: #6c757d; margin-bottom: 2px; }
    .stat-value { font-size: 1.1rem; font-weight: 800; color: #212529; margin: 0; }

    /* Form Elements - Compact Heights */
    .custom-select {
        height: 45px; /* Reduced from 55px */
        border-radius: 8px;
        border: 1px solid #dee2e6;
        font-weight: 600;
        font-size: 0.9rem;
        background-color: #fff;
    }
    .custom-select:focus { border-color: var(--brand-color); box-shadow: none; }

    .btn-gps {
        height: 45px; /* Reduced from 55px */
        border: 1px dashed #adb5bd;
        color: #495057;
        background: transparent;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.2s;
    }
    .btn-gps:hover { border-color: var(--brand-color); color: var(--brand-color); background: #fff5f5; }

    .btn-main {
        height: 45px; /* Reduced from 55px */
        background: var(--brand-color);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.9rem;
    }
    .btn-main:hover { background: var(--brand-hover); color: white; }

    /* Welcome Banner - Compact */
    .welcome-banner {
        background: #212529;
        color: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .welcome-banner h4 { font-weight: 700; margin-bottom: 4px; font-size: 1.25rem; }
    .welcome-banner p { font-size: 0.85rem; margin-bottom: 0; opacity: 0.8; }
    
    .status-badge {
        font-size: 0.75rem;
        background: rgba(255,255,255,0.15);
        padding: 4px 10px;
        border-radius: 4px;
        margin-top: 10px;
        display: inline-block;
    }
</style>

<div class="dashboard-main-body">
    <div class="container-fluid p-0">
        
        <?= $message ?>

        <div class="row g-4">
            <div class="col-lg-8">
                
                <div class="welcome-banner d-flex justify-content-between align-items-center">
                    <div>
                        <h4>Hello, <?= htmlspecialchars(explode(' ', $user_name)[0]) ?>!</h4>
                        <p>Ready for your deliveries?</p>
                        <div class="status-badge">
                            <iconify-icon icon="solar:bus-bold" class="me-1"></iconify-icon> 
                            <?= ucfirst($courier['vehicle_type']) ?> &bull; <?= $courier['vehicle_plate_number'] ?>
                        </div>
                    </div>
                    <div class="d-none d-md-block opacity-25">
                         <iconify-icon icon="solar:bicycling-bold-duotone" style="font-size: 60px;"></iconify-icon>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <div class="stat-box">
                            <div class="stat-icon icon-red">
                                <iconify-icon icon="solar:box-bold-duotone"></iconify-icon>
                            </div>
                            <div>
                                <div class="stat-label">Pending</div>
                                <h3 class="stat-value"><?= $active_tasks ?></h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="stat-box">
                            <div class="stat-icon icon-blue">
                                <iconify-icon icon="solar:wifi-router-round-bold-duotone"></iconify-icon>
                            </div>
                            <div>
                                <div class="stat-label">Status</div>
                                <h3 class="stat-value text-capitalize text-truncate" style="max-width: 100px;">
                                    <?= $courier['status'] ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>

                <a href="rider_tasks.php" class="app-card d-flex align-items-center justify-content-between p-3 text-decoration-none text-dark hover-lift">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-dark text-white rounded p-2 d-flex align-items-center justify-content-center">
                            <iconify-icon icon="solar:clipboard-list-bold-duotone" class="fs-5"></iconify-icon>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0" style="font-size: 0.95rem;">View Delivery List</h6>
                            <small class="text-secondary" style="font-size: 0.75rem;">Manage your active shipments</small>
                        </div>
                    </div>
                    <iconify-icon icon="solar:alt-arrow-right-bold" class="fs-5 text-secondary"></iconify-icon>
                </a>
            </div>

            <div class="col-lg-4">
                <div class="app-card p-3 h-100">
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                        <iconify-icon icon="solar:settings-bold-duotone" class="text-danger"></iconify-icon>
                        <h6 class="fw-bold mb-0" style="font-size: 0.95rem;">Control Panel</h6>
                    </div>

                    <form method="POST" id="statusForm">
                        <div class="mb-3">
                            <label class="fw-bold text-secondary small mb-1 text-uppercase" style="font-size: 0.7rem;">Availability</label>
                            <select name="status" class="form-select custom-select">
                                <option value="available" <?= $courier['status'] == 'available' ? 'selected' : '' ?>>🟢 Available</option>
                                <option value="busy" <?= $courier['status'] == 'busy' ? 'selected' : '' ?>>🔴 Busy</option>
                                <option value="offline" <?= $courier['status'] == 'offline' ? 'selected' : '' ?>>⚫ Offline</option>
                            </select>
                        </div>

                        <input type="hidden" name="latitude" id="lat" value="<?= $courier['current_latitude'] ?>">
                        <input type="hidden" name="longitude" id="lng" value="<?= $courier['current_longitude'] ?>">

                        <div class="mb-3">
                            <label class="fw-bold text-secondary small mb-1 text-uppercase" style="font-size: 0.7rem;">Location</label>
                            <button type="button" class="btn btn-gps w-100" id="gpsBtn" onclick="getLocation()">
                                <iconify-icon icon="solar:map-point-wave-bold" class="me-1 align-text-bottom"></iconify-icon>
                                <span id="gpsText">Fetch GPS</span>
                            </button>
                            <div id="locSuccess" class="text-success small fw-bold mt-1 text-center" style="display:none; font-size: 0.75rem;">
                                Coordinates Ready
                            </div>
                        </div>

                        <button type="submit" class="btn btn-main w-100">Update Status</button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="logout.php" class="text-muted text-decoration-none" style="font-size: 0.8rem;">
                            <iconify-icon icon="solar:logout-linear" class="align-middle"></iconify-icon> Sign Out
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
        btn.style.opacity = "0.6";
        txt.innerHTML = "Loading...";
        
        navigator.geolocation.getCurrentPosition(
            (position) => {
                document.getElementById("lat").value = position.coords.latitude;
                document.getElementById("lng").value = position.coords.longitude;
                
                txt.innerHTML = "GPS Synced";
                btn.style.opacity = "1";
                btn.style.borderColor = "#198754"; // Success Green
                btn.style.color = "#198754";
                btn.style.background = "#f8fff9";
                successMsg.style.display = "block";
            },
            (error) => {
                alert("GPS Error: " + error.message);
                btn.style.opacity = "1";
                txt.innerHTML = "Retry";
            }
        );
    } else { 
        alert("Geolocation not supported");
    }
}
</script>

<?php include "inc/footer.php" ?>