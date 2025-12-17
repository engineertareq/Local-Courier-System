<?php 
session_start();
require 'db.php';

// --- 1. ACCESS CONTROL ---
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    // header("Location: login.php"); 
}

// --- 2. PAGINATION & SEARCH ---
$limit = 10; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';

// Build Query
$sql = "SELECT * FROM parcels WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (tracking_number LIKE :search OR sender_name LIKE :search)";
    $params[':search'] = "%$search%";
}

// Count Total
$count_stmt = $pdo->prepare($sql);
$count_stmt->execute($params);
$total_rows = $count_stmt->rowCount();
$total_pages = ceil($total_rows / $limit);

// Fetch Data
$sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'inc/header.php';
?>

<div class="dashboard-main-body">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <h6 class="fw-semibold mb-0">Invoice List</h6>
        <ul class="d-flex align-items-center gap-2">
            <li class="fw-medium"><a href="index.html" class="hover-text-primary">Dashboard</a></li>
            <li>-</li>
            <li class="fw-medium">Invoices</li>
        </ul>
    </div>

    <div class="card h-100 p-0 radius-12">
        <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center flex-wrap gap-3 justify-content-between">
            <div class="d-flex align-items-center flex-wrap gap-3">
                <form class="navbar-search" method="GET">
                    <div class="position-relative">
                        <input type="text" class="bg-base h-40-px w-auto form-control ps-5" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search Invoice ID or Client">
                        <iconify-icon icon="ion:search-outline" class="position-absolute top-50 start-0 translate-middle-y ms-2 text-lg text-secondary-light"></iconify-icon>
                    </div>
                </form>
            </div>
            <button type="button" class="btn btn-outline-secondary text-sm btn-sm px-12 py-12 radius-8 d-flex align-items-center gap-2"> 
                <iconify-icon icon="solar:export-linear" class="icon text-xl line-height-1"></iconify-icon>
                Export CSV
            </button>
        </div>
        
        <div class="card-body p-24">
            <div class="table-responsive scroll-sm">
                <table class="table bordered-table sm-table mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Invoice ID</th>
                            <th scope="col">Client (Sender)</th>
                            <th scope="col">Date Issued</th>
                            <th scope="col">Amount</th>
                            <th scope="col" class="text-center">Payment Status</th>
                            <th scope="col" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($invoices as $inv): ?>
                        <tr>
                            <td>
                                <a href="invoice.php?id=<?= $inv['parcel_id'] ?>" target="_blank" class="text-primary-600 fw-bold hover-text-primary">
                                    #<?= htmlspecialchars($inv['tracking_number']) ?>
                                </a>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="w-32-px h-32-px rounded-circle bg-primary-50 text-primary-600 d-flex align-items-center justify-content-center me-2">
                                        <?= strtoupper(substr($inv['sender_name'], 0, 1)) ?>
                                    </div>
                                    <span class="text-md fw-medium"><?= htmlspecialchars($inv['sender_name']) ?></span>
                                </div>
                            </td>
                            <td><?= date('d M, Y', strtotime($inv['created_at'])) ?></td>
                            <td class="fw-bold">$<?= number_format($inv['price'], 2) ?></td>
                            <td class="text-center">
                                <?php 
                                    // Logic: If Delivered -> Paid, else Unpaid (Assumption for now)
                                    $status = ($inv['current_status'] == 'delivered') ? 'Paid' : 'Unpaid';
                                    $badge = ($status == 'Paid') ? 'bg-success-focus text-success-main' : 'bg-danger-focus text-danger-main';
                                ?>
                                <span class="<?= $badge ?> px-24 py-4 radius-4 fw-medium text-sm">
                                    <?= $status ?>
                                </span>
                            </td>
                            <td class="text-center"> 
                                <div class="d-flex align-items-center gap-10 justify-content-center">
                                    <a href="invoice.php?id=<?= $inv['parcel_id'] ?>" target="_blank" title="View / Print" class="bg-primary-focus text-primary-600 w-32-px h-32-px d-flex justify-content-center align-items-center rounded-circle"> 
                                        <iconify-icon icon="solar:eye-linear" class="text-lg"></iconify-icon>
                                    </a>
                                    <a href="invoice.php?id=<?= $inv['parcel_id'] ?>" download title="Download" class="bg-info-focus text-info-600 w-32-px h-32-px d-flex justify-content-center align-items-center rounded-circle"> 
                                        <iconify-icon icon="solar:download-linear" class="text-lg"></iconify-icon>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>

                        <?php if(empty($invoices)): ?>
                            <tr><td colspan="6" class="text-center py-4">No invoices found.</td></tr>
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