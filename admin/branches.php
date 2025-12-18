<?php
require 'db.php';

// Fetch all branches from the database
try {
    $stmt = $pdo->query("SELECT * FROM branches ORDER BY branch_id DESC");
    $branches = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Error fetching branches: " . $e->getMessage());
}
?>

<?php include "inc/header.php"?>

  <div class="dashboard-main-body">

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
      <h6 class="fw-semibold mb-0">Branch List</h6>
      <ul class="d-flex align-items-center gap-2">
        <li class="fw-medium">
          <a href="index.html" class="d-flex align-items-center gap-1 hover-text-primary">
            <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
            Dashboard
          </a>
        </li>
        <li>-</li>
        <li class="fw-medium">Branches</li>
      </ul>
    </div>

    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">All Branches</h5>
            <a href="add-branch.php" class="btn btn-primary-600 btn-sm d-flex align-items-center gap-2">
                <iconify-icon icon="solar:add-circle-linear" class="text-xl"></iconify-icon> Add New Branch
            </a>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Branch Name</th>
                    <th scope="col">Contact Info</th>
                    <th scope="col">Address</th>
                    <th scope="col">Status</th>
                    <th scope="col">Joined Date</th>
                    <th scope="col" class="text-center">Action</th> </tr>
                </thead>
                <tbody>
                  <?php if (count($branches) > 0): ?>
                    <?php foreach ($branches as $branch): ?>
                      <tr>
                        <td>#<?= htmlspecialchars($branch['branch_id']) ?></td>
                        <td>
                          <div class="d-flex align-items-center">
                            <h6 class="text-md mb-0 fw-medium"><?= htmlspecialchars($branch['branch_name']) ?></h6>
                          </div>
                        </td>
                        <td>
                          <span class="text-sm d-block mb-1">
                            <iconify-icon icon="solar:phone-calling-linear" class="text-primary me-1"></iconify-icon> 
                            <?= htmlspecialchars($branch['branch_contact']) ?>
                          </span>
                          <span class="text-sm d-block">
                            <iconify-icon icon="mage:email" class="text-primary me-1"></iconify-icon> 
                            <?= htmlspecialchars($branch['branch_email']) ?>
                          </span>
                        </td>
                        <td>
                            <?= htmlspecialchars($branch['branch_address']) ?><br>
                            <small class="text-muted"><?= htmlspecialchars($branch['city']) ?> - <?= htmlspecialchars($branch['zip_code']) ?></small>
                        </td>
                        <td>
                          <?php if ($branch['status'] == 'active'): ?>
                            <span class="badge bg-success-focus text-success-main px-24 py-4 rounded-pill fw-medium text-sm">Active</span>
                          <?php else: ?>
                            <span class="badge bg-danger-focus text-danger-main px-24 py-4 rounded-pill fw-medium text-sm">Inactive</span>
                          <?php endif; ?>
                        </td>
                        <td><?= date('d M Y', strtotime($branch['created_at'])) ?></td>
                        <td class="text-center"> 
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <a href="add-branch.php?id=<?= $branch['branch_id'] ?>" class="w-32-px h-32-px bg-primary-light text-primary-600 rounded-circle d-flex justify-content-center align-items-center">
                                    <iconify-icon icon="solar:pen-new-square-linear" class="icon text-xl"></iconify-icon>
                                </a>
                            </div>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="7" class="text-center py-4">No branches found.</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>

<?php include "inc/footer.php" ?>