<?php
/**
 * planilla_salida.php
 * Genera planilla de entrega de insumos formato Natún.
 *
 * Uso: planilla_salida.php?salida_id=123
 */

require_once '../../../../auth/roles.php';
requireRoles(['admin_super', 'operadormed', 'supervisormed']);
include '../../../../config/db.php';
require_once '../../../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$salida_id = isset($_GET['salida_id']) ? (int)$_GET['salida_id'] : 0;
if (!$salida_id) die('ID de salida requerido.');

// ── Datos de la salida ────────────────────────────────────────────────────
$res = $mysqli->query("
    SELECT
        s.id_salida_med,
        s.fecha_salida,
        COALESCE(c.nombre_programa, '')  AS componente,
        COALESCE(u.nombre_completo,  '')  AS responsable,
        COALESCE(u.cargo,       '')  AS cargo_responsable,
        COALESCE(doc.numero_documento,'') AS numero_factura
    FROM  salidas_med s
    LEFT JOIN programas    c   ON c.id_programa     = s.programa_id
    LEFT JOIN usuarios       u   ON u.id_usuario        = s.entregado_por
    LEFT JOIN roles          r   ON r.id_rol            = u.rol_id
    LEFT JOIN documentos_med doc ON doc.id_documento_med = s.documento_id
    WHERE s.id_salida_med = $salida_id
    LIMIT 1
");
$salida = $res->fetch_assoc();
$res->free();
if (!$salida) die('Salida no encontrada.');

// ── Detalles (una fila por beneficiaria) ──────────────────────────────────
$res = $mysqli->query("
    SELECT
        b.nombre_beneficiario                           AS nombre_beneficiario,
        COALESCE(b.dpi_beneficiario,  '')               AS dpi,
        m.nombre_comercial                              AS medicamento,
        COALESCE(um.nombre_unidad, '')                  AS unidad,
        ds.cantidad
    FROM  detalles_salida_med ds
    JOIN  medicamentos          m  ON m.id_medicamento       = ds.medicamento_id
    LEFT JOIN beneficiarios_med b  ON b.id_beneficiario_med  = ds.beneficiario_id
    LEFT JOIN unidades_medida_med um ON um.id_unidad_med     = ds.unidad_id
    WHERE ds.salida_id = $salida_id
    ORDER BY ds.id_detalle_salida_med
");
$detalles = [];
while ($row = $res->fetch_assoc()) $detalles[] = $row;
$res->free();

// ── Firmas fijas (firmas 2 y 3) ───────────────────────────────────────────
$res_firmas = $mysqli->query("
    SELECT cargo, nombre
    FROM   firmas_planilla_med
    WHERE  activo = 1
    ORDER  BY orden
    LIMIT  2
");
$firmas = [];
while ($f = $res_firmas->fetch_assoc()) $firmas[] = $f;
$res_firmas->free();

// ── Helpers ───────────────────────────────────────────────────────────────
$fmtFecha  = fn($f) => $f ? date('d/m/Y', strtotime($f)) : '';
$numBenef  = count($detalles);

// ── Filas de la tabla ─────────────────────────────────────────────────────
$filas = '';
foreach ($detalles as $i => $d) {
    $n        = $i + 1;
    $cantidad = number_format((float)$d['cantidad'], 0);
    $insumo   = $d['medicamento'] . ($d['unidad'] ? ' ' . $d['unidad'] : '');

    $filas .= "
        <tr>
            <td class='tc'>{$n}</td>
            <td></td>
            <td class='tc'>{$d['nombre_beneficiario']}</td>
            <td class='tc'>{$d['dpi']}</td>
            <td></td>
            <td></td>
            <td></td>
            <td class='tc'>{$cantidad}</td>
            <td class='tc'>{$insumo}</td>
            <td></td>
        </tr>
    ";
}

/* Rellenar filas vacías hasta mínimo 5
$total_filas = count($detalles);
for ($i = $total_filas; $i < 5; $i++) {
    $n = $i + 1;
    $filas .= "
        <tr>
            <td class='tc'>{$n}</td>
            <td></td><td></td><td></td><td></td>
            <td></td><td></td><td></td><td></td><td></td>
        </tr>
    ";
}*/

// ── Firmas del pie ────────────────────────────────────────────────────────
$firma1_nombre = $salida['responsable'];
$firma1_cargo  = $salida['cargo_responsable'];
$firma2_nombre = $firmas[0]['nombre'] ?? '';
$firma2_cargo  = $firmas[0]['cargo']  ?? '';
$firma3_nombre = $firmas[1]['nombre'] ?? '';
$firma3_cargo  = $firmas[1]['cargo']  ?? '';

// ── HTML ──────────────────────────────────────────────────────────────────
$html = "
<!DOCTYPE html>
<html>
<head>
<meta charset='UTF-8'>
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body {
        font-family: Arial, sans-serif;
        font-size: 9px;
        color: #000;
        padding: 15px 20px;
    }

    /* ── Encabezado ── */
    .header-wrap {
        display: table;
        width: 100%;
        margin-bottom: 8px;
    }
    .header-logo {
        display: table-cell;
        width: 120px;
        vertical-align: middle;
        padding-right: 10px;
    }
    .logo-box {
        width: 110px;
        height: 55px;
        border: 1px solid #ccc;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #888;
        font-size: 8px;
        text-align: center;
        padding: 4px;
    }
    .header-datos {
        display: table-cell;
        vertical-align: top;
        font-size: 8px;
        line-height: 1.5;
        color: #444;
    }
    .header-titulo {
        display: table-cell;
        width: 200px;
        vertical-align: middle;
        text-align: center;
    }
    .header-titulo h1 {
        font-size: 11px;
        font-weight: bold;
        text-transform: uppercase;
        border: 1px solid #000;
        padding: 6px 8px;
    }

    /* ── Info salida ── */
    .info-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 6px;
        font-size: 9px;
    }
    .info-table td {
        padding: 3px 4px;
        border: none;
    }
    .info-label { font-weight: bold; width: 120px; }
    .info-line  {
        border-bottom: 1px solid #000;
        min-width: 150px;
        display: inline-block;
        width: 100%;
    }

    /* ── Tabla principal ── */
    table.planilla {
        width: 100%;
        border-collapse: collapse;
        font-size: 8px;
        margin-top: 6px;
    }
    table.planilla th {
        background: #d9d9d9;
        border: 1px solid #000;
        padding: 4px 2px;
        text-align: center;
        font-size: 7.5px;
        line-height: 1.3;
    }
    table.planilla td {
        border: 1px solid #000;
        padding: 0;
        height: 28px;
        vertical-align: middle;
    }
    .tc { text-align: center; padding: 2px; }

    /* ── Pie de firmas ── */
    .firmas-wrap {
        width: 100%;
        margin-top: 30px;
        display: table;
    }
    .firma-col {
        display: table-cell;
        text-align: center;
        padding: 0 15px;
        vertical-align: bottom;
    }
    .firma-linea {
        border-top: 1px solid #000;
        margin-bottom: 3px;
        margin-top: 35px;
    }
    .firma-f {
        font-size: 9px;
        text-align: left;
        margin-bottom: 2px;
    }
    .firma-nombre { font-weight: bold; font-size: 9px; }
    .firma-cargo  { font-size: 8px; color: #333; }

    .footer {
        font-size: 7.5px;
        color: #666;
        text-align: center;
        margin-top: 20px;
        border-top: 1px solid #ccc;
        padding-top: 4px;
    }
</style>
</head>
<body>

<!-- ══ ENCABEZADO ══ -->
<table style='width:100%; border-collapse:collapse; margin-bottom:10px;'>
    <tr>
        <td style='width:120px; vertical-align:middle; padding-right:10px;'>
            <div class='logo-box'>
                Asociación<br><strong>natün</strong>
            </div>
            <div style='font-size:7px; color:#555; margin-top:3px; line-height:1.4;'>
                (+502) 7762-0754<br>
                Calle del Rastro 2-27, Zona 1,<br>
                Panajachel, Guatemala<br>
                info@natunguatemala.org
            </div>
        </td>
        <td style='vertical-align:middle; text-align:center;'>
            <h1 style='font-size:12px; font-weight:bold; text-transform:uppercase;
                       border:1px solid #000; padding:8px 12px; display:inline-block;'>
                PLANILLA DE ENTREGA DE INSUMOS
            </h1>
        </td>
        <td style='width:120px;'></td>
    </tr>
</table>

<!-- ══ DATOS GENERALES ══ -->
<table class='info-table'>
    <tr>
        <td class='info-label'>Programa:</td>
        <td><span class='info-line'>&nbsp;</span></td>
        <td width='20'></td>
        <td class='info-label'>Responsable del Llenado:</td>
        <td><span class='info-line'>&nbsp;{$salida['responsable']}</span></td>
    </tr>
    <tr>
        <td class='info-label'>Componente:</td>
        <td><span class='info-line'>&nbsp;{$salida['componente']}</span></td>
        <td></td>
        <td class='info-label'>No. de Beneficiarios:</td>
        <td><span class='info-line'>&nbsp;{$numBenef}</span></td>
    </tr>
    <tr>
        <td class='info-label'>Nombre de la Actividad:</td>
        <td colspan='4'><span class='info-line'>&nbsp;Entrega de Medicamentos</span></td>
    </tr>
    <tr>
        <td class='info-label'>No. Factura:</td>
        <td><span class='info-line'>&nbsp;{$salida['numero_factura']}</span></td>
        <td></td>
        <td class='info-label'>Fecha de Entrega:</td>
        <td><span class='info-line'>&nbsp;{$fmtFecha($salida['fecha_salida'])}</span></td>
    </tr>
</table>

<!-- ══ TABLA PRINCIPAL ══ -->
<table class='planilla'>
    <thead>
        <tr>
            <th style='width:4%'>No.</th>
            <th style='width:14%'>Nombre del<br>Beneficiario (a)</th>
            <th style='width:16%'>Nombre de la Madre<br>o Encargado</th>
            <th style='width:10%'>DPI</th>
            <th style='width:6%'>Edad</th>
            <th style='width:10%'>Grupo<br>Lingüístico</th>
            <th style='width:7%'>Género</th>
            <th style='width:5%'>Cantidad</th>
            <th style='width:14%'>Insumo para<br>Entregar</th>
            <th style='width:14%'>Firma del Beneficiario<br>o Encargado</th>
        </tr>
    </thead>
    <tbody>
        {$filas}
    </tbody>
</table>

<!-- ══ PIE DE FIRMAS ══ -->
<table style='width:100%; border-collapse:collapse; margin-top:35px;'>
    <tr>
        <td style='text-align:center; padding:0 10px; width:33%;'>
            <div style='border-top:1px solid #000; margin-top:40px; margin-bottom:3px;'></div>
            <div style='font-size:8.5px; text-align:left;'>F.</div>
            <div style='font-weight:bold; font-size:9px;'>{$firma1_nombre}</div>
            <div style='font-size:8px; color:#333;'>{$firma1_cargo}</div>
        </td>
        <td style='text-align:center; padding:0 10px; width:33%;'>
            <div style='border-top:1px solid #000; margin-top:40px; margin-bottom:3px;'></div>
            <div style='font-size:8.5px; text-align:left;'>F.</div>
            <div style='font-weight:bold; font-size:9px;'>{$firma2_nombre}</div>
            <div style='font-size:8px; color:#333;'>{$firma2_cargo}</div>
        </td>
        <td style='text-align:center; padding:0 10px; width:33%;'>
            <div style='border-top:1px solid #000; margin-top:40px; margin-bottom:3px;'></div>
            <div style='font-size:8.5px; text-align:left;'>F.</div>
            <div style='font-weight:bold; font-size:9px;'>{$firma3_nombre}</div>
            <div style='font-size:8px; color:#333;'>{$firma3_cargo}</div>
        </td>
    </tr>
</table>

<div class='footer'>www.natunguatemala.org</div>

</body>
</html>
";

// ── Generar PDF ───────────────────────────────────────────────────────────
$options = new Options();
$options->set('defaultFont', 'Arial');
$options->set('isHtml5ParserEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('Letter', 'landscape');
$dompdf->render();
$dompdf->stream("planilla_salida_{$salida_id}.pdf", ['Attachment' => false]);
