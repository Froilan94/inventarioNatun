<?php
require_once '../../../auth/roles.php';

requireRoles(['admin_super', 'operadormed']);
// me llamo obtener medicamento
include "../../../config/db.php";

$id = intval($_GET["id"]);

$sql = "SELECT * FROM medicamentos WHERE id_medicamento = $id";
$result = $mysqli->query($sql);

echo json_encode($result->fetch_assoc());
?>