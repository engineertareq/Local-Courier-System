<?php 
session_start();
require 'db.php';

// --- 1. ACCESS CONTROL ---
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    // header("Location: login.php"); 
}

// --- 2. DELETE LOGIC ---
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM parcels WHERE parcel_id = ?");
    $stmt->execute([$id]);
    
    $stmt = $pdo->prepare("DELETE FROM parcel_history WHERE parcel_id = ?");
    $stmt->execute([$id]);

    header("Location: shipment_list.php");
    exit();
}

// --- 3. FILTER & PAGINATION ---
$limit = 10; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';

$sql = "SELECT * FROM parcels WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (tracking_number LIKE :search OR receiver_phone LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($status_filter)) {
    $sql .= " AND current_status = :status";
    $params[':status'] = $status_filter;
}

$count_stmt = $pdo->prepare($sql);
$count_stmt->execute($params);
$total_rows = $count_stmt->rowCount();
$total_pages = ceil($total_rows / $limit);

$sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$parcels = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'inc/header.php';
?>

<div class="dashboard-main-body">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <h6 class="fw-semibold mb-0">All Shipments</h6>
        <ul class="d-flex align-items-center gap-2">
            <li class="fw-medium"><a href="index.html" class="hover-text-primary">Dashboard</a></li>
            <li>-</li>
            <li class="fw-medium">Shipments</li>
        </ul>
    </div>

    <div class="card h-100 p-0 radius-12">
        <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center flex-wrap gap-3 justify-content-between">
            <div class="d-flex align-items-center flex-wrap gap-3">
                <form class="navbar-search d-flex gap-2" method="GET">
                    <div class="position-relative">
                        <input type="text" class="bg-base h-40-px w-auto form-control ps-5" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Track ID or Phone">
                        <iconify-icon icon="ion:search-outline" class="position-absolute top-50 start-0 translate-middle-y ms-2 text-lg text-secondary-light"></iconify-icon>
                    </div>
                    <select name="status" class="form-select h-40-px w-auto" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="picked_up" <?= $status_filter == 'picked_up' ? 'selected' : '' ?>>Picked Up</option>
                        <option value="in_transit" <?= $status_filter == 'in_transit' ? 'selected' : '' ?>>In Transit</option>
                        <option value="delivered" <?= $status_filter == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                        <option value="failed" <?= $status_filter == 'failed' ? 'selected' : '' ?>>Failed</option>
                    </select>
                </form>
            </div>
            <a href="create_parcel.php" class="btn btn-primary text-sm btn-sm px-12 py-12 radius-8 d-flex align-items-center gap-2"> 
                <iconify-icon icon="ic:baseline-plus" class="icon text-xl line-height-1"></iconify-icon>
                New Shipment
            </a>
        </div>
        
        <div class="card-body p-24">
            <div class="table-responsive scroll-sm">
                <table class="table bordered-table sm-table mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Tracking ID</th>
                            <th scope="col">Sender</th>
                            <th scope="col">Receiver</th>
                            <th scope="col">Price</th>
                            <th scope="col">Date</th>
                            <th scope="col" class="text-center">Status</th>
                            <th scope="col" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($parcels as $parcel): ?>
                        <tr>
                            <td>
                                <span class="text-md fw-semibold text-primary-light">
                                    <?= htmlspecialchars($parcel['tracking_number']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="text-md fw-medium"><?= htmlspecialchars($parcel['sender_name']) ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="text-md fw-medium"><?= htmlspecialchars($parcel['receiver_name']) ?></span>
                                    <span class="text-xs text-secondary-light"><?= htmlspecialchars($parcel['receiver_phone']) ?></span>
                                </div>
                            </td>
                            <td>$<?= number_format($parcel['price'], 2) ?></td>
                            <td><?= date('d M, Y', strtotime($parcel['created_at'])) ?></td>
                            <td class="text-center">
                                <?php 
                                    $s = $parcel['current_status'];
                                    $badgeClass = 'bg-warning-focus text-warning-main'; 
                                    if($s == 'delivered') $badgeClass = 'bg-success-focus text-success-main';
                                    if($s == 'in_transit') $badgeClass = 'bg-info-focus text-info-main';
                                    if($s == 'failed' || $s == 'cancelled') $badgeClass = 'bg-danger-focus text-danger-main';
                                ?>
                                <span class="<?= $badgeClass ?> px-24 py-4 radius-4 fw-medium text-sm text-uppercase">
                                    <?= str_replace('_', ' ', $s) ?>
                                </span>
                            </td>
                            <td class="text-center"> 
                                <div class="d-flex align-items-center gap-10 justify-content-center">
                                    
                                    <a href="invoice.php?id=<?= $parcel['parcel_id'] ?>" target="_blank" title="Print Invoice" class="bg-primary-focus text-primary-600 w-32-px h-32-px d-flex justify-content-center align-items-center rounded-circle"> 
                                        <iconify-icon icon="solar:printer-linear" class="text-lg"></iconify-icon>
                                    </a>

                                    <a href="update_status.php?tracking_number=<?= $parcel['tracking_number'] ?>" title="Update Status" class="bg-info-focus text-info-600 w-32-px h-32-px d-flex justify-content-center align-items-center rounded-circle"> 
                                        <iconify-icon icon="solar:refresh-circle-linear" class="text-lg"></iconify-icon>
                                    </a>
                                    
                                    <a href="edit_shipment.php?id=<?= $parcel['parcel_id'] ?>" title="Edit" class="bg-success-focus text-success-600 w-32-px h-32-px d-flex justify-content-center align-items-center rounded-circle"> 
                                        <iconify-icon icon="lucide:edit" class="text-md"></iconify-icon>
                                    </a>
                                    
                                    <a href="?delete_id=<?= $parcel['parcel_id'] ?>" onclick="return confirm('Delete this shipment permanently?');" title="Delete" class="bg-danger-focus text-danger-600 w-32-px h-32-px d-flex justify-content-center align-items-center rounded-circle"> 
                                        <iconify-icon icon="fluent:delete-24-regular" class="text-md"></iconify-icon>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(empty($parcels)): ?>
                            <tr><td colspan="7" class="text-center py-4">No shipments found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-24">
                <span>Page <?= $page ?> of <?= $total_pages ?></span>
                <ul class="pagination d-flex flex-wrap align-items-center gap-2 justify-content-center">
                    <?php if($page > 1): ?>
                        <li class="page-item"><a class="page-link bg-neutral-200 text-secondary-light radius-8 border-0 h-32-px w-32-px d-flex align-items-center justify-content-center" href="?page=<?= $page-1 ?>&search=<?= $search ?>"><</a></li>
                    <?php endif; ?>
                    <?php if($page < $total_pages): ?>
                        <li class="page-item"><a class="page-link bg-neutral-200 text-secondary-light radius-8 border-0 h-32-px w-32-px d-flex align-items-center justify-content-center" href="?page=<?= $page+1 ?>&search=<?= $search ?>">></a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php include 'inc/footer.php'; ?>