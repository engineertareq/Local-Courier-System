<?php 
session_start();
include 'db.php';

// --- 1. ACCESS CONTROL ---
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    // header("Location: login.php"); 
    // exit(); 
}

$message = "";

// --- 2. FORM SUBMISSION LOGIC ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Sanitize Inputs
    $full_name   = htmlspecialchars($_POST['full_name']);
    $email       = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $phone       = htmlspecialchars($_POST['phone']);
    $role        = $_POST['role']; 
    $department  = htmlspecialchars($_POST['department']);
    $designation = htmlspecialchars($_POST['designation']);
    $description = htmlspecialchars($_POST['description']);
    
    // Hash the password (maps to 'password_hash')
    $password    = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Image Upload Logic
    $image_name = null; // Default value (maps to 'profile_image')
    $upload_ok = true;

    if (!empty($_FILES['image']['name'])) {
        $target_dir = "users_img/";
        
        // Ensure folder exists
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = basename($_FILES["image"]["name"]);
        $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Unique name
        $unique_name = "user_" . time() . "." . $file_type;
        $target_file = $target_dir . $unique_name;

        // Validate Extension
        $allowed_types = ['jpg', 'jpeg', 'png'];
        if (!in_array($file_type, $allowed_types)) {
            $message = "<div class='alert alert-danger'>Error: Only JPG, JPEG, & PNG files are allowed.</div>";
            $upload_ok = false;
        }

        // Move File
        if ($upload_ok) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_name = $unique_name;
            } else {
                $message = "<div class='alert alert-danger'>Error uploading image.</div>";
                $upload_ok = false;
            }
        }
    }

    // --- 3. DATABASE INSERTION (Updated to match your Table) ---
    if ($upload_ok) {
        try {
            // UPDATED SQL QUERY
            // Note: We exclude 'user_id' so the database auto-increments it
            $sql = "INSERT INTO `users` 
                    (`full_name`, `email`, `password_hash`, `phone`, `role`, `department`, `designation`, `description`, `created_at`, `profile_image`) 
                    VALUES 
                    (:name, :email, :pass, :phone, :role, :dept, :desig, :desc, NOW(), :img)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':name'  => $full_name,
                ':email' => $email,
                ':pass'  => $password,    // Maps to password_hash
                ':phone' => $phone,
                ':role'  => $role,
                ':dept'  => $department,
                ':desig' => $designation,
                ':desc'  => $description,
                ':img'   => $image_name   // Maps to profile_image
            ]);

            $message = "<div class='alert alert-success'>User added successfully!</div>";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $message = "<div class='alert alert-warning'>Error: Email already exists.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Database Error: " . $e->getMessage() . "</div>";
            }
        }
    }
}
?>

<?php include 'inc/header.php'; ?>
    
    <div class="dashboard-main-body">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
  <h6 class="fw-semibold mb-0">Add User</h6>
  <ul class="d-flex align-items-center gap-2">
    <li class="fw-medium">
      <a href="index.html" class="d-flex align-items-center gap-1 hover-text-primary">
        <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
        Dashboard
      </a>
    </li>
    <li>-</li>
    <li class="fw-medium">Add User</li>
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
                                                <div id="imagePreview" style="background-image: url('assets/images/user-dummy.png');"> </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-20">
                                        <label for="name" class="form-label fw-semibold text-primary-light text-sm mb-8">Full Name <span class="text-danger-600">*</span></label>
                                        <input type="text" name="full_name" class="form-control radius-8" id="name" placeholder="Enter Full Name" required>
                                    </div>

                                    <div class="mb-20">
                                        <label for="email" class="form-label fw-semibold text-primary-light text-sm mb-8">Email <span class="text-danger-600">*</span></label>
                                        <input type="email" name="email" class="form-control radius-8" id="email" placeholder="Enter email address" required>
                                    </div>

                                    <div class="mb-20">
                                        <label for="password" class="form-label fw-semibold text-primary-light text-sm mb-8">Password <span class="text-danger-600">*</span></label>
                                        <input type="password" name="password" class="form-control radius-8" id="password" placeholder="Enter password" required>
                                    </div>

                                    <div class="mb-20">
                                        <label for="number" class="form-label fw-semibold text-primary-light text-sm mb-8">Phone</label>
                                        <input type="text" name="phone" class="form-control radius-8" id="number" placeholder="Enter phone number">
                                    </div>

                                    <div class="mb-20">
                                        <label for="role" class="form-label fw-semibold text-primary-light text-sm mb-8">User Role <span class="text-danger-600">*</span></label>
                                        <select class="form-control radius-8 form-select" name="role" id="role" required>
                                            <option value="customer">Customer</option>
                                            <option value="staff">Staff</option>
                                            <option value="admin">Admin</option>
                                        </select>
                                    </div>

                                    <div class="mb-20">
                                        <label for="depart" class="form-label fw-semibold text-primary-light text-sm mb-8">Department</label>
                                        <select class="form-control radius-8 form-select" name="department" id="depart">
                                            <option value="">Select Department</option>
                                            <option value="IT">IT</option>
                                            <option value="HR">HR</option>
                                            <option value="Sales">Sales</option>
                                        </select>
                                    </div>

                                    <div class="mb-20">
                                        <label for="desig" class="form-label fw-semibold text-primary-light text-sm mb-8">Designation</label>
                                        <select class="form-control radius-8 form-select" name="designation" id="desig">
                                            <option value="">Select Designation</option>
                                            <option value="Manager">Manager</option>
                                            <option value="Developer">Developer</option>
                                            <option value="Executive">Executive</option>
                                        </select>
                                    </div>

                                    <div class="mb-20">
                                        <label for="desc" class="form-label fw-semibold text-primary-light text-sm mb-8">Description</label>
                                        <textarea name="description" class="form-control radius-8" id="desc" placeholder="Write description..."></textarea>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-center gap-3">
                                        <button type="button" class="border border-danger-600 bg-hover-danger-200 text-danger-600 text-md px-56 py-11 radius-8"> 
                                            Cancel
                                        </button>
                                        <button type="submit" class="btn btn-primary border border-primary-600 text-md px-56 py-12 radius-8"> 
                                            Save
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
    // ================== Image Upload Js Start ===========================
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
    // ================== Image Upload Js End ===========================
</script>