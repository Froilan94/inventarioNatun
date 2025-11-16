<?php
session_start();
require_once "config/db.php";

if (!isset($_SESSION['role_name']) || $_SESSION['role_name'] !== 'admin_super') {
    http_response_code(403);
    exit("No tiene permisos para realizar esta acción.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre_comercial = trim($_POST["nombre_comercial"] ?? '');
    $nombre_generico  = trim($_POST["nombre_generico"] ?? '');
    $categoria_id     = intval($_POST["categoria_id"] ?? 0);

    $errores = [];

    if ($nombre_comercial === '') $errores[] = "El nombre comercial es obligatorio.";
    if ($nombre_generico === '') $errores[] = "El nombre genérico es obligatorio.";
    if ($categoria_id <= 0) $errores[] = "Debe seleccionar una categoría.";

    if (!empty($errores)) {
        echo json_encode(["status" => "error", "errores" => $errores]);
        exit;
    }

    try {
        $sql = "INSERT INTO medicamentos (nombre_comercial, nombre_generico, categoria_id)
                VALUES (?, ?, ?)";

        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ssi", $nombre_comercial, $nombre_generico, $categoria_id);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "mensaje" => "Medicamento registrado correctamente."]);
        } else {
            echo json_encode(["status" => "error", "mensaje" => $stmt->error]);
        }

        $stmt->close();

    } catch (Exception $e) {
        echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
    }
}
?>
