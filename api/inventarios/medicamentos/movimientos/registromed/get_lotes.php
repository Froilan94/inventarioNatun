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

$sql = "SELECT id_lote_med, medicamento_id, numero_lote, fecha_vencimiento 
        FROM lotes_med 
        WHERE cantidad_actual > 0
        ORDER BY numero_lote ASC";

$result = $mysqli->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        $label = $row["numero_lote"] . " (Vence: " . $row["fecha_vencimiento"] . ")";

        echo '<option value="'. $row["id_lote_med"] .'">'. $label .'</option>';
    }
} else {
    echo '<option value="">No hay lotes disponibles</option>';
}
?>
