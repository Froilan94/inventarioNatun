<?php
include "config/db.php";
$id = intval($_GET['id']);
$mysqli->query("DELETE FROM usuarios WHERE id_usuario = $id");
echo $mysqli->affected_rows > 0 ? "ok" : "error";
