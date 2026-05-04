<?php
ob_start();

header('Content-Type: application/json; charset=utf-8');
require_once '../../../auth/roles.php';
requireRoles(['admin_super']);
include '../../../config/db.php';

$basura = ob_get_clean();
if ($basura) {
    echo json_encode(['ok' => false, 'msg' => 'Error interno del servidor.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$action = $_GET['action'] ?? '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre_completo = trim($_POST["nombre_completo"] ?? '');
    $nombre_usuario  = trim($_POST["nombre_usuario"] ?? '');
    $correo          = trim($_POST["correo"] ?? '');
    $telefono        = trim($_POST["telefono"] ?? '');
    $password        = trim($_POST["password"] ?? '');
    $cargo           = trim($_POST["cargo"] ?? '');    
    $rol_id          = intval($_POST["rol_id"] ?? 0);
    $dpi_usuario     = trim($_POST["dpi_usuario"] ?? '');
    $genero_usuario  = trim($_POST["genero_usuario"] ?? '');
    $departamento_id = !empty($_POST["departamento_id"]) ? intval($_POST["departamento_id"]) : null;

    // Validaciones
    $errores = [];

    if ($nombre_completo === '') $errores[] = "El nombre completo es obligatorio.";
    if ($nombre_usuario === '') $errores[] = "El nombre de usuario es obligatorio.";
    if ($password === '' || strlen($password) < 5) $errores[] = "La contraseña debe tener al menos 5 caracteres.";
    if ($cargo === '') $errores[] = "El cargo es un Campo Obligatorio.";    
    if (!in_array($genero_usuario, ['Masculino', 'Femenino', 'Otros'])) $errores[] = "El género no es válido.";
    if ($rol_id <= 0) $errores[] = "Debe seleccionar un rol.";

    if (!empty($errores)) {
        echo json_encode(["status" => "error", "errores" => $errores]);
        exit;
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    try {

        $sql = "INSERT INTO usuarios 
        (nombre_completo, nombre_usuario, correo, telefono, password_hash, cargo,
         rol_id, dpi_usuario, genero_usuario, departamento_id)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $mysqli->prepare($sql);

        $stmt->bind_param(
            "ssssssissi",
            $nombre_completo,
            $nombre_usuario,
            $correo,
            $telefono,
            $password_hash,
            $cargo,            
            $rol_id,
            $dpi_usuario,
            $genero_usuario,
            $departamento_id
        );

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "mensaje" => "Usuario registrado correctamente."]);
        } else {
            echo json_encode(["status" => "error", "mensaje" => $stmt->error]);
        }

        $stmt->close();

    } catch (Exception $e) {
        echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
    }
}
?>


