<?php
/**
 * unidades_med.php
 * CRUD Unidades de Medida.
 * admin_super → CRUD completo
 * operadormed → get_all, get_one, insertar, actualizar
 * supervisormed → get_all, get_one
 */

header('Content-Type: application/json; charset=utf-8');
require_once '../../../../auth/roles.php';
requireRoles(['admin_super', 'operadormed', 'supervisormed']);
include '../../../../config/db.php';

$action = $_GET['action'] ?? '';
$rol    = $_SESSION['role_name'] ?? '';

function error_json(string $msg, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['ok' => false, 'msg' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}
function solo_lectura(): void {
    error_json('No tiene permisos para esta acción.', 403);
}

switch ($action) {

    case 'get_all':
        $res  = $mysqli->query("
            SELECT id_unidad_med, nombre_unidad
            FROM   unidades_medida_med
            ORDER  BY nombre_unidad
        ");
        $data = [];
        while ($r = $res->fetch_assoc()) $data[] = $r;
        $res->free();
        echo json_encode(['ok' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
        break;

    case 'get_one':
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) error_json('ID requerido.');
        $res = $mysqli->query("
            SELECT id_unidad_med, nombre_unidad
            FROM   unidades_medida_med
            WHERE  id_unidad_med = $id LIMIT 1
        ");
        $row = $res->fetch_assoc(); $res->free();
        if (!$row) error_json('No encontrado.', 404);
        echo json_encode(['ok' => true, 'data' => $row], JSON_UNESCAPED_UNICODE);
        break;

    case 'insertar':
        if ($rol === 'supervisormed') solo_lectura();
        $nombre = trim($_POST['nombre_unidad'] ?? '');
        if (!$nombre) error_json('El nombre es requerido.');
        $stmt = $mysqli->prepare("INSERT INTO unidades_medida_med (nombre_unidad) VALUES (?)");
        $stmt->bind_param('s', $nombre);
        try {
            $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062)
                error_json('Ya existe un registro con ese nombre.');
            error_json('Error interno: ' . $e->getMessage());
        }
        $stmt->close();
        echo json_encode(['ok' => true, 'msg' => 'Unidad registrada correctamente.'], JSON_UNESCAPED_UNICODE);
        break;

    case 'actualizar':
        if ($rol === 'supervisormed') solo_lectura();
        $id     = isset($_POST['id_unidad_med']) ? (int)$_POST['id_unidad_med'] : 0;
        $nombre = trim($_POST['nombre_unidad'] ?? '');
        if (!$id || !$nombre) error_json('Datos incompletos.');
        $stmt = $mysqli->prepare("UPDATE unidades_medida_med SET nombre_unidad=? WHERE id_unidad_med=?");
        $stmt->bind_param('si', $nombre, $id);
        try {
            $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062)
                error_json('Ya existe un registro con ese nombre.');
            error_json('Error interno: ' . $e->getMessage());
        }
        $stmt->close();
        echo json_encode(['ok' => true, 'msg' => 'Unidad actualizada correctamente.'], JSON_UNESCAPED_UNICODE);
        break;

    case 'eliminar':
        if ($rol !== 'admin_super') solo_lectura();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) error_json('ID requerido.');
        $stmt = $mysqli->prepare("DELETE FROM unidades_medida_med WHERE id_unidad_med=?");
        $stmt->bind_param('i', $id);
        try {
            $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1451)
                error_json('No se puede Eliminar: Unidad en USO.');
            error_json('Error interno: ' . $e->getMessage());
        }
        $stmt->close();
        echo json_encode(['ok' => true, 'msg' => 'Unidad eliminada correctamente.'], JSON_UNESCAPED_UNICODE);
        break;

    default:
        error_json('Acción no válida.');
}
