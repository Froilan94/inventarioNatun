<?php
ob_start();

header('Content-Type: application/json; charset=utf-8');
require_once '../../../auth/roles.php';
requireRoles(['admin_super', 'operadormed', 'supervisormed']);
include '../../../config/db.php';

$basura = ob_get_clean();
if ($basura) {
    echo json_encode(['ok' => false, 'msg' => 'Error interno del servidor.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$action = $_GET['action'] ?? '';

$sql = "
    SELECT 
        id_medicamento,
        nombre_comercial,
        nombre_generico,
        activo
    FROM medicamentos
    ORDER BY id_medicamento ASC
";

$result = $mysqli->query($sql);

$medicamentos = [];

while ($row = $result->fetch_assoc()) {
    $medicamentos[] = $row;
}

echo json_encode($medicamentos);