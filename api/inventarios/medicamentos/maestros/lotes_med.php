<?php
/**
 * lotes_med.php
 * Solo consultas — los lotes se crean al registrar ingresos.
 */

header('Content-Type: application/json; charset=utf-8');
require_once '../../../../auth/roles.php';
requireRoles(['admin_super', 'operadormed', 'supervisormed']);
include '../../../../config/db.php';

$action = $_GET['action'] ?? '';

function error_json(string $msg, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['ok' => false, 'msg' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

switch ($action) {

    case 'get_all':
        $med_id = isset($_GET['medicamento_id']) && $_GET['medicamento_id'] !== ''
            ? (int)$_GET['medicamento_id'] : 0;

        $where = $med_id ? "WHERE l.medicamento_id = $med_id" : '';

        $res = $mysqli->query("
            SELECT l.id_lote_med, m.nombre_comercial AS medicamento,
                   l.numero_lote, l.fecha_vencimiento,
                   l.cantidad_inicial, l.cantidad_actual,
                   l.creado_en
            FROM   lotes_med l
            JOIN   medicamentos m ON m.id_medicamento = l.medicamento_id
            $where
            ORDER  BY m.nombre_comercial, l.fecha_vencimiento
        ");
        $data = [];
        while ($r = $res->fetch_assoc()) $data[] = $r;
        $res->free();
        echo json_encode(['ok' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
        break;

    case 'get_medicamentos':
        $res  = $mysqli->query("SELECT id_medicamento AS id, nombre_comercial AS nombre FROM medicamentos WHERE activo=1 ORDER BY nombre_comercial");
        $data = [];
        while ($r = $res->fetch_assoc()) $data[] = $r;
        $res->free();
        echo json_encode(['ok' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
        break;

    default:
        error_json('Acción no válida.');
}
