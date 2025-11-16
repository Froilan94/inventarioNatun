<?php
session_start();
require_once "config/db.php";

if (!isset($_SESSION['role_name']) || $_SESSION['role_name'] !== 'admin_super') {
    http_response_code(403);
    exit("No tiene permisos para realizar esta acción.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $medicamento_id    = intval($_POST["medicamento_id"] ?? 0);
    $numero_lote       = trim($_POST["numero_lote"] ?? '');
    $fecha_vencimiento = trim($_POST["fecha_vencimiento"] ?? '');
    $cantidad_inicial  = intval($_POST["cantidad_inicial"] ?? 0);
    $cantidad_final    = intval($_POST["cantidad_final"] ?? 0);

    $errores = [];

    if ($medicamento_id <= 0) $errores[] = "Debe seleccionar un medicamento.";
    if ($numero_lote === '') $errores[] = "El número de lote es obligatorio.";
    if ($fecha_vencimiento === '') $errores[] = "Debe ingresar la fecha de vencimiento.";

    if (!empty($errores)) {
        echo json_encode(["status" => "error", "errores" => $errores]);
        exit;
    }

    try {
        $sql = "INSERT INTO lotes_med 
                (medicamento_id, numero_lote, fecha_vencimiento, cantidad_inicial, cantidad_final)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("issii", $medicamento_id, $numero_lote, $fecha_vencimiento, $cantidad_inicial, $cantidad_final);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "mensaje" => "Lote registrado correctamente."]);
        } else {
            echo json_encode(["status" => "error", "mensaje" => $stmt->error]);
        }

        $stmt->close();

    } catch (Exception $e) {
        echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
    }
}
?>
