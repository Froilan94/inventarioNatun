<?php
/**
 * programas.php
 * CRUD para Programas — conectado a la tabla `departamentos` en BD.
 * En el frontend se llama "Programa" pero en BD es departamentos.
 *
 * GET  ?action=get_all         → lista todos
 * GET  ?action=get_one&id=N    → obtiene uno
 * GET  ?action=eliminar&id=N   → elimina
 * POST ?action=insertar        → crea nuevo
 * POST ?action=actualizar      → actualiza
 */

header('Content-Type: application/json; charset=utf-8');
require_once '../../../../auth/roles.php';
requireRoles(['admin_super']);
include '../../../../config/db.php';

$action = $_GET['action'] ?? '';

function error_json(string $msg, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['ok' => false, 'msg' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

switch ($action) {

    // ── Listar todos ──────────────────────────────────
    case 'get_all':
        $res  = $mysqli->query("
            SELECT id_departamento AS id_programa,
                   nombre_departamento AS nombre_programa,
                   descripcion
            FROM   departamentos
            ORDER  BY nombre_departamento
        ");
        $data = [];
        while ($r = $res->fetch_assoc()) $data[] = $r;
        $res->free();
        echo json_encode(['ok' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
        break;

    // ── Obtener uno ───────────────────────────────────
    case 'get_one':
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) error_json('ID requerido.');

        $res = $mysqli->query("
            SELECT id_departamento AS id_programa,
                   nombre_departamento AS nombre_programa,
                   descripcion
            FROM   departamentos
            WHERE  id_departamento = $id
            LIMIT  1
        ");
        $row = $res->fetch_assoc();
        $res->free();
        if (!$row) error_json('Programa no encontrado.', 404);
        echo json_encode(['ok' => true, 'data' => $row], JSON_UNESCAPED_UNICODE);
        break;

    // ── Insertar ──────────────────────────────────────
    case 'insertar':
        $nombre      = trim($_POST['nombre_programa'] ?? '');
        $descripcion = trim($_POST['descripcion']     ?? '');

        if (!$nombre) error_json('El nombre es requerido.');
        if (strlen($nombre) > 20)
            error_json('El nombre no puede tener más de 20 caracteres.');

        $stmt = $mysqli->prepare("
            INSERT INTO departamentos (nombre_departamento, descripcion)
            VALUES (?, ?)
        ");
        $desc_val = $descripcion ?: null;
        $stmt->bind_param('ss', $nombre, $desc_val);

        if (!$stmt->execute()) {
            if ($mysqli->errno === 1062)
                error_json('Ya existe un programa con ese nombre.');
            error_json('Error al registrar: ' . $stmt->error);
        }
        $stmt->close();
        echo json_encode(['ok' => true, 'msg' => 'Programa registrado correctamente.'],
                         JSON_UNESCAPED_UNICODE);
        break;

    // ── Actualizar ────────────────────────────────────
    case 'actualizar':
        $id          = isset($_POST['id_programa'])    ? (int)$_POST['id_programa'] : 0;
        $nombre      = trim($_POST['nombre_programa']  ?? '');
        $descripcion = trim($_POST['descripcion']      ?? '');

        if (!$id)     error_json('ID requerido.');
        if (!$nombre) error_json('El nombre es requerido.');
        if (strlen($nombre) > 20)
            error_json('El nombre no puede tener más de 20 caracteres.');

        $stmt = $mysqli->prepare("
            UPDATE departamentos
            SET    nombre_departamento = ?,
                   descripcion         = ?
            WHERE  id_departamento     = ?
        ");
        $desc_val = $descripcion ?: null;
        $stmt->bind_param('ssi', $nombre, $desc_val, $id);

        if (!$stmt->execute()) {
            if ($mysqli->errno === 1062)
                error_json('Ya existe un programa con ese nombre.');
            error_json('Error al actualizar: ' . $stmt->error);
        }
        $stmt->close();
        echo json_encode(['ok' => true, 'msg' => 'Programa actualizado correctamente.'],
                         JSON_UNESCAPED_UNICODE);
        break;

    // ── Eliminar ──────────────────────────────────────
    case 'eliminar':
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) error_json('ID requerido.');

        $stmt = $mysqli->prepare("DELETE FROM departamentos WHERE id_departamento = ?");
        $stmt->bind_param('i', $id);

        if (!$stmt->execute()) {
            if ($mysqli->errno === 1451)
                error_json('No se puede eliminar: el programa está en uso.');
            error_json('Error al eliminar: ' . $stmt->error);
        }
        $stmt->close();
        echo json_encode(['ok' => true, 'msg' => 'Programa eliminado correctamente.'],
                         JSON_UNESCAPED_UNICODE);
        break;

    default:
        error_json('Acción no válida.');
}
