<?php
require "config/db.php";

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
