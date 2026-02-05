<?php
require "../config/db.php";

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=reporte_existencias.xls");

echo "Medicamento\tLote\tVencimiento\tCantidad\tMonto\tProveedor\n";

$sql = "SELECT * FROM vw_reporte_existencias";
$data = $pdo->query($sql);

foreach ($data as $r) {
    echo "{$r['medicamento']}\t{$r['numero_lote']}\t{$r['fecha_vencimiento']}\t{$r['cantidad_existente']}\t{$r['monto_existente']}\t{$r['proveedor_donante']}\n";
}
