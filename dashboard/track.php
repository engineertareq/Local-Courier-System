<?php
session_start();
require 'db.php';
include 'inc/header.php'; 

$tracking_data = null;
$history_logs = [];
$search_performed = false;
$access_denied = false; 


$currentUserPhone = '';
if (isset($_SESSION['user_id'])) {
    $stmtUser = $pdo->prepare("SELECT phone FROM users WHERE user_id = ?");
    $stmtUser->execute([$_SESSION['user_id']]);
    $userRow = $stmtUser->fetch(PDO::FETCH_ASSOC);
    $currentUserPhone = $userRow['phone'] ?? '';
}

$search_term = $_GET['id'] ?? $_GET['tracking_number'] ?? null;

if ($search_term) {
    $search_performed = true;
    $term = trim($search_term);

    $stmt = $pdo->prepare("SELECT * FROM parcels WHERE tracking_number = ? OR parcel_id = ?");
    $stmt->execute([$term, $term]);
    $found_parcel = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($found_parcel) {
        
        if (!empty($currentUserPhone) && 
           ($found_parcel['sender_phone'] == $currentUserPhone || $found_parcel['receiver_phone'] == $currentUserPhone)) {
      
            $tracking_data = $found_parcel;

            $stmtHistory = $pdo->prepare("SELECT * FROM parcel_history WHERE parcel_id = ? ORDER BY timestamp DESC");
            $stmtHistory->execute([$tracking_data['parcel_id']]);
            $history_logs = $stmtHistory->fetchAll(PDO::FETCH_ASSOC);

        } else {
            $access_denied = true;
        }
    }
}

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

<style>
    .tracking-timeline { position: relative; padding-left: 0; list-style: none; }
    .tracking-item { position: relative; padding-bottom: 2.5rem; padding-left: 2.5rem; border-left: 2px dashed #e0e0e0; }
    .tracking-item:last-child { border-left: 0; padding-bottom: 0; }
    .tracking-item .tracking-icon { position: absolute; left: -11px; top: 0; width: 22px; height: 22px; border-radius: 50%; background: #fff; border: 2px solid #6c757d; display: flex; align-items: center; justify-content: center; }
    .tracking-item.active .tracking-icon { border-color: var(--bs-primary); background: var(--bs-primary); box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.2); }
    .tracking-item.active { border-left-color: var(--bs-primary); }
    .tracking-date { font-size: 0.85rem; color: #6c757d; margin-bottom: 4px; }
</style>

<div class="dashboard-main-body">

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <h6 class="fw-semibold mb-0">Track Parcel</h6>
        <ul class="d-flex align-items-center gap-2">
            <li class="fw-medium">
                <a href="index.html" class="d-flex align-items-center gap-1 hover-text-primary">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                    Dashboard
                </a>
            </li>
            <li>-</li>
            <li class="fw-medium">Track Parcel</li>
        </ul>
    </div>

    <div class="card h-100 p-0 radius-12 mb-24">
        <div class="card-body p-24">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <form method="GET" class="position-relative">
                        <input type="text" name="tracking_number" 
                               class="form-control radius-8 ps-5 py-2" 
                               placeholder="Enter Tracking ID (e.g. TRK-123456)" 
                               value="<?= $tracking_data ? htmlspecialchars($tracking_data['tracking_number']) : (isset($_GET['tracking_number']) ? htmlspecialchars($_GET['tracking_number']) : '') ?>" 
                               required>
                        <span class="position-absolute start-0 top-50 translate-middle-y ms-3 text-secondary-light">
                            <iconify-icon icon="solar:magnifer-linear" class="text-xl"></iconify-icon>
                        </span>
                        <button type="submit" class="btn btn-primary position-absolute top-0 end-0 h-100 radius-8 px-4">Track</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php if ($tracking_data): ?>
        <?php $statusColor = getStatusColor($tracking_data['current_status']); ?>
        
        <div class="row gy-4">
            <div class="col-lg-4">
                <div class="card h-100 radius-12 border-0 shadow-sm">
                    <div class="card-header bg-base border-bottom py-3">
                        <h6 class="mb-0 fw-bold">Parcel Details</h6>
                    </div>
                    <div class="card-body p-24">
                        <div class="text-center mb-4">
                            <div class="w-64-px h-64-px bg-<?php echo $statusColor; ?>-focus text-<?php echo $statusColor; ?>-600 rounded-circle d-flex justify-content-center align-items-center mx-auto mb-3">
                                <iconify-icon icon="solar:box-minimalistic-outline" class="text-3xl"></iconify-icon>
                            </div>
                            <h6 class="mb-1"><?= htmlspecialchars($tracking_data['tracking_number']) ?></h6>
                            <span class="badge bg-<?php echo $statusColor; ?>-focus text-<?php echo $statusColor; ?>-600 px-3 py-1 radius-4">
                                <?= ucwords(str_replace('_', ' ', $tracking_data['current_status'])) ?>
                            </span>
                        </div>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="w-40-px h-40-px bg-neutral-100 rounded-circle d-flex justify-content-center align-items-center">
                                <iconify-icon icon="solar:user-outline" class="text-lg text-secondary-light"></iconify-icon>
                            </div>
                            <div>
                                <span class="text-secondary-light text-sm d-block">Receiver</span>
                                <h6 class="text-sm mb-0"><?= htmlspecialchars($tracking_data['receiver_name']) ?></h6>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="w-40-px h-40-px bg-neutral-100 rounded-circle d-flex justify-content-center align-items-center">
                                <iconify-icon icon="solar:map-point-outline" class="text-lg text-secondary-light"></iconify-icon>
                            </div>
                            <div>
                                <span class="text-secondary-light text-sm d-block">Destination</span>
                                <h6 class="text-sm mb-0"><?= htmlspecialchars($tracking_data['receiver_address']) ?></h6>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="w-40-px h-40-px bg-neutral-100 rounded-circle d-flex justify-content-center align-items-center">
                                <iconify-icon icon="solar:calendar-outline" class="text-lg text-secondary-light"></iconify-icon>
                            </div>
                            <div>
                                <span class="text-secondary-light text-sm d-block">Created At</span>
                                <h6 class="text-sm mb-0"><?= date('d M Y', strtotime($tracking_data['created_at'])) ?></h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card h-100 radius-12 border-0 shadow-sm">
                    <div class="card-header bg-base border-bottom py-3">
                        <h6 class="mb-0 fw-bold">Tracking History</h6>
                    </div>
                    <div class="card-body p-24">
                        <ul class="tracking-timeline">
                            <?php foreach ($history_logs as $index => $log): ?>
                                <li class="tracking-item <?php echo ($index === 0) ? 'active' : ''; ?>">
                                    <div class="tracking-icon">
                                        <?php if ($index === 0): ?>
                                            <div class="w-8-px h-8-px bg-white rounded-circle"></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="tracking-content">
                                        <div class="d-flex justify-content-between align-items-center flex-wrap mb-1">
                                            <h6 class="fw-bold mb-0 text-md"><?= htmlspecialchars($log['status']) ?></h6>
                                            <span class="tracking-date bg-neutral-100 px-2 py-1 radius-4 text-xs">
                                                <?= date('d M Y, h:i A', strtotime($log['timestamp'])) ?>
                                            </span>
                                        </div>
                                        <p class="text-secondary-light text-sm mb-1"><?= htmlspecialchars($log['description']) ?></p>
                                        <div class="d-flex align-items-center gap-1 text-xs text-primary-600 fw-medium">
                                            <iconify-icon icon="solar:map-point-outline"></iconify-icon>
                                            <?= htmlspecialchars($log['location']) ?>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                            
                            <?php if (empty($history_logs)): ?>
                                <li class="text-center py-4 text-secondary-light">
                                    No history logs found for this parcel yet.
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    <?php elseif ($access_denied): ?>
        <div class="card h-100 p-0 radius-12">
            <div class="card-body p-24 text-center">
                <div class="mb-3">
                    <iconify-icon icon="solar:shield-warning-bold-duotone" class="text-6xl text-warning-500"></iconify-icon>
                </div>
                <h5 class="mb-2 text-danger">Access Denied</h5>
                <p class="text-secondary-light">
                    You are not authorized to view this parcel.<br>
                    Your registered phone number <strong>(<?= htmlspecialchars($currentUserPhone) ?>)</strong> does not match the Sender or Receiver.
                </p>
                <a href="track.php" class="btn btn-outline-primary mt-3">Try Another ID</a>
            </div>
        </div>

    <?php elseif ($search_performed): ?>
        <div class="card h-100 p-0 radius-12">
            <div class="card-body p-24 text-center">
                <div class="mb-3">
                    <iconify-icon icon="solar:box-minimalistic-broken-line-duotone" class="text-6xl text-danger-500"></iconify-icon>
                </div>
                <h5 class="mb-2">Tracking ID Not Found</h5>
                <p class="text-secondary-light">We couldn't find any parcel matching your query.</p>
                <a href="track_parcel.php" class="btn btn-primary mt-3">Try Again</a>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php include 'inc/footer.php'; ?>