<?php
session_start();
require_once "config/db.php";

if (!isset($_SESSION['role_name']) || $_SESSION['role_name'] !== 'admin_super') {
    http_response_code(403);
    exit("No tiene permisos para realizar esta acción.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre_unidad = trim($_POST["nombre_unidad"] ?? '');

    if ($nombre_unidad === '') {
        echo json_encode(["status" => "error", "mensaje" => "El nombre de unidad es obligatorio."]);
        exit;
    }

    try {
        $sql = "INSERT INTO unidades_medida_med (nombre_unidad) VALUES (?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("s", $nombre_unidad);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "mensaje" => "Unidad registrada correctamente."]);
        } else {
            echo json_encode(["status" => "error", "mensaje" => $stmt->error]);
        }

        $stmt->close();

    } catch (Exception $e) {
        echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
    }
}
?>
