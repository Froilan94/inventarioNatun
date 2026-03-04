<?php
require_once '../../../auth/roles.php';

requireRoles(['admin_super', 'operadormed']);

include "../../../config/db.php";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre_comercial = trim($_POST["nombre_comercial"] ?? '');
    $nombre_generico  = trim($_POST["nombre_generico"] ?? '');

    $errores = [];

    if ($nombre_comercial === '') {
        $errores[] = "El nombre comercial es obligatorio.";
    }

    if ($nombre_generico === '') {
        $errores[] = "El nombre genérico es obligatorio.";
    }

    if (!empty($errores)) {
        echo json_encode([
            "status" => "error",
            "errores" => $errores
        ]);
        exit;
    }

    try {

        $sql = "INSERT INTO medicamentos (nombre_comercial, nombre_generico)
                VALUES (?, ?)";

        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ss", $nombre_comercial, $nombre_generico);

        if ($stmt->execute()) {
            echo json_encode([
                "status" => "success",
                "mensaje" => "Medicamento registrado correctamente."
            ]);
        } else {

            // Detectar error por nombre duplicado (UNIQUE)
            if ($mysqli->errno == 1062) {
                echo json_encode([
                    "status" => "error",
                    "mensaje" => "Ya existe un medicamento con ese nombre comercial."
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "mensaje" => $stmt->error
                ]);
            }
        }

        $stmt->close();

    } catch (Exception $e) {

        echo json_encode([
            "status" => "error",
            "mensaje" => "Error interno del servidor."
        ]);
    }
}
?>
