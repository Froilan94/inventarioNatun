<?php
ob_start();

header('Content-Type: application/json; charset=utf-8');
require_once '../../../auth/roles.php';
requireRoles(['admin_super', 'operadormed']);
include '../../../config/db.php';

$basura = ob_get_clean();
if ($basura) {
    echo json_encode(['ok' => false, 'msg' => 'Error interno del servidor.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$action = $_GET['action'] ?? '';

$id = intval($_POST["id_medicamento"]);
$nombrecomercial = $_POST["nombre_comercial"];
$nombregenerico = $_POST["nombre_generico"];
$activo = intval($_POST["activo"]);

$sql = "UPDATE medicamentos SET
        nombre_comercial = '$nombrecomercial',
        nombre_generico = '$nombregenerico',
        activo = $activo
        WHERE id_medicamento = $id";

if ($mysqli->query($sql)) {
    echo "ok";
} else {
    echo "error";
}
?>