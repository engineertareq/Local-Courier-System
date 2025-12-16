<?php
require 'db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Generate a random Tracking ID (e.g., TRK-8492)
    $tracking_number = "TRK-" . rand(100000, 999999);
    
    $sender = $_POST['sender_name'];
    $receiver = $_POST['receiver_name'];
    $receiver_phone = $_POST['receiver_phone'];
    $receiver_address = $_POST['receiver_address'];
    $price = $_POST['price'];

    try {
        $pdo->beginTransaction();

        // 1. Insert into Parcels
        $stmt = $pdo->prepare("INSERT INTO parcels (tracking_number, sender_name, sender_phone, sender_address, receiver_name, receiver_phone, receiver_address, price, current_status) VALUES (?, ?, '000000000', 'Office', ?, ?, ?, ?, 'pending')");
        $stmt->execute([$tracking_number, $sender, $receiver, $receiver_phone, $receiver_address, $price]);
        $parcel_id = $pdo->lastInsertId();

        // 2. Add initial History Log
        $stmt = $pdo->prepare("INSERT INTO parcel_history (parcel_id, status, description, location) VALUES (?, 'Order Placed', 'Parcel received at source office', 'Main Hub')");
        $stmt->execute([$parcel_id]);

        $pdo->commit();
        $message = "<div class='alert alert-success'>Parcel Created! Tracking ID: <strong>$tracking_number</strong></div>";
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book New Parcel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4>📦 Book New Shipment</h4>
            </div>
            <div class="card-body">
                <?= $message ?>
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Sender Name</label>
                            <input type="text" name="sender_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Receiver Name</label>
                            <input type="text" name="receiver_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Receiver Phone</label>
                        <input type="text" name="receiver_phone" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Receiver Address</label>
                        <textarea name="receiver_address" class="form-control" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Delivery Price ($)</label>
                        <input type="number" step="0.01" name="price" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Create Parcel</button>
                </form>
            </div>
        </div>
        <div class="text-center mt-3">
            <a href="track.php">Go to Tracking Page</a> | <a href="update_status.php">Update Status</a>
        </div>
    </div>
</body>
</html>