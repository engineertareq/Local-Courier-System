<?php
require 'admin/db.php'; // Adjust path if necessary

$parcel = null;
$history_logs = [];
$error_msg = "";
$search_performed = false;

// Initialize variables
$trk = "";
$phone = "";

// Check if form is submitted
if (isset($_REQUEST['tracking_number']) || isset($_REQUEST['phone_number'])) {
    $search_performed = true;
    
    $trk = $_REQUEST['tracking_number'] ?? '';
    $phone = $_REQUEST['phone_number'] ?? '';

    if (!empty($trk) && !empty($phone)) {
        // 1. Verify Tracking Number AND Phone Number
        $stmt = $pdo->prepare("SELECT * FROM parcels WHERE tracking_number = ? AND receiver_phone = ?");
        $stmt->execute([trim($trk), trim($phone)]);
        $parcel = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($parcel) {
            // 2. Fetch History
            $stmtHistory = $pdo->prepare("SELECT * FROM parcel_history WHERE parcel_id = ? ORDER BY timestamp DESC");
            $stmtHistory->execute([$parcel['parcel_id']]);
            $history_logs = $stmtHistory->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $error_msg = "No parcel found matching these details.";
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
        case 'returned': return 'warning';
        default: return 'secondary';
    }
}
?>

<?php include 'inc/header.php'; ?>

<style>
    /* Hero Section */
    .tracking-hero {
        background: linear-gradient(135deg, #0f172a 0%, #334155 100%);
        padding: 80px 0 120px; /* Extra bottom padding for overlap */
        color: white;
        text-align: center;
        margin-bottom: -60px; /* Creates the overlap effect */
    }

    /* Search Card */
    .tracking-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        border: none;
        position: relative; /* Essential for z-index */
        z-index: 2;         /* Puts card ON TOP of the hero background */
    }

    /* Inputs */
    .form-control-lg {
        height: 50px;
        font-size: 1rem;
        border-color: #e2e8f0;
    }
    .form-control-lg:focus {
        border-color: #4834d4;
        box-shadow: 0 0 0 4px rgba(72, 52, 212, 0.1);
    }
    .btn-track {
        height: 50px;
        background-color: #4834d4;
        border: none;
        font-weight: 600;
        letter-spacing: 0.5px;
        transition: all 0.2s;
    }
    .btn-track:hover { background-color: #3624a7; transform: translateY(-1px); }

    /* Timeline Styles */
    .timeline {
        list-style: none;
        padding: 0;
        position: relative;
    }
    .timeline::before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        left: 20px;
        width: 2px;
        background: #e2e8f0;
    }
    .timeline-item {
        position: relative;
        padding-left: 50px;
        padding-bottom: 30px;
    }
    .timeline-item:last-child { padding-bottom: 0; }
    
    .timeline-icon {
        position: absolute;
        left: 10px;
        top: 0;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #fff;
        border: 3px solid #cbd5e1;
        z-index: 1;
    }
    
    /* Active State (Topmost item) */
    .timeline-item.current .timeline-icon {
        border-color: #4834d4;
        background: #4834d4;
        box-shadow: 0 0 0 4px rgba(72, 52, 212, 0.15);
    }
    .timeline-item.current::before {
        content: '';
        position: absolute;
        left: 20px;
        top: 22px;
        bottom: 0;
        width: 2px;
        background: #4834d4; /* Color the line for active path if needed */
    }
</style>

<div class="tracking-hero">
    <div class="container">
        <h1 class="fw-bold mb-2">Track Your Shipment</h1>
        <p class="opacity-75 fs-5 mb-0">Enter your details below to check delivery status.</p>
    </div>
</div>

<div class="container pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">
            
            <div class="tracking-card p-4 p-md-5 mb-5">
                <form method="POST" class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label fw-bold text-secondary text-uppercase small">Tracking Number</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-secondary"><iconify-icon icon="solar:box-minimalistic-bold-duotone"></iconify-icon></span>
                            <input type="text" name="tracking_number" class="form-control form-control-lg border-start-0 ps-0" placeholder="TRK-123456" value="<?= htmlspecialchars($trk) ?>" required>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-bold text-secondary text-uppercase small">Receiver Phone</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-secondary"><iconify-icon icon="solar:phone-bold-duotone"></iconify-icon></span>
                            <input type="text" name="phone_number" class="form-control form-control-lg border-start-0 ps-0" placeholder="017..." value="<?= htmlspecialchars($phone) ?>" required>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-track w-100 rounded-3">
                            Track <iconify-icon icon="solar:arrow-right-linear" class="align-middle ms-1"></iconify-icon>
                        </button>
                    </div>
                </form>

                <?php if($error_msg): ?>
                    <div class="alert alert-danger mt-4 mb-0 border-0 bg-danger-subtle text-danger d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:danger-circle-bold"></iconify-icon>
                        <?= $error_msg ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($parcel): ?>
                <?php $statusColor = getStatusColor($parcel['current_status']); ?>
                
                <div class="row g-4">
                    
                    <div class="col-md-5">
                        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                                <h6 class="fw-bold mb-0">Shipment Info</h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="text-center mb-4 pb-3 border-bottom">
                                    <h3 class="text-primary fw-bold mb-1"><?= htmlspecialchars($parcel['tracking_number']) ?></h3>
                                    <span class="badge bg-<?= $statusColor ?>-subtle text-<?= $statusColor ?> border border-<?= $statusColor ?> px-3 py-1 rounded-pill text-uppercase" style="font-size: 0.75rem;">
                                        <?= str_replace('_', ' ', $parcel['current_status']) ?>
                                    </span>
                                </div>

                                <div class="d-flex align-items-start gap-3 mb-3">
                                    <div class="mt-1"><iconify-icon icon="solar:user-circle-bold" class="text-secondary fs-5"></iconify-icon></div>
                                    <div>
                                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.7rem;">Receiver</small>
                                        <span class="fw-semibold text-dark"><?= htmlspecialchars($parcel['receiver_name']) ?></span>
                                    </div>
                                </div>

                                <div class="d-flex align-items-start gap-3 mb-3">
                                    <div class="mt-1"><iconify-icon icon="solar:map-point-bold" class="text-secondary fs-5"></iconify-icon></div>
                                    <div>
                                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.7rem;">Destination</small>
                                        <span class="fw-semibold text-dark"><?= htmlspecialchars($parcel['receiver_address']) ?></span>
                                    </div>
                                </div>

                                <div class="d-flex align-items-start gap-3">
                                    <div class="mt-1"><iconify-icon icon="solar:calendar-bold" class="text-secondary fs-5"></iconify-icon></div>
                                    <div>
                                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.7rem;">Last Update</small>
                                        <span class="fw-semibold text-dark"><?= date('M d, Y - h:i A', strtotime($parcel['updated_at'])) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                                <h6 class="fw-bold mb-0">Tracking History</h6>
                            </div>
                            <div class="card-body p-4">
                                <?php if(empty($history_logs)): ?>
                                    <p class="text-muted text-center py-3">No history available yet.</p>
                                <?php else: ?>
                                    <ul class="timeline">
                                        <?php foreach ($history_logs as $index => $log): ?>
                                            <li class="timeline-item <?= ($index === 0) ? 'current' : '' ?>">
                                                <div class="timeline-icon"></div>
                                                <div class="d-flex justify-content-between mb-1">
                                                    <span class="fw-bold text-dark"><?= htmlspecialchars($log['status']) ?></span>
                                                    <small class="text-muted text-end" style="font-size: 0.75rem;">
                                                        <?= date('M d', strtotime($log['timestamp'])) ?><br>
                                                        <?= date('h:i A', strtotime($log['timestamp'])) ?>
                                                    </small>
                                                </div>
                                                <p class="text-muted small mb-1"><?= htmlspecialchars($log['description']) ?></p>
                                                <div class="small fw-semibold text-primary">
                                                    <iconify-icon icon="solar:map-point-linear" class="align-middle"></iconify-icon> 
                                                    <?= htmlspecialchars($log['location']) ?>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php include 'inc/footer.php'; ?>