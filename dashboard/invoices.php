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

// 2. FETCH USER PHONE (To match Sender Phone)
try {
    $stmt = $pdo->prepare("SELECT phone FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $userPhone = $user['phone'] ?? '';
} catch (Exception $e) {
    die("Database Error");
}

// 3. FETCH INVOICES (Parcels)
$invoices = [];
if (!empty($userPhone)) {
    try {
        // Base Query
        $sql = "SELECT * FROM parcels WHERE sender_phone = ?";
        $params = [$userPhone];

        // Search Filter
        if (!empty($search)) {
            $sql .= " AND (tracking_number LIKE ? OR receiver_name LIKE ? OR receiver_phone LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        $invoices = [];
    }
}
?>

<?php include "inc/header.php"?>

<div class="dashboard-main-body">

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <h6 class="fw-semibold mb-0">My Invoices</h6>
        <ul class="d-flex align-items-center gap-2">
            <li class="fw-medium">
                <a href="index.php" class="d-flex align-items-center gap-1 hover-text-primary">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                    Dashboard
                </a>
            </li>
            <li>-</li>
            <li class="fw-medium">Invoices</li>
        </ul>
    </div>

    <div class="card h-100 p-0 radius-12">
        <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center flex-wrap gap-3 justify-content-between">
            <div class="d-flex align-items-center flex-wrap gap-3">
                <h5 class="card-title mb-0">Invoice List</h5>
                <span class="badge bg-primary-50 text-primary-600 border border-primary-100 round-4 lh-sm">
                    Total: <?= count($invoices) ?>
                </span>
            </div>

            <form class="d-flex align-items-center gap-2" method="GET">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search Invoice or Name..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn btn-primary">
                        <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
                    </button>
                    <?php if(!empty($search)): ?>
                        <a href="invoices.php" class="btn btn-light border" title="Clear Search">✖</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-4">Invoice #</th>
                            <th scope="col">Date</th>
                            <th scope="col">Recipient</th>
                            <th scope="col">Amount</th>
                            <th scope="col">Pay Status</th>
                            <th scope="col" class="text-end pe-4">Download</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($invoices) > 0): ?>
                            <?php foreach ($invoices as $row): ?>
                                <tr>
                                    <td class="ps-4">
                                        <span class="text-primary-600 fw-bold">INV-<?= $row['tracking_number'] ?></span>
                                        <br>
                                        <small class="text-muted">ID: #<?= $row['tracking_number'] ?></small>
                                    </td>

                                    <td>
                                        <?= date('M d, Y', strtotime($row['created_at'])) ?>
                                        <br>
                                        <small class="text-muted"><?= date('h:i A', strtotime($row['created_at'])) ?></small>
                                    </td>

                                    <td>
                                        <h6 class="text-md mb-0 fw-medium"><?= htmlspecialchars($row['receiver_name']) ?></h6>
                                        <span class="text-sm text-secondary-light"><?= htmlspecialchars($row['receiver_phone']) ?></span>
                                    </td>

                                    <td>
                                        <span class="text-md fw-bold text-dark">
                                            ৳<?= number_format($row['price'], 2) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?php 
                                            $pStatus = $row['payment_status'] ?? 'Unpaid'; 
                                            // Simple logic: if 'Paid', green; otherwise warning/danger
                                            $pClass = (strtolower($pStatus) === 'paid') 
                                                ? 'bg-success-focus text-success-main' 
                                                : 'bg-warning-focus text-warning-main';
                                        ?>
                                        <span class="badge rounded-pill <?= $pClass ?> px-3 py-2 fw-bold text-capitalize">
                                            <?= htmlspecialchars($pStatus) ?>
                                        </span>
                                    </td>

                                    <td class="text-end pe-4">
                                        <a href="invoice.php?tracking_id=<?= $row['tracking_number'] ?>" 
                                           target="_blank" 
                                           class="btn btn-sm btn-primary fw-semibold d-inline-flex align-items-center gap-1">
                                            <iconify-icon icon="solar:download-linear" class="text-lg"></iconify-icon>
                                            Download
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <iconify-icon icon="solar:document-text-linear" class="text-secondary-light display-1 mb-3"></iconify-icon>
                                        <h5 class="text-secondary fw-bold">No Invoices Found</h5>
                                        <p class="text-muted">You haven't generated any shipments yet.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card-footer py-3 bg-white border-top">
            <small class="text-muted">Showing <?= count($invoices) ?> records</small>
        </div>
    </div>
</div>

<?php include "inc/footer.php"?>