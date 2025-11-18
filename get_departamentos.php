<?php
require_once "config/db.php";

$sql = "SELECT id_departamento, nombre_departamento FROM departamentos ORDER BY nombre_departamento ASC";
$result = $mysqli->query($sql);

$departamentos = [];
while ($row = $result->fetch_assoc()) {
    $departamentos[] = $row;
}

echo json_encode($departamentos);
?>
