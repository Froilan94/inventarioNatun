<?php
/**
 * exportar_movimientos_excel.php
 * Genera Excel (.xlsx) del reporte de movimientos con PhpSpreadsheet.
 */

require_once '../../../../auth/roles.php';
requireRoles(['admin_super', 'operadormed', 'supervisormed']);
require_once '../../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['datos'])) {
    die('Datos requeridos.');
}

$rows = json_decode($_POST['datos'], true);
if (!$rows) die('Datos inválidos.');

$fmtFecha = fn($f) => $f ? date('d/m/Y', strtotime($f)) : '—';

// ── Spreadsheet ───────────────────────────────────────────────────────────
$spreadsheet = new Spreadsheet();
$sheet       = $spreadsheet->getActiveSheet();
$sheet->setTitle('Movimientos');

// ── Título ────────────────────────────────────────────────────────────────
$sheet->mergeCells('A1:N1');
$sheet->setCellValue('A1', 'REPORTE DE MOVIMIENTOS DE MEDICAMENTOS');
$sheet->getStyle('A1')->applyFromArray([
    'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FF1A3A5C']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
]);
$sheet->getRowDimension(1)->setRowHeight(25);

$sheet->mergeCells('A2:N2');
$sheet->setCellValue('A2', 'Generado: ' . date('d/m/Y H:i'));
$sheet->getStyle('A2')->applyFromArray([
    'font'      => ['italic' => true, 'size' => 9, 'color' => ['argb' => 'FF888888']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
]);

// ── Encabezados ───────────────────────────────────────────────────────────
$cabeceras = [
    'A' => '#',
    'B' => 'Tipo',
    'C' => 'Fecha',
    'D' => 'Medicamento',
    'E' => 'Nombre Genérico',
    'F' => 'Lote',
    'G' => 'Vencimiento',
    'H' => 'Cantidad',
    'I' => 'Unidad',
    'J' => 'Presentación',
    'K' => 'Precio Unitario',
    'L' => 'Monto',
    'M' => 'Proveedor / Componente',
    'N' => 'Responsable',
];

$filaEnc = 4;
foreach ($cabeceras as $col => $label) {
    $sheet->setCellValue("{$col}{$filaEnc}", $label);
}

$sheet->getStyle("A{$filaEnc}:N{$filaEnc}")->applyFromArray([
    'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1A3A5C']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFFFFFFF']]],
]);
$sheet->getRowDimension($filaEnc)->setRowHeight(18);

// ── Datos ─────────────────────────────────────────────────────────────────
$fila = $filaEnc + 1;
foreach ($rows as $i => $r) {
    $esSalida = $r['tipo'] === 'Salida';
    $bgColor  = $esSalida ? 'FFFFF0F0' : 'FFF0FFF0'; // rojo suave / verde suave

    $sheet->setCellValue("A{$fila}", $i + 1);
    $sheet->setCellValue("B{$fila}", $r['tipo']);
    $sheet->setCellValue("C{$fila}", $fmtFecha($r['fecha']));
    $sheet->setCellValue("D{$fila}", $r['medicamento']);
    $sheet->setCellValue("E{$fila}", $r['nombre_generico'] ?? '');
    $sheet->setCellValue("F{$fila}", $r['lote']);
    $sheet->setCellValue("G{$fila}", $fmtFecha($r['fecha_vencimiento']));
    $sheet->setCellValue("H{$fila}", (float)$r['cantidad']);
    $sheet->setCellValue("I{$fila}", $r['unidad']);
    $sheet->setCellValue("J{$fila}", $r['presentacion']);
    $sheet->setCellValue("K{$fila}", (float)$r['precio_unitario']);
    $sheet->setCellValue("L{$fila}", (float)$r['monto']);
    $sheet->setCellValue("M{$fila}", $r['proveedor_donante']);
    $sheet->setCellValue("N{$fila}", $r['responsable']);

    $sheet->getStyle("A{$fila}:N{$fila}")->applyFromArray([
        'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bgColor]],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCCCCCC']]],
    ]);

    // Formato numérico
    $sheet->getStyle("H{$fila}")->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle("K{$fila}:L{$fila}")->getNumberFormat()->setFormatCode('"Q"#,##0.00');

    $fila++;
}

// ── Fila de totales ───────────────────────────────────────────────────────
$total_ing = array_sum(array_map(fn($r) => $r['tipo'] === 'Ingreso' ? (float)$r['monto'] : 0, $rows));
$total_sal = array_sum(array_map(fn($r) => $r['tipo'] === 'Salida'  ? (float)$r['monto'] : 0, $rows));

$sheet->mergeCells("A{$fila}:K{$fila}");
$sheet->setCellValue("A{$fila}", 'TOTALES');
$sheet->setCellValue("L{$fila}", $total_ing - $total_sal); // balance

$sheet->getStyle("A{$fila}:N{$fila}")->applyFromArray([
    'font'    => ['bold' => true],
    'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE8EFF8']],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
]);
$sheet->getStyle("L{$fila}")->getNumberFormat()->setFormatCode('"Q"#,##0.00');

// ── Anchos de columna ─────────────────────────────────────────────────────
$anchos = [
    'A' => 5,  'B' => 9,  'C' => 11, 'D' => 25, 'E' => 20,
    'F' => 12, 'G' => 11, 'H' => 10, 'I' => 10, 'J' => 12,
    'K' => 13, 'L' => 13, 'M' => 22, 'N' => 20,
];
foreach ($anchos as $col => $ancho) {
    $sheet->getColumnDimension($col)->setWidth($ancho);
}

// ── Congelar encabezados ──────────────────────────────────────────────────
$sheet->freezePane('A5');

// ── Salida ────────────────────────────────────────────────────────────────
$filename = 'movimientos_' . date('Y-m-d') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
