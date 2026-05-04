<?php
ob_start();

header('Content-Type: application/json; charset=utf-8');
require_once '../../../auth/roles.php';
requireRoles(['admin_super']);
include '../../../config/db.php';

$basura = ob_get_clean();
if ($basura) {
    echo json_encode(['ok' => false, 'msg' => 'Error interno del servidor.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$action = $_GET['action'] ?? '';

$sql = "SELECT id_departamento, nombre_departamento FROM departamentos ORDER BY nombre_departamento ASC";
$result = $mysqli->query($sql);

$departamentos = [];
while ($row = $result->fetch_assoc()) {
    $departamentos[] = $row;
}

echo json_encode($departamentos);
?>
