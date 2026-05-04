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

$id = intval($_GET["id"]);

$sql = "SELECT * FROM medicamentos WHERE id_medicamento = $id";
$result = $mysqli->query($sql);

echo json_encode($result->fetch_assoc());
?>