<?php
require 'db.php';

$message = "";

// --- 1. HANDLE ASSIGNMENT FORM SUBMISSION ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['assign_courier'])) {
    $parcel_id = $_POST['parcel_id'];
    $courier_id = $_POST['courier_id'];

    if (!empty($parcel_id) && !empty($courier_id)) {
        try {
            $pdo->beginTransaction();

            // FIXED: Removed "current_status = 'assigned'" because it's not in your DB enum.
            // We keep it as is (likely 'pending') or you could change it to 'pending' explicitly.
            $stmt = $pdo->prepare("UPDATE parcels SET assigned_courier_id = ? WHERE parcel_id = ?");
            $stmt->execute([$courier_id, $parcel_id]);

            // Fetch Courier Name for history
            $stmtName = $pdo->prepare("SELECT u.full_name FROM couriers c JOIN users u ON c.user_id = u.user_id WHERE c.courier_id = ?");
            $stmtName->execute([$courier_id]);
            $courier_name = $stmtName->fetchColumn();

            $desc = "Shipment assigned to rider: " . $courier_name;
            $stmtHist = $pdo->prepare("INSERT INTO parcel_history (parcel_id, status, description, location) VALUES (?, 'Rider Assigned', ?, 'Dispatch Center')");
            $stmtHist->execute([$parcel_id, $desc]);

            $pdo->commit();
            $message = "<div class='alert alert-success alert-dismissible fade show'>
                            Success! Parcel assigned to <strong>$courier_name</strong>.
                            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                        </div>";
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "<div class='alert alert-danger alert-dismissible fade show'>Error: " . $e->getMessage() . "<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
        }
    } else {
        $message = "<div class='alert alert-warning alert-dismissible fade show'>Please select a courier first.<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
    }
}

// --- 2. FETCH DATA ---

// A. Fetch Unassigned Parcels
try {
    $sqlParcels = "SELECT p.*, b.branch_name 
                   FROM parcels p 
                   LEFT JOIN branches b ON p.branch_id = b.branch_id
                   WHERE p.assigned_courier_id IS NULL 
                   AND p.current_status != 'delivered'
                   ORDER BY p.created_at DESC";
    $stmt = $pdo->query($sqlParcels);
    $unassigned_parcels = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $unassigned_parcels = [];
}

// B. Fetch Recently Assigned Parcels (For Review)
try {
    $sqlAssigned = "SELECT p.*, u.full_name as courier_name 
                    FROM parcels p 
                    JOIN couriers c ON p.assigned_courier_id = c.courier_id
                    JOIN users u ON c.user_id = u.user_id
                    WHERE p.current_status != 'delivered'
                    ORDER BY p.updated_at DESC LIMIT 10";
    $stmt = $pdo->query($sqlAssigned);
    $assigned_parcels = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $assigned_parcels = [];
}

// C. Fetch Available Couriers
try {
    // Note: Matches your DB status 'available'
    $sqlCouriers = "SELECT c.courier_id, c.vehicle_type, u.full_name 
                    FROM couriers c 
                    JOIN users u ON c.user_id = u.user_id 
                    WHERE c.status = 'available'";
    $stmt = $pdo->query($sqlCouriers);
    $couriers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $couriers = [];
}
?>

<?php include "inc/header.php"?>

<div class="dashboard-main-body">

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <h6 class="fw-semibold mb-0">Dispatch Center</h6>
        <ul class="d-flex align-items-center gap-2">
            <li class="fw-medium"><a href="index.html" class="hover-text-primary">Dashboard</a></li>
            <li>-</li>
            <li class="fw-medium">Assign Shipments</li>
        </ul>
    </div>

    <?= $message ?>

    <div class="card h-100 p-0 radius-12 mb-4">
        <div class="card-header border-bottom bg-base py-16 px-24">
            <h6 class="text-lg fw-semibold mb-0">Unassigned Parcels</h6>
        </div>
        <div class="card-body p-24">
            <div class="table-responsive scroll-sm">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th>Tracking ID</th>
                            <th>Route</th>
                            <th>Status</th>
                            <th style="width: 300px;">Assign Courier</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($unassigned_parcels) > 0): ?>
                            <?php foreach ($unassigned_parcels as $parcel): ?>
                                <tr>
                                    <td>
                                        <span class="fw-bold text-primary"><?= htmlspecialchars($parcel['tracking_number']) ?></span><br>
                                        <small class="text-secondary"><?= date('M d', strtotime($parcel['created_at'])) ?></small>
                                    </td>
                                    <td>
                                        <small>To: <?= htmlspecialchars($parcel['receiver_address']) ?></small>
                                    </td>
                                    <td><span class="badge bg-warning text-dark"><?= $parcel['current_status'] ?></span></td>
                                    <td>
                                        <form method="POST" class="d-flex gap-2">
                                            <input type="hidden" name="parcel_id" value="<?= $parcel['parcel_id'] ?>">
                                            <select name="courier_id" class="form-select form-select-sm" required>
                                                <option value="" selected disabled>Select Rider...</option>
                                                <?php foreach ($couriers as $rider): ?>
                                                    <option value="<?= $rider['courier_id'] ?>">
                                                        <?= htmlspecialchars($rider['full_name']) ?> (<?= $rider['vehicle_type'] ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" name="assign_courier" class="btn btn-primary btn-sm">
                                                <iconify-icon icon="solar:check-circle-bold"></iconify-icon>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center">No unassigned parcels found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card h-100 p-0 radius-12">
        <div class="card-header border-bottom bg-base py-16 px-24">
            <h6 class="text-lg fw-semibold mb-0">Review: Recently Assigned</h6>
        </div>
        <div class="card-body p-24">
            <div class="table-responsive scroll-sm">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th>Tracking ID</th>
                            <th>Status</th>
                            <th>Assigned Rider</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($assigned_parcels) > 0): ?>
                            <?php foreach ($assigned_parcels as $parcel): ?>
                                <tr>
                                    <td>
                                        <span class="fw-bold"><?= htmlspecialchars($parcel['tracking_number']) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info text-dark"><?= $parcel['current_status'] ?></span>
                                    </td>
                                    <td>
                                        <iconify-icon icon="solar:bicycling-bold" class="align-middle me-1"></iconify-icon>
                                        <strong><?= htmlspecialchars($parcel['courier_name']) ?></strong>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-secondary" disabled>Assigned</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center">No assignments made yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php include "inc/footer.php" ?>