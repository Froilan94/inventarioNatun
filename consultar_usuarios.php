<?php
include "config/db.php";

$sql = "SELECT id_usuario, nombre_completo, nombre_usuario, correo, telefono, dpi_usuario, genero_usuario, activo 
        FROM usuarios ORDER BY id_usuario ASC";

$result = $mysqli->query($sql);

$usuarios = [];

while ($row = $result->fetch_assoc()) {
    $usuarios[] = $row;
}

echo json_encode($usuarios);

