<?php
/**
 * firmas_planilla.php
 * CRUD Firmas de Planilla — solo admin_super.
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

    case 'get_all':
        $res  = $mysqli->query("SELECT id_firma, cargo, nombre, orden, activo FROM firmas_planilla_med ORDER BY orden");
        $data = [];
        while ($r = $res->fetch_assoc()) $data[] = $r;
        $res->free();
        echo json_encode(['ok' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
        break;

    case 'get_one':
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) error_json('ID requerido.');
        $res = $mysqli->query("SELECT id_firma, cargo, nombre, orden, activo FROM firmas_planilla_med WHERE id_firma=$id LIMIT 1");
        $row = $res->fetch_assoc(); $res->free();
        if (!$row) error_json('No encontrado.', 404);
        echo json_encode(['ok' => true, 'data' => $row], JSON_UNESCAPED_UNICODE);
        break;

    case 'insertar':
        $cargo  = trim($_POST['cargo']  ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $orden  = isset($_POST['orden']) ? (int)$_POST['orden'] : 1;
        if (!$cargo || !$nombre) error_json('Cargo y nombre son requeridos.');
        $stmt = $mysqli->prepare("INSERT INTO firmas_planilla_med (cargo, nombre, orden) VALUES (?,?,?)");
        $stmt->bind_param('ssi', $cargo, $nombre, $orden);
        if (!$stmt->execute()) error_json('Error: ' . $stmt->error);
/*        if (!$stmt->execute()) {
            if ($mysqli->errno === 1062)
                error_json('Ya existe una comunidad con ese nombre.');
            error_json('Error al registrar: ' . $stmt->error);
        }

        try {
            $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062)
                error_json('Ya existe una comunidad con ese nombre.');
            error_json('Error interno al registrar: ' . $e->getMessage());
        }*/          
        $stmt->close();
        echo json_encode(['ok' => true, 'msg' => 'Firma registrada correctamente.'], JSON_UNESCAPED_UNICODE);
        break;

    case 'actualizar':
        $id     = isset($_POST['id_firma']) ? (int)$_POST['id_firma'] : 0;
        $cargo  = trim($_POST['cargo']     ?? '');
        $nombre = trim($_POST['nombre']    ?? '');
        $orden  = isset($_POST['orden'])   ? (int)$_POST['orden'] : 1;
        $activo = isset($_POST['activo'])  ? (int)$_POST['activo'] : 1;
        if (!$id || !$cargo || !$nombre) error_json('Datos incompletos.');
        $stmt = $mysqli->prepare("UPDATE firmas_planilla_med SET cargo=?, nombre=?, orden=?, activo=? WHERE id_firma=?");
        $stmt->bind_param('ssiii', $cargo, $nombre, $orden, $activo, $id);
        if (!$stmt->execute()) error_json('Error: ' . $stmt->error);
        $stmt->close();
        echo json_encode(['ok' => true, 'msg' => 'Firma actualizada correctamente.'], JSON_UNESCAPED_UNICODE);
        break;

    case 'eliminar':
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) error_json('ID requerido.');
        $stmt = $mysqli->prepare("DELETE FROM firmas_planilla_med WHERE id_firma=?");
        $stmt->bind_param('i', $id);
        if (!$stmt->execute()) error_json('Error: ' . $stmt->error);
        $stmt->close();
        echo json_encode(['ok' => true, 'msg' => 'Firma eliminada correctamente.'], JSON_UNESCAPED_UNICODE);
        break;

    default:
        error_json('Acción no válida.');
}
