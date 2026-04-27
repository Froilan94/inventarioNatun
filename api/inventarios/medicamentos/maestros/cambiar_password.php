<?php
session_start();
require_once "../../../../config/db.php";

header('Content-Type: application/json');

// 🔹 Helper para respuestas de error
function error_json($mensaje, $codigo = 400) {
    http_response_code($codigo);
    echo json_encode([
        "status" => "error",
        "mensaje" => $mensaje
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 🔹 Helper éxito
function success_json($mensaje) {
    echo json_encode([
        "status" => "success",
        "mensaje" => $mensaje
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 🔐 Validar sesión
if (empty($_SESSION["user_id"])) {
    error_json("Sesión no válida", 401);
}

$id_usuario = (int) $_SESSION["user_id"];

// 🔹 Obtener datos
$password_actual    = $_POST["password_actual"] ?? '';
$password_nueva     = $_POST["password_nueva"] ?? '';
$password_confirmar = $_POST["password_confirmar"] ?? '';

// 🔹 Validaciones
if (strlen($password_nueva) < 5) {
    error_json("La nueva contraseña debe tener al menos 5 caracteres.");
}

if ($password_nueva !== $password_confirmar) {
    error_json("Las contraseñas no coinciden.");
}

try {

    // 🔍 Obtener contraseña actual
    $stmt = $mysqli->prepare("SELECT password_hash FROM usuarios WHERE id_usuario = ?");
    if (!$stmt) error_json("Error en la consulta.");

    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        error_json("Usuario no encontrado.");
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    // 🔐 Verificar contraseña actual
    if (!password_verify($password_actual, $user["password_hash"])) {
        error_json("Contraseña actual incorrecta.");
    }

    // 🔐 Generar nuevo hash
    $nuevo_hash = password_hash($password_nueva, PASSWORD_DEFAULT);

    // 🔄 Actualizar contraseña
    $stmt = $mysqli->prepare("UPDATE usuarios SET password_hash=? WHERE id_usuario=?");
    if (!$stmt) error_json("Error al preparar actualización.");

    $stmt->bind_param("si", $nuevo_hash, $id_usuario);

    try {
        $stmt->execute();
    } catch (mysqli_sql_exception $e) {

        // 🔥 Manejo específico de errores SQL
        if ($e->getCode() === 1062) {
            error_json("Error duplicado inesperado.");
        }

        error_json("Error interno: " . $e->getMessage(), 500);
    }

    $stmt->close();

    success_json("Contraseña actualizada correctamente");

} catch (Exception $e) {
    error_json("Error del servidor: " . $e->getMessage(), 500);
}