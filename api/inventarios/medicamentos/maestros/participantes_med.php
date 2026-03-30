<?php
/**
 * participantes_med.php
 * CRUD Participantes (beneficiarios_med en BD).
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
            SELECT b.id_beneficiario_med, b.nombre_beneficiario,
                   b.dpi_beneficiario, b.direccion_beneficiario,
                   b.telefono, b.genero_beneficiario,
                   COALESCE(d.nombre_departamento, '—') AS departamento,
                   b.activo
            FROM   beneficiarios_med b
            LEFT JOIN departamentos d ON d.id_departamento = b.departamento_id
            ORDER  BY b.nombre_beneficiario
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
            SELECT id_beneficiario_med, nombre_beneficiario, dpi_beneficiario,
                   direccion_beneficiario, telefono, genero_beneficiario,
                   departamento_id, activo
            FROM   beneficiarios_med WHERE id_beneficiario_med=$id LIMIT 1
        ");
        $row = $res->fetch_assoc(); $res->free();
        if (!$row) error_json('No encontrado.', 404);
        echo json_encode(['ok' => true, 'data' => $row], JSON_UNESCAPED_UNICODE);
        break;

    case 'get_departamentos':
        $res  = $mysqli->query("SELECT id_departamento AS id, nombre_departamento AS nombre FROM departamentos ORDER BY nombre_departamento");
        $data = [];
        while ($r = $res->fetch_assoc()) $data[] = $r;
        $res->free();
        echo json_encode(['ok' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
        break;

    case 'insertar':
        if ($rol === 'supervisormed') solo_lectura();
        $nombre  = trim($_POST['nombre_beneficiario']    ?? '');
        $dpi     = trim($_POST['dpi_beneficiario']       ?? '');
        $dir     = trim($_POST['direccion_beneficiario'] ?? '') ?: null;
        $tel     = trim($_POST['telefono']               ?? '') ?: null;
        $genero  = trim($_POST['genero_beneficiario']    ?? '');
        $depto   = isset($_POST['departamento_id']) && $_POST['departamento_id'] !== '' ? (int)$_POST['departamento_id'] : null;

        if (!$nombre) error_json('El nombre es requerido.');
        if (!$dpi)    error_json('El DPI es requerido.');
        if (!in_array($genero, ['Masculino','Femenino','Otros'])) error_json('Género inválido.');

        $stmt = $mysqli->prepare("
            INSERT INTO beneficiarios_med
                (nombre_beneficiario, dpi_beneficiario, direccion_beneficiario,
                 telefono, genero_beneficiario, departamento_id)
            VALUES (?,?,?,?,?,?)
        ");
        $stmt->bind_param('sssssi', $nombre, $dpi, $dir, $tel, $genero, $depto);
        try {
            $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062)
                error_json('Ya existe un participante con ese nombre o DPI.');
            error_json('Error interno: ' . $e->getMessage());
        }        
        $stmt->close();
        echo json_encode(['ok' => true, 'msg' => 'Participante registrado correctamente.'], JSON_UNESCAPED_UNICODE);
        break;

    case 'actualizar':
        if ($rol === 'supervisormed') solo_lectura();
        $id      = isset($_POST['id_beneficiario_med']) ? (int)$_POST['id_beneficiario_med'] : 0;
        $nombre  = trim($_POST['nombre_beneficiario']    ?? '');
        $dpi     = trim($_POST['dpi_beneficiario']       ?? '');
        $dir     = trim($_POST['direccion_beneficiario'] ?? '') ?: null;
        $tel     = trim($_POST['telefono']               ?? '') ?: null;
        $genero  = trim($_POST['genero_beneficiario']    ?? '');
        $depto   = isset($_POST['departamento_id']) && $_POST['departamento_id'] !== '' ? (int)$_POST['departamento_id'] : null;
        $activo  = isset($_POST['activo']) ? (int)$_POST['activo'] : 1;

        if (!$id || !$nombre || !$dpi) error_json('Datos incompletos.');

        $stmt = $mysqli->prepare("
            UPDATE beneficiarios_med
            SET nombre_beneficiario=?, dpi_beneficiario=?, direccion_beneficiario=?,
                telefono=?, genero_beneficiario=?, departamento_id=?, activo=?
            WHERE id_beneficiario_med=?
        ");
        $stmt->bind_param('sssssiii', $nombre, $dpi, $dir, $tel, $genero, $depto, $activo, $id);
        try {
            $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062)
                error_json('Ya existe un participante con ese nombre o DPI.');
            error_json('Error interno: ' . $e->getMessage());
        }        
        $stmt->close();
        echo json_encode(['ok' => true, 'msg' => 'Participante actualizado correctamente.'], JSON_UNESCAPED_UNICODE);
        break;

    case 'eliminar':
        if ($rol !== 'admin_super') solo_lectura();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) error_json('ID requerido.');
        $stmt = $mysqli->prepare("DELETE FROM beneficiarios_med WHERE id_beneficiario_med=?");
        $stmt->bind_param('i', $id);
        try {
            $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1451)
                error_json('No se puede eliminar: participante en uso.');
            error_json('Error interno: ' . $e->getMessage());
        }        
        $stmt->close();
        echo json_encode(['ok' => true, 'msg' => 'Participante eliminado correctamente.'], JSON_UNESCAPED_UNICODE);
        break;

    default:
        error_json('Acción no válida.');
}
