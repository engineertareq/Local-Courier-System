<?php
require 'db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['branch_name'];
    $email = $_POST['branch_email'];
    $contact = $_POST['branch_contact'];
    $city = $_POST['city'];
    $zip = $_POST['zip_code'];
    $address = $_POST['branch_address'];

    try {
        $stmt = $pdo->prepare("INSERT INTO branches (branch_name, branch_email, branch_contact, branch_address, city, zip_code) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $contact, $address, $city, $zip]);
        
        $message = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                        Success! <strong>$name</strong> has been created successfully.
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                    </div>";
    } catch (Exception $e) {
        $message = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                        Error: " . $e->getMessage() . "
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                    </div>";
    }
}
?>

<?php include "inc/header.php" ?>

<div class="dashboard-main-body">

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <h6 class="fw-semibold mb-0">Branch Management</h6>
        <ul class="d-flex align-items-center gap-2">
            <li class="fw-medium">
                <a href="index.html" class="d-flex align-items-center gap-1 hover-text-primary">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                    Dashboard
                </a>
            </li>
            <li>-</li>
            <li class="fw-medium">
                <a href="branches.php" class="hover-text-primary">Branches</a>
            </li>
            <li>-</li>
            <li class="fw-medium">Add Branch</li>
        </ul>
    </div>

    <div class="row gy-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Add New Branch</h5>
                </div>
                <div class="card-body">
                    
                    <?= $message ?>

                    <form method="POST">
                        <div class="row gy-3">
                            
                            <div class="col-md-6">
                                <label class="form-label">Branch Name</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="solar:buildings-bold-duotone"></iconify-icon>
                                    </span>
                                    <input type="text" name="branch_name" class="form-control" placeholder="Ex: Dhaka Main Hub" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Branch Email</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="mage:email"></iconify-icon>
                                    </span>
                                    <input type="email" name="branch_email" class="form-control" placeholder="branch@company.com" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Contact Number</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="solar:phone-calling-linear"></iconify-icon>
                                    </span>
                                    <input type="text" name="branch_contact" class="form-control" placeholder="+880 1XXX XXXXXX" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">City</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="solar:city-linear"></iconify-icon>
                                    </span>
                                    <input type="text" name="city" class="form-control" placeholder="Ex: Chittagong" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Zip/Postal Code</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="solar:mailbox-linear"></iconify-icon>
                                    </span>
                                    <input type="text" name="zip_code" class="form-control" placeholder="Ex: 1205" required>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Full Address</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="solar:map-point-outline"></iconify-icon>
                                    </span>
                                    <textarea name="branch_address" class="form-control" placeholder="Enter street address, building info..." rows="3" required></textarea>
                                </div>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary-600">
                                    <iconify-icon icon="solar:check-circle-linear" class="me-1"></iconify-icon>
                                    Save Branch
                                </button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "inc/footer.php" ?>