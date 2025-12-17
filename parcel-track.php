<?php
require 'admin/db.php';

$parcel = null;
$history_logs = [];
$error_msg = "";
$search_performed = false;

// Initialize variables to empty strings to avoid "Undefined key" warnings
$trk = "";
$phone = "";

// Check if form is submitted via POST or link via GET
if (isset($_REQUEST['tracking_number']) || isset($_REQUEST['phone_number'])) {
    $search_performed = true;
    
    // Safely get values using $_REQUEST (covers both $_GET and $_POST)
    $trk = $_REQUEST['tracking_number'] ?? '';
    $phone = $_REQUEST['phone_number'] ?? '';

    if (!empty($trk) && !empty($phone)) {
        // 1. Verify Tracking Number AND Phone Number match
        $stmt = $pdo->prepare("SELECT * FROM parcels WHERE tracking_number = ? AND receiver_phone = ?");
        $stmt->execute([trim($trk), trim($phone)]);
        $parcel = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($parcel) {
            // 2. Fetch History if match found
            $stmtHistory = $pdo->prepare("SELECT * FROM parcel_history WHERE parcel_id = ? ORDER BY timestamp DESC");
            $stmtHistory->execute([$parcel['parcel_id']]);
            $history_logs = $stmtHistory->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $error_msg = "No parcel found matching that Tracking ID and Phone Number.";
        }
    } else {
        $error_msg = "Please enter both Tracking ID and Phone Number.";
    }
}

// Helper for Status Colors
function getStatusColor($status) {
    switch (strtolower($status)) {
        case 'delivered': return 'success';
        case 'in_transit': return 'info';
        case 'picked_up': return 'primary';
        case 'cancelled': return 'danger';
        default: return 'warning';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Your Shipment | Public Tracking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    
    <style>
        body { background-color: #f5f7fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .hero-section {
            background: linear-gradient(135deg, #4834d4 0%, #686de0 100%);
            padding: 80px 0 100px;
            color: white;
            margin-bottom: -50px; /* Overlap effect */
        }
        .search-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            background: white;
        }
        
        /* Timeline Styles */
        .tracking-timeline { padding-left: 0; list-style: none; }
        .tracking-item { position: relative; padding-bottom: 2.5rem; padding-left: 2.5rem; border-left: 2px dashed #e0e0e0; }
        .tracking-item:last-child { border-left: 0; padding-bottom: 0; }
        .tracking-item .tracking-icon {
            position: absolute; left: -11px; top: 0; width: 22px; height: 22px;
            border-radius: 50%; background: #fff; border: 2px solid #6c757d;
            display: flex; align-items: center; justify-content: center;
        }
        .tracking-item.active .tracking-icon {
            border-color: #4834d4; background: #4834d4; box-shadow: 0 0 0 4px rgba(72, 52, 212, 0.2);
        }
        .tracking-item.active { border-left-color: #4834d4; }
    </style>
</head>
<body>

    <div class="hero-section text-center">
        <div class="container">
            <h1 class="fw-bold mb-3">Track Your Shipment</h1>
            <p class="opacity-75 fs-5">Enter your tracking number and phone number to see current status.</p>
        </div>
    </div>

    <div class="container pb-5">
        <div class="row justify-content-center">
            
            <div class="col-lg-8">
                <div class="search-card p-4 mb-4">
                    <form method="POST" class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold text-secondary">Tracking Number</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><iconify-icon icon="solar:box-minimalistic-outline"></iconify-icon></span>
                                <input type="text" name="tracking_number" class="form-control" placeholder="TRK-XXXXXX" value="<?= htmlspecialchars($_POST['tracking_number'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold text-secondary">Receiver Phone</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><iconify-icon icon="solar:phone-outline"></iconify-icon></span>
                                <input type="text" name="phone_number" class="form-control" placeholder="Enter Phone Number" value="<?= htmlspecialchars($_POST['phone_number'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100 fw-bold py-2" style="background-color: #4834d4; border:none;">
                                Track
                            </button>
                        </div>
                    </form>
                    
                    <?php if($error_msg): ?>
                        <div class="alert alert-danger mt-3 mb-0 d-flex align-items-center gap-2">
                            <iconify-icon icon="solar:danger-circle-outline" class="text-xl"></iconify-icon>
                            <?= $error_msg ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($parcel): ?>
            <?php $statusColor = getStatusColor($parcel['current_status']); ?>
            
            <div class="col-lg-10">
                <div class="row gy-4">
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                            <div class="card-body p-4">
                                <h5 class="fw-bold mb-4">Shipment Details</h5>
                                
                                <div class="text-center mb-4 p-3 bg-light rounded-3">
                                    <h3 class="mb-1 text-primary fw-bold"><?= htmlspecialchars($parcel['tracking_number']) ?></h3>
                                    <span class="badge bg-<?= $statusColor ?> px-3 py-2 rounded-pill text-uppercase">
                                        <?= str_replace('_', ' ', $parcel['current_status']) ?>
                                    </span>
                                </div>

                                <ul class="list-unstyled">
                                    <li class="mb-3">
                                        <small class="text-muted d-block">Receiver Name</small>
                                        <span class="fw-semibold"><?= htmlspecialchars($parcel['receiver_name']) ?></span>
                                    </li>
                                    <li class="mb-3">
                                        <small class="text-muted d-block">Destination Address</small>
                                        <span class="fw-semibold"><?= htmlspecialchars($parcel['receiver_address']) ?></span>
                                    </li>
                                    <li class="mb-3">
                                        <small class="text-muted d-block">Estimated / Last Update</small>
                                        <span class="fw-semibold"><?= date('F j, Y', strtotime($parcel['updated_at'])) ?></span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                            <div class="card-body p-4">
                                <h5 class="fw-bold mb-4">Tracking History</h5>
                                
                                <ul class="tracking-timeline">
                                    <?php foreach ($history_logs as $index => $log): ?>
                                        <li class="tracking-item <?= ($index === 0) ? 'active' : '' ?>">
                                            <div class="tracking-icon">
                                                <?php if ($index === 0): ?>
                                                    <div style="width: 8px; height: 8px; background: white; border-radius: 50%;"></div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="ps-2">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($log['status']) ?></h6>
                                                    <small class="text-muted bg-light px-2 py-1 rounded">
                                                        <?= date('M d, H:i A', strtotime($log['timestamp'])) ?>
                                                    </small>
                                                </div>
                                                <p class="text-muted small mb-1"><?= htmlspecialchars($log['description']) ?></p>
                                                <div class="small text-primary fw-semibold">
                                                    <iconify-icon icon="solar:map-point-outline" style="vertical-align: -2px"></iconify-icon> 
                                                    <?= htmlspecialchars($log['location']) ?>
                                                </div>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>

</body>
</html>