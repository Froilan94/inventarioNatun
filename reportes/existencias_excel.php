<?php
require_once "../config/db.php";

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=reporte_existencias.xls");
header("Pragma: no-cache");
header("Expires: 0");

$sql = "SELECT * FROM vw_reporte_existencias";
$result = $mysqli->query($sql);

if (!$result) {
    echo "Error SQL\t" . $mysqli->error;
    exit;
}

echo "Medicamento\tLote\tVencimiento\tCantidad\tMonto\tProveedor\n";

while ($r = $result->fetch_assoc()) {
    echo $r['medicamento'] . "\t";
    echo $r['numero_lote'] . "\t";
    echo $r['fecha_vencimiento'] . "\t";
    echo $r['cantidad_actual'] . "\t";
    echo $r['monto_existente'] . "\t";
    echo $r['proveedor_donante'] . "\n";
}

exit;
