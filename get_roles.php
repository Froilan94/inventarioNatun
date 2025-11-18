<?php
require_once "config/db.php";

$sql = "SELECT id_rol, nombre_rol FROM roles ORDER BY nombre_rol ASC";
$result = $mysqli->query($sql);

$roles = [];
while ($row = $result->fetch_assoc()) {
    $roles[] = $row;
}

echo json_encode($roles);
?>
