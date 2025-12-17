<?php 
session_start();
include 'db.php';

// --- 1. ACCESS CONTROL ---
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    // header("Location: login.php");
}

$message = "";
$user_id = $_GET['id'] ?? null;

// Redirect if no ID provided
if (!$user_id) {
    header("Location: users_list.php");
    exit();
}

// --- 2. FETCH EXISTING DATA ---
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "User not found!";
    exit();
}

// --- 3. UPDATE LOGIC ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Sanitize Inputs
    $full_name   = htmlspecialchars($_POST['full_name']);
    $email       = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $phone       = htmlspecialchars($_POST['phone']);
    $role        = $_POST['role']; 
    $department  = htmlspecialchars($_POST['department']);
    $designation = htmlspecialchars($_POST['designation']);
    $description = htmlspecialchars($_POST['description']);
    
    // --- Password Logic ---
    // Only update password if the field is NOT empty
    $password_sql = "";
    $params = [
        ':name'  => $full_name,
        ':email' => $email,
        ':phone' => $phone,
        ':role'  => $role,
        ':dept'  => $department,
        ':desig' => $designation,
        ':desc'  => $description,
        ':id'    => $user_id
    ];

    if (!empty($_POST['password'])) {
        $password_sql = ", password_hash = :pass";
        $params[':pass'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }

    // --- Image Logic ---
    $image_sql = "";
    $upload_ok = true;

    if (!empty($_FILES['image']['name'])) {
        $target_dir = "users_img/";
        $file_name = basename($_FILES["image"]["name"]);
        $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $unique_name = "user_" . time() . "." . $file_type;
        $target_file = $target_dir . $unique_name;

        // Validation
        $allowed = ['jpg', 'jpeg', 'png'];
        if (!in_array($file_type, $allowed)) {
            $message = "<div class='alert alert-danger'>Error: Only JPG, JPEG, & PNG allowed.</div>";
            $upload_ok = false;
        }

        if ($upload_ok) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                // Delete Old Image if exists
                if (!empty($user['profile_image']) && file_exists($target_dir . $user['profile_image'])) {
                    unlink($target_dir . $user['profile_image']);
                }
                
                $image_sql = ", profile_image = :img";
                $params[':img'] = $unique_name;
            } else {
                $message = "<div class='alert alert-danger'>Error uploading file.</div>";
                $upload_ok = false;
            }
        }
    }

    // --- Execute Update ---
    if ($upload_ok) {
        try {
            $sql = "UPDATE users SET 
                    full_name = :name, 
                    email = :email, 
                    phone = :phone, 
                    role = :role, 
                    department = :dept, 
                    designation = :desig, 
                    description = :desc
                    $password_sql
                    $image_sql
                    WHERE user_id = :id";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            // Refresh user data to show updates immediately
            $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            $message = "<div class='alert alert-success'>Profile updated successfully!</div>";

        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $message = "<div class='alert alert-warning'>Error: Email already taken by another user.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Database Error: " . $e->getMessage() . "</div>";
            }
        }
    }
}

// Current Image Path for Preview
$current_img = "users_img/" . $user['profile_image'];
if (empty($user['profile_image']) || !file_exists($current_img)) {
    $current_img = "assets/images/user-dummy.png";
}
?>

<?php include 'inc/header.php'; ?>
    
<div class="dashboard-main-body">
    
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <h6 class="fw-semibold mb-0">Edit User</h6>
        <ul class="d-flex align-items-center gap-2">
            <li class="fw-medium"><a href="users_list.php" class="hover-text-primary">Users Grid</a></li>
            <li>-</li>
            <li class="fw-medium">Edit User</li>
        </ul>
    </div>

    <div class="card h-100 p-0 radius-12">
        <div class="card-body p-24">
            <div class="row justify-content-center">
                <div class="col-xxl-6 col-xl-8 col-lg-10">
                    <div class="card border">
                        <div class="card-body">
                            
                            <?php echo $message; ?>

                            <form action="" method="POST" enctype="multipart/form-data">
                            
                                <h6 class="text-md text-primary-light mb-16">Profile Image</h6>

                                <div class="mb-24 mt-16">
                                    <div class="avatar-upload">
                                        <div class="avatar-edit position-absolute bottom-0 end-0 me-24 mt-16 z-1 cursor-pointer">
                                            <input type='file' name="image" id="imageUpload" accept=".png, .jpg, .jpeg" hidden>
                                            <label for="imageUpload" class="w-32-px h-32-px d-flex justify-content-center align-items-center bg-primary-50 text-primary-600 border border-primary-600 bg-hover-primary-100 text-lg rounded-circle">
                                                <iconify-icon icon="solar:camera-outline" class="icon"></iconify-icon>
                                            </label>
                                        </div>
                                        <div class="avatar-preview">
                                            <div id="imagePreview" style="background-image: url('<?php echo $current_img; ?>');"> </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-20">
                                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Full Name <span class="text-danger-600">*</span></label>
                                    <input type="text" name="full_name" class="form-control radius-8" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                </div>

                                <div class="mb-20">
                                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Email <span class="text-danger-600">*</span></label>
                                    <input type="email" name="email" class="form-control radius-8" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>

                                <div class="mb-20">
                                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Password</label>
                                    <input type="password" name="password" class="form-control radius-8" placeholder="Leave blank to keep current password">
                                </div>

                                <div class="mb-20">
                                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Phone</label>
                                    <input type="text" name="phone" class="form-control radius-8" value="<?php echo htmlspecialchars($user['phone']); ?>">
                                </div>

                                <div class="mb-20">
                                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">User Role <span class="text-danger-600">*</span></label>
                                    <select class="form-control radius-8 form-select" name="role" required>
                                        <option value="customer" <?php echo ($user['role'] == 'customer') ? 'selected' : ''; ?>>Customer</option>
                                        <option value="staff" <?php echo ($user['role'] == 'staff') ? 'selected' : ''; ?>>Staff</option>
                                        <option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                    </select>
                                </div>

                                <div class="mb-20">
                                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Department</label>
                                    <select class="form-control radius-8 form-select" name="department">
                                        <option value="">Select Department</option>
                                        <option value="IT" <?php echo ($user['department'] == 'IT') ? 'selected' : ''; ?>>IT</option>
                                        <option value="HR" <?php echo ($user['department'] == 'HR') ? 'selected' : ''; ?>>HR</option>
                                        <option value="Sales" <?php echo ($user['department'] == 'Sales') ? 'selected' : ''; ?>>Sales</option>
                                        <option value="Development" <?php echo ($user['department'] == 'Development') ? 'selected' : ''; ?>>Development</option>
                                    </select>
                                </div>

                                <div class="mb-20">
                                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Designation</label>
                                    <select class="form-control radius-8 form-select" name="designation">
                                        <option value="">Select Designation</option>
                                        <option value="Manager" <?php echo ($user['designation'] == 'Manager') ? 'selected' : ''; ?>>Manager</option>
                                        <option value="Developer" <?php echo ($user['designation'] == 'Developer') ? 'selected' : ''; ?>>Developer</option>
                                        <option value="Frontend developer" <?php echo ($user['designation'] == 'Frontend developer') ? 'selected' : ''; ?>>Frontend developer</option>
                                        <option value="Executive" <?php echo ($user['designation'] == 'Executive') ? 'selected' : ''; ?>>Executive</option>
                                    </select>
                                </div>

                                <div class="mb-20">
                                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Description</label>
                                    <textarea name="description" class="form-control radius-8"><?php echo htmlspecialchars($user['description']); ?></textarea>
                                </div>

                                <div class="d-flex align-items-center justify-content-center gap-3">
                                    <a href="users_list.php" class="border border-danger-600 bg-hover-danger-200 text-danger-600 text-md px-56 py-11 radius-8 text-decoration-none"> 
                                        Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary border border-primary-600 text-md px-56 py-12 radius-8"> 
                                        Update Profile
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
  
<?php include 'inc/footer.php' ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Image Preview Script
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
</script>