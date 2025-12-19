<?php
require 'db.php';

// Fetch Couriers with User Details (JOIN)
// REMOVED 'u.phone_number' to prevent database errors
try {
    $sql = "SELECT c.*, u.full_name, u.email 
            FROM couriers c 
            JOIN users u ON c.user_id = u.user_id 
            ORDER BY c.courier_id DESC";
    $stmt = $pdo->query($sql);
    $couriers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // If table doesn't exist or query fails
    $couriers = [];
}
?>

<?php include "inc/header.php"?>

<div class="dashboard-main-body">

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <h6 class="fw-semibold mb-0">Courier Team</h6>
        <ul class="d-flex align-items-center gap-2">
            <li class="fw-medium">
                <a href="index.html" class="d-flex align-items-center gap-1 hover-text-primary">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                    Dashboard
                </a>
            </li>
            <li>-</li>
            <li class="fw-medium">Riders List</li>
        </ul>
    </div>

    <div class="card h-100 p-0 radius-12">
        <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center justify-content-between">
            <h6 class="text-lg fw-semibold mb-0">All Registered Couriers</h6>
            <a href="add_courier.php" class="btn btn-primary-600 btn-sm px-3 d-flex align-items-center gap-2">
                <iconify-icon icon="solar:add-circle-bold" class="text-lg"></iconify-icon> Add New Rider
            </a>
        </div>
        
        <div class="card-body p-24">
            <div class="table-responsive scroll-sm">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Rider Name</th>
                            <th scope="col">Vehicle Info</th>
                            <th scope="col">Contact Email</th> <th scope="col">Status</th>
                            <th scope="col">Current Loc</th>
                            <th scope="col" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($couriers) > 0): ?>
                            <?php foreach ($couriers as $rider): ?>
                                <tr>
                                    <td>#<?= $rider['courier_id'] ?></td>
                                    
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="w-40-px h-40-px rounded-circle bg-primary-light d-flex justify-content-center align-items-center text-primary-600 fw-bold me-3">
                                                <?= strtoupper(substr($rider['full_name'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <h6 class="text-md mb-0 fw-medium"><?= htmlspecialchars($rider['full_name']) ?></h6>
                                                <small class="text-secondary-light">ID: U-<?= $rider['user_id'] ?></small>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <?php 
                                                // Dynamic Icon based on vehicle type
                                                $icon = "solar:bus-bold-duotone"; // Default
                                                if(stripos($rider['vehicle_type'], 'bike') !== false) $icon = "solar:bicycling-bold-duotone";
                                                if(stripos($rider['vehicle_type'], 'cycle') !== false) $icon = "solar:bicycling-round-bold-duotone";
                                                if(stripos($rider['vehicle_type'], 'truck') !== false) $icon = "game-icons:truck";
                                            ?>
                                            <iconify-icon icon="<?= $icon ?>" class="text-2xl text-primary"></iconify-icon>
                                            <div>
                                                <span class="d-block text-sm fw-medium text-primary-light"><?= htmlspecialchars($rider['vehicle_type']) ?></span>
                                                <span class="d-block text-xs text-secondary"><?= htmlspecialchars($rider['vehicle_plate_number']) ?></span>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="d-block text-sm"><?= htmlspecialchars($rider['email']) ?></span>
                                    </td>

                                    <td>
                                        <?php if ($rider['status'] == 'available'): ?>
                                            <span class="badge bg-success-focus text-success-main px-24 py-4 rounded-pill fw-medium text-sm">Active</span>
                                        <?php elseif ($rider['status'] == 'busy'): ?>
                                            <span class="badge bg-warning-focus text-warning-main px-24 py-4 rounded-pill fw-medium text-sm">On Delivery</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-focus text-danger-main px-24 py-4 rounded-pill fw-medium text-sm">Inactive</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if($rider['current_latitude'] != 0): ?>
                                            <a href="https://maps.google.com/?q=<?= $rider['current_latitude'] ?>,<?= $rider['current_longitude'] ?>" target="_blank" class="text-primary-600 text-sm hover-text-primary-800 d-flex align-items-center gap-1">
                                                <iconify-icon icon="solar:map-point-bold"></iconify-icon> View Map
                                            </a>
                                        <?php else: ?>
                                            <span class="text-secondary-light text-sm">Offline</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                            <a href="#" class="w-32-px h-32-px bg-primary-light text-primary-600 rounded-circle d-flex justify-content-center align-items-center">
                                                <iconify-icon icon="solar:pen-new-square-linear" class="icon text-xl"></iconify-icon>
                                            </a>
                                            <a href="#" class="w-32-px h-32-px bg-danger-light text-danger-600 rounded-circle d-flex justify-content-center align-items-center" onclick="return confirm('Are you sure?');">
                                                <iconify-icon icon="solar:trash-bin-trash-linear" class="icon text-xl"></iconify-icon>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <iconify-icon icon="solar:user-block-rounded-linear" class="text-4xl text-secondary-light mb-2"></iconify-icon>
                                    <h6 class="text-secondary-light">No Couriers Found</h6>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php include "inc/footer.php" ?>