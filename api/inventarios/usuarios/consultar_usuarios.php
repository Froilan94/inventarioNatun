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

$sql = "SELECT id_usuario, nombre_completo, nombre_usuario, correo, telefono, cargo, dpi_usuario, genero_usuario, activo 
        FROM usuarios ORDER BY id_usuario ASC";

$result = $mysqli->query($sql);

$usuarios = [];

while ($row = $result->fetch_assoc()) {
    $usuarios[] = $row;
}

echo json_encode($usuarios);

