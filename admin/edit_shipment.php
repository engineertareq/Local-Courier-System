<?php 
session_start();
require 'db.php';

// Access Check
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    // header("Location: login.php"); exit();
}

$id = $_GET['id'] ?? null;
$message = "";

if (!$id) {
    header("Location: shipment_list.php");
    exit();
}

// Fetch Existing Data
$stmt = $pdo->prepare("SELECT * FROM parcels WHERE parcel_id = ?");
$stmt->execute([$id]);
$parcel = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$parcel) die("Parcel not found.");

// Update Logic
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sender_name = $_POST['sender_name'];
    $receiver_name = $_POST['receiver_name'];
    $receiver_phone = $_POST['receiver_phone'];
    $receiver_address = $_POST['receiver_address'];
    $price = $_POST['price'];
    $status = $_POST['status']; // Admin override status

    try {
        $sql = "UPDATE parcels SET 
                sender_name = ?, 
                receiver_name = ?, 
                receiver_phone = ?, 
                receiver_address = ?, 
                price = ?,
                current_status = ?
                WHERE parcel_id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$sender_name, $receiver_name, $receiver_phone, $receiver_address, $price, $status, $id]);
        
        // Refresh data
        $stmt = $pdo->prepare("SELECT * FROM parcels WHERE parcel_id = ?");
        $stmt->execute([$id]);
        $parcel = $stmt->fetch(PDO::FETCH_ASSOC);

        $message = "<div class='alert alert-success'>Shipment details updated successfully!</div>";
    } catch (PDOException $e) {
        $message = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
}
?>

<?php include 'inc/header.php'; ?>

<div class="dashboard-main-body">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <h6 class="fw-semibold mb-0">Edit Shipment</h6>
        <ul class="d-flex align-items-center gap-2">
            <li class="fw-medium"><a href="shipment_list.php" class="hover-text-primary">Shipments</a></li>
            <li>-</li>
            <li class="fw-medium">Edit #<?= htmlspecialchars($parcel['tracking_number']) ?></li>
        </ul>
    </div>

    <div class="card h-100 p-0 radius-12">
        <div class="card-body p-24">
            <?= $message ?>
            
            <form method="POST">
                <div class="row gy-4">
                    
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-primary-light">Sender Name</label>
                        <input type="text" name="sender_name" class="form-control radius-8" value="<?= htmlspecialchars($parcel['sender_name']) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-primary-light">Receiver Name</label>
                        <input type="text" name="receiver_name" class="form-control radius-8" value="<?= htmlspecialchars($parcel['receiver_name']) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-primary-light">Receiver Phone</label>
                        <input type="text" name="receiver_phone" class="form-control radius-8" value="<?= htmlspecialchars($parcel['receiver_phone']) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-primary-light">Price ($)</label>
                        <input type="number" step="0.01" name="price" class="form-control radius-8" value="<?= htmlspecialchars($parcel['price']) ?>" required>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-semibold text-primary-light">Receiver Address</label>
                        <textarea name="receiver_address" class="form-control radius-8" rows="2" required><?= htmlspecialchars($parcel['receiver_address']) ?></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-primary-light">Current Status (Override)</label>
                        <select name="status" class="form-select radius-8">
                            <option value="picked_up" <?= $parcel['current_status'] == 'picked_up' ? 'selected' : '' ?>>Picked Up</option>
                            <option value="in_transit" <?= $parcel['current_status'] == 'in_transit' ? 'selected' : '' ?>>In Transit</option>
                            <option value="out_for_delivery" <?= $parcel['current_status'] == 'out_for_delivery' ? 'selected' : '' ?>>Out For Delivery</option>
                            <option value="delivered" <?= $parcel['current_status'] == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                            <option value="failed" <?= $parcel['current_status'] == 'failed' ? 'selected' : '' ?>>Failed</option>
                            <option value="cancelled" <?= $parcel['current_status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                        <small class="text-muted">Note: Changing status here will NOT add a history log.</small>
                    </div>

                    <div class="col-12 d-flex gap-3 mt-4">
                        <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                        <a href="shipment_list.php" class="btn btn-outline-secondary px-4">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'inc/footer.php'; ?>