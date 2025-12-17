<?php
session_start();
require 'db.php';

// Access Control
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    header("Location: login.php"); exit();
}

$id = $_GET['id'] ?? null;

if (!$id) die("Invalid Invoice ID");

// Fetch Parcel Data
$stmt = $pdo->prepare("SELECT * FROM parcels WHERE parcel_id = ?");
$stmt->execute([$id]);
$parcel = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$parcel) die("Parcel not found");

// Current Date for Invoice Date
$invoice_date = date('d M, Y');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?= $parcel['tracking_number'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Libre+Barcode+39+Text&display=swap" rel="stylesheet">
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>

    <style>
        body { background: #f5f7fa; color: #333; font-family: 'Segoe UI', sans-serif; }
        
        .invoice-container {
            max-width: 800px;
            margin: 40px auto;
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }

        .barcode {
            font-family: 'Libre Barcode 39 Text', cursive;
            font-size: 40px;
            color: #333;
        }

        .brand-color { color: #4834d4; }
        .bg-brand { background-color: #4834d4 !important; color: white; }

        .table-invoice th { background-color: #f8f9fa; color: #6c757d; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; }
        .table-invoice td { vertical-align: middle; }

        /* Print Specific Styles */
        @media print {
            body { background: #fff; -webkit-print-color-adjust: exact; }
            .invoice-container { box-shadow: none; margin: 0; padding: 0; width: 100%; max-width: 100%; border-radius: 0; }
            .no-print { display: none !important; }
            .btn { display: none; }
        }
    </style>
</head>
<body>

<div class="container">
    
    <div class="d-flex justify-content-center gap-3 my-4 no-print">
        <a href="shipment_list.php" class="btn btn-secondary">
            <iconify-icon icon="solar:arrow-left-linear"></iconify-icon> Back
        </a>
        <button onclick="window.print()" class="btn btn-primary bg-brand border-0">
            <iconify-icon icon="solar:printer-bold"></iconify-icon> Print Invoice
        </button>
    </div>

    <div class="invoice-container">
        
        <div class="d-flex justify-content-between align-items-center border-bottom pb-4 mb-4">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <iconify-icon icon="solar:box-bold-duotone" class="brand-color fs-2"></iconify-icon>
                    <h3 class="fw-bold m-0 brand-color">Desh Courier</h3>
                </div>
                <p class="mb-0 text-muted small">Global Logistics Solution</p>
                <p class="mb-0 text-muted small">Dhaka, Bangladesh</p>
                <p class="mb-0 text-muted small">support@deshcourier.com</p>
            </div>
            <div class="text-end">
                <h2 class="fw-bold text-uppercase text-secondary opacity-25">Invoice</h2>
                <div class="barcode"><?= $parcel['tracking_number'] ?></div>
                <p class="mb-0"><strong>Date:</strong> <?= $invoice_date ?></p>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-6">
                <h6 class="text-uppercase text-secondary fw-bold small mb-3">Sender (From)</h6>
                <h5 class="fw-bold"><?= htmlspecialchars($parcel['sender_name']) ?></h5>
                <p class="mb-1 text-muted"><?= htmlspecialchars($parcel['sender_phone']) ?></p>
                <p class="mb-0 text-muted" style="max-width: 250px;"><?= htmlspecialchars($parcel['sender_address']) ?></p>
            </div>
            <div class="col-6 text-end">
                <h6 class="text-uppercase text-secondary fw-bold small mb-3">Receiver (Bill To)</h6>
                <h5 class="fw-bold"><?= htmlspecialchars($parcel['receiver_name']) ?></h5>
                <p class="mb-1 text-muted"><?= htmlspecialchars($parcel['receiver_phone']) ?></p>
                <p class="mb-0 text-muted d-inline-block text-start" style="max-width: 250px;"><?= htmlspecialchars($parcel['receiver_address']) ?></p>
            </div>
        </div>

        <table class="table table-invoice mb-4">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Tracking ID</th>
                    <th class="text-end">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong class="d-block">Courier Delivery Charge</strong>
                        <span class="text-muted small">Standard Delivery Package</span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark border"><?= strtoupper($parcel['current_status']) ?></span>
                    </td>
                    <td class="text-center"><?= $parcel['tracking_number'] ?></td>
                    <td class="text-end fw-bold">$<?= number_format($parcel['price'], 2) ?></td>
                </tr>
            </tbody>
        </table>

        <div class="row justify-content-end">
            <div class="col-md-5">
                <div class="table-responsive">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="text-muted">Subtotal:</td>
                            <td class="text-end fw-bold">$<?= number_format($parcel['price'], 2) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tax (0%):</td>
                            <td class="text-end fw-bold">$0.00</td>
                        </tr>
                        <tr class="border-top">
                            <td class="fs-5 fw-bold brand-color py-3">Total:</td>
                            <td class="fs-5 fw-bold brand-color text-end py-3">$<?= number_format($parcel['price'], 2) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-5 pt-4 border-top text-center text-muted small">
            <p class="mb-1">Thank you for choosing Desh Courier!</p>
            <p>Terms & Conditions apply. This is a computer-generated invoice.</p>
        </div>

    </div>
</div>

</body>
</html>