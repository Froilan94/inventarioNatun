<?php
ob_start();

header('Content-Type: application/json; charset=utf-8');
require_once '../auth/roles.php';
requireRoles(['admin_super', 'operadormed', 'supervisormed']);
include '../config/db.php';

$basura = ob_get_clean();
if ($basura) {
    echo json_encode(['ok' => false, 'msg' => 'Error interno del servidor.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$action = $_GET['action'] ?? '';

$sql = "
SELECT *,
CASE
    WHEN fecha_vencimiento <= CURDATE() + INTERVAL 30 DAY THEN 'CRITICO'
    WHEN fecha_vencimiento <= CURDATE() + INTERVAL 90 DAY THEN 'ADVERTENCIA'
    ELSE 'NORMAL'
END AS estado_vencimiento
FROM vw_reporte_existencias
WHERE 1=1
";

$params = [];

if (!empty($_GET['fechaInicio'])) {
    $sql .= " AND fecha_ingreso >= :fechaInicio";
    $params[':fechaInicio'] = $_GET['fechaInicio'];
}
if (!empty($_GET['fechaFin'])) {
    $sql .= " AND fecha_ingreso <= :fechaFin";
    $params[':fechaFin'] = $_GET['fechaFin'];
}
if (!empty($_GET['medicamento'])) {
    $sql .= " AND id_medicamento = :medicamento";
    $params[':medicamento'] = $_GET['medicamento'];
}
if (!empty($_GET['proveedor'])) {
    $sql .= " AND proveedor_donante LIKE :proveedor";
    $params[':proveedor'] = "%" . $_GET['proveedor'] . "%";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
