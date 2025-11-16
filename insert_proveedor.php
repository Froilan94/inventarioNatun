<?php
session_start();
require_once "config/db.php";

if (!isset($_SESSION['role_name']) || $_SESSION['role_name'] !== 'admin_super') {
    http_response_code(403);
    exit("No tiene permisos para realizar esta acción.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nit_proveedor   = trim($_POST["nit_proveedor"] ?? '');
    $nombre_proveedor = trim($_POST["nombre_proveedor"] ?? '');
    $genero_proveedor = trim($_POST["genero_proveedor"] ?? '');
    $tipo_proveedor   = trim($_POST["tipo_proveedor"] ?? '');
    $telefono         = trim($_POST["telefono"] ?? '');
    $direccion        = trim($_POST["direccion"] ?? '');
    $correo           = trim($_POST["correo"] ?? '');

    $errores = [];

    if ($nit_proveedor === '') $errores[] = "El NIT es obligatorio.";
    if ($nombre_proveedor === '') $errores[] = "El nombre del proveedor es obligatorio.";
    if (!in_array($genero_proveedor, ['Masculino', 'Femenino', 'Otros'])) $errores[] = "Género inválido.";
    if (!in_array($tipo_proveedor, ['Fabricante', 'Proveedor', 'Donante'])) $errores[] = "Tipo de proveedor inválido.";

    if (!empty($errores)) {
        echo json_encode(["status" => "error", "errores" => $errores]);
        exit;
    }

    try {
        $sql = "INSERT INTO proveedores_med
                (nit_proveedor, nombre_proveedor, genero_proveedor, tipo_proveedor, telefono, direccion, correo)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("sssssss", $nit_proveedor, $nombre_proveedor, $genero_proveedor, $tipo_proveedor, $telefono, $direccion, $correo);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "mensaje" => "Proveedor registrado correctamente."]);
        } else {
            echo json_encode(["status" => "error", "mensaje" => $stmt->error]);
        }

        $stmt->close();

    } catch (Exception $e) {
        echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
    }
}
?>
