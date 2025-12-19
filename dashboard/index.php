<?php
require 'db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// 1. SECURITY: Check Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// 2. FETCH USER INFO
try {
    $stmt = $pdo->prepare("SELECT full_name, phone FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $userPhone = $user['phone'] ?? '';
    $userName = $user['full_name'] ?? 'Customer';

} catch (Exception $e) {
    die("Database Error");
}

// 3. FETCH STATS
$stats = [
    'total' => 0,
    'pending' => 0,
    'delivered' => 0,
    'total_spent' => 0
];

if (!empty($userPhone)) {
    // Total Orders
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM parcels WHERE sender_phone = ?");
    $stmt->execute([$userPhone]);
    $stats['total'] = $stmt->fetchColumn();

    // Active/Pending
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM parcels WHERE sender_phone = ? AND current_status != 'delivered' AND current_status != 'cancelled'");
    $stmt->execute([$userPhone]);
    $stats['pending'] = $stmt->fetchColumn();

    // Delivered
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM parcels WHERE sender_phone = ? AND current_status = 'delivered'");
    $stmt->execute([$userPhone]);
    $stats['delivered'] = $stmt->fetchColumn();
    
    // Total Spent
    $stmt = $pdo->prepare("SELECT SUM(price) FROM parcels WHERE sender_phone = ?");
    $stmt->execute([$userPhone]);
    $stats['total_spent'] = $stmt->fetchColumn() ?: 0;

    // 4. FETCH RECENT 5 ORDERS
    $stmt = $pdo->prepare("SELECT * FROM parcels WHERE sender_phone = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([$userPhone]);
    $recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $recent_orders = [];
}
?>

<?php include "inc/header.php"?>

<div class="dashboard-main-body">

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <h6 class="fw-semibold mb-0">Dashboard</h6>
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

    <div class="card bg-primary-600 text-white mb-24 border-0 radius-12 overflow-hidden" style="background: linear-gradient(45deg, #4154f1, #2effff);">
        <div class="card-body p-24 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h4 class="fw-bold mb-2 text-white">Welcome back, <?= htmlspecialchars($userName) ?>! 👋</h4>
                <p class="mb-0 text-white-50">Here is what's happening with your shipments today.</p>
            </div>
            <a href="create_parcel.php" class="btn btn-base fw-bold radius-8 text-primary-600 bg-white">
                <iconify-icon icon="solar:box-bold" class="align-middle me-1"></iconify-icon> New Shipment
            </a>
        </div>
    </div>

    <div class="row gy-4 mb-24">
        <div class="col-xxl-3 col-sm-6">
            <div class="card h-100 radius-12 border-0 shadow-none bg-base">
                <div class="card-body p-24 d-flex align-items-center gap-3">
                    <div class="w-50-px h-50-px d-flex justify-content-center align-items-center bg-primary-50 text-primary-600 rounded-circle">
                        <iconify-icon icon="solar:box-bold-duotone" class="text-2xl"></iconify-icon>
                    </div>
                    <div>
                        <p class="fw-medium text-secondary-light mb-1">Total Parcels</p>
                        <h6 class="fw-bold mb-0 text-primary-light"><?= $stats['total'] ?></h6>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-sm-6">
            <div class="card h-100 radius-12 border-0 shadow-none bg-base">
                <div class="card-body p-24 d-flex align-items-center gap-3">
                    <div class="w-50-px h-50-px d-flex justify-content-center align-items-center bg-warning-50 text-warning-600 rounded-circle">
                        <iconify-icon icon="solar:truck-bold-duotone" class="text-2xl"></iconify-icon>
                    </div>
                    <div>
                        <p class="fw-medium text-secondary-light mb-1">Active Shipments</p>
                        <h6 class="fw-bold mb-0 text-primary-light"><?= $stats['pending'] ?></h6>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-sm-6">
            <div class="card h-100 radius-12 border-0 shadow-none bg-base">
                <div class="card-body p-24 d-flex align-items-center gap-3">
                    <div class="w-50-px h-50-px d-flex justify-content-center align-items-center bg-success-50 text-success-600 rounded-circle">
                        <iconify-icon icon="solar:check-circle-bold-duotone" class="text-2xl"></iconify-icon>
                    </div>
                    <div>
                        <p class="fw-medium text-secondary-light mb-1">Delivered</p>
                        <h6 class="fw-bold mb-0 text-primary-light"><?= $stats['delivered'] ?></h6>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-sm-6">
            <div class="card h-100 radius-12 border-0 shadow-none bg-base">
                <div class="card-body p-24 d-flex align-items-center gap-3">
                    <div class="w-50-px h-50-px d-flex justify-content-center align-items-center bg-info-50 text-info-600 rounded-circle">
                        <iconify-icon icon="solar:wallet-money-bold-duotone" class="text-2xl"></iconify-icon>
                    </div>
                    <div>
                        <p class="fw-medium text-secondary-light mb-1">Total Spent</p>
                        <h6 class="fw-bold mb-0 text-primary-light">$<?= number_format($stats['total_spent'], 2) ?></h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card h-100 p-0 radius-12">
        <div class="card-header border-bottom bg-base py-16 px-24 d-flex justify-content-between align-items-center">
            <h6 class="text-lg fw-semibold mb-0">Recent Activity</h6>
            <a href="all_shipments.php" class="text-primary-600 fw-medium text-sm hover-text-primary-700">
                View All <iconify-icon icon="solar:arrow-right-linear" class="align-middle"></iconify-icon>
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-primary-50">
                        <tr>
                            <th class="ps-24 py-16 text-secondary-light text-sm fw-medium">Tracking ID</th>
                            <th class="py-16 text-secondary-light text-sm fw-medium">Receiver</th>
                            <th class="py-16 text-secondary-light text-sm fw-medium">Date</th>
                            <th class="py-16 text-secondary-light text-sm fw-medium">Status</th>
                            <th class="py-16 text-secondary-light text-sm fw-medium">Amount</th>
                            <th class="pe-24 py-16 text-secondary-light text-sm fw-medium text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($recent_orders) > 0): ?>
                            <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td class="ps-24">
                                    <span class="text-primary-600 fw-bold">#<?= $order['tracking_number'] ?></span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="text-primary-light fw-semibold text-md"><?= htmlspecialchars($order['receiver_name']) ?></span>
                                        <span class="text-secondary-light text-sm"><?= htmlspecialchars($order['receiver_phone']) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-secondary-light text-md"><?= date('M d, Y', strtotime($order['created_at'])) ?></span>
                                </td>
                                <td>
                                    <?php 
                                    // Mapping status to Wowdash Soft Badges from badges.html
                                    // Format: badge text-sm fw-semibold bg-{color}-100 px-20 py-9 radius-4 text-{color}-600
                                    $statusClass = 'bg-neutral-200 text-neutral-600'; // Default
                                    
                                    if($order['current_status'] == 'pending') {
                                        $statusClass = 'bg-warning-100 text-warning-600';
                                    } elseif($order['current_status'] == 'delivered') {
                                        $statusClass = 'bg-success-100 text-success-600';
                                    } elseif($order['current_status'] == 'cancelled' || $order['current_status'] == 'returned') {
                                        $statusClass = 'bg-danger-100 text-danger-600';
                                    } elseif($order['current_status'] == 'in_transit') {
                                        $statusClass = 'bg-info-100 text-info-600';
                                    } elseif($order['current_status'] == 'picked_up') {
                                        $statusClass = 'bg-lilac-100 text-lilac-600';
                                    }
                                    ?>
                                    <span class="badge text-sm fw-semibold px-20 py-9 radius-4 <?= $statusClass ?>">
                                        <?= str_replace('_', ' ', ucfirst($order['current_status'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="text-primary-light fw-bold">$<?= number_format($order['price'], 2) ?></span>
                                </td>
                                <td class="pe-24 text-end">
                                    <a href="track.php?id=<?= $order['tracking_number'] ?>" 
                                       class="w-32-px h-32-px bg-primary-50 text-primary-600 rounded-circle d-inline-flex justify-content-center align-items-center hover-bg-primary-600 hover-text-white">
                                        <iconify-icon icon="solar:eye-linear"></iconify-icon>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-24">
                                    <div class="d-flex flex-column align-items-center">
                                        <iconify-icon icon="solar:box-minimalistic-linear" class="text-secondary-light text-5xl mb-3"></iconify-icon>
                                        <p class="text-secondary-light mb-0">No recent shipments found.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php include "inc/footer.php"?>