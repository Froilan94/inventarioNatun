<?php
/**
 * ingreso_med.php
 * Endpoint para el formulario de Registro de Ingreso de Medicamentos.
 *
 * Acciones GET  ?action=...
 *   - get_datos_iniciales  → medicamentos, proveedores, unidades, presentaciones, usuario sesión
 *
 * Acciones POST ?action=...
 *   - registrar_ingreso    → graba documento, ingreso, lotes y detalles en transacción
 */

header('Content-Type: application/json; charset=utf-8');
require_once '../../../../../auth/roles.php';
requireRoles(['admin_super', 'operadormed', 'supervisormed']);
include '../../../../../config/db.php'; // $mysqli disponible

$action = $_GET['action'] ?? '';

// ── Helpers ────────────────────────────────────────────────────────────────
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
// ──────────────────────────────────────────────────────────────────────────

switch ($action) {

    // ══════════════════════════════════════════════════════════════════════
    // GET_DATOS_INICIALES
    // Devuelve todo lo necesario para poblar el formulario de una sola vez.
    // ══════════════════════════════════════════════════════════════════════
    case 'get_datos_iniciales':

        // 1. Medicamentos activos
        $medicamentos = query_rows($mysqli, "
            SELECT id_medicamento  AS id,
                   nombre_comercial AS nombre
            FROM   medicamentos
            WHERE  activo = 1
            ORDER  BY nombre_comercial
        ");

        // 2. Proveedores activos
        $proveedores = query_rows($mysqli, "
            SELECT id_proveedor_med AS id,
                   nombre_proveedor  AS nombre,
                   tipo_proveedor
            FROM   proveedores_med
            WHERE  activo = 1
            ORDER  BY nombre_proveedor
        ");

        // 3. Unidades de medida
        $unidades = query_rows($mysqli, "
            SELECT id_unidad_med AS id,
                   nombre_unidad  AS nombre
            FROM   unidades_medida_med
            ORDER  BY nombre_unidad
        ");

        // 4. Presentaciones
        $presentaciones = query_rows($mysqli, "
            SELECT id_presentacion_med AS id,
                   nombre_presentacion  AS nombre
            FROM   presentaciones_med
            ORDER  BY nombre_presentacion
        ");

        // 5. Usuario en sesión → "Recibido por"
        //    Si tienes el id en sesión úsalo; si no, devuelve lista de operadores
        $usuario_sesion = null;
        $operadores     = [];

        if (!empty($_SESSION['id_usuario'])) {
            $uid = (int)$_SESSION['id_usuario'];
            $usuario_sesion = query_one($mysqli, "
                SELECT id_usuario      AS id,
                       nombre_completo  AS nombre
                FROM   usuarios
                WHERE  id_usuario = $uid
                  AND  activo = 1
                LIMIT 1
            ");
        }

        // Fallback: lista de operadores/admin para elegir
        if (!$usuario_sesion) {
            $operadores = query_rows($mysqli, "
                SELECT u.id_usuario      AS id,
                       u.nombre_completo  AS nombre
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
            'proveedores'    => $proveedores,
            'unidades'       => $unidades,
            'presentaciones' => $presentaciones,
            'usuario_sesion' => $usuario_sesion,   // null si no hay sesión
            'operadores'     => $operadores,        // [] si hay sesión
        ], JSON_UNESCAPED_UNICODE);
        break;

    // ══════════════════════════════════════════════════════════════════════
    // REGISTRAR_INGRESO  (POST)
    // Graba en orden:
    //   1. documentos_med
    //   2. ingresos_med
    //   3. Por cada línea de detalle:
    //      a. lotes_med  (INSERT si nuevo / UPDATE suma si ya existe)
    //      b. detalles_ingreso_med
    // Todo en una sola transacción.
    // ══════════════════════════════════════════════════════════════════════
    case 'registrar_ingreso':

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_json('Método no permitido.', 405);
        }

        $body = json_decode(file_get_contents('php://input'), true);
        if (!$body) error_json('Cuerpo de la petición inválido.', 400);

        // ── Cabecera del ingreso ──────────────────────────────────────
        $tipo_doc      = trim($body['tipo_documento']    ?? '');
        $num_doc       = trim($body['numero_documento']  ?? '');
        $serie_doc     = trim($body['serie_documento']   ?? '');
        $fecha_ingreso = trim($body['fecha_ingreso']     ?? '');
        $proveedor_id  = isset($body['proveedor_id'])  && $body['proveedor_id']  !== '' ? (int)$body['proveedor_id']  : null;
        $recibido_por  = isset($body['recibido_por'])  && $body['recibido_por']  !== '' ? (int)$body['recibido_por']  : null;
        $detalles      = $body['detalles'] ?? [];

        // ── Validaciones básicas ──────────────────────────────────────
        if (!$tipo_doc)      error_json('Tipo de documento requerido.',   422);
        if (!$fecha_ingreso) error_json('Fecha de ingreso requerida.',    422);
        if (empty($detalles))error_json('Debe agregar al menos un detalle.', 422);

        $tipos_validos = ['Factura', 'Recibo_donacion', 'Cardex', 'Acta'];
        if (!in_array($tipo_doc, $tipos_validos)) error_json('Tipo de documento inválido.', 422);

        // Validar detalles
        foreach ($detalles as $i => $d) {
            $linea = $i + 1;
            if (empty($d['medicamento_id']))  error_json("Línea $linea: medicamento requerido.", 422);
            if (empty($d['cantidad']) || (float)$d['cantidad'] <= 0)
                                              error_json("Línea $linea: cantidad debe ser mayor a 0.", 422);
        }

        // ── Transacción ───────────────────────────────────────────────
        $mysqli->begin_transaction();

        try {

            // 1. Insertar documento
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

            // 2. Insertar cabecera de ingreso
            $stmt = $mysqli->prepare("
                INSERT INTO ingresos_med
                    (fecha_ingreso, documento_id, proveedor_id, recibido_por)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param('siii', $fecha_ingreso, $documento_id, $proveedor_id, $recibido_por);
            if (!$stmt->execute()) throw new Exception('Error al insertar ingreso: ' . $stmt->error);
            $ingreso_id = $mysqli->insert_id;
            $stmt->close();

            // 3. Agrupar líneas por (medicamento_id + numero_lote)
            //    Si el usuario repite el mismo lote en varias líneas → sumamos antes de insertar
            $lotes_agrupados = [];
            foreach ($detalles as $d) {
                $med_id     = (int)$d['medicamento_id'];
                $num_lote   = trim($d['numero_lote']       ?? '');
                $fecha_venc = trim($d['fecha_vencimiento'] ?? '') ?: null;
                $cantidad   = (float)$d['cantidad'];
                $unidad_id  = isset($d['unidad_id'])       && $d['unidad_id']       !== '' ? (int)$d['unidad_id']       : null;
                $pres_id    = isset($d['presentacion_id']) && $d['presentacion_id'] !== '' ? (int)$d['presentacion_id'] : null;
                $precio     = isset($d['precio_unitario'])  ? (float)$d['precio_unitario'] : 0;

                // Clave de agrupación
                $clave = $med_id . '||' . $num_lote;

                if (!isset($lotes_agrupados[$clave])) {
                    $lotes_agrupados[$clave] = [
                        'medicamento_id'   => $med_id,
                        'numero_lote'      => $num_lote,
                        'fecha_vencimiento'=> $fecha_venc,
                        'cantidad_total'   => 0,
                        'unidad_id'        => $unidad_id,
                        'presentacion_id'  => $pres_id,
                        'precio_unitario'  => $precio,
                        'lineas'           => [],   // guardamos lineas originales para detalles
                    ];
                }
                $lotes_agrupados[$clave]['cantidad_total'] += $cantidad;
                $lotes_agrupados[$clave]['lineas'][] = [
                    'cantidad'        => $cantidad,
                    'unidad_id'       => $unidad_id,
                    'presentacion_id' => $pres_id,
                    'precio_unitario' => $precio,
                ];
            }

            // 4. Por cada lote agrupado: INSERT o UPDATE lotes_med + INSERT detalle
            foreach ($lotes_agrupados as $grupo) {
                $med_id     = $grupo['medicamento_id'];
                $num_lote   = $grupo['numero_lote'];
                $fecha_venc = $grupo['fecha_vencimiento'];
                $cant_total = $grupo['cantidad_total'];

                // ¿Ya existe este lote para este medicamento?
                $chk = $mysqli->prepare("
                    SELECT id_lote_med, cantidad_actual
                    FROM   lotes_med
                    WHERE  medicamento_id = ? AND numero_lote = ?
                    LIMIT 1
                ");
                $chk->bind_param('is', $med_id, $num_lote);
                $chk->execute();
                $res_chk = $chk->get_result();
                $lote_existente = $res_chk->fetch_assoc();
                $chk->close();

                if ($lote_existente) {
                    // Lote existe → sumar cantidad
                    $lote_id       = (int)$lote_existente['id_lote_med'];
                    $nueva_cantidad = (float)$lote_existente['cantidad_actual'] + $cant_total;

                    $upd = $mysqli->prepare("
                        UPDATE lotes_med
                        SET    cantidad_actual  = ?,
                               cantidad_inicial = cantidad_inicial + ?,
                               fecha_vencimiento = COALESCE(?, fecha_vencimiento)
                        WHERE  id_lote_med = ?
                    ");
                    $upd->bind_param('ddsi', $nueva_cantidad, $cant_total, $fecha_venc, $lote_id);
                    if (!$upd->execute()) throw new Exception('Error al actualizar lote: ' . $upd->error);
                    $upd->close();

                } else {
                    // Lote nuevo → INSERT
                    $ins = $mysqli->prepare("
                        INSERT INTO lotes_med
                            (medicamento_id, numero_lote, fecha_vencimiento,
                             cantidad_inicial, cantidad_actual)
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $ins->bind_param('issdd', $med_id, $num_lote, $fecha_venc, $cant_total, $cant_total);
                    if (!$ins->execute()) throw new Exception('Error al insertar lote: ' . $ins->error);
                    $lote_id = $mysqli->insert_id;
                    $ins->close();
                }

                // Insertar una línea de detalle por cada línea original del grupo
                foreach ($grupo['lineas'] as $linea) {
                    $stmt_det = $mysqli->prepare("
                        INSERT INTO detalles_ingreso_med
                            (ingreso_id, medicamento_id, lote_id,
                             cantidad, unidad_id, presentacion_id, precio_unitario)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt_det->bind_param(
                        'iiiidid',
                        $ingreso_id,
                        $med_id,
                        $lote_id,
                        $linea['cantidad'],
                        $linea['unidad_id'],
                        $linea['presentacion_id'],
                        $linea['precio_unitario']
                    );
                    if (!$stmt_det->execute()) throw new Exception('Error al insertar detalle: ' . $stmt_det->error);
                    $stmt_det->close();
                }
            }

            $mysqli->commit();

            echo json_encode([
                'ok'         => true,
                'msg'        => 'Ingreso registrado correctamente.',
                'ingreso_id' => $ingreso_id,
            ], JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            $mysqli->rollback();
            error_json('Transacción cancelada: ' . $e->getMessage());
        }
        break;

    // ══════════════════════════════════════════════════════════════════════
    default:
        error_json('Acción no válida.', 400);
}
