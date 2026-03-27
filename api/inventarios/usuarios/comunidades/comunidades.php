<?php
/**
 * comunidades.php
 * CRUD para la tabla comunidades.
 *
 * GET  ?action=get_all         → lista todas
 * GET  ?action=get_one&id=N    → obtiene una
 * GET  ?action=eliminar&id=N   → elimina
 * POST ?action=insertar        → crea nueva
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

    // ── Listar todas ──────────────────────────────────
    case 'get_all':
        $res  = $mysqli->query("
            SELECT id_comunidad, nombre_comunidad, direccion
            FROM   comunidades
            ORDER  BY nombre_comunidad
        ");
        $data = [];
        while ($r = $res->fetch_assoc()) $data[] = $r;
        $res->free();
        echo json_encode(['ok' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
        break;

    // ── Obtener una ───────────────────────────────────
    case 'get_one':
        $id  = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) error_json('ID requerido.');

        $res = $mysqli->query("
            SELECT id_comunidad, nombre_comunidad, direccion
            FROM   comunidades
            WHERE  id_comunidad = $id
            LIMIT  1
        ");
        $row = $res->fetch_assoc();
        $res->free();
        if (!$row) error_json('Comunidad no encontrada.', 404);
        echo json_encode(['ok' => true, 'data' => $row], JSON_UNESCAPED_UNICODE);
        break;

    // ── Insertar ──────────────────────────────────────
    case 'insertar':
        $nombre   = trim($_POST['nombre_comunidad'] ?? '');
        $direccion = trim($_POST['direccion']        ?? '');

        if (!$nombre) error_json('El nombre es requerido.');

        $stmt = $mysqli->prepare("
            INSERT INTO comunidades (nombre_comunidad, direccion)
            VALUES (?, ?)
        ");
        $dir_val = $direccion ?: null;
        $stmt->bind_param('ss', $nombre, $dir_val);

        if (!$stmt->execute()) {
            if ($mysqli->errno === 1062)
                error_json('Ya existe una comunidad con ese nombre.');
            error_json('Error al registrar: ' . $stmt->error);
        }
        $stmt->close();
        echo json_encode(['ok' => true, 'msg' => 'Comunidad registrada correctamente.'],
                         JSON_UNESCAPED_UNICODE);
        break;

    // ── Actualizar ────────────────────────────────────
    case 'actualizar':
        $id       = isset($_POST['id_comunidad'])     ? (int)$_POST['id_comunidad'] : 0;
        $nombre   = trim($_POST['nombre_comunidad']   ?? '');
        $direccion = trim($_POST['direccion']          ?? '');

        if (!$id)     error_json('ID requerido.');
        if (!$nombre) error_json('El nombre es requerido.');

        $stmt = $mysqli->prepare("
            UPDATE comunidades
            SET    nombre_comunidad = ?,
                   direccion        = ?
            WHERE  id_comunidad     = ?
        ");
        $dir_val = $direccion ?: null;
        $stmt->bind_param('ssi', $nombre, $dir_val, $id);

        if (!$stmt->execute()) {
            if ($mysqli->errno === 1062)
                error_json('Ya existe una comunidad con ese nombre.');
            error_json('Error al actualizar: ' . $stmt->error);
        }
        $stmt->close();
        echo json_encode(['ok' => true, 'msg' => 'Comunidad actualizada correctamente.'],
                         JSON_UNESCAPED_UNICODE);
        break;

    // ── Eliminar ──────────────────────────────────────
    case 'eliminar':
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) error_json('ID requerido.');

        $stmt = $mysqli->prepare("DELETE FROM comunidades WHERE id_comunidad = ?");
        $stmt->bind_param('i', $id);

        if (!$stmt->execute()) {
            // FK violation — comunidad en uso
            if ($mysqli->errno === 1451)
                error_json('No se puede eliminar: la comunidad está en uso.');
            error_json('Error al eliminar: ' . $stmt->error);
        }
        $stmt->close();
        echo json_encode(['ok' => true, 'msg' => 'Comunidad eliminada correctamente.'],
                         JSON_UNESCAPED_UNICODE);
        break;

    default:
        error_json('Acción no válida.');
}
