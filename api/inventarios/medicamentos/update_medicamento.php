<?php
require_once '../../../auth/roles.php';

requireRoles(['admin_super', 'operadormed']);

include "../../../config/db.php";

$id = intval($_POST["id_medicamento"]);
$nombrecomercial = $_POST["nombre_comercial"];
$nombregenerico = $_POST["nombre_generico"];
$activo = intval($_POST["activo"]);

$sql = "UPDATE medicamentos SET
        nombre_comercial = '$nombrecomercial',
        nombre_generico = '$nombregenerico',
        activo = $activo
        WHERE id_medicamento = $id";

if ($mysqli->query($sql)) {
    echo "ok";
} else {
    echo "error";
}
?>