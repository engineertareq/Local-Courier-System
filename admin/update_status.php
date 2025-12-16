<?php
require 'db.php';
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $trk = $_POST['tracking_number'];
    $new_status = $_POST['status'];
    $location = $_POST['location'];
    $description = $_POST['description'];

    // Get Parcel ID
    $stmt = $pdo->prepare("SELECT parcel_id FROM parcels WHERE tracking_number = ?");
    $stmt->execute([$trk]);
    $parcel = $stmt->fetch();

    if ($parcel) {
        $parcel_id = $parcel['parcel_id'];

        try {
            $pdo->beginTransaction();

            // 1. Update Main Status
            $stmtUpdate = $pdo->prepare("UPDATE parcels SET current_status = ? WHERE parcel_id = ?");
            $stmtUpdate->execute([$new_status, $parcel_id]);

            // 2. Add History Log
            $stmtLog = $pdo->prepare("INSERT INTO parcel_history (parcel_id, status, description, location) VALUES (?, ?, ?, ?)");
            $stmtLog->execute([$parcel_id, ucwords(str_replace('_', ' ', $new_status)), $description, $location]);

            $pdo->commit();
            $message = "<div class='alert alert-success'>Status Updated Successfully!</div>";
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Invalid Tracking Number</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Parcel Status</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-warning">
                <h4>🚚 Update Delivery Status</h4>
            </div>
            <div class="card-body">
                <?= $message ?>
                <form method="POST">
                    <div class="mb-3">
                        <label>Tracking Number</label>
                        <input type="text" name="tracking_number" class="form-control" required placeholder="TRK-XXXXXX">
                    </div>
                    <div class="mb-3">
                        <label>New Status</label>
                        <select name="status" class="form-select">
                            <option value="picked_up">Picked Up</option>
                            <option value="in_transit">In Transit</option>
                            <option value="out_for_delivery">Out for Delivery</option>
                            <option value="delivered">Delivered</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Current Location</label>
                        <input type="text" name="location" class="form-control" required placeholder="e.g. Dhaka Hub">
                    </div>
                    <div class="mb-3">
                        <label>Description / Note</label>
                        <input type="text" name="description" class="form-control" placeholder="e.g. Package arrived at facility">
                    </div>
                    <button type="submit" class="btn btn-warning w-100">Update Status</button>
                </form>
            </div>
        </div>
        <div class="text-center mt-3">
            <a href="track.php">Go to Tracking Page</a> | <a href="create_parcel.php">Back to Create</a>
        </div>
    </div>
</body>
</html>