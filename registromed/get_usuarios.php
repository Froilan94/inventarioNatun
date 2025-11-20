<?php
require "config/db.php";

$sql = "SELECT id_usuario, nombre_completo 
        FROM usuarios 
        WHERE activo = 1
        ORDER BY nombre_completo ASC";

$result = $mysqli->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<option value="'. $row["id_usuario"] .'">'. $row["nombre_completo"] .'</option>';
    }
} else {
    echo '<option value="">No hay usuarios</option>';
}
?>
