<?php
require 'db.php'; 

define('STORE_ID', 'aamarpaytest');
define('SIGNATURE_KEY', 'dbb74894e82415a2f7ff0ec3a97e4183');
define('VERIFY_URL', 'https://sandbox.aamarpay.com/api/v1/trxcheck/request.php');
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mer_txnid'])) {

    $tracking_number = $_POST['mer_txnid'];


    $verify_url = VERIFY_URL . "?request_id=$tracking_number&store_id=" . STORE_ID . "&signature_key=" . SIGNATURE_KEY . "&type=json";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $verify_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);
    if (isset($result['pay_status']) && $result['pay_status'] == 'Successful') {
        
        try {
            $sql = "UPDATE parcels SET payment_status = 'Paid', updated_at = NOW() WHERE tracking_number = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$tracking_number]);

            $stmtGetId = $pdo->prepare("SELECT parcel_id FROM parcels WHERE tracking_number = ?");
            $stmtGetId->execute([$tracking_number]);
            $parcel = $stmtGetId->fetch();

            if ($parcel) {
                $desc = "Payment Verified via Aamarpay. Trans ID: " . $tracking_number;

                $historySql = "INSERT INTO parcel_history 
                              (parcel_id, status, description, location, updated_by_user_id, timestamp) 
                              VALUES (?, 'Payment Verified', ?, 'Online', NULL, NOW())";
                
                $stmtHist = $pdo->prepare($historySql);
                $stmtHist->execute([$parcel['parcel_id'], $desc]);
            }

            echo '<!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Payment Success</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            </head>
            <body class="d-flex align-items-center justify-content-center vh-100 bg-light">
                <div class="card text-center shadow p-5" style="max-width: 500px;">
                    <div class="text-success mb-3" style="font-size: 5rem;">✔</div>
                    <h2 class="mb-3">Payment Successful!</h2>
                    <p>Your tracking number is <strong>' . htmlspecialchars($tracking_number) . '</strong></p>
                    <p class="text-muted">The payment status has been updated to <strong>Paid</strong>.</p>
                    <a href="index.php" class="btn btn-primary mt-3">Back to Dashboard</a>
                </div>
            </body>
            </html>';

        } catch (Exception $e) {
            // Handle Database Errors
            echo '<div style="color:red; text-align:center; margin-top:50px;">
                    <h3>Database Error</h3>
                    <p>' . $e->getMessage() . '</p>
                  </div>';
        }

    } else {
        // Handle Verification Failures
        echo '<div style="color:red; text-align:center; margin-top:50px;">
                <h3>Payment Verification Failed</h3>
                <p>Status: ' . htmlspecialchars($result['pay_status'] ?? 'Unknown') . '</p>
              </div>';
    }

} else {
    echo "Invalid Access. No transaction ID received.";
}
?>