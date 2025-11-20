<?php
require_once "config/db.php";

$sql = "SELECT id_categoria_med, nombre_categoria FROM categorias_med ORDER BY nombre_categoria ASC";
$result = $mysqli->query($sql);

$categorias_med = [];
while ($row = $result->fetch_assoc()) {
    $categorias_med[] = $row;
}

echo json_encode($categorias_med);
?>
