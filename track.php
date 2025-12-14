<?php
require 'db.php';

$tracking_data = null;
$history_logs = [];

if (isset($_GET['tracking_number'])) {
    $trk = $_GET['tracking_number'];

    // Fetch Parcel Details
    $stmt = $pdo->prepare("SELECT * FROM parcels WHERE tracking_number = ?");
    $stmt->execute([$trk]);
    $tracking_data = $stmt->fetch();

    if ($tracking_data) {
        // Fetch History Timeline
        $stmtHistory = $pdo->prepare("SELECT * FROM parcel_history WHERE parcel_id = ? ORDER BY timestamp DESC");
        $stmtHistory->execute([$tracking_data['parcel_id']]);
        $history_logs = $stmtHistory->fetchAll();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Parcel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .timeline-step { border-left: 3px solid #0d6efd; padding-left: 20px; padding-bottom: 20px; position: relative; }
        .timeline-step::before { content: ''; width: 12px; height: 12px; background: #0d6efd; border-radius: 50%; position: absolute; left: -7.5px; top: 0; }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5" style="max-width: 600px;">
        <h2 class="text-center mb-4">🔍 Track Your Package</h2>
        
        <form method="GET" class="d-flex mb-4">
            <input type="text" name="tracking_number" class="form-control me-2" placeholder="Enter Tracking ID (e.g. TRK-123456)" required>
            <button type="submit" class="btn btn-primary">Track</button>
        </form>

        <?php if ($tracking_data): ?>
            <div class="card shadow mb-4">
                <div class="card-body">
                    <h5 class="card-title">Tracking ID: <?= htmlspecialchars($tracking_data['tracking_number']) ?></h5>
                    <p class="mb-1"><strong>Status:</strong> <span class="badge bg-info text-dark"><?= strtoupper($tracking_data['current_status']) ?></span></p>
                    <p class="mb-1"><strong>Receiver:</strong> <?= htmlspecialchars($tracking_data['receiver_name']) ?></p>
                    <p><strong>Destination:</strong> <?= htmlspecialchars($tracking_data['receiver_address']) ?></p>
                </div>
            </div>

            <h5 class="mb-3">Delivery Timeline</h5>
            <div class="card shadow">
                <div class="card-body">
                    <?php foreach ($history_logs as $log): ?>
                        <div class="timeline-step">
                            <p class="fw-bold mb-0"><?= htmlspecialchars($log['status']) ?></p>
                            <small class="text-muted"><?= $log['timestamp'] ?> - <?= htmlspecialchars($log['location']) ?></small>
                            <p class="small mt-1"><?= htmlspecialchars($log['description']) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php elseif (isset($_GET['tracking_number'])): ?>
            <div class="alert alert-danger">Tracking ID not found.</div>
        <?php endif; ?>
    </div>
</body>
</html>