<?php
session_start();
require_once "config/db.php";

if (!isset($_SESSION['role_name']) || $_SESSION['role_name'] !== 'admin_super') {
    http_response_code(403);
    exit("No tiene permisos para realizar esta acción.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre_beneficiario  = trim($_POST["nombre_beneficiario"] ?? '');
    $dpi_beneficiario     = trim($_POST["dpi_beneficiario"] ?? '');
    $direccion_beneficiario = trim($_POST["direccion_beneficiario"] ?? '');
    $telefono             = trim($_POST["telefono"] ?? '');
    $genero               = trim($_POST["genero_beneficiario"] ?? '');
    $departamento_id      = intval($_POST["departamento_id"] ?? 0);

    $errores = [];

    if ($nombre_beneficiario === '') $errores[] = "El nombre del beneficiario es obligatorio.";
    if (!in_array($genero, ['Masculino', 'Femenino', 'Otros'])) $errores[] = "Género inválido.";
    if ($departamento_id <= 0) $errores[] = "Debe seleccionar un departamento.";

    if (!empty($errores)) {
        echo json_encode(["status" => "error", "errores" => $errores]);
        exit;
    }

    try {
        $sql = "INSERT INTO beneficiarios_med
                (nombre_beneficiario, dpi_beneficiario, direccion_beneficiario, telefono, genero_beneficiario, departamento_id)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("sssssi", $nombre_beneficiario, $dpi_beneficiario, $direccion_beneficiario, $telefono, $genero, $departamento_id);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "mensaje" => "Beneficiario registrado correctamente."]);
        } else {
            echo json_encode(["status" => "error", "mensaje" => $stmt->error]);
        }

        $stmt->close();

    } catch (Exception $e) {
        echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
    }
}
?>
