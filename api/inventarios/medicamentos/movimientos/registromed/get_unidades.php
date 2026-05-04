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

$sql = "SELECT id_unidad_med, nombre_unidad 
        FROM unidades_medida_med 
        ORDER BY nombre_unidad ASC";

$result = $mysqli->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<option value="'. $row["id_unidad_med"] .'">'. $row["nombre_unidad"] .'</option>';
    }
} else {
    echo '<option value="">No hay unidades</option>';
}
?>
