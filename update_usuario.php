<?php
include "config/db.php";

$id = intval($_POST["id_usuario"]);
$nombre = $_POST["nombre_completo"];
$usuario = $_POST["nombre_usuario"];
$correo = $_POST["correo"];
$telefono = $_POST["telefono"];
$dpi = $_POST["dpi_usuario"];
$genero = $_POST["genero_usuario"];
$rol = intval($_POST["rol_id"]);
$departamento = intval($_POST["departamento_id"]);
$activo = intval($_POST["activo"]);

$sql = "UPDATE usuarios SET
        nombre_completo = '$nombre',
        nombre_usuario = '$usuario',
        correo = '$correo',
        telefono = '$telefono',
        dpi_usuario = '$dpi',
        genero_usuario = '$genero',
        rol_id = $rol,
        departamento_id = $departamento,
        activo = $activo
        WHERE id_usuario = $id";

if ($mysqli->query($sql)) {
    echo "ok";
} else {
    echo "error";
}
?>
