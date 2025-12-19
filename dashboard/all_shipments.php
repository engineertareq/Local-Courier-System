<?php
require 'db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// 1. SECURITY: Check Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// 2. FETCH USER PHONE
try {
    $stmt = $pdo->prepare("SELECT phone FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $userPhone = $user['phone'] ?? '';
} catch (Exception $e) {
    die("Database Error");
}

// 3. FETCH SHIPMENTS (With Search Logic)
$shipments = [];
if (!empty($userPhone)) {
    try {
        $sql = "SELECT * FROM parcels WHERE sender_phone = ?";
        $params = [$userPhone];

        // Add search filter if user typed something
        if (!empty($search)) {
            $sql .= " AND (tracking_number LIKE ? OR receiver_name LIKE ? OR receiver_phone LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $shipments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        $shipments = [];
    }
}
?>

<?php include "inc/header.php"?>

<div class="dashboard-main-body">

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <h6 class="fw-semibold mb-0">All Shipments</h6>
        <ul class="d-flex align-items-center gap-2">
            <li class="fw-medium">
                <a href="index.php" class="d-flex align-items-center gap-1 hover-text-primary">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                    Dashboard
                </a>
            </li>
            <li>-</li>
            <li class="fw-medium">History</li>
        </ul>
    </div>

    <div class="card h-100 p-0 radius-12">
        <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center flex-wrap gap-3 justify-content-between">
            
            <div class="d-flex align-items-center flex-wrap gap-3">
                <h5 class="card-title mb-0">Shipment History</h5>
                <span class="badge bg-primary-50 text-primary-600 border border-primary-100 round-4 lh-sm">
                    Total: <?= count($shipments) ?>
                </span>
            </div>

            <form class="d-flex align-items-center gap-2" method="GET">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search Tracking ID or Name..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn btn-primary">
                        <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
                    </button>
                    <?php if(!empty($search)): ?>
                        <a href="all_shipments.php" class="btn btn-light border" title="Clear Search">✖</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-4">Tracking ID</th>
                            <th scope="col">Receiver Info</th>
                            <th scope="col">Date Created</th>
                            <th scope="col">Status</th>
                            <th scope="col">Payment</th>
                            <th scope="col">Amount</th>
                            <th scope="col" class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($shipments) > 0): ?>
                            <?php foreach ($shipments as $row): ?>
                                <tr>
                                    <td class="ps-4">
                                        <span class="text-primary-600 fw-bold">#<?= $row['tracking_number'] ?></span>
                                        <br>
                                        <small class="text-muted"><?= $row['parcel_type'] ?></small>
                                    </td>

                                    <td>
                                        <h6 class="text-md mb-0 fw-medium"><?= htmlspecialchars($row['receiver_name']) ?></h6>
                                        <span class="text-sm text-secondary-light"><?= htmlspecialchars($row['receiver_phone']) ?></span>
                                    </td>

                                    <td>
                                        <?= date('M d, Y', strtotime($row['created_at'])) ?>
                                        <br>
                                        <small class="text-muted"><?= date('h:i A', strtotime($row['created_at'])) ?></small>
                                    </td>

                                    <td>
                                        <?php 
                                            // Status Badge Logic
                                            $st = $row['current_status'];
                                            $badgeClass = 'bg-secondary-focus text-secondary-main'; // Default
                                            
                                            if($st == 'pending') $badgeClass = 'bg-warning-focus text-warning-main';
                                            elseif($st == 'picked_up') $badgeClass = 'bg-info-focus text-info-main';
                                            elseif($st == 'in_transit') $badgeClass = 'bg-primary-focus text-primary-main';
                                            elseif($st == 'delivered') $badgeClass = 'bg-success-focus text-success-main';
                                            elseif($st == 'cancelled' || $st == 'returned') $badgeClass = 'bg-danger-focus text-danger-main';
                                        ?>
                                        <span class="badge rounded-pill <?= $badgeClass ?> px-3 py-2 fw-bold text-capitalize">
                                            <?= str_replace('_', ' ', $st) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <span class="text-sm fw-medium text-secondary-light">
                                            <?= htmlspecialchars($row['payment_method']) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <span class="text-md fw-bold text-dark">
                                            $<?= number_format($row['price'], 2) ?>
                                        </span>
                                    </td>

                                    <td class="text-end pe-4">
                                        <a href="track.php?id=<?= $row['tracking_number'] ?>" class="btn btn-sm btn-outline-primary fw-semibold d-inline-flex align-items-center gap-1">
                                            Track <iconify-icon icon="solar:arrow-right-linear"></iconify-icon>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <iconify-icon icon="solar:box-minimalistic-linear" class="text-secondary-light display-1 mb-3"></iconify-icon>
                                        <h5 class="text-secondary fw-bold">No Shipments Found</h5>
                                        <p class="text-muted">You haven't placed any orders yet.</p>
                                        <a href="create_parcel.php" class="btn btn-primary mt-2">Create First Shipment</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card-footer py-3 bg-white border-top">
            <small class="text-muted">Showing <?= count($shipments) ?> results</small>
        </div>
    </div>
</div>

<?php include "inc/footer.php"?>