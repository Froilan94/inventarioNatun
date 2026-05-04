<?php
ob_start();

header('Content-Type: application/json; charset=utf-8');
require_once '../../../auth/roles.php';
requireRoles(['admin_super']); /* 'admin_super', 'operadormed', 'supervisormed' */
include '../../../config/db.php';

$basura = ob_get_clean();
if ($basura) {
    echo json_encode(['ok' => false, 'msg' => 'Error interno del servidor.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$action = $_GET['action'] ?? '';

$id = intval($_POST["id_usuario"]);
$nombre = $_POST["nombre_completo"];
$usuario = $_POST["nombre_usuario"];
$correo = $_POST["correo"];
$telefono = $_POST["telefono"];
$cargo = $_POST["cargo"];
$dpi = $_POST["dpi_usuario"];
$genero = $_POST["genero_usuario"];
$rol = intval($_POST["rol_id"]);
$departamento = intval($_POST["departamento_id"]);
$activo = intval($_POST["activo"]);

$sql = "UPDATE usuarios SET
        nombre_completo = '$nombre',
        nombre_usuario = '$usuario',
        correo = '$correo',
        telefono = '$telefono',
        cargo = '$cargo',
        dpi_usuario = '$dpi',
        genero_usuario = '$genero',
        rol_id = $rol,
        departamento_id = $departamento,
        activo = $activo
        WHERE id_usuario = $id";

if ($mysqli->query($sql)) {
    echo "ok";
} else {
    echo "error";
}
?>
