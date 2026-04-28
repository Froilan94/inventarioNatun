<?php
/**
 * existencias.php
 * Endpoint para el Reporte de Existencias de Medicamentos.
 *
 * Acciones (GET ?action=...):
 *   - get_filtros     → Opciones para los <select>
 *   - get_existencias → Inventario con filtros opcionales
 */

header('Content-Type: application/json; charset=utf-8');
require_once '../../../../auth/roles.php';

requireRoles([
    'admin_super',
    'operadormed',
    'supervisormed'
]);

include "../../../../config/db.php";

$action = $_GET['action'] ?? '';

// Umbral "stock bajo" y días "próximo a vencer" (ajusta según necesites)
define('UMBRAL_BAJO',  10);
define('DIAS_PROXIMO', 90);

// ── Helper: error JSON ─────────────────────────────────────────────────────
function error_json(string $msg, int $code = 500): void {
    http_response_code($code);
    echo json_encode(['ok' => false, 'msg' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

// ── Helper: ejecutar query y devolver array asociativo ─────────────────────
function query_rows(mysqli $db, string $sql): array {
    $res = $db->query($sql);
    if (!$res) error_json('Error DB: ' . $db->error);
    $rows = [];
    while ($row = $res->fetch_assoc()) $rows[] = $row;
    $res->free();
    return $rows;
}

// ───────────────────────────────────────────────────────────────────────────

switch ($action) {

    // ══════════════════════════════════════════════════════════════════════
    // GET_FILTROS — puebla los <select> del HTML
    // ══════════════════════════════════════════════════════════════════════
    case 'get_filtros':

        $medicamentos = query_rows($mysqli, "
            SELECT id_medicamento  AS id,
                   nombre_comercial AS nombre
            FROM   medicamentos
            WHERE  activo = 1
            ORDER  BY nombre_comercial
        ");

        $presentaciones = query_rows($mysqli, "
            SELECT id_presentacion_med AS id,
                   nombre_presentacion  AS nombre
            FROM   presentaciones_med
            ORDER  BY nombre_presentacion
        ");

        // Solo lotes con stock disponible para no saturar el select
        $lotes = query_rows($mysqli, "
            SELECT lm.id_lote_med     AS id,
                   lm.numero_lote     AS nombre,
                   m.nombre_comercial AS medicamento
            FROM   lotes_med lm
            JOIN   medicamentos m ON m.id_medicamento = lm.medicamento_id
            WHERE  lm.cantidad_actual > 0
            ORDER  BY lm.numero_lote
        ");

        echo json_encode([
            'ok'   => true,
            'data' => compact('medicamentos', 'presentaciones', 'lotes'),
        ], JSON_UNESCAPED_UNICODE);
        break;

    // ══════════════════════════════════════════════════════════════════════
    // GET_EXISTENCIAS — inventario con filtros
    // ══════════════════════════════════════════════════════════════════════
    case 'get_existencias':

        $medicamento_id  = trim($_GET['medicamento_id']  ?? '');
        $presentacion_id = trim($_GET['presentacion_id'] ?? '');
        $lote_id         = trim($_GET['lote_id']         ?? '');
        $filtro_stock    = trim($_GET['filtro_stock']    ?? ''); // agotado|bajo|normal
        $filtro_venc     = trim($_GET['filtro_venc']     ?? ''); // vencido|proximo

        // Castear a int para evitar inyección SQL (solo IDs numéricos)
        $med_id_safe  = $medicamento_id  !== '' ? (int)$medicamento_id  : null;
        $pres_id_safe = $presentacion_id !== '' ? (int)$presentacion_id : null;
        $lote_id_safe = $lote_id         !== '' ? (int)$lote_id         : null;
        $umbral       = UMBRAL_BAJO;
        $dias         = DIAS_PROXIMO;

        // ── Construir cláusulas WHERE ──────────────────────────────────
        $where = ['1=1'];

        if ($med_id_safe !== null)  $where[] = "lm.medicamento_id = $med_id_safe";
        if ($lote_id_safe !== null) $where[] = "lm.id_lote_med = $lote_id_safe";
        if ($pres_id_safe !== null) $where[] = "dim_last.presentacion_id = $pres_id_safe";

        switch ($filtro_stock) {
            case 'agotado': $where[] = 'lm.cantidad_actual = 0';                                       break;
            case 'bajo':    $where[] = "lm.cantidad_actual > 0 AND lm.cantidad_actual <= $umbral";     break;
            case 'normal':  $where[] = "lm.cantidad_actual > $umbral";                                 break;
        }

        switch ($filtro_venc) {
            case 'vencido': $where[] = 'lm.fecha_vencimiento < CURDATE()';                             break;
            case 'proximo': $where[] = "lm.fecha_vencimiento >= CURDATE()
                                        AND lm.fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL $dias DAY)"; break;
        }

        $whereSQL = implode(' AND ', $where);

        // ── Consulta principal ─────────────────────────────────────────
        /*
         * dim_last: subconsulta que toma el registro más reciente de
         * detalles_ingreso_med por lote → precio, presentación y unidad
         * del último ingreso de ese lote.
         */
        $sql = "
            SELECT
                m.id_medicamento,
                m.nombre_comercial                               AS medicamento,
                m.nombre_generico,
                COALESCE(pm.nombre_presentacion, '—')            AS presentacion,
                COALESCE(um.nombre_unidad,       '—')            AS unidad,
                lm.id_lote_med,
                COALESCE(lm.numero_lote,         'Sin lote')     AS lote,
                lm.fecha_vencimiento,
                lm.cantidad_actual                               AS stock,
                COALESCE(dim_last.precio_unitario, 0)            AS precio_unitario,
                COALESCE(dim_last.numero_factura, '—')           AS no_factura,
                ROUND(lm.cantidad_actual *
                      COALESCE(dim_last.precio_unitario, 0), 4)  AS valor_total
            FROM lotes_med lm
            JOIN medicamentos m
                 ON m.id_medicamento = lm.medicamento_id
            /* Último detalle de ingreso por lote */
            LEFT JOIN (
                SELECT dim.lote_id,
                       dim.presentacion_id,
                       dim.unidad_id,
                       dim.precio_unitario,
                       d.numero_documento AS numero_factura 
                FROM   detalles_ingreso_med dim
                INNER JOIN (
                    SELECT   lote_id,
                             MAX(id_detalle_ingreso_med) AS max_id
                    FROM     detalles_ingreso_med
                    WHERE    lote_id IS NOT NULL
                    GROUP BY lote_id
                ) ult ON ult.lote_id = dim.lote_id
                      AND ult.max_id = dim.id_detalle_ingreso_med
                LEFT JOIN ingresos_med im 
                   ON im.id_ingreso_med = dim.ingreso_id
                LEFT JOIN documentos_med d 
                   ON d.id_documento_med = im.documento_id          
            ) dim_last ON dim_last.lote_id = lm.id_lote_med
            LEFT JOIN presentaciones_med pm
                 ON pm.id_presentacion_med = dim_last.presentacion_id
            LEFT JOIN unidades_medida_med um
                 ON um.id_unidad_med = dim_last.unidad_id
            WHERE $whereSQL
            ORDER BY m.nombre_comercial, lm.fecha_vencimiento
        ";

        $result = $mysqli->query($sql);
        if (!$result) error_json('Error DB: ' . $mysqli->error);

        $rows = [];
        while ($row = $result->fetch_assoc()) $rows[] = $row;
        $result->free();

        // ── Calcular estados en PHP ────────────────────────────────────
        $hoy = new DateTime();
        foreach ($rows as &$row) {
            $stock = (float)$row['stock'];

            if ($stock <= 0)            $row['estado'] = 'agotado';
            elseif ($stock <= $umbral)  $row['estado'] = 'bajo';
            else                        $row['estado'] = 'normal';

            if (!empty($row['fecha_vencimiento'])) {
                $fv   = new DateTime($row['fecha_vencimiento']);
                $diff = (int)$hoy->diff($fv)->format('%r%a'); // negativo = ya venció
                if ($diff < 0)           $row['estado_venc'] = 'vencido';
                elseif ($diff <= $dias)  $row['estado_venc'] = 'proximo';
                else                     $row['estado_venc'] = 'vigente';
            } else {
                $row['estado_venc'] = 'sin_fecha';
            }
        }
        unset($row);

        // ── Resumen de tarjetas ────────────────────────────────────────
        $resumen = [
            'total_filas'   => count($rows),
            'total_valor'   => array_sum(array_column($rows, 'valor_total')),
            'agotados'      => count(array_filter($rows, fn($r) => $r['estado']      === 'agotado')),
            'stock_bajo'    => count(array_filter($rows, fn($r) => $r['estado']      === 'bajo')),
            'vencidos'      => count(array_filter($rows, fn($r) => $r['estado_venc'] === 'vencido')),
            'proximos_venc' => count(array_filter($rows, fn($r) => $r['estado_venc'] === 'proximo')),
        ];

        echo json_encode([
            'ok'      => true,
            'resumen' => $resumen,
            'data'    => $rows,
        ], JSON_UNESCAPED_UNICODE);
        break;
    // ══════════════════════════════════════════════════════════════════════
// ══════════════════════════════════════════════════════════════════════
// EXPORTAR — Excel, PDF, CSV
// ══════════════════════════════════════════════════════════════════════
case 'exportar':

    $formato         = trim($_GET['formato']         ?? '');
    $medicamento_id  = trim($_GET['medicamento_id']  ?? '');
    $presentacion_id = trim($_GET['presentacion_id'] ?? '');
    $lote_id         = trim($_GET['lote_id']         ?? '');
    $filtro_stock    = trim($_GET['filtro_stock']    ?? '');
    $filtro_venc     = trim($_GET['filtro_venc']     ?? '');

    $med_id_safe  = $medicamento_id  !== '' ? (int)$medicamento_id  : null;
    $pres_id_safe = $presentacion_id !== '' ? (int)$presentacion_id : null;
    $lote_id_safe = $lote_id         !== '' ? (int)$lote_id         : null;
    $umbral       = UMBRAL_BAJO;
    $dias         = DIAS_PROXIMO;

    $where = ['1=1'];
    if ($med_id_safe  !== null) $where[] = "lm.medicamento_id = $med_id_safe";
    if ($lote_id_safe !== null) $where[] = "lm.id_lote_med = $lote_id_safe";
    if ($pres_id_safe !== null) $where[] = "dim_last.presentacion_id = $pres_id_safe";

    switch ($filtro_stock) {
        case 'agotado': $where[] = 'lm.cantidad_actual = 0';                                   break;
        case 'bajo':    $where[] = "lm.cantidad_actual > 0 AND lm.cantidad_actual <= $umbral"; break;
        case 'normal':  $where[] = "lm.cantidad_actual > $umbral";                             break;
    }
    switch ($filtro_venc) {
        case 'vencido': $where[] = 'lm.fecha_vencimiento < CURDATE()';                        break;
        case 'proximo': $where[] = "lm.fecha_vencimiento >= CURDATE()
                                    AND lm.fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL $dias DAY)"; break;
    }
    $whereSQL = implode(' AND ', $where);

    $sql = "
        SELECT
            m.nombre_comercial                               AS Medicamento,
            m.nombre_generico                                AS Generico,
            COALESCE(pm.nombre_presentacion, '—')            AS Presentacion,
            COALESCE(um.nombre_unidad,       '—')            AS Unidad,
            COALESCE(lm.numero_lote,         'Sin lote')     AS Lote,
            lm.fecha_vencimiento                             AS Vencimiento,
            lm.cantidad_actual                               AS Stock,
            COALESCE(dim_last.precio_unitario, 0)            AS PrecioUnitario,
            ROUND(lm.cantidad_actual *
                  COALESCE(dim_last.precio_unitario, 0), 4)  AS ValorTotal,
            COALESCE(dim_last.numero_factura, '—')           AS NoFactura
        FROM lotes_med lm
        JOIN medicamentos m ON m.id_medicamento = lm.medicamento_id
        LEFT JOIN (
            SELECT dim.lote_id,
                   dim.presentacion_id,
                   dim.unidad_id,
                   dim.precio_unitario,
                   d.numero_documento AS numero_factura
            FROM   detalles_ingreso_med dim
            INNER JOIN (
                SELECT   lote_id, MAX(id_detalle_ingreso_med) AS max_id
                FROM     detalles_ingreso_med
                WHERE    lote_id IS NOT NULL
                GROUP BY lote_id
            ) ult ON ult.lote_id = dim.lote_id
                  AND ult.max_id = dim.id_detalle_ingreso_med
            LEFT JOIN ingresos_med im   ON im.id_ingreso_med  = dim.ingreso_id
            LEFT JOIN documentos_med d  ON d.id_documento_med = im.documento_id
        ) dim_last ON dim_last.lote_id = lm.id_lote_med
        LEFT JOIN presentaciones_med pm ON pm.id_presentacion_med = dim_last.presentacion_id
        LEFT JOIN unidades_medida_med um ON um.id_unidad_med       = dim_last.unidad_id
        WHERE $whereSQL
        ORDER BY m.nombre_comercial, lm.fecha_vencimiento
    ";

    $result = $mysqli->query($sql);
    if (!$result) error_json('Error DB: ' . $mysqli->error);

    $rows = [];
    while ($row = $result->fetch_assoc()) $rows[] = $row;
    $result->free();

    // Calcular estado stock y vencimiento (para el PDF)
    $hoy = new DateTime();
    foreach ($rows as &$row) {
        $stock = (float)$row['Stock'];
        $row['EstadoStock'] = $stock <= 0 ? 'Agotado' : ($stock <= $umbral ? 'Stock bajo' : 'Normal');

        if (!empty($row['Vencimiento'])) {
            $diff = (int)$hoy->diff(new DateTime($row['Vencimiento']))->format('%r%a');
            $row['EstadoVenc'] = $diff < 0 ? 'Vencido' : ($diff <= $dias ? 'Próx. vencer' : '');
        } else {
            $row['EstadoVenc'] = '';
        }
    }
    unset($row);

    $fecha_reporte = date('d/m/Y H:i');
    $nombre_archivo = 'existencias_' . date('Ymd_His');

    // ── CSV ────────────────────────────────────────────────────────────
    if ($formato === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"{$nombre_archivo}.csv\"");
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8 para Excel
        fputcsv($out, ['Medicamento','Genérico','Presentación','Unidad','Lote',
                       'Vencimiento','Stock','Precio Unitario','Valor Total',
                       'No. Factura','Estado Stock','Estado Venc.'], ',');
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['Medicamento'], $r['Generico'],    $r['Presentacion'],
                $r['Unidad'],      $r['Lote'],        $r['Vencimiento'] ?? '',
                $r['Stock'],       $r['PrecioUnitario'], $r['ValorTotal'],
                $r['NoFactura'],   $r['EstadoStock'], $r['EstadoVenc'],
            ], ',');
        }
        fclose($out);
        exit;
    }

    // ── EXCEL ──────────────────────────────────────────────────────────
    if ($formato === 'excel') {
        require_once '../../../../vendor/autoload.php';
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Existencias');

        // Título
        $sheet->mergeCells('A1:L1');
        $sheet->setCellValue('A1', 'Reporte de Existencias — ' . $fecha_reporte);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A1')->getAlignment()
              ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Encabezados
        $headers = ['Medicamento','Genérico','Presentación','Unidad','Lote',
                    'Vencimiento','Stock','Precio Unitario','Valor Total',
                    'No. Factura','Estado Stock','Estado Venc.'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '2', $h);
            $sheet->getStyle($col . '2')->getFont()->setBold(true);
            $sheet->getStyle($col . '2')->getFill()
                  ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                  ->getStartColor()->setARGB('FF343A40');
            $sheet->getStyle($col . '2')->getFont()->getColor()->setARGB('FFFFFFFF');
            $col++;
        }

        // Datos
        $fila = 3;
        foreach ($rows as $r) {
            $sheet->setCellValue('A' . $fila, $r['Medicamento']);
            $sheet->setCellValue('B' . $fila, $r['Generico']);
            $sheet->setCellValue('C' . $fila, $r['Presentacion']);
            $sheet->setCellValue('D' . $fila, $r['Unidad']);
            $sheet->setCellValue('E' . $fila, $r['Lote']);
            $sheet->setCellValue('F' . $fila, $r['Vencimiento'] ?? '');
            $sheet->setCellValue('G' . $fila, (float)$r['Stock']);
            $sheet->setCellValue('H' . $fila, (float)$r['PrecioUnitario']);
            $sheet->setCellValue('I' . $fila, (float)$r['ValorTotal']);
            $sheet->setCellValue('J' . $fila, $r['NoFactura']);
            $sheet->setCellValue('K' . $fila, $r['EstadoStock']);
            $sheet->setCellValue('L' . $fila, $r['EstadoVenc']);

            // Color por estado
            if ($r['EstadoStock'] === 'Agotado' || $r['EstadoVenc'] === 'Vencido') {
                $sheet->getStyle("A{$fila}:L{$fila}")->getFill()
                      ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                      ->getStartColor()->setARGB('FFFFC7CE');
            } elseif ($r['EstadoStock'] === 'Stock bajo' || $r['EstadoVenc'] === 'Próx. vencer') {
                $sheet->getStyle("A{$fila}:L{$fila}")->getFill()
                      ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                      ->getStartColor()->setARGB('FFFFEB9C');
            }
            $fila++;
        }

        // Autoajuste de columnas
        foreach (range('A', 'L') as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$nombre_archivo}.xlsx\"");
        header('Cache-Control: max-age=0');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // ── PDF (dompdf) ───────────────────────────────────────────────────
    if ($formato === 'pdf') {
        require_once '../../../../vendor/autoload.php';
        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'Helvetica');
        $dompdf = new \Dompdf\Dompdf($options);

        // Construir tabla HTML
        $filas_html = '';
        foreach ($rows as $r) {
            $venc      = $r['Vencimiento'] ? date('d/m/Y', strtotime($r['Vencimiento'])) : '—';
            $stock_fmt = number_format((float)$r['Stock'],         2, '.', ',');
            $precio    = number_format((float)$r['PrecioUnitario'],2, '.', ',');
            $valor     = number_format((float)$r['ValorTotal'],    2, '.', ',');

            $tr_style = '';
            if ($r['EstadoStock'] === 'Agotado' || $r['EstadoVenc'] === 'Vencido')
                $tr_style = 'background:#ffc7ce;';
            elseif ($r['EstadoStock'] === 'Stock bajo' || $r['EstadoVenc'] === 'Próx. vencer')
                $tr_style = 'background:#ffeb9c;';

            $estado_label = trim($r['EstadoStock'] . ' ' . $r['EstadoVenc']);

            $filas_html .= "
            <tr style=\"{$tr_style}\">
                <td>{$r['Medicamento']}</td>
                <td>{$r['Presentacion']}</td>
                <td>{$r['Unidad']}</td>
                <td>{$r['Lote']}</td>
                <td>{$venc}</td>
                <td style='text-align:right'>{$stock_fmt}</td>
                <td style='text-align:right'>Q {$precio}</td>
                <td style='text-align:right'>Q {$valor}</td>
                <td>{$r['NoFactura']}</td>
                <td>{$estado_label}</td>
            </tr>";
        }

        $total_valor = number_format(array_sum(array_column($rows, 'ValorTotal')), 2, '.', ',');

        $html = "
        <!DOCTYPE html><html><head><meta charset='utf-8'>
        <style>
            body  { font-family: Helvetica, Arial, sans-serif; font-size: 8px; }
            h2    { font-size: 13px; margin-bottom: 2px; }
            p.sub { font-size: 8px; color: #555; margin: 0 0 8px; }
            table { width: 100%; border-collapse: collapse; }
            th    { background: #343a40; color: #fff; padding: 4px 3px; font-size: 7.5px; }
            td    { border: 1px solid #ccc; padding: 3px; }
            tr:nth-child(even) { background: #f8f8f8; }
            .total { text-align:right; font-weight:bold; font-size:9px; margin-top:6px; }
        </style></head><body>
        <h2>Reporte de Existencias de Medicamentos</h2>
        <p class='sub'>Generado: {$fecha_reporte} &nbsp;|&nbsp; Total lotes: " . count($rows) . " &nbsp;|&nbsp; Valor total: Q {$total_valor}</p>
        <table>
            <thead>
                <tr>
                    <th>Medicamento</th><th>Presentación</th><th>Unidad</th>
                    <th>Lote</th><th>Vencimiento</th><th>Stock</th>
                    <th>P. Unitario</th><th>Valor Total</th>
                    <th>No. Factura</th><th>Estado</th>
                </tr>
            </thead>
            <tbody>{$filas_html}</tbody>
        </table>
        </body></html>";

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream("{$nombre_archivo}.pdf", ['Attachment' => true]);
        exit;
    }

    error_json('Formato no válido. Use: excel, pdf o csv.', 400);
    break;
    // ══════════════════════════════════════════════════════════════════════
    default:
        error_json('Acción no válida.', 400);
}
