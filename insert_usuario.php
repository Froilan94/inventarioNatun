<?php
// insert_usuario.php
session_start();
require_once "config/db.php";

// Solo admin puede registrar
if (!isset($_SESSION['role_name']) || $_SESSION['role_name'] !== 'admin_super') {
    http_response_code(403);
    exit("No tiene permisos para realizar esta acción.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre_completo = trim($_POST["nombre_completo"] ?? '');
    $nombre_usuario  = trim($_POST["nombre_usuario"] ?? '');
    $correo          = trim($_POST["correo"] ?? '');
    $telefono        = trim($_POST["telefono"] ?? '');
    $password        = trim($_POST["password"] ?? '');
    $rol_id          = intval($_POST["rol_id"] ?? 0);
    $dpi_usuario     = trim($_POST["dpi_usuario"] ?? '');
    $genero_usuario  = trim($_POST["genero_usuario"] ?? '');
    $departamento_id = !empty($_POST["departamento_id"]) ? intval($_POST["departamento_id"]) : null;

    // Validaciones
    $errores = [];

    if ($nombre_completo === '') $errores[] = "El nombre completo es obligatorio.";
    if ($nombre_usuario === '') $errores[] = "El nombre de usuario es obligatorio.";
    if ($password === '' || strlen($password) < 5) $errores[] = "La contraseña debe tener al menos 5 caracteres.";
    if (!in_array($genero_usuario, ['Masculino', 'Femenino', 'Otros'])) $errores[] = "El género no es válido.";
    if ($rol_id <= 0) $errores[] = "Debe seleccionar un rol.";

    if (!empty($errores)) {
        echo json_encode(["status" => "error", "errores" => $errores]);
        exit;
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    try {

        $sql = "INSERT INTO usuarios 
        (nombre_completo, nombre_usuario, correo, telefono, password_hash,
         rol_id, dpi_usuario, genero_usuario, departamento_id)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $mysqli->prepare($sql);

        $stmt->bind_param(
            "sssssissi",
            $nombre_completo,
            $nombre_usuario,
            $correo,
            $telefono,
            $password_hash,
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


