<?php 
// session_start();
require 'db.php';

// --- 1. DASHBOARD STATS LOGIC ---

// A. Total Shipments
$stmt = $pdo->query("SELECT COUNT(*) FROM parcels");
$total_shipments = $stmt->fetchColumn();

// B. Total Revenue (Sum of price for non-cancelled orders)
$stmt = $pdo->query("SELECT SUM(price) FROM parcels WHERE current_status != 'cancelled'");
$total_revenue = $stmt->fetchColumn() ?: 0;

// C. Delivered Count
$stmt = $pdo->query("SELECT COUNT(*) FROM parcels WHERE current_status = 'delivered'");
$delivered_count = $stmt->fetchColumn();

// D. Pending/Processing (Total Orders for the card)
$stmt = $pdo->query("SELECT COUNT(*) FROM parcels WHERE current_status != 'delivered' AND current_status != 'cancelled'");
$active_orders = $stmt->fetchColumn();


// --- 2. DISTRICT ANALYTICS LOGIC ---
// We count parcels based on address keywords
function getCountByLocation($pdo, $keyword) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM parcels WHERE receiver_address LIKE ?");
    $stmt->execute(["%$keyword%"]);
    return $stmt->fetchColumn();
}

$dhaka_count = getCountByLocation($pdo, 'Dhaka');
$ctg_count   = getCountByLocation($pdo, 'Chittagong');
$sylhet_count = getCountByLocation($pdo, 'Sylhet');
$khulna_count = getCountByLocation($pdo, 'Khulna');

// Calculate percentages
$dhaka_pct   = ($total_shipments > 0) ? round(($dhaka_count / $total_shipments) * 100) : 0;
$ctg_pct     = ($total_shipments > 0) ? round(($ctg_count / $total_shipments) * 100) : 0;
$sylhet_pct  = ($total_shipments > 0) ? round(($sylhet_count / $total_shipments) * 100) : 0;
$khulna_pct  = ($total_shipments > 0) ? round(($khulna_count / $total_shipments) * 100) : 0;


// --- 3. FETCH RECENT ORDERS ---
$stmt = $pdo->query("SELECT * FROM parcels ORDER BY created_at DESC LIMIT 5");
$recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);


// --- 4. FETCH ONE ACTIVE SHIPMENT FOR "LIVE TRACKING" ---
$stmt = $pdo->query("SELECT * FROM parcels WHERE current_status IN ('in_transit', 'out_for_delivery') ORDER BY created_at DESC LIMIT 1");
$live_parcel = $stmt->fetch(PDO::FETCH_ASSOC);

// If no active parcel, fallback to the latest one
if(!$live_parcel) {
    $stmt = $pdo->query("SELECT * FROM parcels ORDER BY created_at DESC LIMIT 1");
    $live_parcel = $stmt->fetch(PDO::FETCH_ASSOC);
}

include 'inc/header.php'; 
?>

<div class="dashboard-main-body">

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <h6 class="fw-semibold mb-0">Shipment Dashboard</h6>
        <ul class="d-flex align-items-center gap-2">
            <li class="fw-medium">
                <a href="index.php" class="d-flex align-items-center gap-1 hover-text-primary">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                    Dashboard
                </a>
            </li>
            <li>-</li>
            <li class="fw-medium">Shipment</li>
        </ul>
    </div>

    <div class="mt-4">
        <div class="row gy-4">

            <div class="col-xxxl-9">
                <div class="row g-3">
                    
                    <div class="col-lg-3 col-sm-6">
                        <div class="card shadow-none border radius-12 bg-gradient-start-1 h-100">
                            <div class="card-body p-16">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-8">
                                    <div>
                                        <p class="fw-medium text-secondary-light mb-1 text-sm">Total Shipments</p>
                                        <h6 class="mb-0"><?= number_format($total_shipments) ?></h6>
                                    </div>
                                    <div class="w-50-px h-50-px bg-cyan rounded-circle d-flex justify-content-center align-items-center">
                                        <iconify-icon icon="solar:box-bold-duotone" class="text-white text-2xl mb-0"></iconify-icon>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-sm-6">
                        <div class="card shadow-none border radius-12 bg-gradient-start-2 h-100">
                            <div class="card-body p-16">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-8">
                                    <div>
                                        <p class="fw-medium text-secondary-light mb-1 text-sm">Active Orders</p>
                                        <h6 class="mb-0"><?= number_format($active_orders) ?></h6>
                                    </div>
                                    <div class="w-50-px h-50-px bg-purple rounded-circle d-flex justify-content-center align-items-center">
                                        <iconify-icon icon="fa-solid:award" class="text-white text-2xl mb-0"></iconify-icon>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-sm-6">
                        <div class="card shadow-none border radius-12 bg-gradient-start-3 h-100">
                            <div class="card-body p-16">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-8">
                                    <div>
                                        <p class="fw-medium text-secondary-light mb-1 text-sm">Revenue</p>
                                        <h6 class="mb-0">৳<?= number_format($total_revenue) ?></h6>
                                    </div>
                                    <div class="w-50-px h-50-px bg-primary-600 rounded-circle d-flex justify-content-center align-items-center">
                                        <iconify-icon icon="solar:wallet-bold-duotone" class="text-white text-2xl mb-0"></iconify-icon>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-sm-6">
                        <div class="card shadow-none border radius-12 bg-gradient-start-4 h-100">
                            <div class="card-body p-16">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-8">
                                    <div>
                                        <p class="fw-medium text-secondary-light mb-1 text-sm">Delivered</p>
                                        <h6 class="mb-0"><?= number_format($delivered_count) ?></h6>
                                    </div>
                                    <div class="w-50-px h-50-px bg-success-main rounded-circle d-flex justify-content-center align-items-center">
                                        <iconify-icon icon="solar:check-circle-bold-duotone" class="text-white text-2xl mb-0"></iconify-icon>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card rounded-4 overflow-hidden border-0 mt-24">
                    <div class="card-header">
                        <div class="d-flex align-items-center flex-wrap gap-2 justify-content-between">
                            <h6 class="mb-2 fw-bold text-lg mb-0">Sales Figure</h6>
                        </div>
                    </div>
                    <div class="card-body p-24">
                        <ul class="d-flex flex-wrap align-items-center justify-content-center gap-3">
                            <li class="d-flex align-items-center gap-2">
                                <span class="w-12-px h-8-px rounded-pill bg-warning-600"></span>
                                <span class="text-secondary-light text-sm fw-semibold line-height-1">Parcel</span>
                            </li>
                            <li class="d-flex align-items-center gap-2">
                                <span class="w-12-px h-8-px rounded-pill bg-primary-600"></span>
                                <span class="text-secondary-light text-sm fw-semibold line-height-1">Document</span>
                            </li>
                        </ul>
                        <div id="salesFigureChart" class="barChart"></div>
                    </div>
                </div>
            </div>

            <div class="col-xxxl-3">
                <div class="shadow-7 p-0 radius-12 bg-base overflow-hidden h-100">
                    <div class="d-flex align-items-center flex-wrap gap-2 justify-content-between py-12 px-20">
                        <h6 class="mb-0 fw-semibold text-lg">Live Tracking</h6>
                        <a href="shipment_list.php" class="text-primary-600 hover-text-primary d-flex align-items-center gap-1">
                            View All <iconify-icon icon="solar:alt-arrow-right-linear" class="icon"></iconify-icon>
                        </a>
                    </div>
                    <div class="card-body pt-0 ps-20 pb-20 pe-20">
                        <div>
                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d233668.38703692693!2d90.27923991057244!3d23.780573258035957!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3755b8b087026b81%3A0x8fa563bbdd5904c2!2sDhaka!5e0!3m2!1sen!2sbd!4v1700000000000!5m2!1sen!2sbd" 
                                height="185" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" 
                                class="w-100 radius-12 overflow-hidden filter-grayscale-1">
                            </iframe>
                        </div>
                        
                        <?php if($live_parcel): ?>
                        <div class="d-flex align-items-center my-20 gap-8 justify-content-between">
                            <div>
                                <p class="fw-medium text-secondary-light mb-2">Tracking ID</p>
                                <h6 class="mb-0 fw-semibold text-xl"><?= $live_parcel['tracking_number'] ?></h6>
                            </div>
                            <span class="bg-primary-50 text-primary-600 px-16 py-2 radius-2 fw-medium text-sm text-capitalize">
                                <?= str_replace('_', ' ', $live_parcel['current_status']) ?>
                            </span>
                        </div>

                        <div class="left-line-dotted position-relative d-flex flex-column gap-12">
                            <div class="left-line__circle d-flex align-items-center ps-16 position-relative justify-content-between gap-16">
                                <div>
                                    <span class="fw-semibold text-primary-light text-sm d-block">Picked Up</span>
                                    <span class="fw-normal text-secondary-light text-sm d-block"><?= date('M d', strtotime($live_parcel['created_at'])) ?></span>
                                </div>
                            </div>
                            
                            <div class="left-line__circle d-flex align-items-center ps-16 position-relative justify-content-between gap-16">
                                <div>
                                    <span class="fw-semibold text-primary-light text-sm d-block">Current Status</span>
                                    <span class="fw-normal text-secondary-light text-sm d-block text-capitalize">
                                        <?= str_replace('_', ' ', $live_parcel['current_status']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 mb-16 bg-neutral-100 p-10 radius-6 d-flex align-items-center justify-content-between gap-8">
                            <div class="d-flex align-items-center gap-12">
                                <span class="w-40-px h-40-px radius-4 overflow-hidden rounded-circle bg-white d-flex justify-content-center align-items-center">
                                    <iconify-icon icon="solar:user-bold" class="text-xl"></iconify-icon>
                                </span>
                                <div>
                                    <span class="d-block text-primary-light fw-medium line-height-1"><?= explode(' ', $live_parcel['receiver_name'])[0] ?></span>
                                    <span class="d-block text-secondary-light text-sm">Receiver</span>
                                </div>
                            </div>
                        </div>
                        <a href="update_status.php?tracking_number=<?= $live_parcel['tracking_number'] ?>" class="btn btn-primary-600 w-100 radius-6 py-10 text-decoration-none">Update Status</a>
                        
                        <?php else: ?>
                            <p class="text-center py-4">No active shipments.</p>
                        <?php endif; ?>

                    </div>
                </div>
            </div>

            <div class="col-xxl-4 col-lg-6">
                <div class="shadow-7 p-0 radius-12 bg-base overflow-hidden h-100">
                    <div class="d-flex align-items-center flex-wrap gap-2 justify-content-between py-16 px-20 border-bottom border-neutral-200">
                        <h6 class="mb-0 fw-semibold text-lg">Top Shipping Methods</h6>
                    </div>
                    <div class="card-body p-20 d-flex align-items-center flex-wrap">
                        <div id="multipleSeriesChart" class="apexcharts-tooltip-z-none square-marker check-marker series-gap-24 d-flex justify-content-center"></div>
                    </div>
                </div>
            </div>
              
            <div class="col-xxl-4 col-lg-6">
                <div class="shadow-7 p-0 radius-12 bg-base overflow-hidden h-100">
                    <div class="d-flex align-items-center flex-wrap gap-2 justify-content-between py-16 px-20 border-bottom border-neutral-200">
                        <h6 class="mb-0 fw-semibold text-lg">This Month Orders</h6>
                    </div>
                    <div class="card-body p-20">
                        <div id="monthOrderChart"></div>
                    </div>
                </div>
            </div>
               
            <div class="col-xxl-4 col-lg-6">
                <div class="card radius-8 border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center flex-wrap gap-2 justify-content-between">
                            <h6 class="mb-2 fw-bold text-lg">Districts Status</h6>
                        </div>
                    </div>

                    <div id="world-map" style="opacity: 0.5;"></div> 

                    <div class="card-body p-24 max-h-266-px scroll-sm overflow-y-auto">
                        <div class="d-flex flex-column gap-16">
                            
                            <div class="d-flex align-items-center justify-content-between gap-3">
                                <div class="d-flex align-items-center w-100">
                                    <div class="w-32-px h-32-px rounded-circle flex-shrink-0 me-12 bg-success-100 d-flex align-items-center justify-content-center text-success-600">D</div>
                                    <div class="flex-grow-1">
                                        <h6 class="text-sm mb-0">Dhaka</h6>
                                        <span class="text-xs text-secondary-light fw-medium"><?= $dhaka_count ?> Shipments</span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2 w-100">
                                    <div class="w-100 max-w-66 ms-auto">
                                        <div class="progress progress-sm rounded-pill">
                                            <div class="progress-bar bg-primary-600 rounded-pill" style="width: <?= $dhaka_pct ?>%;"></div>
                                        </div>
                                    </div>
                                    <span class="text-secondary-light font-xs fw-semibold"><?= $dhaka_pct ?>%</span>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between gap-3">
                                <div class="d-flex align-items-center w-100">
                                    <div class="w-32-px h-32-px rounded-circle flex-shrink-0 me-12 bg-warning-100 d-flex align-items-center justify-content-center text-warning-600">C</div>
                                    <div class="flex-grow-1">
                                        <h6 class="text-sm mb-0">Chittagong</h6>
                                        <span class="text-xs text-secondary-light fw-medium"><?= $ctg_count ?> Shipments</span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2 w-100">
                                    <div class="w-100 max-w-66 ms-auto">
                                        <div class="progress progress-sm rounded-pill">
                                            <div class="progress-bar bg-warning-600 rounded-pill" style="width: <?= $ctg_pct ?>%;"></div>
                                        </div>
                                    </div>
                                    <span class="text-secondary-light font-xs fw-semibold"><?= $ctg_pct ?>%</span>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between gap-3">
                                <div class="d-flex align-items-center w-100">
                                    <div class="w-32-px h-32-px rounded-circle flex-shrink-0 me-12 bg-info-100 d-flex align-items-center justify-content-center text-info-600">S</div>
                                    <div class="flex-grow-1">
                                        <h6 class="text-sm mb-0">Sylhet</h6>
                                        <span class="text-xs text-secondary-light fw-medium"><?= $sylhet_count ?> Shipments</span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2 w-100">
                                    <div class="w-100 max-w-66 ms-auto">
                                        <div class="progress progress-sm rounded-pill">
                                            <div class="progress-bar bg-info rounded-pill" style="width: <?= $sylhet_pct ?>%;"></div>
                                        </div>
                                    </div>
                                    <span class="text-secondary-light font-xs fw-semibold"><?= $sylhet_pct ?>%</span>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-8">
                <div class="shadow-7 radius-12 bg-base h-100 overflow-hidden">
                    <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center justify-content-between">
                        <h6 class="text-lg fw-semibold mb-0">Recent Orders</h6>
                        <a href="shipment_list.php" class="text-primary-600 hover-text-primary d-flex align-items-center gap-1">
                            View All <iconify-icon icon="solar:alt-arrow-right-linear" class="icon"></iconify-icon>
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive scroll-sm">
                            <table class="table bordered-table table-py-20 mb-0 rounded-0 border-0">
                                <thead>
                                    <tr>
                                        <th scope="col" class="rounded-0 text-secondary-light">Order No</th>
                                        <th scope="col" class="rounded-0 text-secondary-light">Receiver</th>
                                        <th scope="col" class="rounded-0 text-secondary-light">Price</th>
                                        <th scope="col" class="rounded-0 text-secondary-light">Date</th>
                                        <th scope="col" class="rounded-0 text-secondary-light text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($recent_orders as $order): ?>
                                    <tr>
                                        <td class="border-bottom border-neutral-200 text-secondary-light">
                                            #<?= $order['tracking_number'] ?>
                                        </td>
                                        <td class="border-bottom border-neutral-200 text-secondary-light">
                                            <?= htmlspecialchars($order['receiver_name']) ?>
                                        </td>
                                        <td class="border-bottom border-neutral-200 text-secondary-light">
                                            $<?= number_format($order['price'], 2) ?>
                                        </td>
                                        <td class="border-bottom border-neutral-200 text-secondary-light">
                                            <?= date('Y-m-d', strtotime($order['created_at'])) ?>
                                        </td>
                                        <td class="border-bottom border-neutral-200 text-center">
                                            <div class="max-w-100-px mx-auto">
                                                <?php
                                                    $s = $order['current_status'];
                                                    $cls = 'bg-warning-focus text-warning-main';
                                                    if($s == 'delivered') $cls = 'bg-success-focus text-success-main';
                                                    if($s == 'in_transit') $cls = 'bg-info-focus text-info-main';
                                                    if($s == 'failed') $cls = 'bg-danger-focus text-danger-main';
                                                ?>
                                                <span class="<?= $cls ?> px-16 py-2 rounded-pill fw-medium text-sm w-100 text-capitalize">
                                                    <?= str_replace('_', ' ', $s) ?>
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>
<?php include 'inc/footer.php' ?>