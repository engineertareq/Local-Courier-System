<?php 
session_start();
include 'db.php';

// --- 1. ACCESS CONTROL ---
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    // header("Location: login.php"); // Uncomment to redirect
}

// --- 2. DELETE LOGIC ---
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    
    // Optional: Delete the image file from server before deleting record
    $stmt = $pdo->prepare("SELECT profile_image FROM users WHERE user_id = ?");
    $stmt->execute([$id]);
    $img = $stmt->fetchColumn();
    if ($img && file_exists("users_img/" . $img)) {
        unlink("users_img/" . $img);
    }

    // Delete Record
    $delete_stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
    $delete_stmt->execute([$id]);
    
    header("Location: " . $_SERVER['PHP_SELF']); // Refresh page
    exit();
}

// --- 3. SEARCH & PAGINATION LOGIC ---
$limit = 10; // Rows per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build Query
$sql = "SELECT * FROM users WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (full_name LIKE :search OR email LIKE :search)";
    $params[':search'] = "%$search%";
}

// Count Total Rows (for Pagination)
$count_stmt = $pdo->prepare($sql);
$count_stmt->execute($params);
$total_rows = $count_stmt->rowCount();
$total_pages = ceil($total_rows / $limit);

// Fetch Data for Current Page
$sql .= " ORDER BY user_id DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);

// Bind params explicitly for limit/offset (PDO requirements)
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'inc/header.php';
?>
    
<div class="dashboard-main-body">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <h6 class="fw-semibold mb-0">Users Grid</h6>
        <ul class="d-flex align-items-center gap-2">
            <li class="fw-medium">
                <a href="index.html" class="d-flex align-items-center gap-1 hover-text-primary">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                    Dashboard
                </a>
            </li>
            <li>-</li>
            <li class="fw-medium">Users Grid</li>
        </ul>
    </div>

    <div class="card h-100 p-0 radius-12">
        <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center flex-wrap gap-3 justify-content-between">
            <div class="d-flex align-items-center flex-wrap gap-3">
                <span class="text-md fw-medium text-secondary-light mb-0">Show</span>
                <select class="form-select form-select-sm w-auto ps-12 py-6 radius-12 h-40-px">
                    <option>10</option>
                </select>
                
                <form class="navbar-search" method="GET" action="">
                    <input type="text" class="bg-base h-40-px w-auto" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search Name/Email">
                    <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
                </form>

            </div>
            <a href="add-user.php" class="btn btn-primary text-sm btn-sm px-12 py-12 radius-8 d-flex align-items-center gap-2"> 
                <iconify-icon icon="ic:baseline-plus" class="icon text-xl line-height-1"></iconify-icon>
                Add New User
            </a>
        </div>
        
        <div class="card-body p-24">
            <div class="table-responsive scroll-sm">
                <table class="table bordered-table sm-table mb-0">
                    <thead>
                        <tr>
                            <th scope="col">
                                <div class="d-flex align-items-center gap-10">
                                    <div class="form-check style-check d-flex align-items-center">
                                        <input class="form-check-input radius-4 border input-form-dark" type="checkbox" name="checkbox" id="selectAll">
                                    </div>
                                    S.L
                                </div>
                            </th>
                            <th scope="col">Join Date</th>
                            <th scope="col">Name</th>
                            <th scope="col">Email</th>
                            <th scope="col">Department</th>
                            <th scope="col">Designation</th>
                            <th scope="col" class="text-center">Role</th>
                            <th scope="col" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                        <?php if (count($users) > 0): ?>
                            <?php foreach ($users as $index => $user): ?>
                            <?php 
                                // Image Logic
                                $img_path = "users_img/" . $user['profile_image'];
                                if (empty($user['profile_image']) || !file_exists($img_path)) {
                                    $img_path = "assets/images/user-dummy.png"; // Fallback image
                                }
                                
                                // Serial Number Calculation
                                $serial = $offset + $index + 1;
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-10">
                                        <div class="form-check style-check d-flex align-items-center">
                                            <input class="form-check-input radius-4 border border-neutral-400" type="checkbox" name="checkbox">
                                        </div>
                                        <?php echo str_pad($serial, 2, '0', STR_PAD_LEFT); ?>
                                    </div>
                                </td>
                                <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo $img_path; ?>" alt="Image" class="w-40-px h-40-px rounded-circle flex-shrink-0 me-12 overflow-hidden object-fit-cover">
                                        <div class="flex-grow-1">
                                            <span class="text-md mb-0 fw-normal text-secondary-light"><?php echo htmlspecialchars($user['full_name']); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="text-md mb-0 fw-normal text-secondary-light"><?php echo htmlspecialchars($user['email']); ?></span></td>
                                <td><?php echo htmlspecialchars($user['department']); ?></td>
                                <td><?php echo htmlspecialchars($user['designation']); ?></td>
                                <td class="text-center">
                                    <?php 
                                        $badgeClass = ($user['role'] == 'admin') ? 'bg-danger-focus text-danger-600' : 
                                                     (($user['role'] == 'staff') ? 'bg-info-focus text-info-600' : 'bg-success-focus text-success-600');
                                    ?>
                                    <span class="<?php echo $badgeClass; ?> border px-24 py-4 radius-4 fw-medium text-sm text-capitalize">
                                        <?php echo htmlspecialchars($user['role']); ?>
                                    </span> 
                                </td>
                                <td class="text-center"> 
                                    <div class="d-flex align-items-center gap-10 justify-content-center">
                                        <a href="view_user.php?id=<?php echo $user['user_id']; ?>" type="button" class="bg-info-focus bg-hover-info-200 text-info-600 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle"> 
                                            <iconify-icon icon="majesticons:eye-line" class="icon text-xl"></iconify-icon>
                                        </a>
                                        <a href="edit_user.php?id=<?php echo $user['user_id']; ?>" type="button" class="bg-success-focus text-success-600 bg-hover-success-200 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle"> 
                                            <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
                                        </a>
                                        <a href="?delete_id=<?php echo $user['user_id']; ?>" onclick="return confirm('Are you sure you want to delete this user?');" class="bg-danger-focus bg-hover-danger-200 text-danger-600 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle"> 
                                            <iconify-icon icon="fluent:delete-24-regular" class="menu-icon"></iconify-icon>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">No users found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-24">
                <span>Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $limit, $total_rows); ?> of <?php echo $total_rows; ?> entries</span>
                <ul class="pagination d-flex flex-wrap align-items-center gap-2 justify-content-center">
                    
                    <?php if($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md" href="?page=<?php echo $page-1; ?>&search=<?php echo $search; ?>">
                            <iconify-icon icon="ep:d-arrow-left"></iconify-icon>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item">
                        <a class="page-link <?php echo ($i == $page) ? 'bg-primary-600 text-white' : 'bg-neutral-200 text-secondary-light'; ?> fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md" href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                    <?php endfor; ?>

                    <?php if($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md" href="?page=<?php echo $page+1; ?>&search=<?php echo $search; ?>">
                            <iconify-icon icon="ep:d-arrow-right"></iconify-icon>
                        </a>
                    </li>
                    <?php endif; ?>

                </ul>
            </div>
        </div>
    </div>
</div>
  
<?php include 'inc/footer.php' ?>

<script>
    // Optional: Select All checkbox functionality
    $('#selectAll').click(function() {
        $('input[name="checkbox"]').prop('checked', this.checked);
    });
</script>