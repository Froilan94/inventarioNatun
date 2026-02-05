<?php
require "../config/db.php";
require "../libs/fpdf/fpdf.php";

$pdf = new FPDF('L','mm','A4');
$pdf->AddPage();
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,10,'Reporte de Existencias',0,1,'C');

$pdf->SetFont('Arial','B',9);
$pdf->Cell(40,8,'Medicamento',1);
$pdf->Cell(30,8,'Lote',1);
$pdf->Cell(30,8,'Vencimiento',1);
$pdf->Cell(30,8,'Cantidad',1);
$pdf->Cell(30,8,'Monto',1);
$pdf->Cell(60,8,'Proveedor',1);
$pdf->Ln();

$sql = "SELECT * FROM vw_reporte_existencias";
$data = $pdo->query($sql);

$pdf->SetFont('Arial','',9);
foreach ($data as $r) {
    $pdf->Cell(40,8,$r['medicamento'],1);
    $pdf->Cell(30,8,$r['numero_lote'],1);
    $pdf->Cell(30,8,$r['fecha_vencimiento'],1);
    $pdf->Cell(30,8,$r['cantidad_existente'],1);
    $pdf->Cell(30,8,$r['monto_existente'],1);
    $pdf->Cell(60,8,$r['proveedor_donante'],1);
    $pdf->Ln();
}

$pdf->Output();
