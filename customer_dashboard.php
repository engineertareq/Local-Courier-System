<?php
session_start();
require 'db.php';

// 1. Security Check: Redirect if not logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit;
}

$my_phone = $_SESSION['phone'];
$my_name = $_SESSION['name'];

// 2. Fetch Parcels belonging to this user (Matched by Phone)
$stmt = $pdo->prepare("SELECT * FROM parcels WHERE sender_phone = ? ORDER BY created_at DESC");
$stmt->execute([$my_phone]);
$my_parcels = $stmt->fetchAll();

// 3. Calculate Quick Stats
$total = count($my_parcels);
$pending = 0;
$delivered = 0;

foreach ($my_parcels as $p) {
    if ($p['current_status'] == 'delivered') $delivered++;
    else $pending++;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand" href="#">Courier Service</a>
    <div class="d-flex">
        <span class="navbar-text text-white me-3">Welcome, <?= htmlspecialchars($my_name) ?></span>
        <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
  </div>
</nav>

<div class="container mt-4">
    
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-center text-white bg-secondary mb-3">
                <div class="card-body">
                    <h3><?= $total ?></h3>
                    <p>Total Parcels</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center text-white bg-warning mb-3">
                <div class="card-body">
                    <h3><?= $pending ?></h3>
                    <p>In Transit / Pending</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center text-white bg-success mb-3">
                <div class="card-body">
                    <h3><?= $delivered ?></h3>
                    <p>Delivered</p>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>My Shipment History</h4>
        <button class="btn btn-primary" onclick="alert('Contact admin to book a new parcel!')">+ Book New Parcel</button>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <?php if (count($my_parcels) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Tracking #</th>
                            <th>Receiver</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($my_parcels as $row): ?>
                        <tr>
                            <td class="fw-bold"><?= $row['tracking_number'] ?></td>
                            <td>
                                <?= htmlspecialchars($row['receiver_name']) ?><br>
                                <small class="text-muted"><?= $row['receiver_phone'] ?></small>
                            </td>
                            <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                            <td>
                                <?php 
                                    $badge = 'bg-secondary';
                                    if($row['current_status'] == 'delivered') $badge = 'bg-success';
                                    elseif($row['current_status'] == 'picked_up') $badge = 'bg-info';
                                    elseif($row['current_status'] == 'out_for_delivery') $badge = 'bg-warning text-dark';
                                ?>
                                <span class="badge <?= $badge ?>"><?= strtoupper(str_replace('_', ' ', $row['current_status'])) ?></span>
                            </td>
                            <td>
                                <a href="track.php?tracking_number=<?= $row['tracking_number'] ?>" class="btn btn-sm btn-outline-primary">Track</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <div class="alert alert-info">You haven't sent any parcels yet.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>