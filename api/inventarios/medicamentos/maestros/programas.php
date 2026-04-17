<?php
/**
 * programas.php
 * CRUD de programas / componentes
 */

header('Content-Type: application/json; charset=utf-8');
require_once '../../../../auth/roles.php';
requireRoles(['admin_super', 'operadormed']);
include '../../../../config/db.php';

$action = $_GET['action'] ?? '';

function error_json(string $msg, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['ok' => false, 'msg' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

function ok_json($data = [], string $msg = ''): void {
    echo json_encode(['ok' => true, 'data' => $data, 'msg' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

switch ($action) {

    // ─────────────────────────────────────────────
    // LISTAR
    // ─────────────────────────────────────────────
    case 'get_all':
        $res = $mysqli->query("
            SELECT id_programa, nombre_programa, descripcion
            FROM programas
            ORDER BY nombre_programa
        ");

        $data = [];
        while ($r = $res->fetch_assoc()) $data[] = $r;
        $res->free();

        ok_json($data);
        break;

    // ─────────────────────────────────────────────
    // OBTENER UNO
    // ─────────────────────────────────────────────
    case 'get_one':
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) error_json('ID inválido.');

        $stmt = $mysqli->prepare("
            SELECT id_programa, nombre_programa, descripcion
            FROM programas
            WHERE id_programa = ?
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();

        if (!$res->num_rows) error_json('Programa no encontrado.', 404);

        ok_json($res->fetch_assoc());
        break;

    // ─────────────────────────────────────────────
    // INSERTAR
    // ─────────────────────────────────────────────
    case 'insertar':
        $nombre = trim($_POST['nombre_programa'] ?? '');
        $desc   = trim($_POST['descripcion'] ?? '');

        if ($nombre === '') {
            error_json('El nombre del programa es obligatorio.');
        }

        $stmt = $mysqli->prepare("
            INSERT INTO programas (nombre_programa, descripcion)
            VALUES (?, ?)
        ");
        $stmt->bind_param('ss', $nombre, $desc);

        if (!$stmt->execute()) {
            // error por duplicado
            if ($mysqli->errno === 1062) {
                error_json('Ya existe un programa con ese nombre.');
            }
            error_json('Error al insertar.');
        }

        ok_json([], 'Programa registrado correctamente.');
        break;

    // ─────────────────────────────────────────────
    // ACTUALIZAR
    // ─────────────────────────────────────────────
    case 'actualizar':
        $id     = isset($_POST['id_programa']) ? (int)$_POST['id_programa'] : 0;
        $nombre = trim($_POST['nombre_programa'] ?? '');
        $desc   = trim($_POST['descripcion'] ?? '');

        if (!$id) error_json('ID inválido.');
        if ($nombre === '') error_json('El nombre es obligatorio.');

        $stmt = $mysqli->prepare("
            UPDATE programas
            SET nombre_programa = ?, descripcion = ?
            WHERE id_programa = ?
        ");
        $stmt->bind_param('ssi', $nombre, $desc, $id);

        if (!$stmt->execute()) {
            if ($mysqli->errno === 1062) {
                error_json('Ya existe un programa con ese nombre.');
            }
            error_json('Error al actualizar.');
        }

        ok_json([], 'Programa actualizado correctamente.');
        break;

    // ─────────────────────────────────────────────
    // ELIMINAR
    // ─────────────────────────────────────────────
    case 'eliminar':
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) error_json('ID inválido.');

        $stmt = $mysqli->prepare("DELETE FROM programas WHERE id_programa = ?");
        $stmt->bind_param('i', $id);

        if (!$stmt->execute()) {
            error_json('Error al eliminar.');
        }

        ok_json([], 'Programa eliminado correctamente.');
        break;

    default:
        error_json('Acción no válida.');
}