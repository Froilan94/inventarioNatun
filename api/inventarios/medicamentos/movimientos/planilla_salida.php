<?php
/**
 * planilla_salida.php
 * Genera la planilla de firmas en PDF para una salida registrada.
 *
 * Uso: planilla_salida.php?salida_id=123
 *
 * Requiere mPDF: composer require mpdf/mpdf
 * O con CDN manual: https://github.com/mpdf/mpdf/releases
 */

require_once '../../../../auth/roles.php';
requireRoles(['admin_super', 'operadormed', 'supervisormed']);
include '../../../../config/db.php';

$salida_id = isset($_GET['salida_id']) ? (int)$_GET['salida_id'] : 0;
if (!$salida_id) die('ID de salida requerido.');

// ── Cargar datos de la salida ─────────────────────────────────────────────
$res = $mysqli->query("
    SELECT
        s.id_salida_med,
        s.fecha_salida,
        d.tipo_documento,
        d.numero_documento,
        d.serie_documento,
        COALESCE(c.nombre_comunidad, '—') AS componente,
        COALESCE(u.nombre_completo,  '—') AS entregado_por
    FROM  salidas_med s
    LEFT  JOIN documentos_med d ON d.id_documento_med = s.documento_id
    LEFT  JOIN comunidades    c ON c.id_comunidad      = s.comunidad_id
    LEFT  JOIN usuarios       u ON u.id_usuario        = s.entregado_por
    WHERE s.id_salida_med = $salida_id
    LIMIT 1
");
$salida = $res->fetch_assoc();
$res->free();

if (!$salida) die('Salida no encontrada.');

// ── Cargar detalles ───────────────────────────────────────────────────────
$res = $mysqli->query("
    SELECT
        m.nombre_comercial                          AS medicamento,
        COALESCE(l.numero_lote,  '—')               AS lote,
        ds.cantidad,
        COALESCE(um.nombre_unidad, '—')             AS unidad,
        COALESCE(pm.nombre_presentacion, '—')       AS presentacion,
        COALESCE(b.nombre_beneficiario, '—')        AS beneficiario,
        COALESCE(b.dpi_beneficiario, '—')           AS dpi,
        ds.precio_unitario,
        ds.subtotal
    FROM  detalles_salida_med ds
    JOIN  medicamentos         m  ON m.id_medicamento      = ds.medicamento_id
    LEFT  JOIN lotes_med       l  ON l.id_lote_med         = ds.lote_id
    LEFT  JOIN unidades_medida_med um ON um.id_unidad_med  = ds.unidad_id
    LEFT  JOIN presentaciones_med  pm ON pm.id_presentacion_med = ds.presentacion_id
    LEFT  JOIN beneficiarios_med   b  ON b.id_beneficiario_med  = ds.beneficiario_id
    WHERE ds.salida_id = $salida_id
    ORDER BY ds.id_detalle_salida_med
");

$detalles = [];
while ($row = $res->fetch_assoc()) $detalles[] = $row;
$res->free();

// ── Construir HTML para el PDF ────────────────────────────────────────────
$fecha_formateada = date('d/m/Y', strtotime($salida['fecha_salida']));
$fecha_generado   = date('d/m/Y H:i');

// Filas de la tabla de detalles
$filas_detalle = '';
foreach ($detalles as $i => $d) {
    $n         = $i + 1;
    $cantidad  = number_format((float)$d['cantidad'], 2);
    $precio    = 'Q ' . number_format((float)$d['precio_unitario'], 2);
    $subtotal  = 'Q ' . number_format((float)$d['subtotal'], 2);

    $filas_detalle .= "
        <tr>
            <td style='text-align:center;'>$n</td>
            <td>{$d['medicamento']}</td>
            <td style='text-align:center;'>{$d['lote']}</td>
            <td style='text-align:center;'>$cantidad</td>
            <td style='text-align:center;'>{$d['unidad']}</td>
            <td style='text-align:center;'>{$d['presentacion']}</td>
            <td style='text-align:right;'>$precio</td>
            <td style='text-align:right;'>$subtotal</td>
        </tr>
    ";
}

// Filas de la planilla de firmas (una por beneficiaria)
$filas_firmas = '';
foreach ($detalles as $i => $d) {
    $n = $i + 1;
    $filas_firmas .= "
        <tr style='height:35px;'>
            <td style='text-align:center; width:5%;'>$n</td>
            <td style='width:30%;'>{$d['beneficiario']}</td>
            <td style='text-align:center; width:15%;'>{$d['dpi']}</td>
            <td style='width:12%;'></td>
            <td style='width:25%;'></td>
            <td style='text-align:center; width:13%;'>" . number_format((float)$d['cantidad'], 2) . " {$d['unidad']}</td>
        </tr>
    ";
}

$html = "
<!DOCTYPE html>
<html>
<head>
<meta charset='UTF-8'>
<style>
    body        { font-family: Arial, sans-serif; font-size: 10px; color: #222; }
    h1          { font-size: 14px; text-align: center; margin: 0 0 4px; }
    h2          { font-size: 11px; text-align: center; margin: 0 0 10px; color: #555; }
    .seccion    { margin-bottom: 14px; }
    .datos-grid { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    .datos-grid td { padding: 3px 6px; font-size: 9.5px; }
    .label      { font-weight: bold; color: #444; width: 120px; }
    table.detalle, table.firmas {
        width: 100%; border-collapse: collapse; font-size: 9px;
    }
    table.detalle th, table.detalle td,
    table.firmas  th, table.firmas  td {
        border: 1px solid #bbb; padding: 4px 5px;
    }
    table.detalle thead tr { background-color: #2c3e50; color: #fff; }
    table.firmas  thead tr { background-color: #1a5276; color: #fff; }
    .firma-celda { border-bottom: 1px solid #555 !important; border-top: none !important; border-left: none !important; border-right: none !important; }
    .pie { margin-top: 30px; }
    .firma-box  { display:inline-block; width: 200px; text-align: center; margin: 0 30px; }
    .firma-line { border-top: 1px solid #333; margin-bottom: 4px; margin-top: 40px; }
    .separador  { border-top: 2px dashed #aaa; margin: 20px 0; }
</style>
</head>
<body>

<!-- ══ ENCABEZADO ══ -->
<h1>PLANILLA DE ENTREGA DE MEDICAMENTOS</h1>
<h2>Componente / Programa: {$salida['componente']}</h2>

<!-- ══ DATOS GENERALES ══ -->
<table class='datos-grid seccion'>
    <tr>
        <td class='label'>No. de Salida:</td>
        <td><strong>{$salida['id_salida_med']}</strong></td>
        <td class='label'>Fecha de Salida:</td>
        <td>$fecha_formateada</td>
        <td class='label'>Documento:</td>
        <td>{$salida['tipo_documento']} — {$salida['numero_documento']}</td>
    </tr>
    <tr>
        <td class='label'>Serie:</td>
        <td>{$salida['serie_documento']}</td>
        <td class='label'>Entregado por:</td>
        <td>{$salida['entregado_por']}</td>
        <td class='label'>Generado:</td>
        <td>$fecha_generado</td>
    </tr>
</table>

<!-- ══ DETALLE DE MEDICAMENTOS ══ -->
<div class='seccion'>
<strong>Detalle de Medicamentos Entregados</strong>
<table class='detalle' style='margin-top:5px;'>
    <thead>
        <tr>
            <th style='width:4%;'>#</th>
            <th style='width:22%;'>Medicamento</th>
            <th style='width:10%;'>Lote</th>
            <th style='width:8%;'>Cantidad</th>
            <th style='width:8%;'>Unidad</th>
            <th style='width:10%;'>Presentación</th>
            <th style='width:10%;'>Precio Unit.</th>
            <th style='width:10%;'>Subtotal</th>
        </tr>
    </thead>
    <tbody>
        $filas_detalle
    </tbody>
</table>
</div>

<div class='separador'></div>

<!-- ══ PLANILLA DE FIRMAS ══ -->
<strong style='font-size:11px;'>PLANILLA DE RECEPCIÓN — FIRMAS DE BENEFICIARIAS</strong>
<p style='font-size:9px; color:#555; margin:3px 0 6px;'>
    Yo, la persona abajo firmante, confirmo haber recibido los medicamentos indicados en buen estado.
</p>

<table class='firmas' style='margin-top:5px;'>
    <thead>
        <tr>
            <th>#</th>
            <th>Nombre completo</th>
            <th>DPI</th>
            <th>Huella</th>
            <th>Firma</th>
            <th>Cantidad recibida</th>
        </tr>
    </thead>
    <tbody>
        $filas_firmas
    </tbody>
</table>

<!-- ══ PIE DE PÁGINA ══ -->
<div class='pie'>
    <table style='width:100%; border:none;'>
        <tr>
            <td style='text-align:center; border:none;'>
                <div class='firma-box'>
                    <div class='firma-line'></div>
                    <div>Entregado por</div>
                    <div style='color:#555;'>{$salida['entregado_por']}</div>
                </div>
            </td>
            <td style='text-align:center; border:none;'>
                <div class='firma-box'>
                    <div class='firma-line'></div>
                    <div>Revisado por</div>
                </div>
            </td>
            <td style='text-align:center; border:none;'>
                <div class='firma-box'>
                    <div class='firma-line'></div>
                    <div>Autorizado por</div>
                </div>
            </td>
        </tr>
    </table>
</div>

</body>
</html>
";

// ── Generar PDF con mPDF ──────────────────────────────────────────────────
require_once '../../../../vendor/autoload.php';  // mPDF vía Composer

use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('defaultFont', 'Arial');
$options->set('isHtml5ParserEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('Letter', 'portrait');
$dompdf->render();
$dompdf->stream("planilla_salida_{$salida_id}.pdf", [ // 'I' = mostrar en navegador
    'Attachment' => false  // false = mostrar en navegador, true = descargar
]); 
