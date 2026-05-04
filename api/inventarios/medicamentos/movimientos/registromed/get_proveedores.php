<?php
ob_start();

header('Content-Type: application/json; charset=utf-8');
require_once 'auth/roles.php';
requireRoles(['admin_super', 'operadormed', 'supervisormed']);
include 'config/db.php';

$basura = ob_get_clean();
if ($basura) {
    echo json_encode(['ok' => false, 'msg' => 'Error interno del servidor.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$action = $_GET['action'] ?? '';

$sql = "SELECT id_proveedor_med, nombre_proveedor 
        FROM proveedores_med 
        WHERE activo = 1
        ORDER BY nombre_proveedor ASC";

$result = $mysqli->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<option value="'. $row["id_proveedor_med"] .'">'. $row["nombre_proveedor"] .'</option>';
    }
} else {
    echo '<option value="">No hay proveedores</option>';
}
?>
