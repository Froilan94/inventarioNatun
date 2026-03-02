<?php
include "../../../config/db.php";
$id = intval($_GET['id']);
$mysqli->query("DELETE FROM medicamentos WHERE id_medicamento = $id");
echo $mysqli->affected_rows > 0 ? "ok" : "error";
