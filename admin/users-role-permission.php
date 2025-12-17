<?php 
session_start();
include 'db.php';

// --- 1. ACCESS CONTROL ---
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    // header("Location: login.php"); // Uncomment in production
}

// --- 2. AJAX HANDLER (For Inline Updates) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $id = $_POST['user_id'];
    $success = false;

    try {
        if ($_POST['action'] === 'update_role') {
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE user_id = ?");
            $success = $stmt->execute([$_POST['value'], $id]);
        } 
        elseif ($_POST['action'] === 'update_permission') {
            $stmt = $pdo->prepare("UPDATE users SET permission_group = ? WHERE user_id = ?");
            $success = $stmt->execute([$_POST['value'], $id]);
        }
        elseif ($_POST['action'] === 'update_status') {
            $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE user_id = ?");
            $success = $stmt->execute([$_POST['status'], $id]);
        }
        elseif ($_POST['action'] === 'delete_user') {
            // Optional: Delete image file logic here
            $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
            $success = $stmt->execute([$id]);
        }

        echo json_encode(['success' => $success]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit; // Stop script execution after AJAX response
}

// --- 3. FETCH USERS ---
$stmt = $pdo->query("SELECT * FROM users ORDER BY user_id DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'inc/header.php';
?>

<div class="dashboard-main-body">

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <h6 class="fw-semibold mb-0">User Roles & Permissions</h6>
        <ul class="d-flex align-items-center gap-2">
            <li class="fw-medium">
                <a href="index.html" class="d-flex align-items-center gap-1 hover-text-primary">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                    Dashboard
                </a>
            </li>
            <li>-</li>
            <li class="fw-medium">User Roles & Permissions</li>
        </ul>
    </div>
    
    <div class="card basic-data-table">
        <div class="card-body">
            <div class="overflow-x-auto">
                <table class="table bordered-table mb-0 mx-0" id="dataTable" data-page-length='10'>
                    <thead>
                        <tr>
                            <th scope="col">User</th>
                            <th scope="col">Status</th>
                            <th scope="col">Role</th>
                            <th scope="col">Permission Group</th>
                            <th scope="col">Location</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($users as $user): ?>
                            <?php 
                                // Image handling
                                $img = "users_img/" . $user['profile_image'];
                                if(empty($user['profile_image']) || !file_exists($img)) {
                                    $img = "assets/images/user-dummy.png"; 
                                }
                                
                                // Status Badge Logic
                                $statusClass = 'bg-success-focus text-success-main';
                                if($user['status'] == 'Inactive') $statusClass = 'bg-danger-focus text-danger-main';
                                if($user['status'] == 'Pending') $statusClass = 'bg-warning-focus text-warning-main';
                            ?>
                            <tr id="row-<?php echo $user['user_id']; ?>">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo $img; ?>" alt="Image" class="w-40-px h-40-px rounded-circle flex-shrink-0 me-12 overflow-hidden object-fit-cover">
                                        <div class="flex-grow-1">
                                            <span class="text-md mb-0 fw-bolder text-primary-light d-block"><?php echo htmlspecialchars($user['full_name']); ?></span>
                                            <span class="text-sm mb-0 fw-normal text-secondary-light d-block"><?php echo htmlspecialchars($user['email']); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td> 
                                    <span class="status-badge <?php echo $statusClass; ?> px-20 py-4 rounded fw-medium text-sm">
                                        <?php echo htmlspecialchars($user['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <select class="form-control w-auto border border-neutral-900 fw-semibold text-primary-light text-sm change-role" data-id="<?php echo $user['user_id']; ?>">
                                        <option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                        <option value="staff" <?php echo ($user['role'] == 'staff') ? 'selected' : ''; ?>>Staff</option>
                                        <option value="customer" <?php echo ($user['role'] == 'customer') ? 'selected' : ''; ?>>Customer</option>
                                        <option value="manager" <?php echo ($user['role'] == 'manager') ? 'selected' : ''; ?>>Manager</option>
                                    </select>
                                </td>
                                <td>
                                    <select class="form-control w-auto border border-neutral-900 fw-semibold text-primary-light text-sm change-permission" data-id="<?php echo $user['user_id']; ?>">
                                        <option <?php echo ($user['permission_group'] == 'Full Access') ? 'selected' : ''; ?>>Full Access</option>
                                        <option <?php echo ($user['permission_group'] == 'View Only') ? 'selected' : ''; ?>>View Only</option>
                                        <option <?php echo ($user['permission_group'] == 'Accounting') ? 'selected' : ''; ?>>Accounting</option>
                                        <option <?php echo ($user['permission_group'] == 'Management') ? 'selected' : ''; ?>>Management</option>
                                        <option <?php echo ($user['permission_group'] == 'Hosts') ? 'selected' : ''; ?>>Hosts</option>
                                    </select>
                                </td>
                                <td>
                                    <span class="text-sm mb-0 fw-normal text-secondary-light d-block">
                                        <?php echo !empty($user['location']) ? htmlspecialchars($user['location']) : 'Unknown'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if($user['status'] == 'Active'): ?>
                                            <button type="button" class="btn rounded border text-neutral-500 border-neutral-500 radius px-4 py-6 bg-hover-neutral-500 text-hover-white flex-grow-1 action-btn" data-id="<?php echo $user['user_id']; ?>" data-action="update_status" data-val="Inactive">Deactivate</button>
                                        <?php elseif($user['status'] == 'Pending'): ?>
                                            <button type="button" class="btn rounded border text-success-600 border-success-600 radius px-4 py-6 bg-hover-success-600 text-hover-white flex-grow-1 action-btn" data-id="<?php echo $user['user_id']; ?>" data-action="update_status" data-val="Active">Approve</button>
                                        <?php else: ?>
                                            <button type="button" class="btn rounded border text-info-500 border-info-500 radius px-4 py-6 bg-hover-info-500 text-hover-white flex-grow-1 action-btn" data-id="<?php echo $user['user_id']; ?>" data-action="update_status" data-val="Active">Activate</button>
                                        <?php endif; ?>

                                        <a href="edit_user.php?id=<?php echo $user['user_id']; ?>" class="btn rounded border text-info-500 border-info-500 radius px-4 py-6 bg-hover-info-500 text-hover-white flex-grow-1 text-center text-decoration-none">Edit</a>
                                        
                                        <button type="button" class="btn rounded border text-danger-500 border-danger-500 radius px-4 py-6 bg-hover-danger-500 text-hover-white flex-grow-1 delete-btn" data-id="<?php echo $user['user_id']; ?>">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'inc/footer.php' ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

<script>
    let table = new DataTable('#dataTable');

    $(document).ready(function() {
        
        // 1. Handle Role Change
        $('.change-role').on('change', function() {
            updateData($(this).data('id'), 'update_role', $(this).val());
        });

        // 2. Handle Permission Change
        $('.change-permission').on('change', function() {
            updateData($(this).data('id'), 'update_permission', $(this).val());
        });

        // 3. Handle Status Buttons (Activate/Deactivate/Approve)
        $('.action-btn').on('click', function() {
            let userId = $(this).data('id');
            let status = $(this).data('val');
            let btn = $(this);

            $.ajax({
                url: '',
                type: 'POST',
                data: { action: 'update_status', user_id: userId, status: status },
                success: function(response) {
                    if(response.success) {
                        location.reload(); // Reload to see status badge/button change
                    } else {
                        alert('Error updating status');
                    }
                }
            });
        });

        // 4. Handle Delete
        $('.delete-btn').on('click', function() {
            if(!confirm('Are you sure you want to delete this user?')) return;
            
            let userId = $(this).data('id');
            let row = $('#row-' + userId);

            $.ajax({
                url: '',
                type: 'POST',
                data: { action: 'delete_user', user_id: userId },
                success: function(response) {
                    if(response.success) {
                        // Remove row visually without reload
                        row.fadeOut(300, function() { $(this).remove(); });
                    } else {
                        alert('Error deleting user');
                    }
                }
            });
        });

        // Generic Update Function
        function updateData(id, action, value) {
            $.ajax({
                url: '',
                type: 'POST',
                data: { action: action, user_id: id, value: value },
                success: function(response) {
                    if(!response.success) {
                        alert('Failed to update. Check database connection.');
                    }
                },
                error: function() {
                    alert('Server Error');
                }
            });
        }
    });
</script>