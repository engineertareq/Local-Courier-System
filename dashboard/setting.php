<?php 
session_start();
include 'db.php';


if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {

}

$message = "";
$user_id = $_GET['id'] ?? null;

if (!$user_id && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} elseif (!$user_id) {
    echo "No user ID specified.";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] == 'update_profile') {
        
        $full_name   = htmlspecialchars($_POST['full_name']);
        $email       = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $phone       = htmlspecialchars($_POST['phone']);
        $department  = htmlspecialchars($_POST['department']);
        $designation = htmlspecialchars($_POST['designation']);
        $description = htmlspecialchars($_POST['description']);
        

        $image_sql = "";
        $params = [
            ':name'  => $full_name,
            ':email' => $email,
            ':phone' => $phone,
            ':dept'  => $department,
            ':desig' => $designation,
            ':desc'  => $description,
            ':id'    => $user_id
        ];

        if (!empty($_FILES['image']['name'])) {
            $target_dir = "users_img/";
            $file_name = basename($_FILES["image"]["name"]);
            $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $unique_name = "user_" . time() . "." . $file_type;
            $target_file = $target_dir . $unique_name;

            if (in_array($file_type, ['jpg', 'jpeg', 'png']) && move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_sql = ", profile_image = :img";
                $params[':img'] = $unique_name;
            }
        }

        try {
            $sql = "UPDATE users SET 
                    full_name = :name, 
                    email = :email, 
                    phone = :phone, 
                    department = :dept, 
                    designation = :desig, 
                    description = :desc
                    $image_sql
                    WHERE user_id = :id";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $message = "<div class='alert alert-success'>Profile updated successfully!</div>";
        } catch (PDOException $e) {
            $message = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
        }
    }


    if (isset($_POST['action']) && $_POST['action'] == 'change_password') {
        $new_pass = $_POST['new_password'];
        $confirm_pass = $_POST['confirm_password'];

        if ($new_pass === $confirm_pass && !empty($new_pass)) {
            $hash = password_hash($new_pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
            if ($stmt->execute([$hash, $user_id])) {
                $message = "<div class='alert alert-success'>Password changed successfully!</div>";
            } else {
                $message = "<div class='alert alert-danger'>Database error.</div>";
            }
        } else {
            $message = "<div class='alert alert-warning'>Passwords do not match or are empty.</div>";
        }
    }
}


$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "User not found.";
    exit();
}


$img_src = "users_img/" . $user['profile_image'];
if (empty($user['profile_image']) || !file_exists($img_src)) {
    $img_src = "assets/images/user-dummy.png";
}
?>

<?php include 'inc/header.php'; ?>

<div class="dashboard-main-body">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <h6 class="fw-semibold mb-0">View Profile</h6>
        <ul class="d-flex align-items-center gap-2">
            <li class="fw-medium">
                <a href="users_list.php" class="d-flex align-items-center gap-1 hover-text-primary">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                    Dashboard
                </a>
            </li>
            <li>-</li>
            <li class="fw-medium">View Profile</li>
        </ul>
    </div>

    <?php if($message) echo $message; ?>

    <div class="row gy-4">
        <div class="col-lg-4">
            <div class="user-grid-card position-relative border radius-16 overflow-hidden bg-base h-100">
                <img src="assets/images/user-grid/user-grid-bg1.png" alt="Image" class="w-100 object-fit-cover">
                <div class="pb-24 ms-16 mb-24 me-16 mt--100">
                    <div class="text-center border border-top-0 border-start-0 border-end-0">
                        <img src="<?php echo $img_src; ?>" alt="Image" class="border br-white border-width-2-px w-200-px h-200-px rounded-circle object-fit-cover">
                        <h6 class="mb-0 mt-16"><?php echo htmlspecialchars($user['full_name']); ?></h6>
                        <span class="text-secondary-light mb-16"><?php echo htmlspecialchars($user['email']); ?></span>
                    </div>
                    <div class="mt-24">
                        <h6 class="text-xl mb-16">Personal Info</h6>
                        <ul>
                            <li class="d-flex align-items-center gap-1 mb-12">
                                <span class="w-30 text-md fw-semibold text-primary-light">Full Name</span>
                                <span class="w-70 text-secondary-light fw-medium">: <?php echo htmlspecialchars($user['full_name']); ?></span>
                            </li>
                            <li class="d-flex align-items-center gap-1 mb-12">
                                <span class="w-30 text-md fw-semibold text-primary-light"> Email</span>
                                <span class="w-70 text-secondary-light fw-medium">: <?php echo htmlspecialchars($user['email']); ?></span>
                            </li>
                            <li class="d-flex align-items-center gap-1 mb-12">
                                <span class="w-30 text-md fw-semibold text-primary-light"> Phone</span>
                                <span class="w-70 text-secondary-light fw-medium">: <?php echo htmlspecialchars($user['phone']); ?></span>
                            </li>
                            <li class="d-flex align-items-center gap-1 mb-12">
                                <span class="w-30 text-md fw-semibold text-primary-light"> Department</span>
                                <span class="w-70 text-secondary-light fw-medium">: <?php echo htmlspecialchars($user['department']); ?></span>
                            </li>
                            <li class="d-flex align-items-center gap-1 mb-12">
                                <span class="w-30 text-md fw-semibold text-primary-light"> Designation</span>
                                <span class="w-70 text-secondary-light fw-medium">: <?php echo htmlspecialchars($user['designation']); ?></span>
                            </li>
                            <li class="d-flex align-items-center gap-1">
                                <span class="w-30 text-md fw-semibold text-primary-light"> Description</span>
                                <span class="w-70 text-secondary-light fw-medium">: <?php echo htmlspecialchars($user['description']); ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-body p-24">
                    <ul class="nav border-gradient-tab nav-pills mb-20 d-inline-flex" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex align-items-center px-24 active" id="pills-edit-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-edit-profile" type="button" role="tab" aria-controls="pills-edit-profile" aria-selected="true">
                                Edit Profile 
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex align-items-center px-24" id="pills-change-passwork-tab" data-bs-toggle="pill" data-bs-target="#pills-change-passwork" type="button" role="tab" aria-controls="pills-change-passwork" aria-selected="false" tabindex="-1">
                                Change Password 
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex align-items-center px-24" id="pills-notification-tab" data-bs-toggle="pill" data-bs-target="#pills-notification" type="button" role="tab" aria-controls="pills-notification" aria-selected="false" tabindex="-1">
                                Notification Settings
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="pills-tabContent"> 
                        
                        <div class="tab-pane fade show active" id="pills-edit-profile" role="tabpanel" aria-labelledby="pills-edit-profile-tab" tabindex="0">
                            <h6 class="text-md text-primary-light mb-16">Profile Image</h6>
                            
                            <form action="" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="update_profile">
                                
                                <div class="mb-24 mt-16">
                                    <div class="avatar-upload">
                                        <div class="avatar-edit position-absolute bottom-0 end-0 me-24 mt-16 z-1 cursor-pointer">
                                            <input type='file' name="image" id="imageUpload" accept=".png, .jpg, .jpeg" hidden>
                                            <label for="imageUpload" class="w-32-px h-32-px d-flex justify-content-center align-items-center bg-primary-50 text-primary-600 border border-primary-600 bg-hover-primary-100 text-lg rounded-circle">
                                                <iconify-icon icon="solar:camera-outline" class="icon"></iconify-icon>
                                            </label>
                                        </div>
                                        <div class="avatar-preview">
                                            <div id="imagePreview" style="background-image: url('<?php echo $img_src; ?>');"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="mb-20">
                                            <label for="name" class="form-label fw-semibold text-primary-light text-sm mb-8">Full Name <span class="text-danger-600">*</span></label>
                                            <input type="text" name="full_name" class="form-control radius-8" id="name" value="<?php echo htmlspecialchars($user['full_name']); ?>">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="mb-20">
                                            <label for="email" class="form-label fw-semibold text-primary-light text-sm mb-8">Email <span class="text-danger-600">*</span></label>
                                            <input type="email" name="email" class="form-control radius-8" id="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="mb-20">
                                            <label for="number" class="form-label fw-semibold text-primary-light text-sm mb-8">Phone</label>
                                            <input type="text" name="phone" class="form-control radius-8" id="number" value="<?php echo htmlspecialchars($user['phone']); ?>">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="mb-20">
                                            <label for="depart" class="form-label fw-semibold text-primary-light text-sm mb-8">Department </label>
                                            <select class="form-control radius-8 form-select" name="department" id="depart">
                                                <option value="">Select Department</option>
                                                <option value="IT" <?php echo ($user['department']=='IT')?'selected':''; ?>>IT</option>
                                                <option value="Development" <?php echo ($user['department']=='Development')?'selected':''; ?>>Development</option>
                                                <option value="HR" <?php echo ($user['department']=='HR')?'selected':''; ?>>HR</option>
                                                <option value="Sales" <?php echo ($user['department']=='Sales')?'selected':''; ?>>Sales</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="mb-20">
                                            <label for="desig" class="form-label fw-semibold text-primary-light text-sm mb-8">Designation </label>
                                            <select class="form-control radius-8 form-select" name="designation" id="desig">
                                                <option value="">Select Designation</option>
                                                <option value="Manager" <?php echo ($user['designation']=='Manager')?'selected':''; ?>>Manager</option>
                                                <option value="Developer" <?php echo ($user['designation']=='Developer')?'selected':''; ?>>Developer</option>
                                                <option value="Frontend developer" <?php echo ($user['designation']=='Frontend developer')?'selected':''; ?>>Frontend developer</option>
                                                <option value="Executive" <?php echo ($user['designation']=='Executive')?'selected':''; ?>>Executive</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="mb-20">
                                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Role </label>
                                            <input type="text" class="form-control radius-8" value="<?php echo htmlspecialchars($user['role']); ?>" disabled>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="mb-20">
                                            <label for="desc" class="form-label fw-semibold text-primary-light text-sm mb-8">Description</label>
                                            <textarea name="description" class="form-control radius-8" id="desc"><?php echo htmlspecialchars($user['description']); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-center gap-3">
                                    <button type="button" class="border border-danger-600 bg-hover-danger-200 text-danger-600 text-md px-56 py-11 radius-8"> 
                                        Cancel
                                    </button>
                                    <button type="submit" class="btn btn-primary border border-primary-600 text-md px-56 py-12 radius-8"> 
                                        Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane fade" id="pills-change-passwork" role="tabpanel" aria-labelledby="pills-change-passwork-tab" tabindex="0">
                            <form action="" method="POST">
                                <input type="hidden" name="action" value="change_password">
                                
                                <div class="mb-20">
                                    <label for="your-password" class="form-label fw-semibold text-primary-light text-sm mb-8">New Password <span class="text-danger-600">*</span></label>
                                    <div class="position-relative">
                                        <input type="password" name="new_password" class="form-control radius-8" id="your-password" placeholder="Enter New Password*" required>
                                        <span class="toggle-password ri-eye-line cursor-pointer position-absolute end-0 top-50 translate-middle-y me-16 text-secondary-light" data-toggle="#your-password"></span>
                                    </div>
                                </div>
                                <div class="mb-20">
                                    <label for="confirm-password" class="form-label fw-semibold text-primary-light text-sm mb-8">Confirm Password <span class="text-danger-600">*</span></label>
                                    <div class="position-relative">
                                        <input type="password" name="confirm_password" class="form-control radius-8" id="confirm-password" placeholder="Confirm Password*" required>
                                        <span class="toggle-password ri-eye-line cursor-pointer position-absolute end-0 top-50 translate-middle-y me-16 text-secondary-light" data-toggle="#confirm-password"></span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-center gap-3">
                                    <button type="submit" class="btn btn-primary border border-primary-600 text-md px-56 py-12 radius-8"> 
                                        Update Password
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane fade" id="pills-notification" role="tabpanel" aria-labelledby="pills-notification-tab" tabindex="0">
                             <div class="form-switch switch-primary py-12 px-16 border radius-8 position-relative mb-16">
                                <label for="companzNew" class="position-absolute w-100 h-100 start-0 top-0"></label>
                                <div class="d-flex align-items-center gap-3 justify-content-between">
                                    <span class="form-check-label line-height-1 fw-medium text-secondary-light">Company News</span>
                                    <input class="form-check-input" type="checkbox" role="switch" id="companzNew">
                                </div>
                            </div>
                            <div class="form-switch switch-primary py-12 px-16 border radius-8 position-relative mb-16">
                                <label for="pushNotifcation" class="position-absolute w-100 h-100 start-0 top-0"></label>
                                <div class="d-flex align-items-center gap-3 justify-content-between">
                                    <span class="form-check-label line-height-1 fw-medium text-secondary-light">Push Notification</span>
                                    <input class="form-check-input" type="checkbox" role="switch" id="pushNotifcation" checked>
                                </div>
                            </div>
                            </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
  
<?php include 'inc/footer.php' ?>

<script>

    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#imagePreview').css('background-image', 'url('+e.target.result +')');
                $('#imagePreview').hide();
                $('#imagePreview').fadeIn(650);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    $("#imageUpload").change(function() {
        readURL(this);
    });
    function initializePasswordToggle(toggleSelector) {
        $(toggleSelector).on('click', function() {
            $(this).toggleClass("ri-eye-off-line"); 
            var input = $($(this).attr("data-toggle"));
            if (input.attr("type") === "password") {
                input.attr("type", "text");
            } else {
                input.attr("type", "password");
            }
        });
    }
    initializePasswordToggle('.toggle-password');
</script>