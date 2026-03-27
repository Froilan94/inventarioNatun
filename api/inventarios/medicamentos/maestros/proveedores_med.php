<?php
/**
 * proveedores_med.php
 * CRUD Proveedores/Donantes.
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
function solo_lectura(): void { error_json('No tiene permisos para esta acción.', 403); }

switch ($action) {

    case 'get_all':
        $res  = $mysqli->query("
            SELECT id_proveedor_med, nit_proveedor, nombre_proveedor,
                   genero_proveedor, tipo_proveedor, telefono,
                   direccion, correo, activo
            FROM   proveedores_med
            ORDER  BY nombre_proveedor
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
            SELECT id_proveedor_med, nit_proveedor, nombre_proveedor,
                   genero_proveedor, tipo_proveedor, telefono,
                   direccion, correo, activo
            FROM   proveedores_med WHERE id_proveedor_med=$id LIMIT 1
        ");
        $row = $res->fetch_assoc(); $res->free();
        if (!$row) error_json('No encontrado.', 404);
        echo json_encode(['ok' => true, 'data' => $row], JSON_UNESCAPED_UNICODE);
        break;

    case 'insertar':
        if ($rol === 'supervisormed') solo_lectura();
        $nombre  = trim($_POST['nombre_proveedor'] ?? '');
        $nit     = trim($_POST['nit_proveedor']    ?? '') ?: null;
        $genero  = trim($_POST['genero_proveedor'] ?? '');
        $tipo    = trim($_POST['tipo_proveedor']   ?? '');
        $tel     = trim($_POST['telefono']          ?? '') ?: null;
        $dir     = trim($_POST['direccion']         ?? '') ?: null;
        $correo  = trim($_POST['correo']            ?? '') ?: null;

        if (!$nombre) error_json('El nombre es requerido.');
        if (!in_array($genero, ['Masculino','Femenino','Otros'])) error_json('Género inválido.');
        if (!in_array($tipo,   ['Fabricante','Proveedor','Donante'])) error_json('Tipo inválido.');

        $stmt = $mysqli->prepare("
            INSERT INTO proveedores_med
                (nit_proveedor, nombre_proveedor, genero_proveedor,
                 tipo_proveedor, telefono, direccion, correo)
            VALUES (?,?,?,?,?,?,?)
        ");
        $stmt->bind_param('sssssss', $nit, $nombre, $genero, $tipo, $tel, $dir, $correo);
        if (!$stmt->execute()) {
            if ($mysqli->errno === 1062) error_json('Ya existe un proveedor con ese nombre o NIT.');
            error_json('Error: ' . $stmt->error);
        }
        $stmt->close();
        echo json_encode(['ok' => true, 'msg' => 'Proveedor registrado correctamente.'], JSON_UNESCAPED_UNICODE);
        break;

    case 'actualizar':
        if ($rol === 'supervisormed') solo_lectura();
        $id      = isset($_POST['id_proveedor_med']) ? (int)$_POST['id_proveedor_med'] : 0;
        $nombre  = trim($_POST['nombre_proveedor']  ?? '');
        $nit     = trim($_POST['nit_proveedor']     ?? '') ?: null;
        $genero  = trim($_POST['genero_proveedor']  ?? '');
        $tipo    = trim($_POST['tipo_proveedor']    ?? '');
        $tel     = trim($_POST['telefono']           ?? '') ?: null;
        $dir     = trim($_POST['direccion']          ?? '') ?: null;
        $correo  = trim($_POST['correo']             ?? '') ?: null;
        $activo  = isset($_POST['activo']) ? (int)$_POST['activo'] : 1;

        if (!$id || !$nombre) error_json('Datos incompletos.');

        $stmt = $mysqli->prepare("
            UPDATE proveedores_med
            SET nit_proveedor=?, nombre_proveedor=?, genero_proveedor=?,
                tipo_proveedor=?, telefono=?, direccion=?, correo=?, activo=?
            WHERE id_proveedor_med=?
        ");
        $stmt->bind_param('sssssssii', $nit, $nombre, $genero, $tipo, $tel, $dir, $correo, $activo, $id);
        if (!$stmt->execute()) {
            if ($mysqli->errno === 1062) error_json('Ya existe un proveedor con ese nombre o NIT.');
            error_json('Error: ' . $stmt->error);
        }
        $stmt->close();
        echo json_encode(['ok' => true, 'msg' => 'Proveedor actualizado correctamente.'], JSON_UNESCAPED_UNICODE);
        break;

    case 'eliminar':
        if ($rol !== 'admin_super') solo_lectura();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) error_json('ID requerido.');
        $stmt = $mysqli->prepare("DELETE FROM proveedores_med WHERE id_proveedor_med=?");
        $stmt->bind_param('i', $id);
        if (!$stmt->execute()) {
            if ($mysqli->errno === 1451) error_json('No se puede eliminar: proveedor en uso.');
            error_json('Error: ' . $stmt->error);
        }
        $stmt->close();
        echo json_encode(['ok' => true, 'msg' => 'Proveedor eliminado correctamente.'], JSON_UNESCAPED_UNICODE);
        break;

    default:
        error_json('Acción no válida.');
}
