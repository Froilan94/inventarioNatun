<?php
include "config/db.php";

$id = $_POST['id_usuario'];

$sql = "UPDATE usuarios SET
            nombre_completo = ?,
            nombre_usuario = ?,
            correo = ?,
            telefono = ?,
            dpi_usuario = ?,
            genero_usuario = ?,
            activo = ?
        WHERE id_usuario = ?";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param(
    "ssssssii",
    $_POST['nombre_completo'],
    $_POST['nombre_usuario'],
    $_POST['correo'],
    $_POST['telefono'],
    $_POST['dpi_usuario'],
    $_POST['genero_usuario'],
    $_POST['activo'],
    $id
);

echo $stmt->execute() ? "ok" : "error";
