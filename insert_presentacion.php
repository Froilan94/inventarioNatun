<?php
session_start();
require_once "config/db.php";

if (!isset($_SESSION['role_name']) || $_SESSION['role_name'] !== 'admin_super') {
    http_response_code(403);
    exit("No tiene permisos para realizar esta acción.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre_presentacion = trim($_POST["nombre_presentacion"] ?? '');

    if ($nombre_presentacion === '') {
        echo json_encode(["status" => "error", "mensaje" => "El nombre de la presentación es obligatorio."]);
        exit;
    }

    try {
        $sql = "INSERT INTO presentaciones_med (nombre_presentacion) VALUES (?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("s", $nombre_presentacion);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "mensaje" => "Presentación registrada correctamente."]);
        } else {
            echo json_encode(["status" => "error", "mensaje" => $stmt->error]);
        }

        $stmt->close();

    } catch (Exception $e) {
        echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
    }
}
?>
