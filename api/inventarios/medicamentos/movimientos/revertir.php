<?php
ob_start();

header('Content-Type: application/json; charset=utf-8');
require_once '../../../../auth/roles.php';
requireRoles(['admin_super', 'operadormed', 'supervisormed']);
include '../../../../config/db.php';

$basura = ob_get_clean();
if ($basura) {
    echo json_encode(['ok' => false, 'msg' => 'Error interno del servidor.'], JSON_UNESCAPED_UNICODE);
    exit;
}

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

function query_one(mysqli $db, string $sql): ?array {
    $res = $db->query($sql);
    if (!$res) error_json('Error DB: ' . $db->error);
    $row = $res->fetch_assoc();
    $res->free();
    return $row ?: null;
}

switch ($action) {

    // ══════════════════════════════════════════════════════════════════════
    // GET_INGRESOS — lista de ingresos para mostrar en tabla
    // ══════════════════════════════════════════════════════════════════════
    case 'get_ingresos':
        $rows = query_rows($mysqli, "
            SELECT
                i.id_ingreso_med                        AS id,
                i.fecha_ingreso                         AS fecha,
                COALESCE(p.nombre_proveedor, '—')       AS proveedor,
                COALESCE(u.nombre_completo,  '—')       AS recibido_por,
                COALESCE(d.tipo_documento,   '—')       AS tipo_documento,
                COALESCE(d.numero_documento, '—')       AS numero_documento,
                COUNT(di.id_detalle_ingreso_med)        AS total_lineas,
                SUM(di.cantidad)                        AS total_cantidad
            FROM  ingresos_med i
            LEFT JOIN proveedores_med p  ON p.id_proveedor_med  = i.proveedor_id
            LEFT JOIN usuarios        u  ON u.id_usuario        = i.recibido_por
            LEFT JOIN documentos_med  d  ON d.id_documento_med  = i.documento_id
            LEFT JOIN detalles_ingreso_med di ON di.ingreso_id  = i.id_ingreso_med
            GROUP BY i.id_ingreso_med
            ORDER BY i.fecha_ingreso DESC, i.id_ingreso_med DESC
        ");

        echo json_encode(['ok' => true, 'data' => $rows], JSON_UNESCAPED_UNICODE);
        break;

    // ══════════════════════════════════════════════════════════════════════
    // GET_SALIDAS — lista de salidas para mostrar en tabla
    // ══════════════════════════════════════════════════════════════════════
    case 'get_salidas':
        $rows = query_rows($mysqli, "
            SELECT
                s.id_salida_med                         AS id,
                s.fecha_salida                          AS fecha,
                COALESCE(pg.nombre_programa, '—')       AS programa,
                COALESCE(u.nombre_completo,  '—')       AS entregado_por,
                COALESCE(d.tipo_documento,   '—')       AS tipo_documento,
                COALESCE(d.numero_documento, '—')       AS numero_documento,
                COUNT(ds.id_detalle_salida_med)         AS total_lineas,
                SUM(ds.cantidad)                        AS total_cantidad
            FROM  salidas_med s
            LEFT JOIN programas      pg ON pg.id_programa       = s.programa_id
            LEFT JOIN usuarios        u  ON u.id_usuario        = s.entregado_por
            LEFT JOIN documentos_med  d  ON d.id_documento_med  = s.documento_id
            LEFT JOIN detalles_salida_med ds ON ds.salida_id    = s.id_salida_med
            GROUP BY s.id_salida_med
            ORDER BY s.fecha_salida DESC, s.id_salida_med DESC
        ");

        echo json_encode(['ok' => true, 'data' => $rows], JSON_UNESCAPED_UNICODE);
        break;

    // ══════════════════════════════════════════════════════════════════════
    // REVERTIR_INGRESO
    // 1. Verifica que ningún lote tenga salidas
    // 2. Elimina lotes creados por este ingreso
    // 3. Elimina detalles → cabecera → documento
    // ══════════════════════════════════════════════════════════════════════
    case 'revertir_ingreso':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') error_json('Método no permitido.', 405);

        $body      = json_decode(file_get_contents('php://input'), true);
        $ingreso_id = isset($body['ingreso_id']) ? (int)$body['ingreso_id'] : 0;
        if (!$ingreso_id) error_json('ID de ingreso requerido.', 422);

        // Verificar que existe
        $ingreso = query_one($mysqli, "
            SELECT id_ingreso_med, documento_id
            FROM   ingresos_med
            WHERE  id_ingreso_med = $ingreso_id
        ");
        if (!$ingreso) error_json('Ingreso no encontrado.', 404);

        // Obtener lotes creados por este ingreso
        $detalles = query_rows($mysqli, "
            SELECT lote_id, cantidad
            FROM   detalles_ingreso_med
            WHERE  ingreso_id = $ingreso_id
              AND  lote_id IS NOT NULL
        ");

        // Verificar que ningún lote tenga salidas registradas
        $lotes_con_salidas = [];
        foreach ($detalles as $d) {
            $lote_id = (int)$d['lote_id'];
            $tiene_salidas = query_one($mysqli, "
                SELECT COUNT(*) AS total
                FROM   detalles_salida_med
                WHERE  lote_id = $lote_id
            ");
            if ((int)$tiene_salidas['total'] > 0) {
                $lote_info = query_one($mysqli, "
                    SELECT numero_lote FROM lotes_med WHERE id_lote_med = $lote_id
                ");
                $lotes_con_salidas[] = $lote_info['numero_lote'] ?? "ID $lote_id";
            }
        }

        if (!empty($lotes_con_salidas)) {
            error_json(
                'No se puede revertir. Los siguientes lotes ya tienen salidas registradas: '
                . implode(', ', $lotes_con_salidas),
                409
            );
        }

        // ── Transacción ───────────────────────────────────────────────
        $mysqli->begin_transaction();
        try {
            $doc_id = $ingreso['documento_id'];

            // Eliminar lotes creados por este ingreso
            $lotes_eliminados = [];
            foreach ($detalles as $d) {
                $lote_id = (int)$d['lote_id'];
                if (in_array($lote_id, $lotes_eliminados)) continue; // ya fue borrado
                if (!$mysqli->query("DELETE FROM lotes_med WHERE id_lote_med = $lote_id"))
                    throw new Exception('Error al eliminar lote: ' . $mysqli->error);
                $lotes_eliminados[] = $lote_id;
            }

            // Eliminar detalles
            if (!$mysqli->query("DELETE FROM detalles_ingreso_med WHERE ingreso_id = $ingreso_id"))
                throw new Exception('Error al eliminar detalles: ' . $mysqli->error);

            // Eliminar cabecera
            if (!$mysqli->query("DELETE FROM ingresos_med WHERE id_ingreso_med = $ingreso_id"))
                throw new Exception('Error al eliminar ingreso: ' . $mysqli->error);

            // Eliminar documento
            if ($doc_id) {
                if (!$mysqli->query("DELETE FROM documentos_med WHERE id_documento_med = $doc_id"))
                    throw new Exception('Error al eliminar documento: ' . $mysqli->error);
            }

            $mysqli->commit();
            echo json_encode(['ok' => true, 'msg' => 'Ingreso revertido correctamente.'], JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            $mysqli->rollback();
            error_json('Transacción cancelada: ' . $e->getMessage());
        }
        break;

    // ══════════════════════════════════════════════════════════════════════
    // REVERTIR_SALIDA
    // 1. Suma de vuelta la cantidad a cada lote
    // 2. Elimina detalles → cabecera → documento
    // ══════════════════════════════════════════════════════════════════════
    case 'revertir_salida':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') error_json('Método no permitido.', 405);

        $body     = json_decode(file_get_contents('php://input'), true);
        $salida_id = isset($body['salida_id']) ? (int)$body['salida_id'] : 0;
        if (!$salida_id) error_json('ID de salida requerido.', 422);

        // Verificar que existe
        $salida = query_one($mysqli, "
            SELECT id_salida_med, documento_id
            FROM   salidas_med
            WHERE  id_salida_med = $salida_id
        ");
        if (!$salida) error_json('Salida no encontrada.', 404);

        // Obtener detalles para restaurar stock
        $detalles = query_rows($mysqli, "
            SELECT lote_id, cantidad
            FROM   detalles_salida_med
            WHERE  salida_id = $salida_id
              AND  lote_id IS NOT NULL
        ");

        // ── Transacción ───────────────────────────────────────────────
        $mysqli->begin_transaction();
        try {
            $doc_id = $salida['documento_id'];

            // Restaurar stock en cada lote
            foreach ($detalles as $d) {
                $lote_id  = (int)$d['lote_id'];
                $cantidad = (float)$d['cantidad'];
                if (!$mysqli->query("
                    UPDATE lotes_med
                    SET    cantidad_actual = cantidad_actual + $cantidad
                    WHERE  id_lote_med    = $lote_id
                ")) throw new Exception('Error al restaurar lote: ' . $mysqli->error);
            }

            // Eliminar detalles
            if (!$mysqli->query("DELETE FROM detalles_salida_med WHERE salida_id = $salida_id"))
                throw new Exception('Error al eliminar detalles: ' . $mysqli->error);

            // Eliminar cabecera
            if (!$mysqli->query("DELETE FROM salidas_med WHERE id_salida_med = $salida_id"))
                throw new Exception('Error al eliminar salida: ' . $mysqli->error);

            // Eliminar documento
            if ($doc_id) {
                if (!$mysqli->query("DELETE FROM documentos_med WHERE id_documento_med = $doc_id"))
                    throw new Exception('Error al eliminar documento: ' . $mysqli->error);
            }

            $mysqli->commit();
            echo json_encode(['ok' => true, 'msg' => 'Salida revertida correctamente.'], JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            $mysqli->rollback();
            error_json('Transacción cancelada: ' . $e->getMessage());
        }
        break;

    default:
        error_json('Acción no válida.', 400);
}