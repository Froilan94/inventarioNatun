<?php
/**
 * movimientos.php
 * Endpoint para Reporte de Movimientos de Medicamentos.
 *
 * GET ?action=get_filtros       → medicamentos, lotes, proveedores
 * GET ?action=get_movimientos   → ingresos y/o salidas con filtros
 */

header('Content-Type: application/json; charset=utf-8');
require_once '../../../../auth/roles.php';
requireRoles(['admin_super', 'operadormed', 'supervisormed']);
include '../../../../config/db.php';

$action = $_GET['action'] ?? '';

function error_json(string $msg, int $code = 500): void {
    http_response_code($code);
    echo json_encode(['ok' => false, 'msg' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

function query_rows(mysqli $db, string $sql): array {
    $res = $db->query($sql);
    if (!$res) error_json('Error DB: ' . $db->error);
    $rows = [];
    while ($r = $res->fetch_assoc()) $rows[] = $r;
    $res->free();
    return $rows;
}

switch ($action) {

    // ══════════════════════════════════════════════════════════════════════
    // GET_FILTROS
    // ══════════════════════════════════════════════════════════════════════
    case 'get_filtros':

        $medicamentos = query_rows($mysqli, "
            SELECT id_medicamento   AS id,
                   nombre_comercial AS nombre
            FROM   medicamentos
            WHERE  activo = 1
            ORDER  BY nombre_comercial
        ");

        $lotes = query_rows($mysqli, "
            SELECT DISTINCT numero_lote AS lote
            FROM   lotes_med
            WHERE  numero_lote IS NOT NULL
            ORDER  BY numero_lote
        ");

        $proveedores = query_rows($mysqli, "
            SELECT id_proveedor_med AS id,
                   nombre_proveedor AS nombre
            FROM   proveedores_med
            WHERE  activo = 1
            ORDER  BY nombre_proveedor
        ");

        // Responsables: usuarios que han hecho salidas
        $responsables = query_rows($mysqli, "
            SELECT DISTINCT u.id_usuario      AS id,
                            u.nombre_completo AS nombre
            FROM   usuarios u
            JOIN   roles r ON r.id_rol = u.rol_id
            WHERE  u.activo = 1
              AND  r.nombre_rol IN ('admin_super','operadormed','supervisormed')
            ORDER  BY u.nombre_completo
        ");

        // Componentes/Comunidades
        $componentes = query_rows($mysqli, "
            SELECT id_programa     AS id,
                   nombre_programa AS nombre
            FROM   programas
            ORDER  BY nombre_programa
        ");

        echo json_encode([
            'ok'           => true,
            'medicamentos' => $medicamentos,
            'lotes'        => $lotes,
            'proveedores'  => $proveedores,
            'responsables' => $responsables,
            'componentes'  => $componentes,
        ], JSON_UNESCAPED_UNICODE);
        break;

    // ══════════════════════════════════════════════════════════════════════
    // GET_MOVIMIENTOS
    // Une ingresos y salidas con UNION ALL según filtros
    // ══════════════════════════════════════════════════════════════════════
    case 'get_movimientos':

        $tipo           = trim($_GET['tipo']            ?? '');
        $fecha_ini      = trim($_GET['fecha_inicio']    ?? '');
        $fecha_fin      = trim($_GET['fecha_fin']        ?? '');
        $med_id         = isset($_GET['medicamento_id'])  && $_GET['medicamento_id']  !== '' ? (int)$_GET['medicamento_id']  : 0;
        $lote_txt       = trim($_GET['numero_lote']     ?? '');
        $prov_id        = isset($_GET['proveedor_id'])    && $_GET['proveedor_id']    !== '' ? (int)$_GET['proveedor_id']    : 0;
        $responsable_id = isset($_GET['responsable_id'])  && $_GET['responsable_id']  !== '' ? (int)$_GET['responsable_id']  : 0;
        $componente_id  = isset($_GET['programa_id'])   && $_GET['programa_id']   !== '' ? (int)$_GET['programa_id']   : 0;

        // ── Filtros comunes ───────────────────────────────────────────
        $w_med  = $med_id  ? "AND d.medicamento_id = $med_id" : '';
        $w_lote = $lote_txt ? "AND l.numero_lote = '" . $mysqli->real_escape_string($lote_txt) . "'" : '';

        // ── INGRESOS ──────────────────────────────────────────────────
        $w_ing_fecha = '';
        if ($fecha_ini) $w_ing_fecha .= "AND i.fecha_ingreso >= '$fecha_ini'";
        if ($fecha_fin) $w_ing_fecha .= " AND i.fecha_ingreso <= '$fecha_fin'";
        $w_prov = $prov_id ? "AND i.proveedor_id = $prov_id" : '';

        $sql_ing = "
            SELECT
                'Ingreso'                               AS tipo,
                i.id_ingreso_med                        AS id_movimiento,
                i.fecha_ingreso                         AS fecha,
                m.nombre_comercial                      AS medicamento,
                m.nombre_generico,
                COALESCE(l.numero_lote,  '—')           AS lote,
                l.fecha_vencimiento,
                d.cantidad,
                COALESCE(um.nombre_unidad,  '—')        AS unidad,
                COALESCE(pm.nombre_presentacion, '—')   AS presentacion,
                d.precio_unitario,
                d.subtotal                              AS monto,
                COALESCE(p.nombre_proveedor, '—')       AS proveedor_donante,
                COALESCE(u.nombre_completo,  '—')       AS responsable,
                NULL                                    AS beneficiario,
                COALESCE(doc.tipo_documento, '—')       AS tipo_documento,
                COALESCE(doc.numero_documento,'—')      AS numero_documento
            FROM   detalles_ingreso_med d
            JOIN   ingresos_med         i   ON i.id_ingreso_med  = d.ingreso_id
            JOIN   medicamentos         m   ON m.id_medicamento  = d.medicamento_id
            LEFT JOIN lotes_med         l   ON l.id_lote_med     = d.lote_id
            LEFT JOIN unidades_medida_med um ON um.id_unidad_med = d.unidad_id
            LEFT JOIN presentaciones_med  pm ON pm.id_presentacion_med = d.presentacion_id
            LEFT JOIN proveedores_med    p   ON p.id_proveedor_med = i.proveedor_id
            LEFT JOIN usuarios           u   ON u.id_usuario     = i.recibido_por
            LEFT JOIN documentos_med     doc ON doc.id_documento_med = i.documento_id
            WHERE  1=1
              $w_ing_fecha $w_med $w_lote $w_prov
        ";

        // ── SALIDAS ───────────────────────────────────────────────────
        $w_sal_fecha = '';
        if ($fecha_ini) $w_sal_fecha .= "AND s.fecha_salida >= '$fecha_ini'";
        if ($fecha_fin) $w_sal_fecha .= " AND s.fecha_salida <= '$fecha_fin'";
        $w_resp = $responsable_id ? "AND s.entregado_por = $responsable_id" : '';
        $w_comp = $componente_id  ? "AND s.programa_id  = $componente_id"  : '';

        $sql_sal = "
            SELECT
                'Salida'                                AS tipo,
                s.id_salida_med                         AS id_movimiento,
                s.fecha_salida                          AS fecha,
                m.nombre_comercial                      AS medicamento,
                m.nombre_generico,
                COALESCE(l.numero_lote,  '—')           AS lote,
                l.fecha_vencimiento,
                d.cantidad,
                COALESCE(um.nombre_unidad,  '—')        AS unidad,
                COALESCE(pm.nombre_presentacion, '—')   AS presentacion,
                d.precio_unitario,
                d.subtotal                              AS monto,
                COALESCE(c.nombre_programa, '—')       AS proveedor_donante,
                COALESCE(u.nombre_completo,  '—')       AS responsable,
                COALESCE(b.nombre_beneficiario, '—')    AS beneficiario,
                COALESCE(doc.tipo_documento, '—')       AS tipo_documento,
                COALESCE(doc.numero_documento,'—')      AS numero_documento
            FROM   detalles_salida_med d
            JOIN   salidas_med          s   ON s.id_salida_med   = d.salida_id
            JOIN   medicamentos         m   ON m.id_medicamento  = d.medicamento_id
            LEFT JOIN lotes_med         l   ON l.id_lote_med     = d.lote_id
            LEFT JOIN unidades_medida_med um ON um.id_unidad_med = d.unidad_id
            LEFT JOIN presentaciones_med  pm ON pm.id_presentacion_med = d.presentacion_id
            LEFT JOIN programas        c   ON c.id_programa   = s.programa_id
            LEFT JOIN usuarios           u   ON u.id_usuario     = s.entregado_por
            LEFT JOIN beneficiarios_med  b   ON b.id_beneficiario_med = d.beneficiario_id
            LEFT JOIN documentos_med     doc ON doc.id_documento_med  = s.documento_id
            WHERE  1=1
              $w_sal_fecha $w_med $w_lote $w_resp $w_comp
        ";

        // ── Ejecutar según tipo seleccionado ──────────────────────────
        if ($tipo === 'Ingresos') {
            $sql_final = "$sql_ing ORDER BY fecha DESC, id_movimiento DESC";
        } elseif ($tipo === 'Salidas') {
            $sql_final = "$sql_sal ORDER BY fecha DESC, id_movimiento DESC";
        } else {
            // Todos
            $sql_final = "($sql_ing) UNION ALL ($sql_sal) ORDER BY fecha DESC, id_movimiento DESC";
        }

        $rows = query_rows($mysqli, $sql_final);

        // Totales resumen
        $total_ingresos = 0; $total_salidas = 0;
        foreach ($rows as $r) {
            if ($r['tipo'] === 'Ingreso') $total_ingresos += (float)$r['monto'];
            else                          $total_salidas  += (float)$r['monto'];
        }

        echo json_encode([
            'ok'             => true,
            'data'           => $rows,
            'total_ingresos' => $total_ingresos,
            'total_salidas'  => $total_salidas,
            'total_registros'=> count($rows),
        ], JSON_UNESCAPED_UNICODE);
        break;

    default:
        error_json('Acción no válida.', 400);
}
