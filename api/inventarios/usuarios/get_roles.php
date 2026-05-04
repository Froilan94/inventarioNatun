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

$sql = "SELECT id_rol, nombre_rol FROM roles ORDER BY nombre_rol ASC";
$result = $mysqli->query($sql);

$roles = [];
while ($row = $result->fetch_assoc()) {
    $roles[] = $row;
}

echo json_encode($roles);
?>
