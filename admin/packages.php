<?php
session_start(); // Ensure session is started to check for admin role
include 'db.php';
include 'inc/header.php';

// Fetch Active Packages
$stmt = $pdo->query("SELECT * FROM delivery_packages WHERE status = 'Active'");
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper function to render a plan card
function renderPackageCard($pkg, $location, $index) {
    // 1. Data Mapping
    $price = ($location === 'inside') ? $pkg['price_inside_dhaka'] : $pkg['price_outside_dhaka'];
    $location_label = ($location === 'inside') ? 'Inside Dhaka' : 'Outside Dhaka';
    
    // 2. Dynamic Styling (Cycle through 3 styles based on Index)
    $style_index = $index % 3; 

    // Defaults
    $bg_class = 'bg-lilac-100';
    $text_color = 'text-secondary-light';
    $title_color = '';
    $icon_img = 'assets/images/pricing/price-icon1.png';
    $check_icon_bg = 'bg-lilac-600 text-white';
    $btn_class = 'bg-lilac-600 text-white border-lilac-600 bg-hover-lilac-700';
    $is_popular = false;

    // Apply Styles
    if ($style_index == 1) { // The "Pro" look
        $bg_class = 'bg-primary-600';
        $text_color = 'text-white';
        $title_color = 'text-white';
        $icon_img = 'assets/images/pricing/price-icon2.png';
        $check_icon_bg = 'bg-white text-primary-600';
        $btn_class = 'bg-white text-primary-600 border-white bg-hover-primary-50';
        $is_popular = true;
    } elseif ($style_index == 2) { // The "Green" look
        $bg_class = 'bg-success-100';
        $icon_img = 'assets/images/pricing/price-icon3.png';
        $check_icon_bg = 'bg-success-600 text-white';
        $btn_class = 'bg-success-600 text-white border-success-600 bg-hover-success-700';
    }

    ?>
    <div class="col-xxl-4 col-sm-6 pricing-plan-wrapper">
        <div class="pricing-plan position-relative radius-24 overflow-hidden border <?php echo $bg_class; ?> <?php echo $is_popular ? 'scale-item px-40 py-50' : ''; ?>">
            
            <?php if($is_popular): ?>
                <span class="bg-white bg-opacity-25 text-white radius-24 py-8 px-24 text-sm position-absolute end-0 top-0 z-1 rounded-start-top-0 rounded-end-bottom-0">Best Value</span>
            <?php endif; ?>

            <div class="d-flex align-items-center gap-16">
                <span class="w-72-px h-72-px d-flex justify-content-center align-items-center radius-16 bg-base">
                    <img src="<?php echo $icon_img; ?>" alt="Icon">
                </span>
                <div>
                    <span class="fw-medium text-md <?php echo $text_color; ?>"><?php echo htmlspecialchars($location_label); ?></span>
                    <h6 class="mb-0 <?php echo $title_color; ?>"><?php echo htmlspecialchars($pkg['package_name']); ?></h6>
                </div>
            </div>

            <p class="mt-16 mb-0 <?php echo $text_color; ?> mb-28">
                Reliable delivery within <strong class="<?php echo $title_color; ?>"><?php echo htmlspecialchars($pkg['delivery_time']); ?></strong>.
            </p>
            
            <h3 class="mb-24 <?php echo $title_color; ?>">
                ৳<?php echo number_format($price); ?> 
                <span class="fw-medium text-md <?php echo $text_color; ?>">/ kg</span> 
            </h3>
            
            <span class="mb-20 fw-medium <?php echo $title_color; ?>">What’s included</span>
            <ul class="<?php echo $title_color; ?>">
                <li class="d-flex align-items-center gap-16 mb-16">
                    <span class="w-24-px h-24-px d-flex justify-content-center align-items-center rounded-circle <?php echo $check_icon_bg; ?>">
                        <iconify-icon icon="iconamoon:check-light" class="text-lg"></iconify-icon>
                    </span>
                    <span class="<?php echo $text_color; ?> text-lg">Delivery in <?php echo htmlspecialchars($pkg['delivery_time']); ?></span>
                </li>
                <li class="d-flex align-items-center gap-16 mb-16">
                    <span class="w-24-px h-24-px d-flex justify-content-center align-items-center rounded-circle <?php echo $check_icon_bg; ?>">
                        <iconify-icon icon="iconamoon:check-light" class="text-lg"></iconify-icon>
                    </span>
                    <span class="<?php echo $text_color; ?> text-lg">Real-time Tracking</span>
                </li>
                <li class="d-flex align-items-center gap-16 mb-16">
                    <span class="w-24-px h-24-px d-flex justify-content-center align-items-center rounded-circle <?php echo $check_icon_bg; ?>">
                        <iconify-icon icon="iconamoon:check-light" class="text-lg"></iconify-icon>
                    </span>
                    <span class="<?php echo $text_color; ?> text-lg">Doorstep Pickup</span>
                </li>
                <li class="d-flex align-items-center gap-16">
                    <span class="w-24-px h-24-px d-flex justify-content-center align-items-center rounded-circle <?php echo $check_icon_bg; ?>">
                        <iconify-icon icon="iconamoon:check-light" class="text-lg"></iconify-icon>
                    </span>
                    <span class="<?php echo $text_color; ?> text-lg">SMS Updates</span>
                </li>
            </ul>

            <div class="d-flex gap-2 mt-28">
                <a href="create_parcel.php" class="flex-grow-1 text-center border text-sm btn-sm py-10 radius-8 <?php echo $btn_class; ?> text-decoration-none">
                    Book Shipment
                </a>
                
                <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="edit_package.php?id=<?php echo $pkg['package_id']; ?>" 
                       class="d-flex align-items-center justify-content-center border border-white text-white bg-white bg-opacity-25 text-hover-white px-12 radius-8" 
                       title="Edit Package" style="width: 48px;">
                        <iconify-icon icon="lucide:edit" class="text-xl"></iconify-icon>
                    </a>
                <?php endif; ?>
            </div>
            
        </div>
    </div>
    <?php
}
?>

<div class="dashboard-main-body">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <h6 class="fw-semibold mb-0">Delivery Packages</h6>
        
        <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <div class="d-flex align-items-center gap-3">
            <a href="add_package.php" class="btn btn-primary text-sm btn-sm px-12 py-12 radius-8 d-flex align-items-center gap-2"> 
                <iconify-icon icon="ic:baseline-plus" class="icon text-xl line-height-1"></iconify-icon>
                Add Package
            </a>
        </div>
        <?php endif; ?>
    </div>

    <div class="card h-100 p-0 radius-12 overflow-hidden">
        <div class="card-header border-bottom bg-base py-16 px-24">
            <h6 class="mb-0 text-lg">Current Shipping Rates</h6>
        </div>
        <div class="card-body p-40">
            <div class="row justify-content-center">
                <div class="col-xxl-10">
                    <div class="text-center">
                        <h4 class="mb-16">Choose Your Plan</h4>
                        <p class="mb-0 text-lg text-secondary-light">Transparent pricing based on location and weight.</p>
                    </div>
                    
                    <ul class="nav nav-pills button-tab mt-32 pricing-tab justify-content-center" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link px-24 py-10 text-md rounded-pill text-secondary-light fw-medium active" id="pills-inside-tab" data-bs-toggle="pill" data-bs-target="#pills-inside" type="button" role="tab" aria-controls="pills-inside" aria-selected="true">
                                Inside Dhaka
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link px-24 py-10 text-md rounded-pill text-secondary-light fw-medium" id="pills-outside-tab" data-bs-toggle="pill" data-bs-target="#pills-outside" type="button" role="tab" aria-controls="pills-outside" aria-selected="false" tabindex="-1">
                                Outside Dhaka
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="pills-tabContent">
                        
                        <div class="tab-pane fade show active" id="pills-inside" role="tabpanel" aria-labelledby="pills-inside-tab" tabindex="0">
                            <div class="row gy-4">
                                <?php 
                                if(count($packages) > 0) {
                                    foreach($packages as $index => $pkg) {
                                        renderPackageCard($pkg, 'inside', $index);
                                    }
                                } else {
                                    echo '<div class="col-12 text-center py-5"><p class="text-muted">No active packages found.</p></div>';
                                }
                                ?>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="pills-outside" role="tabpanel" aria-labelledby="pills-outside-tab" tabindex="0">
                            <div class="row gy-4">
                                <?php 
                                if(count($packages) > 0) {
                                    foreach($packages as $index => $pkg) {
                                        renderPackageCard($pkg, 'outside', $index);
                                    }
                                } else {
                                    echo '<div class="col-12 text-center py-5"><p class="text-muted">No active packages found.</p></div>';
                                }
                                ?>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc/footer.php' ?>