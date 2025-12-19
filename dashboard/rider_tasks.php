<?php
require 'db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// 1. SECURITY: Check if logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

// 2. GET COURIER ID
try {
    $stmt = $pdo->prepare("SELECT courier_id FROM couriers WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $courier_id = $stmt->fetchColumn();

    if (!$courier_id) {
        die("<div class='alert alert-danger m-4'>Access Denied. You are not a registered courier. <a href='logout.php'>Logout</a></div>");
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// 3. HANDLE STATUS UPDATES
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_parcel'])) {
    $parcel_id = $_POST['parcel_id'];
    $new_status = $_POST['parcel_status'];
    
    // Map status to a readable description for history
    $desc_map = [
        'picked_up' => 'Courier has picked up the parcel.',
        'in_transit' => 'Shipment is on the way.',
        'out_for_delivery' => 'Courier is out for delivery.',
        'delivered' => 'Package delivered successfully.',
        'returned' => 'Package returned to hub.'
    ];
    $desc = $desc_map[$new_status] ?? 'Status updated by courier.';

    try {
        $pdo->beginTransaction();

        // Update Parcel Status
        $stmt = $pdo->prepare("UPDATE parcels SET current_status = ? WHERE parcel_id = ? AND assigned_courier_id = ?");
        $stmt->execute([$new_status, $parcel_id, $courier_id]);

        // Add History Entry
        $stmt = $pdo->prepare("INSERT INTO parcel_history (parcel_id, status, description, location, updated_by_user_id) VALUES (?, ?, ?, 'On Route', ?)");
        $stmt->execute([$parcel_id, ucwords(str_replace('_', ' ', $new_status)), $desc, $user_id]);

        $pdo->commit();
        $message = "<div class='alert alert-success fixed-top m-3 shadow' style='z-index: 1050;'>
                        <iconify-icon icon='solar:check-circle-bold' class='align-middle'></iconify-icon> 
                        Status Updated! 
                        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                    </div>";
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
}

// 4. FETCH ASSIGNED TASKS (Showing ALL active tasks)
// We only exclude 'delivered' and 'cancelled' to ensure you see everything else.
try {
    $sql = "SELECT * FROM parcels 
            WHERE assigned_courier_id = ? 
            AND current_status NOT IN ('delivered', 'cancelled') 
            ORDER BY created_at ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$courier_id]);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $tasks = [];
    $message = "<div class='alert alert-danger'>Fetch Error: " . $e->getMessage() . "</div>";
}
?>

<?php include "inc/header.php"?>

<div class="dashboard-main-body">
    <?= $message ?>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-0 fw-bold">My Deliveries</h5>
            <small class="text-secondary"><?= count($tasks) ?> pending tasks</small>
        </div>
        <a href="index.php" class="btn btn-sm btn-outline-secondary">
            <iconify-icon icon="solar:arrow-left-linear" class="align-middle"></iconify-icon> Dashboard
        </a>
    </div>

    <div class="row gy-3">
        <?php if (count($tasks) > 0): ?>
            <?php foreach ($tasks as $task): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden">
                        
                        <div class="card-header bg-primary-50 d-flex justify-content-between align-items-center py-3">
                            <div>
                                <span class="d-block fw-bold text-primary"><?= $task['tracking_number'] ?></span>
                            </div>
                            <span class="badge bg-white text-dark border shadow-sm">
                                $<?= number_format($task['price'], 2) ?>
                            </span>
                        </div>

                        <div class="card-body">
                            <div class="mb-3">
                                <?php if(stripos($task['payment_method'], 'cash') !== false): ?>
                                    <div class="alert alert-warning py-2 px-3 fs-small mb-0 d-flex align-items-center gap-2">
                                        <iconify-icon icon="solar:hand-money-bold" class="fs-5"></iconify-icon> 
                                        <div>
                                            <small class="d-block lh-1 text-muted">Collect Cash</small>
                                            <strong class="text-dark">$<?= number_format($task['price'], 2) ?></strong>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="badge bg-secondary-focus text-secondary-main border px-3 py-2"><?= $task['payment_method'] ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="timeline-sm">
                                <div class="d-flex gap-3 mb-3">
                                    <div class="mt-1"><iconify-icon icon="solar:box-bold" class="text-secondary fs-5"></iconify-icon></div>
                                    <div>
                                        <small class="text-muted fw-bold d-block text-uppercase" style="font-size: 0.7rem;">Pickup From</small>
                                        <span class="fw-medium text-dark"><?= htmlspecialchars($task['sender_name']) ?></span>
                                        <small class="d-block text-secondary"><?= htmlspecialchars($task['sender_address']) ?></small>
                                    </div>
                                </div>
                                <div class="d-flex gap-3">
                                    <div class="mt-1"><iconify-icon icon="solar:map-point-bold" class="text-primary fs-5"></iconify-icon></div>
                                    <div>
                                        <small class="text-muted fw-bold d-block text-uppercase" style="font-size: 0.7rem;">Deliver To</small>
                                        <span class="fw-medium text-dark d-block mb-1"><?= htmlspecialchars($task['receiver_address']) ?></span>
                                        <div class="d-flex gap-2 mt-2">
                                            <a href="tel:<?= $task['receiver_phone'] ?>" class="btn btn-sm btn-success-focus text-success-main py-1 px-3 fw-bold">
                                                <iconify-icon icon="solar:phone-bold"></iconify-icon> Call
                                            </a>
                                            <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($task['receiver_address']) ?>" target="_blank" class="btn btn-sm btn-primary-focus text-primary-main py-1 px-3 fw-bold">
                                                <iconify-icon icon="solar:map-point-bold"></iconify-icon> Map
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-white border-top p-3">
                            <form method="POST">
                                <input type="hidden" name="parcel_id" value="<?= $task['parcel_id'] ?>">
                                <label class="form-label text-xs fw-bold text-secondary mb-1">UPDATE STATUS</label>
                                <div class="input-group">
                                    <select name="parcel_status" class="form-select form-select-sm fw-bold border-primary">
                                        <option value="picked_up" <?= $task['current_status']=='picked_up'?'selected':'' ?>>📦 Picked Up</option>
                                        <option value="in_transit" <?= $task['current_status']=='in_transit'?'selected':'' ?>>🚚 In Transit</option>
                                        <option value="out_for_delivery" <?= $task['current_status']=='out_for_delivery'?'selected':'' ?>>🛵 Out for Delivery</option>
                                        <option value="delivered" class="text-success fw-bold">✅ Delivered</option>
                                        <option value="returned" class="text-danger fw-bold">↩️ Returned</option>
                                    </select>
                                    <button type="submit" name="update_parcel" class="btn btn-primary btn-sm fw-bold px-3">Update</button>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="mb-3">
                    <iconify-icon icon="solar:clipboard-check-linear" class="text-gray-200 display-1"></iconify-icon>
                </div>
                <h5 class="text-secondary fw-bold">No Pending Deliveries</h5>
                <p class="text-muted">Good job! You have no active tasks right now.</p>
                </div>
        <?php endif; ?>
    </div>
</div>

<?php include "inc/footer.php" ?>