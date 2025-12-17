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
        // Note: Assuming sender_phone is default '000000000' and sender_address is 'Office' based on your logic
        $stmt = $pdo->prepare("INSERT INTO parcels (tracking_number, sender_name, sender_phone, sender_address, receiver_name, receiver_phone, receiver_address, price, current_status) VALUES (?, ?, '000000000', 'Office', ?, ?, ?, ?, 'pending')");
        $stmt->execute([$tracking_number, $sender, $receiver, $receiver_phone, $receiver_address, $price]);
        $parcel_id = $pdo->lastInsertId();

        // 2. Add initial History Log
        $stmt = $pdo->prepare("INSERT INTO parcel_history (parcel_id, status, description, location) VALUES (?, 'Order Placed', 'Parcel received at source office', 'Main Hub')");
        $stmt->execute([$parcel_id]);

        $pdo->commit();
        $message = "<div class='alert alert-success alert-dismissible fade show' role='alert'>Parcel Created! Tracking ID: <strong>$tracking_number</strong><button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>Error: " . $e->getMessage() . "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
    }
}
?>

<?php include "inc/header.php"?>

  <div class="dashboard-main-body">

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
      <h6 class="fw-semibold mb-0">Parcel Creation</h6>
      <ul class="d-flex align-items-center gap-2">
        <li class="fw-medium">
          <a href="index.html" class="d-flex align-items-center gap-1 hover-text-primary">
            <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
            Dashboard
          </a>
        </li>
        <li>-</li>
        <li class="fw-medium">Create Parcel</li>
      </ul>
    </div>
    
    <div class="row gy-4">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">Book New Shipment</h5>
          </div>
          <div class="card-body">
            
            <?php if (!empty($message)) echo $message; ?>

            <form method="POST">
              <div class="row gy-3">
                
                <div class="col-md-6">
                  <label class="form-label">Sender Name</label>
                  <div class="icon-field">
                    <span class="icon">
                      <iconify-icon icon="f7:person"></iconify-icon>
                    </span>
                    <input type="text" name="sender_name" class="form-control" placeholder="Enter Sender Name" required>
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Receiver Name</label>
                  <div class="icon-field">
                    <span class="icon">
                      <iconify-icon icon="f7:person-badge"></iconify-icon>
                    </span>
                    <input type="text" name="receiver_name" class="form-control" placeholder="Enter Receiver Name" required>
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Receiver Phone</label>
                  <div class="icon-field">
                    <span class="icon">
                      <iconify-icon icon="solar:phone-calling-linear"></iconify-icon>
                    </span>
                    <input type="text" name="receiver_phone" class="form-control" placeholder="+1 (555) 000-0000" required>
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Delivery Price ($)</label>
                  <div class="icon-field">
                    <span class="icon">
                      <iconify-icon icon="solar:dollar-minimalistic-linear"></iconify-icon>
                    </span>
                    <input type="number" step="0.01" name="price" class="form-control" placeholder="0.00" required>
                  </div>
                </div>

                <div class="col-12">
                  <label class="form-label">Receiver Address</label>
                  <div class="icon-field">
                    <span class="icon">
                      <iconify-icon icon="solar:map-point-outline"></iconify-icon>
                    </span>
                    <textarea name="receiver_address" class="form-control" placeholder="Enter full delivery address" rows="3" required></textarea>
                  </div>
                </div>

                <div class="col-12">
                  <button type="submit" class="btn btn-primary-600">Create Parcel</button>
                </div>

              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

<?php include "inc/footer.php" ?>