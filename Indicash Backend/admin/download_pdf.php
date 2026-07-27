<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require('fpdf/fpdf.php');

// Use your existing connection logic
$servername = "localhost";
$username = "apluscrm_mtkkdb";
$password = "&RNDrt3LA3sF";
$dbname = "apluscrm_mtkdb";
$con = mysqli_connect($servername, $username, $password, $dbname);

if (!$con) { die("Connection failed: " . mysqli_connect_error()); }

// --- NEW FILTER LOGIC ---
$type = $_GET['type'] ?? 'all';
$user_id = $_GET['user_id'] ?? '';
$utr = $_GET['utr'] ?? '';

$conditions = ["status='SUCCESS'"]; // We only want successful payments

if ($type == 'upi' || $type == 'gateway') {
    $conditions[] = "(payment_id IS NOT NULL AND payment_id != '')";
} elseif ($type == 'manual' || $type == 'gateway_manual') {
    $conditions[] = "(payment_id IS NULL OR payment_id = '')";
}

if (!empty($user_id)) {
    $conditions[] = "mobile='" . mysqli_real_escape_string($con, $user_id) . "'";
}

if (!empty($utr)) {
    $conditions[] = "payment_id LIKE '%" . mysqli_real_escape_string($con, $utr) . "%'";
}

$whereClause = "WHERE " . implode(' AND ', $conditions);
// -----------------------

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',14);
$pdf->Cell(190,10,'Add Money History Report',0,1,'C');
$pdf->Ln(5);

// Headers
$pdf->SetFont('Arial','B',10);
$pdf->Cell(60,10,'Name',1);
$pdf->Cell(35,10,'Amount',1);
$pdf->Cell(45,10,'Payment ID/UTR',1);
$pdf->Cell(50,10,'Date',1);
$pdf->Ln();

$pdf->SetFont('Arial','',10);

$result = mysqli_query($con, "SELECT * FROM payments $whereClause ORDER BY id DESC");

while($row = mysqli_fetch_assoc($result)) {
    $p_id = !empty($row['payment_id']) ? $row['payment_id'] : 'Manual';
    
    $pdf->Cell(60,8, substr($row['name'], 0, 30), 1);
    $pdf->Cell(35,8, $row['amount'], 1);
    $pdf->Cell(45,8, $p_id, 1);
    $pdf->Cell(50,8, date('d-m-Y h:i A', strtotime($row['created_at'])), 1);
    $pdf->Ln();
}

$pdf->Output("D", "Money_History_Report.pdf");
exit;
?>