<?php
session_start();
require 'db.php';
// Adjust this path to where you put the fpdf.php file
require('libs/fpdf.php'); 

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

// --- START PDF GENERATION ---

class PDF extends FPDF
{
    // Page Header
    function Header()
    {
        // Logo (Adjust path if needed, or remove if no image)
        // $this->Image('assets/images/logo.png',10,6,30);
        
        $this->SetFont('Arial','B',20);
        $this->SetTextColor(72, 52, 212); // Brand Color (Desh Courier Blue)
        $this->Cell(0,10,'Desh Courier',0,1,'L');
        
        $this->SetFont('Arial','',9);
        $this->SetTextColor(100,100,100);
        $this->Cell(0,5,'Global Logistics Solution',0,1,'L');
        $this->Cell(0,5,'Dhaka, Bangladesh',0,1,'L');
        $this->Cell(0,5,'support@deshcourier.com',0,1,'L');
        
        // Invoice Title (Right Aligned)
        $this->SetXY(120, 10);
        $this->SetFont('Arial','B',24);
        $this->SetTextColor(220,220,220); // Light Grey
        $this->Cell(80,10,'INVOICE',0,1,'R');
        
        $this->SetXY(120, 25);
        $this->SetFont('Arial','B',12);
        $this->SetTextColor(50,50,50);
        $this->Cell(80,6,'# ' . $GLOBALS['parcel']['tracking_number'],0,1,'R');
        
        $this->SetFont('Arial','',10);
        // Using created_at for the booking date
        $this->Cell(190,6,'Booking Date: ' . date('d M, Y', strtotime($GLOBALS['parcel']['created_at'])),0,1,'R');
        
        $this->Ln(15); // Line break
        $this->SetDrawColor(200,200,200);
        $this->Line(10, 45, 200, 45);
        $this->Ln(5);
    }

    // Page Footer
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->SetTextColor(128);
        $this->Cell(0,10,'Thank you for choosing Desh Courier! ',0,0,'C');
    }
}

// Create PDF
$pdf = new PDF();
$pdf->AddPage();

// --- SENDER & RECEIVER INFO ---
$pdf->SetFont('Arial','B',10);
$pdf->SetTextColor(108, 117, 125); // Secondary Color

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
// Increased substring limit slightly
$pdf->Cell(95, 5, substr($parcel['sender_address'], 0, 50) . '...', 0, 0, 'L');
$pdf->Cell(95, 5, substr($parcel['receiver_address'], 0, 50) . '...', 0, 1, 'R');

$pdf->Ln(10);

// --- NEW SECTION: SHIPMENT DETAILS GRID ---
// This adds: Weight, Parcel Type, Delivery Type, Payment Method
$pdf->SetFillColor(240, 240, 240); // Lighter Gray
$pdf->SetTextColor(50, 50, 50);
$pdf->SetFont('Arial','B',9);

// Header Row for Details
$pdf->Cell(47, 8, 'Weight', 1, 0, 'C', true);
$pdf->Cell(47, 8, 'Parcel Type', 1, 0, 'C', true);
$pdf->Cell(47, 8, 'Delivery Type', 1, 0, 'C', true);
$pdf->Cell(49, 8, 'Payment Method', 1, 1, 'C', true);

// Data Row for Details
$pdf->SetFillColor(255, 255, 255); // White
$pdf->SetFont('Arial','',9);
$pdf->Cell(47, 8, $parcel['weight_kg'] . ' KG', 1, 0, 'C', false);
$pdf->Cell(47, 8, ucfirst($parcel['parcel_type']), 1, 0, 'C', false);
$pdf->Cell(47, 8, ucfirst($parcel['delivery_type']), 1, 0, 'C', false);
$pdf->Cell(49, 8, ucfirst($parcel['payment_method']), 1, 1, 'C', false);

$pdf->Ln(10);

// --- FINANCIAL TABLE HEADER ---
$pdf->SetFillColor(248, 249, 250); // Light Gray Background
$pdf->SetFont('Arial','B',10);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(80, 10, 'Description', 1, 0, 'L', true);
$pdf->Cell(40, 10, 'Status', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Tracking ID', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Amount', 1, 1, 'R', true);

// --- TABLE ROW ---
$pdf->SetFont('Arial','',10);
$pdf->SetTextColor(0, 0, 0);

// Description combines Type and Weight for clarity
$descText = 'Courier Charge (' . $parcel['parcel_type'] . ' - ' . $parcel['weight_kg'] . 'kg)';

$pdf->Cell(80, 12, $descText, 1, 0, 'L');
$pdf->Cell(40, 12, strtoupper($parcel['current_status']), 1, 0, 'C');
$pdf->Cell(40, 12, $parcel['tracking_number'], 1, 0, 'C');
$pdf->Cell(30, 12, '$' . number_format($parcel['price'], 2), 1, 1, 'R');

$pdf->Ln(5);

// --- TOTALS SECTION ---
$pdf->SetX(120); // Move to right side
$pdf->SetFont('Arial','',10);
$pdf->Cell(50, 6, 'Subtotal:', 0, 0, 'R');
$pdf->SetFont('Arial','B',10);
$pdf->Cell(30, 6, '$' . number_format($parcel['price'], 2), 0, 1, 'R');

$pdf->SetX(120);
$pdf->SetFont('Arial','',10);
$pdf->Cell(50, 6, 'Tax (0%):', 0, 0, 'R');
$pdf->SetFont('Arial','B',10);
$pdf->Cell(30, 6, '$0.00', 0, 1, 'R');

$pdf->Ln(2);
$pdf->SetX(120);
$pdf->SetDrawColor(200,200,200);
$pdf->Line(125, $pdf->GetY(), 200, $pdf->GetY()); // Separator Line
$pdf->Ln(3);

$pdf->SetX(120);
$pdf->SetFont('Arial','B',14);
$pdf->SetTextColor(72, 52, 212); // Brand Blue
$pdf->Cell(50, 8, 'Total:', 0, 0, 'R');
$pdf->Cell(30, 8, '$' . number_format($parcel['price'], 2), 0, 1, 'R');

// Payment status badge
$pdf->Ln(5);
$pdf->SetX(120);
$pdf->SetFont('Arial','I',10);
$pdf->SetTextColor(100,100,100);
$pdf->Cell(80, 6, 'Paid via: ' . ucfirst($parcel['payment_method']), 0, 1, 'R');


// --- OUTPUT ---
$pdf->Output('D', 'Invoice-' . $parcel['tracking_number'] . '.pdf');
?>