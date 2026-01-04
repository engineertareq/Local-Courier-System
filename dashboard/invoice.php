<?php
session_start();
require 'db.php';
require('libs/fpdf.php'); 

// --- ACCESS CONTROL ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php"); 
    exit();
}

$tracking_id = $_GET['tracking_id'] ?? null;
if (!$tracking_id) {
    die("Invalid Tracking ID");
}

// --- DATA FETCHING ---
$stmt = $pdo->prepare("SELECT * FROM parcels WHERE tracking_number = ?");
$stmt->execute([$tracking_id]);
$parcel = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$parcel) {
    die("Parcel/Invoice not found");
}

$userStmt = $pdo->prepare("SELECT phone FROM users WHERE user_id = ?");
$userStmt->execute([$_SESSION['user_id']]);
$currentUser = $userStmt->fetch(PDO::FETCH_ASSOC);

if (!$currentUser || $parcel['sender_phone'] !== $currentUser['phone']) {
    die("Error: You are not authorized to view this invoice.");
}

// --- PDF CLASS ---
class PDF extends FPDF
{
    function Header()
    {
        $this->SetFont('Arial','B',20);
        $this->SetTextColor(72, 52, 212); 
        $this->Cell(0,10,'Desh Courier',0,1,'L');
        
        $this->SetFont('Arial','',9);
        $this->SetTextColor(100,100,100);
        $this->Cell(0,5,'Global Logistics Solution',0,1,'L');
        $this->Cell(0,5,'Dhaka, Bangladesh',0,1,'L');
        $this->Cell(0,5,'support@deshcourier.com',0,1,'L');
        
        $this->SetXY(120, 10);
        $this->SetFont('Arial','B',24);
        $this->SetTextColor(220,220,220); 
        $this->Cell(80,10,'INVOICE',0,1,'R');
        
        $this->SetXY(120, 25);
        $this->SetFont('Arial','B',12);
        $this->SetTextColor(50,50,50);
        $this->Cell(80,6,'# ' . $GLOBALS['parcel']['tracking_number'],0,1,'R');
        
        $this->SetFont('Arial','',10);
        $this->Cell(190,6,'Booking Date: ' . date('d M, Y', strtotime($GLOBALS['parcel']['created_at'])),0,1,'R');
        
        $this->Ln(15); 
        $this->SetDrawColor(200,200,200);
        $this->Line(10, 45, 200, 45);
        $this->Ln(5);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->SetTextColor(128);
        $this->Cell(0,10,'Thank you for choosing Desh Courier! ',0,0,'C');
    }
}

// --- GENERATE PDF ---
$pdf = new PDF();
$pdf->AddPage();

// SENDER & RECEIVER
$pdf->SetFont('Arial','B',10);
$pdf->SetTextColor(108, 117, 125); 

$y = $pdf->GetY();
$pdf->Cell(95, 6, 'SENDER (FROM)', 0, 0, 'L');
$pdf->Cell(95, 6, 'RECEIVER (BILL TO)', 0, 1, 'R');

$pdf->SetFont('Arial','B',12);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(95, 8, $parcel['sender_name'], 0, 0, 'L');
$pdf->Cell(95, 8, $parcel['receiver_name'], 0, 1, 'R');

$pdf->SetFont('Arial','',10);
$pdf->SetTextColor(80, 80, 80);
$pdf->Cell(95, 5, $parcel['sender_phone'], 0, 0, 'L');
$pdf->Cell(95, 5, $parcel['receiver_phone'], 0, 1, 'R');

$pdf->SetFont('Arial','',9);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(95, 5, substr($parcel['sender_address'], 0, 50) . '...', 0, 0, 'L');
$pdf->Cell(95, 5, substr($parcel['receiver_address'], 0, 50) . '...', 0, 1, 'R');

$pdf->Ln(10);

// DETAILS GRID
$pdf->SetFillColor(240, 240, 240);
$pdf->SetTextColor(50, 50, 50);
$pdf->SetFont('Arial','B',9);

$pdf->Cell(47, 8, 'Weight', 1, 0, 'C', true);
$pdf->Cell(47, 8, 'Parcel Type', 1, 0, 'C', true);
$pdf->Cell(47, 8, 'Delivery Type', 1, 0, 'C', true);
$pdf->Cell(49, 8, 'Payment Method', 1, 1, 'C', true);

$pdf->SetFillColor(255, 255, 255);
$pdf->SetFont('Arial','',9);
$pdf->Cell(47, 8, $parcel['weight_kg'] . ' KG', 1, 0, 'C', false);
$pdf->Cell(47, 8, ucfirst($parcel['parcel_type']), 1, 0, 'C', false);
$pdf->Cell(47, 8, ucfirst($parcel['delivery_type']), 1, 0, 'C', false);
$pdf->Cell(49, 8, ucfirst($parcel['payment_method']), 1, 1, 'C', false);

$pdf->Ln(10);

// FINANCIAL TABLE
$pdf->SetFillColor(248, 249, 250);
$pdf->SetFont('Arial','B',10);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(80, 10, 'Description', 1, 0, 'L', true);
$pdf->Cell(40, 10, 'Status', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Tracking ID', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Amount', 1, 1, 'R', true);

$pdf->SetFont('Arial','',10);
$pdf->SetTextColor(0, 0, 0);

$descText = 'Courier Charge (' . $parcel['parcel_type'] . ' - ' . $parcel['weight_kg'] . 'kg)';

$pdf->Cell(80, 12, $descText, 1, 0, 'L');
$pdf->Cell(40, 12, strtoupper($parcel['current_status']), 1, 0, 'C');
$pdf->Cell(40, 12, $parcel['tracking_number'], 1, 0, 'C');

// --- CHANGED TO 'Tk' HERE ---
$pdf->Cell(30, 12, 'Tk ' . number_format($parcel['price'], 2), 1, 1, 'R');

$pdf->Ln(5);

// --- TOTALS ---
$pdf->SetX(120);
$pdf->SetFont('Arial','',10);
$pdf->Cell(50, 6, 'Subtotal:', 0, 0, 'R');
$pdf->SetFont('Arial','B',10);

// --- CHANGED TO 'Tk' HERE ---
$pdf->Cell(30, 6, 'Tk ' . number_format($parcel['price'], 2), 0, 1, 'R');

$pdf->SetX(120);
$pdf->SetFont('Arial','',10);
$pdf->Cell(50, 6, 'Tax (0%):', 0, 0, 'R');
$pdf->SetFont('Arial','B',10);

// --- CHANGED TO 'Tk' HERE ---
$pdf->Cell(30, 6, 'Tk 0.00', 0, 1, 'R');

$pdf->Ln(2);
$pdf->SetX(120);
$pdf->SetDrawColor(200,200,200);
$pdf->Line(125, $pdf->GetY(), 200, $pdf->GetY()); 
$pdf->Ln(3);

$pdf->SetX(120);
$pdf->SetFont('Arial','B',14);
$pdf->SetTextColor(72, 52, 212);
$pdf->Cell(50, 8, 'Total:', 0, 0, 'R');

// --- CHANGED TO 'Tk' HERE ---
$pdf->Cell(30, 8, 'Tk ' . number_format($parcel['price'], 2), 0, 1, 'R');

$pdf->Ln(5);
$pdf->SetX(120);
$pdf->SetFont('Arial','I',10);
$pdf->SetTextColor(100,100,100);
$pdf->Cell(80, 6, 'Paid via: ' . ucfirst($parcel['payment_method']), 0, 1, 'R');

$pdf->Output('D', 'Invoice-' . $parcel['tracking_number'] . '.pdf');
?>