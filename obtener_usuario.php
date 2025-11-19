<?php
include "config/db.php";

$id = intval($_GET["id"]);

$sql = "SELECT * FROM usuarios WHERE id_usuario = $id";
$result = $mysqli->query($sql);

echo json_encode($result->fetch_assoc());
?>
