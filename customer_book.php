<?php
session_start();
require 'db.php';

// 1. Security: Ensure user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit;
}

$message = "";

// 2. Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Generate Tracking Number
    $tracking_number = "LCS-" . rand(100000, 999999);
    
    // Sender Details (From Session/Form)
    $sender_name = $_SESSION['name'];
    $sender_phone = $_SESSION['phone'];
    $sender_address = $_POST['sender_address']; // They fill this manually
    
    // Receiver Details
    $receiver_name = $_POST['receiver_name'];
    $receiver_phone = $_POST['receiver_phone'];
    $receiver_address = $_POST['receiver_address'];
    
    // Parcel Details
    $weight = $_POST['weight'];
    $price = 0.00; // Price is 0 until Admin reviews it (or you can add logic to calculate it)

    try {
        $pdo->beginTransaction();

        // Insert Parcel
        $sql = "INSERT INTO parcels (tracking_number, sender_name, sender_phone, sender_address, receiver_name, receiver_phone, receiver_address, weight_kg, price, current_status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$tracking_number, $sender_name, $sender_phone, $sender_address, $receiver_name, $receiver_phone, $receiver_address, $weight, $price]);
        
        $parcel_id = $pdo->lastInsertId();

        // Add History Log
        $stmtLog = $pdo->prepare("INSERT INTO parcel_history (parcel_id, status, description, location) VALUES (?, 'Order Placed', 'Customer created the order online', 'Online Portal')");
        $stmtLog->execute([$parcel_id]);

        $pdo->commit();
        
        // Success Message
        $message = "<div class='alert alert-success'>
                        <strong>Success!</strong> Your tracking ID is $tracking_number. 
                        <a href='customer_dashboard.php'>Return to Dashboard</a>
                    </div>";
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
    <title>Book a Shipment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">📦 New Shipment Request</h4>
                    <a href="customer_dashboard.php" class="btn btn-sm btn-light">Back to Dashboard</a>
                </div>
                <div class="card-body">
                    <?= $message ?>
                    
                    <form method="POST">
                        <h5 class="text-secondary mb-3">1. Sender Information (You)</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label>Your Name</label>
                                <input type="text" class="form-control" value="<?= $_SESSION['name'] ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label>Your Phone</label>
                                <input type="text" class="form-control" value="<?= $_SESSION['phone'] ?>" readonly>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label>Pickup Address <span class="text-danger">*</span></label>
                            <input type="text" name="sender_address" class="form-control" required placeholder="Where should we pick up the parcel?">
                        </div>

                        <hr>

                        <h5 class="text-secondary mb-3">2. Receiver Information</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label>Receiver Name <span class="text-danger">*</span></label>
                                <input type="text" name="receiver_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label>Receiver Phone <span class="text-danger">*</span></label>
                                <input type="text" name="receiver_phone" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label>Delivery Address <span class="text-danger">*</span></label>
                            <textarea name="receiver_address" class="form-control" rows="2" required></textarea>
                        </div>

                        <hr>

                        <h5 class="text-secondary mb-3">3. Parcel Details</h5>
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label>Est. Weight (kg)</label>
                                <input type="number" step="0.1" name="weight" class="form-control" placeholder="1.0">
                            </div>
                            <div class="col-md-8">
                                <label>Package Content / Note</label>
                                <input type="text" class="form-control" placeholder="e.g. Documents, Electronics (Fragile)">
                            </div>
                        </div>

                        <div class="alert alert-warning small">
                            Note: The final shipping price will be calculated by the courier upon pickup.
                        </div>

                        <button type="submit" class="btn btn-success w-100 py-2 fw-bold">Confirm Booking</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>