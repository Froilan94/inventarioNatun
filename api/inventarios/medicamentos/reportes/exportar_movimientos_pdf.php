<?php
/**
 * exportar_movimientos_pdf.php
 * Genera PDF del reporte de movimientos con:
 *  - Línea de firma por cada registro de salida
 *  - Casilla de selección respetada (solo exporta los enviados)
 *  - Pie: Elaborado por / Autorizado por / Visto Bueno
 */

require_once '../../../../auth/roles.php';
requireRoles(['admin_super', 'operadormed', 'supervisormed']);
require_once '../../../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['datos'])) {
    die('Datos requeridos.');
}

$rows = json_decode($_POST['datos'], true);
if (!$rows) die('Datos inválidos.');

// ── Helpers ───────────────────────────────────────────────────────────────
$fmt      = fn($n) => 'Q ' . number_format((float)$n, 2, '.', ',');
$fmtFecha = fn($f) => $f ? date('d/m/Y', strtotime($f)) : '—';
$fechaHoy = date('d/m/Y H:i');

// ── Separar ingresos y salidas ────────────────────────────────────────────
$ingresos = array_filter($rows, fn($r) => $r['tipo'] === 'Ingreso');
$salidas  = array_filter($rows, fn($r) => $r['tipo'] === 'Salida');

$total_ing = array_sum(array_column(array_values($ingresos), 'monto'));
$total_sal = array_sum(array_column(array_values($salidas),  'monto'));

// ── Construir filas de tabla ───────────────────────────────────────────────
function buildFilas(array $rows, bool $conFirma): string {
    global $fmt, $fmtFecha;
    $html = '';
    $n    = 1;

    foreach ($rows as $r) {
        $badge = $r['tipo'] === 'Ingreso'
            ? '<span style="background:#198754;color:#fff;padding:1px 5px;border-radius:3px;font-size:8px;">Ingreso</span>'
            : '<span style="background:#dc3545;color:#fff;padding:1px 5px;border-radius:3px;font-size:8px;">Salida</span>';

        $html .= "
            <tr>
                <td style='text-align:center;'>{$n}</td>
                <td>{$badge}</td>
                <td>{$fmtFecha($r['fecha'])}</td>
                <td>
                    <strong>{$r['medicamento']}</strong>
                    " . ($r['nombre_generico'] ? "<br><small style='color:#666'>{$r['nombre_generico']}</small>" : '') . "
                </td>
                <td style='text-align:center;'><code>{$r['lote']}</code></td>
                <td style='text-align:center;'>{$fmtFecha($r['fecha_vencimiento'])}</td>
                <td style='text-align:right;'>" . number_format((float)$r['cantidad'], 2) . "</td>
                <td>{$r['unidad']}</td>
                <td style='text-align:right;'>{$fmt($r['precio_unitario'])}</td>
                <td style='text-align:right;'>{$fmt($r['monto'])}</td>
                <td>{$r['proveedor_donante']}</td>
                <td>{$r['responsable']}</td>
                <td>" . ($r['beneficiario'] ?? '—') . "</td>
            </tr>";

        // Línea de firma solo para salidas
        if ($conFirma && $r['tipo'] === 'Salida') {
            $html .= "
            <tr style='height:22px; background:#fafafa;'>
                <td colspan='13' style='border-top:none; padding: 2px 8px;'>
                    <span style='font-size:7.5px; color:#888;'>
                        Firma beneficiaria: _____________________________________ &nbsp;&nbsp;
                        DPI: " . ($r['beneficiario'] ?? '_______________') . " &nbsp;&nbsp;
                        Huella: ________
                    </span>
                </td>
            </tr>";
        }

        $n++;
    }
    return $html;
}

$filas = buildFilas($rows, true);

// ── HTML ──────────────────────────────────────────────────────────────────
$html = "
<!DOCTYPE html>
<html>
<head>
<meta charset='UTF-8'>
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: Arial, sans-serif; font-size: 9px; color: #222; }
    h1   { font-size: 13px; text-align:center; margin-bottom:4px; }
    h2   { font-size: 10px; text-align:center; color:#555; margin-bottom:10px; }

    .datos-grid { width:100%; border-collapse:collapse; margin-bottom:8px; }
    .datos-grid td { padding:2px 5px; font-size:9px; }
    .label { font-weight:bold; color:#444; width:110px; }

    .resumen { width:100%; border-collapse:collapse; margin-bottom:12px; }
    .resumen td { padding:4px 8px; border:1px solid #ddd; text-align:center; }
    .resumen .val { font-size:12px; font-weight:bold; }

    table.mov { width:100%; border-collapse:collapse; font-size:7.5px; }
    table.mov th { background:#1a3a5c; color:#fff; padding:4px 3px; border:1px solid #1a3a5c; }
    table.mov td { border:1px solid #ccc; padding:3px; vertical-align:top; }
    table.mov tr:nth-child(even) td { background:#f5f8ff; }

    .separador { border-top:2px dashed #aaa; margin:18px 0; }

    .firmas-pie { width:100%; border-collapse:collapse; margin-top:35px; }
    .firmas-pie td { text-align:center; border:none; padding:0 20px; }
    .linea-firma { border-top:1px solid #333; margin-top:40px; margin-bottom:5px; }
    .cargo { font-size:8px; color:#555; }

    .footer { font-size:7.5px; color:#aaa; text-align:right; margin-top:10px; }
</style>
</head>
<body>

<h1>📋 Reporte de Movimientos de Medicamentos</h1>
<h2>Generado el $fechaHoy</h2>

<!-- Resumen -->
<table class='resumen'>
    <tr>
        <td>
            <div class='val'>" . count($rows) . "</div>
            <div>Registros</div>
        </td>
        <td>
            <div class='val' style='color:#198754;'>{$fmt($total_ing)}</div>
            <div>Total Ingresos</div>
        </td>
        <td>
            <div class='val' style='color:#dc3545;'>{$fmt($total_sal)}</div>
            <div>Total Salidas</div>
        </td>
        <td>
            <div class='val' style='color:#0d6efd;'>{$fmt($total_ing - $total_sal)}</div>
            <div>Balance</div>
        </td>
    </tr>
</table>

<!-- Tabla de movimientos -->
<table class='mov'>
    <thead>
        <tr>
            <th style='width:3%'>#</th>
            <th style='width:5%'>Tipo</th>
            <th style='width:6%'>Fecha</th>
            <th style='width:14%'>Medicamento</th>
            <th style='width:6%'>Lote</th>
            <th style='width:7%'>Vencimiento</th>
            <th style='width:5%'>Cantidad</th>
            <th style='width:5%'>Unidad</th>
            <th style='width:7%'>Precio</th>
            <th style='width:7%'>Monto</th>
            <th style='width:10%'>Proveedor/Componente</th>
            <th style='width:10%'>Responsable</th>
            <th style='width:10%'>Beneficiaria</th>
        </tr>
    </thead>
    <tbody>
        $filas
    </tbody>
</table>

<div class='separador'></div>

<!-- Pie de firmas -->
<table class='firmas-pie'>
    <tr>
        <td>
            <div class='linea-firma'></div>
            <div><strong>Elaborado por</strong></div>
            <div class='cargo'>Nombre y cargo</div>
        </td>
        <td>
            <div class='linea-firma'></div>
            <div><strong>Autorizado por</strong></div>
            <div class='cargo'>Nombre y cargo</div>
        </td>
        <td>
            <div class='linea-firma'></div>
            <div><strong>Visto Bueno</strong></div>
            <div class='cargo'>Nombre y cargo</div>
        </td>
    </tr>
</table>

<div class='footer'>Inventario Natun — $fechaHoy</div>

</body>
</html>
";

// ── Generar PDF ───────────────────────────────────────────────────────────
$options = new Options();
$options->set('defaultFont', 'Arial');
$options->set('isHtml5ParserEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('Letter', 'landscape'); // horizontal para tabla ancha
$dompdf->render();
$dompdf->stream('movimientos_' . date('Y-m-d') . '.pdf', ['Attachment' => false]);
