<?php
require_once '../../../auth/roles.php';
requireRoles(['admin_super', 'operadormed']);

include "../../../config/db.php";

header('Content-Type: application/json');

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
        $stmt->execute();

        echo json_encode([
            "status" => "success",
            "mensaje" => "Medicamento registrado correctamente."
        ]);

        $stmt->close();

    } catch (mysqli_sql_exception $e) {

        // 🔥 AQUÍ ESTÁ LA CLAVE
        if ($e->getCode() == 1062) {
            echo json_encode([
                "status" => "error",
                "mensaje" => "⚠️ Nombre duplicado: ya existe un medicamento con ese nombre comercial."
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "mensaje" => "Error interno del servidor."
            ]);
        }
    }
}
?>
