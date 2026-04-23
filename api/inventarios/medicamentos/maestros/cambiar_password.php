<?php
session_start();
require_once "../../../../config/db.php";

// Verificar sesión
if (!isset($_SESSION["id_usuario"])) {
    echo json_encode(["status"=>"error","mensaje"=>"Sesión no válida"]);
    exit;
}

$id_usuario = $_SESSION["id_usuario"];

$password_actual    = $_POST["password_actual"] ?? '';
$password_nueva     = $_POST["password_nueva"] ?? '';
$password_confirmar = $_POST["password_confirmar"] ?? '';

$errores = [];

if (strlen($password_nueva) < 5) {
    $errores[] = "La nueva contraseña debe tener al menos 5 caracteres.";
}

if ($password_nueva !== $password_confirmar) {
    $errores[] = "Las contraseñas no coinciden.";
}

if (!empty($errores)) {
    echo json_encode(["status"=>"error","errores"=>$errores]);
    exit;
}

// Obtener contraseña actual
$stmt = $mysqli->prepare("SELECT password_hash FROM usuarios WHERE id_usuario = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Verificar contraseña actual
if (!password_verify($password_actual, $user["password_hash"])) {
    echo json_encode(["status"=>"error","mensaje"=>"Contraseña actual incorrecta"]);
    exit;
}

// Actualizar contraseña
$nuevo_hash = password_hash($password_nueva, PASSWORD_DEFAULT);

$stmt = $mysqli->prepare("UPDATE usuarios SET password_hash=? WHERE id_usuario=?");
$stmt->bind_param("si", $nuevo_hash, $id_usuario);

if ($stmt->execute()) {
    echo json_encode(["status"=>"success","mensaje"=>"Contraseña actualizada correctamente"]);
} else {
    echo json_encode(["status"=>"error","mensaje"=>$stmt->error]);
}