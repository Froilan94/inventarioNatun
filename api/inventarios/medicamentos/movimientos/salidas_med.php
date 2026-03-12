<?php
/**
 * salidas_med.php
 * Endpoint para el formulario de Registro de Salida de Medicamentos.
 *
 * Acciones GET  ?action=...
 *   - get_datos_iniciales   → medicamentos, componentes, usuarios
 *   - get_lotes_medicamento → lotes con stock > 0 + datos del último ingreso
 *   - get_beneficiarios     → búsqueda por nombre/DPI
 *
 * Acciones POST ?action=...
 *   - registrar_salida      → graba documento, salida, detalles y descuenta stock
 */

header('Content-Type: application/json; charset=utf-8');
require_once '../../../../auth/roles.php';
requireRoles(['admin_super', 'operadormed', 'supervisormed']);
include '../../../../config/db.php';

$action = $_GET['action'] ?? '';

// ── Helpers ───────────────────────────────────────────────────────────────
function error_json(string $msg, int $code = 500): void {
    http_response_code($code);
    echo json_encode(['ok' => false, 'msg' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

function query_rows(mysqli $db, string $sql): array {
    $res = $db->query($sql);
    if (!$res) error_json('Error DB: ' . $db->error);
    $rows = [];
    while ($row = $res->fetch_assoc()) $rows[] = $row;
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
// ─────────────────────────────────────────────────────────────────────────

switch ($action) {

    // ══════════════════════════════════════════════════════════════════════
    // GET_DATOS_INICIALES
    // ══════════════════════════════════════════════════════════════════════
    case 'get_datos_iniciales':

        $medicamentos = query_rows($mysqli, "
            SELECT id_medicamento   AS id,
                   nombre_comercial AS nombre
            FROM   medicamentos
            WHERE  activo = 1
            ORDER  BY nombre_comercial
        ");

        $componentes = query_rows($mysqli, "
            SELECT id_comunidad     AS id,
                   nombre_comunidad AS nombre
            FROM   comunidades
            ORDER  BY nombre_comunidad
        ");

        $usuario_sesion = null;
        $operadores     = [];

        if (!empty($_SESSION['user_id'])) {
            $uid = (int)$_SESSION['user_id'];
            $usuario_sesion = query_one($mysqli, "
                SELECT id_usuario      AS id,
                       nombre_completo AS nombre
                FROM   usuarios
                WHERE  id_usuario = $uid AND activo = 1
                LIMIT  1
            ");
        }

        if (!$usuario_sesion) {
            $operadores = query_rows($mysqli, "
                SELECT u.id_usuario      AS id,
                       u.nombre_completo AS nombre
                FROM   usuarios u
                JOIN   roles r ON r.id_rol = u.rol_id
                WHERE  u.activo = 1
                  AND  r.nombre_rol IN ('admin_super','operadormed','supervisormed')
                ORDER  BY u.nombre_completo
            ");
        }

        echo json_encode([
            'ok'             => true,
            'medicamentos'   => $medicamentos,
            'componentes'    => $componentes,
            'usuario_sesion' => $usuario_sesion,
            'operadores'     => $operadores,
        ], JSON_UNESCAPED_UNICODE);
        break;

    // ══════════════════════════════════════════════════════════════════════
    // GET_LOTES_MEDICAMENTO
    // Devuelve lotes con stock > 0 + unidad, presentación y precio
    // del último detalle de ingreso registrado para ese lote
    // ══════════════════════════════════════════════════════════════════════
    case 'get_lotes_medicamento':

        $med_id = isset($_GET['medicamento_id']) ? (int)$_GET['medicamento_id'] : 0;
        if (!$med_id) error_json('Medicamento requerido.', 422);

        $lotes = query_rows($mysqli, "
            SELECT
                l.id_lote_med                        AS id,
                l.numero_lote                        AS lote,
                l.fecha_vencimiento                  AS vencimiento,
                l.cantidad_actual                    AS stock,
                COALESCE(d.unidad_id,        0)      AS unidad_id,
                COALESCE(um.nombre_unidad,   '—')    AS unidad_nombre,
                COALESCE(d.presentacion_id,  0)      AS presentacion_id,
                COALESCE(pm.nombre_presentacion,'—') AS presentacion_nombre,
                COALESCE(d.precio_unitario,  0)      AS precio_unitario
            FROM lotes_med l
            LEFT JOIN (
                SELECT d1.*
                FROM   detalles_ingreso_med d1
                INNER JOIN (
                    SELECT lote_id, MAX(id_detalle_ingreso_med) AS max_id
                    FROM   detalles_ingreso_med
                    GROUP  BY lote_id
                ) ult ON ult.lote_id = d1.lote_id
                      AND ult.max_id = d1.id_detalle_ingreso_med
            ) d  ON d.lote_id  = l.id_lote_med
            LEFT JOIN unidades_medida_med um ON um.id_unidad_med       = d.unidad_id
            LEFT JOIN presentaciones_med  pm ON pm.id_presentacion_med = d.presentacion_id
            WHERE l.medicamento_id  = $med_id
              AND l.cantidad_actual > 0
            ORDER BY l.fecha_vencimiento ASC
        ");

        echo json_encode(['ok' => true, 'lotes' => $lotes], JSON_UNESCAPED_UNICODE);
        break;

    // ══════════════════════════════════════════════════════════════════════
    // GET_BENEFICIARIOS
    // ══════════════════════════════════════════════════════════════════════
    case 'get_beneficiarios':

        $q = trim($_GET['q'] ?? '');
        if (strlen($q) < 2) {
            echo json_encode(['ok' => true, 'beneficiarios' => []], JSON_UNESCAPED_UNICODE);
            break;
        }

        $q_safe = $mysqli->real_escape_string($q);

        $beneficiarios = query_rows($mysqli, "
            SELECT id_beneficiario_med AS id,
                   nombre_beneficiario AS nombre,
                   dpi_beneficiario    AS dpi
            FROM   beneficiarios_med
            WHERE  activo = 1
              AND  (nombre_beneficiario LIKE '%$q_safe%'
                OR  dpi_beneficiario    LIKE '%$q_safe%')
            ORDER  BY nombre_beneficiario
            LIMIT  15
        ");

        echo json_encode(['ok' => true, 'beneficiarios' => $beneficiarios], JSON_UNESCAPED_UNICODE);
        break;

    // ══════════════════════════════════════════════════════════════════════
    // REGISTRAR_SALIDA (POST)
    // ══════════════════════════════════════════════════════════════════════
    case 'registrar_salida':

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') error_json('Método no permitido.', 405);

        $body = json_decode(file_get_contents('php://input'), true);
        if (!$body) error_json('Cuerpo de la petición inválido.', 400);

        $tipo_doc      = trim($body['tipo_documento']   ?? '');
        $num_doc       = trim($body['numero_documento'] ?? '');
        $serie_doc     = trim($body['serie_documento']  ?? '');
        $fecha_salida  = trim($body['fecha_salida']     ?? '');
        $componente_id = isset($body['componente_id']) && $body['componente_id'] !== '' ? (int)$body['componente_id'] : null;
        $entregado_por = isset($body['entregado_por']) && $body['entregado_por'] !== '' ? (int)$body['entregado_por'] : null;
        $detalles      = $body['detalles'] ?? [];

        if (!$tipo_doc)       error_json('Tipo de documento requerido.', 422);
        if (!$fecha_salida)   error_json('Fecha de salida requerida.', 422);
        if (empty($detalles)) error_json('Debe agregar al menos un detalle.', 422);

        $tipos_validos = ['Factura', 'Recibo_donacion', 'Cardex', 'Acta'];
        if (!in_array($tipo_doc, $tipos_validos)) error_json('Tipo de documento inválido.', 422);

        foreach ($detalles as $i => $d) {
            $linea = $i + 1;
            if (empty($d['medicamento_id'])) error_json("Línea $linea: medicamento requerido.", 422);
            if (empty($d['lote_id']))        error_json("Línea $linea: lote requerido.", 422);
            if (empty($d['cantidad']) || (float)$d['cantidad'] <= 0)
                error_json("Línea $linea: cantidad debe ser mayor a 0.", 422);
        }

        // Validar stock ANTES de la transacción
        foreach ($detalles as $i => $d) {
            $linea   = $i + 1;
            $lote_id = (int)$d['lote_id'];
            $cant    = (float)$d['cantidad'];

            $lote = query_one($mysqli, "
                SELECT numero_lote, cantidad_actual
                FROM   lotes_med
                WHERE  id_lote_med = $lote_id
                LIMIT  1
            ");

            if (!$lote) error_json("Línea $linea: lote no encontrado.", 422);

            if ((float)$lote['cantidad_actual'] < $cant) {
                error_json(
                    "Línea $linea: stock insuficiente en lote {$lote['numero_lote']}. " .
                    "Disponible: {$lote['cantidad_actual']}, solicitado: $cant.",
                    422
                );
            }
        }

        $mysqli->begin_transaction();

        try {
            // 1. Documento
            $stmt = $mysqli->prepare("
                INSERT INTO documentos_med
                    (tipo_documento, fecha_documento, numero_documento, serie_documento)
                VALUES (?, NOW(), ?, ?)
            ");
            $num_doc_val   = $num_doc   ?: null;
            $serie_doc_val = $serie_doc ?: null;
            $stmt->bind_param('sss', $tipo_doc, $num_doc_val, $serie_doc_val);
            if (!$stmt->execute()) throw new Exception('Error al insertar documento: ' . $stmt->error);
            $documento_id = $mysqli->insert_id;
            $stmt->close();

            // 2. Cabecera salida
            $stmt = $mysqli->prepare("
                INSERT INTO salidas_med
                    (fecha_salida, documento_id, comunidad_id, entregado_por)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param('siii', $fecha_salida, $documento_id, $componente_id, $entregado_por);
            if (!$stmt->execute()) throw new Exception('Error al insertar salida: ' . $stmt->error);
            $salida_id = $mysqli->insert_id;
            $stmt->close();

            // 3. Detalles + descuento stock
            foreach ($detalles as $d) {
                $med_id          = (int)$d['medicamento_id'];
                $lote_id         = (int)$d['lote_id'];
                $cantidad        = (float)$d['cantidad'];
                $unidad_id       = isset($d['unidad_id'])       && $d['unidad_id']       !== '' ? (int)$d['unidad_id']       : null;
                $pres_id         = isset($d['presentacion_id']) && $d['presentacion_id'] !== '' ? (int)$d['presentacion_id'] : null;
                $beneficiario_id = isset($d['beneficiario_id']) && $d['beneficiario_id'] !== '' ? (int)$d['beneficiario_id'] : null;
                $precio          = (float)($d['precio_unitario'] ?? 0);

                $stmt = $mysqli->prepare("
                    INSERT INTO detalles_salida_med
                        (salida_id, medicamento_id, lote_id,
                         cantidad, unidad_id, presentacion_id,
                         beneficiario_id, precio_unitario)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->bind_param('iiidiidi',
                    $salida_id, $med_id, $lote_id,
                    $cantidad, $unidad_id, $pres_id,
                    $beneficiario_id, $precio
                );
                if (!$stmt->execute()) throw new Exception('Error al insertar detalle: ' . $stmt->error);
                $stmt->close();

                $upd = $mysqli->prepare("
                    UPDATE lotes_med
                    SET    cantidad_actual = cantidad_actual - ?
                    WHERE  id_lote_med    = ?
                ");
                $upd->bind_param('di', $cantidad, $lote_id);
                if (!$upd->execute()) throw new Exception('Error al descontar lote: ' . $upd->error);
                $upd->close();
            }

            $mysqli->commit();

            echo json_encode([
                'ok'        => true,
                'msg'       => 'Salida registrada correctamente.',
                'salida_id' => $salida_id,
            ], JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            $mysqli->rollback();
            error_json('Transacción cancelada: ' . $e->getMessage());
        }
        break;

    default:
        error_json('Acción no válida.', 400);
}
