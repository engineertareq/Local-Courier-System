<?php
require 'db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";


try {
    $userStmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $userStmt->execute([$user_id]);
    $currentUser = $userStmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
}

try {
    $stmt = $pdo->query("SELECT * FROM delivery_packages WHERE status = 'Active'");
    $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->query("SELECT branch_id, branch_name FROM branches WHERE status = 'active'");
    $branches = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $packages = [];
    $branches = [];
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tracking_number = "TRK-" . rand(100000, 999999);
    
    $sender = $_POST['sender_name'];
    $sender_phone = $_POST['sender_phone'];
    $sender_address = $_POST['sender_address'];
    $receiver = $_POST['receiver_name'];
    $receiver_phone = $_POST['receiver_phone'];
    $receiver_address = $_POST['receiver_address'];
    $branch_id = !empty($_POST['branch_id']) ? $_POST['branch_id'] : NULL;
    $payment_method = $_POST['payment_method'];
    $package_id = $_POST['package_id'];
    $parcel_type = $_POST['parcel_type']; 
    $weight = floatval($_POST['weight']);
    $location = $_POST['location']; 

    $stmtPkg = $pdo->prepare("SELECT * FROM delivery_packages WHERE package_id = ?");
    $stmtPkg->execute([$package_id]);
    $selected_pkg = $stmtPkg->fetch();

    $calculated_price = 0;
    if ($selected_pkg) {
        $rate = ($location === 'inside') ? $selected_pkg['price_inside_dhaka'] : $selected_pkg['price_outside_dhaka'];
        $calculated_price = $weight * $rate;
        if($calculated_price < $rate) $calculated_price = $rate; 
    }

    try {
        $pdo->beginTransaction();

        $sql = "INSERT INTO parcels 
                (tracking_number, sender_name, sender_phone, sender_address, receiver_name, receiver_phone, receiver_address, weight_kg, price, payment_method, branch_id, current_status, payment_status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'Unpaid', NOW())";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $tracking_number, $sender, $sender_phone, $sender_address,  
            $receiver, $receiver_phone, $receiver_address, $weight,          
            $calculated_price, $payment_method, $branch_id
        ]);
        
        $parcel_id = $pdo->lastInsertId();

        $desc = "Order placed. Status: Pending. Type: $parcel_type. Method: $payment_method";
        $stmtHistory = $pdo->prepare("INSERT INTO parcel_history (parcel_id, status, description, location, updated_by_user_id) VALUES (?, 'Order Placed', ?, 'Main Hub', ?)");
        $stmtHistory->execute([$parcel_id, $desc, $user_id]);

        $pdo->commit();

     

        if ($payment_method == 'amarpay') {
            
            $payment_data = [
                'store_id' => 'aamarpaytest', 
                'signature_key' => 'dbb74894e82415a2f7ff0ec3a97e4183', 
                'cus_name' => $sender,
                'cus_email' => 'customer@example.com', 
                'cus_phone' => $sender_phone,
                'cus_add1' => $sender_address,
                'cus_add2' => $sender_address,
                'cus_city' => 'Dhaka',
                'cus_country' => 'Bangladesh',
                'amount' => $calculated_price,
                'currency' => 'BDT',
                'tran_id' => $tracking_number, 
                'success_url' => 'http://localhost/TAREQ/Local-Courier-System/dashboard/payment_success.php', 
                'fail_url' => 'http://localhost/TAREQ/Local-Courier-System/dashboard/fail.php',
                'cancel_url' => 'http://localhost/TAREQ/Local-Courier-System/dashboard/index.php?status=cancel',
                'desc' => 'Parcel Delivery Charge',
                'type' => 'json'
            ];

            $url = 'https://sandbox.aamarpay.com/jsonpost.php'; 
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payment_data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            curl_close($ch);

            $result = json_decode($response, true);

            if (isset($result['payment_url']) && !empty($result['payment_url'])) {
                header("Location: " . $result['payment_url']);
                exit(); 
            } else {
                $message = "<div class='alert alert-danger'>Payment Gateway Error. Please try Cash.</div>";
            }

        } else {
            $message = "<div class='alert alert-success alert-dismissible fade show'>Parcel Created! Tracking ID: <strong>$tracking_number</strong> <button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
        }

    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "<div class='alert alert-danger alert-dismissible fade show'>Error: " . $e->getMessage() . "<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
    }
}
?>


<?php include "inc/header.php"?>

<div class="dashboard-main-body">

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
      <h6 class="fw-semibold mb-0">Parcel Creation</h6>
      <ul class="d-flex align-items-center gap-2">
        <li class="fw-medium">
          <a href="index.php" class="d-flex align-items-center gap-1 hover-text-primary">
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

            <form method="POST" id="parcelForm">
              <div class="row gy-3">
                
                <div class="col-12"><h6 class="text-primary-light border-bottom pb-2">1. Customer Info</h6></div>

                <div class="col-md-4">
                  <label class="form-label">Sender Name</label>
                  <div class="icon-field">
                    <span class="icon"><iconify-icon icon="solar:user-bold-duotone"></iconify-icon></span>
                    <input type="text" name="sender_name" class="form-control" value="<?= htmlspecialchars($currentUser['full_name'] ?? '') ?>" placeholder="Enter Sender Name" required>
                  </div>
                </div>

                <div class="col-md-4">
                  <label class="form-label">Sender Phone</label>
                  <div class="icon-field">
                    <span class="icon"><iconify-icon icon="solar:phone-calling-bold-duotone"></iconify-icon></span>
                    <input type="text" name="sender_phone" class="form-control" value="<?= htmlspecialchars($currentUser['phone'] ?? '') ?>" placeholder="Sender Phone" required>
                  </div>
                </div>

                <div class="col-md-4">
                  <label class="form-label">Pickup Address</label>
                  <div class="icon-field">
                    <span class="icon"><iconify-icon icon="solar:map-point-bold-duotone"></iconify-icon></span>
                     <input type="text" name="sender_address" class="form-control" value="<?= htmlspecialchars($currentUser['address'] ?? '') ?>" placeholder="Sender Address" required>
                  </div>
                </div>

                <div class="col-12 mt-2"><hr class="text-muted opacity-25"></div>

                <div class="col-md-4">
                  <label class="form-label">Receiver Name</label>
                  <div class="icon-field">
                    <span class="icon"><iconify-icon icon="solar:user-id-bold-duotone"></iconify-icon></span>
                    <input type="text" name="receiver_name" class="form-control" placeholder="Enter Receiver Name" required>
                  </div>
                </div>

                <div class="col-md-4">
                  <label class="form-label">Receiver Phone</label>
                  <div class="icon-field">
                    <span class="icon"><iconify-icon icon="solar:phone-calling-bold-duotone"></iconify-icon></span>
                    <input type="text" name="receiver_phone" class="form-control" placeholder="+880..." required>
                  </div>
                </div>

                <div class="col-md-4">
                  <label class="form-label">Delivery Address</label>
                  <div class="icon-field">
                    <span class="icon"><iconify-icon icon="solar:map-point-bold-duotone"></iconify-icon></span>
                    <input type="text" name="receiver_address" class="form-control" placeholder="Full address" required>
                  </div>
                </div>

                <div class="col-12 mt-4"><h6 class="text-primary-light border-bottom pb-2">2. Logistics & Payment</h6></div>

                <div class="col-md-6">
                    <label class="form-label">Nearest Branch</label>
                    <div class="icon-field">
                        <span class="icon"><iconify-icon icon="solar:buildings-bold-duotone"></iconify-icon></span>
                        <select name="branch_id" class="form-select" required>
                            <option value="" disabled selected>Select Branch</option>
                            <?php foreach ($branches as $branch): ?>
                                <option value="<?= $branch['branch_id'] ?>">
                                    <?= htmlspecialchars($branch['branch_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Payment Method</label>
                    <div class="icon-field">
                        <span class="icon"><iconify-icon icon="solar:card-linear"></iconify-icon></span>
                        <select name="payment_method" class="form-select" required>
                            <option value="Cash (Receiver)" selected>Cash (Receiver Pay / COD)</option>
                            <option value="Cash (Sender)">Cash (Sender Pay)</option>
                            <option value="amarpay">Amarpay</option>
                            <option value="2Checkout">2Checkout</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 mt-4"><h6 class="text-primary-light border-bottom pb-2">3. Package Details & Pricing</h6></div>

                <div class="col-md-6">
                    <label class="form-label">Select Package Plan</label>
                    <select name="package_id" id="package_id" class="form-select" required>
                        <option value="" selected disabled>Choose a plan...</option>
                        <?php foreach($packages as $pkg): ?>
                            <option value="<?= $pkg['package_id'] ?>" 
                                    data-inside="<?= $pkg['price_inside_dhaka'] ?>" 
                                    data-outside="<?= $pkg['price_outside_dhaka'] ?>">
                                <?= $pkg['package_name'] ?> (<?= $pkg['delivery_time'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Parcel Type</label>
                    <select name="parcel_type" class="form-select" required>
                        <option value="Parcel">Parcel (Box/Goods)</option>
                        <option value="Document">Document (Files)</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Weight (KG)</label>
                    <div class="input-group">
                        <input type="number" name="weight" id="weight" class="form-control" step="0.1" min="0.1" placeholder="e.g. 1.5" required>
                        <span class="input-group-text">KG</span>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Delivery Area</label>
                    <select name="location" id="location" class="form-select" required>
                        <option value="inside">Inside Dhaka</option>
                        <option value="outside">Outside Dhaka</option>
                    </select>
                </div>

                <div class="col-md-4">
                  <label class="form-label">Total Cost</label>
                  <div class="icon-field">
                    <span class="icon text-success"><iconify-icon icon="solar:dollar-minimalistic-bold-duotone"></iconify-icon></span>
                    <input type="text" name="price_display" id="price_display" class="form-control fw-bold text-success" placeholder="0.00" readonly>
                    </div>
                </div>

                <div class="col-12 mt-3">
                  <button type="submit" class="btn btn-primary-600 px-4 py-2">
                    <iconify-icon icon="solar:box-bold-duotone" class="align-middle me-1"></iconify-icon> Create Parcel
                  </button>
                </div>

              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

<?php include "inc/footer.php"?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const packageSelect = document.getElementById('package_id');
        const weightInput = document.getElementById('weight');
        const locationSelect = document.getElementById('location');
        const priceDisplay = document.getElementById('price_display');

        function calculatePrice() {
            const selectedOption = packageSelect.options[packageSelect.selectedIndex];
            if (!selectedOption || !selectedOption.value) return;

            const rateInside = parseFloat(selectedOption.getAttribute('data-inside')) || 0;
            const rateOutside = parseFloat(selectedOption.getAttribute('data-outside')) || 0;
            
            const weight = parseFloat(weightInput.value) || 0;
            const location = locationSelect.value;

     
            let baseRate = (location === 'inside') ? rateInside : rateOutside;
       
            let total = weight * baseRate;
            if (total < baseRate && weight > 0) {
                total = baseRate;
            }

            priceDisplay.value = total.toFixed(2);
        }

        packageSelect.addEventListener('change', calculatePrice);
        weightInput.addEventListener('input', calculatePrice);
        locationSelect.addEventListener('change', calculatePrice);
    });
</script>
<style>
    .icon-field { position: relative; }
    .icon-field .icon {
        position: absolute; top: 50%; left: 16px; transform: translateY(-50%);
        font-size: 1.2rem; color: #6c757d; pointer-events: none; z-index: 5;
    }
    .icon-field .form-control, .icon-field .form-select { padding-left: 45px !important; }
</style>