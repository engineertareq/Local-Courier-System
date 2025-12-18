<?php
require 'db.php';

$message = "";

// 1. Fetch Active Packages for the Dropdown
$stmt = $pdo->query("SELECT * FROM delivery_packages WHERE status = 'Active'");
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Generate Tracking ID
    $tracking_number = "TRK-" . rand(100000, 999999);
    
    // Inputs
    $sender = $_POST['sender_name'];
    $receiver = $_POST['receiver_name'];
    $receiver_phone = $_POST['receiver_phone'];
    $receiver_address = $_POST['receiver_address'];
    
    // Pricing Logic Variables
    $package_id = $_POST['package_id'];
    $parcel_type = $_POST['parcel_type']; // Document or Parcel
    $weight = floatval($_POST['weight']);
    $location = $_POST['location']; // 'inside' or 'outside'

    // 2. Server-Side Price Calculation (Security)
    // Fetch specific package rates again to ensure data integrity
    $stmtPkg = $pdo->prepare("SELECT * FROM delivery_packages WHERE package_id = ?");
    $stmtPkg->execute([$package_id]);
    $selected_pkg = $stmtPkg->fetch();

    $calculated_price = 0;
    if ($selected_pkg) {
        $rate = ($location === 'inside') ? $selected_pkg['price_inside_dhaka'] : $selected_pkg['price_outside_dhaka'];
        $calculated_price = $weight * $rate;
        
        // Optional: Minimum charge logic (e.g., minimum 1kg charge)
        if($weight < 1) $calculated_price = $rate; 
    }

    try {
        $pdo->beginTransaction();

        // 3. Insert into Parcels
        // Note: You might want to add columns for weight/type/package_id to your database later.
        // For now, we save the calculated price.
        $sql = "INSERT INTO parcels 
                (tracking_number, sender_name, sender_phone, sender_address, receiver_name, receiver_phone, receiver_address, price, current_status, created_at) 
                VALUES (?, ?, '000000000', 'Office', ?, ?, ?, ?, 'picked_up', NOW())";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$tracking_number, $sender, $receiver, $receiver_phone, $receiver_address, $calculated_price]);
        $parcel_id = $pdo->lastInsertId();

        // 4. Log Details in History
        // We add the specific package details here so you know what was selected
        $desc = "Order placed. Type: $parcel_type, Weight: {$weight}KG, Loc: " . ucfirst($location) . " Dhaka.";
        
        $stmtHistory = $pdo->prepare("INSERT INTO parcel_history (parcel_id, status, description, location) VALUES (?, 'Order Placed', ?, 'Main Hub')");
        $stmtHistory->execute([$parcel_id, $desc]);

        $pdo->commit();
        $message = "<div class='alert alert-success alert-dismissible fade show'>Parcel Created! Tracking ID: <strong>$tracking_number</strong> - Cost: $$calculated_price <button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
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

            <form method="POST" id="parcelForm">
              <div class="row gy-3">
                
                <div class="col-12"><h6 class="text-primary-light border-bottom pb-2">1. Customer Info</h6></div>

                <div class="col-md-6">
                  <label class="form-label">Sender Name</label>
                  <div class="icon-field">
                    <span class="icon"><iconify-icon icon="solar:user-bold-duotone"></iconify-icon></span>
                    <input type="text" name="sender_name" class="form-control" placeholder="Enter Sender Name" required>
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Receiver Name</label>
                  <div class="icon-field">
                    <span class="icon"><iconify-icon icon="solar:user-id-bold-duotone"></iconify-icon></span>
                    <input type="text" name="receiver_name" class="form-control" placeholder="Enter Receiver Name" required>
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Receiver Phone</label>
                  <div class="icon-field">
                    <span class="icon"><iconify-icon icon="solar:phone-calling-bold-duotone"></iconify-icon></span>
                    <input type="text" name="receiver_phone" class="form-control" placeholder="+880..." required>
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Delivery Address</label>
                  <div class="icon-field">
                    <span class="icon"><iconify-icon icon="solar:map-point-bold-duotone"></iconify-icon></span>
                    <input type="text" name="receiver_address" class="form-control" placeholder="Full address" required>
                  </div>
                </div>

                <div class="col-12 mt-4"><h6 class="text-primary-light border-bottom pb-2">2. Package Details & Pricing</h6></div>

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
                    <input type="hidden" name="price" id="price_hidden">
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
        const priceHidden = document.getElementById('price_hidden');

        function calculatePrice() {
            // Get selected package option
            const selectedOption = packageSelect.options[packageSelect.selectedIndex];
            
            // Safety check
            if (!selectedOption || !selectedOption.value) return;

            // Get rates from data attributes
            const rateInside = parseFloat(selectedOption.getAttribute('data-inside')) || 0;
            const rateOutside = parseFloat(selectedOption.getAttribute('data-outside')) || 0;
            
            // Get user inputs
            const weight = parseFloat(weightInput.value) || 0;
            const location = locationSelect.value;

            // Determine rate based on location
            let ratePerKg = (location === 'inside') ? rateInside : rateOutside;

            // Calculate Total
            let total = weight * ratePerKg;

            // Update UI
            priceDisplay.value = total.toFixed(2);
            priceHidden.value = total.toFixed(2);
        }

        // Attach Event Listeners
        packageSelect.addEventListener('change', calculatePrice);
        weightInput.addEventListener('input', calculatePrice);
        locationSelect.addEventListener('change', calculatePrice);
    });
</script>