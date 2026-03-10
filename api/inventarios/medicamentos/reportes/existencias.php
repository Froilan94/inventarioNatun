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
                       dim.precio_unitario
                FROM   detalles_ingreso_med dim
                INNER JOIN (
                    SELECT   lote_id,
                             MAX(id_detalle_ingreso_med) AS max_id
                    FROM     detalles_ingreso_med
                    WHERE    lote_id IS NOT NULL
                    GROUP BY lote_id
                ) ult ON ult.lote_id = dim.lote_id
                      AND ult.max_id = dim.id_detalle_ingreso_med
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
    default:
        error_json('Acción no válida.', 400);
}
