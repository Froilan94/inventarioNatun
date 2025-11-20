<?php
require "config/db.php";

// Iniciar transacción
$mysqli->begin_transaction();

try {

    // 1️⃣ Insertar documento (si aplica)
    $tipo = $_POST['tipo_documento'];

    $documento_id = null;

    if ($tipo != "") {

        $stmt = $mysqli->prepare("
            INSERT INTO documentos_med (tipo_documento, numero_documento, serie_documento) 
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("sss", 
            $_POST["tipo_documento"],
            $_POST["numero_documento"],
            $_POST["serie_documento"]
        );
        $stmt->execute();
        $documento_id = $stmt->insert_id;
    }

    // 2️⃣ Insertar ingreso
    $stmt = $mysqli->prepare("
        INSERT INTO ingresos_med (documento_id, proveedor_id, fecha_ingreso, recibido_por)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("iiss",
        $documento_id,
        $_POST["proveedor_id"],
        $_POST["fecha_ingreso"],
        $_POST["recibido_por"]
    );
    $stmt->execute();

    $ingreso_id = $stmt->insert_id;

    // 3️⃣ Insertar detalles (varios)
    $cantidades = $_POST["cantidad"];
    $meds       = $_POST["medicamento_id"];
    $lotes      = $_POST["lote_id"];
    $unidades   = $_POST["unidad_id"];
    $present    = $_POST["presentacion_id"];
    $precios    = $_POST["precio_unitario"];
    $subtotales = $_POST["subtotal"];

    $stmt = $mysqli->prepare("
        INSERT INTO detalles_ingreso_med 
        (ingreso_id, medicamento_id, lote_id, cantidad, unidad_id, presentacion_id, precio_unitario, subtotal)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    for ($i = 0; $i < count($cantidades); $i++) {
        $stmt->bind_param("iiidiiid",
            $ingreso_id,
            $meds[$i],
            $lotes[$i],
            $cantidades[$i],
            $unidades[$i],
            $present[$i],
            $precios[$i],
            $subtotales[$i]
        );
        $stmt->execute();
    }

    // Todo bien 👍
    $mysqli->commit();
    header("Location: ingresos_med.php?exito=1");

} catch (Exception $e) {

    // Algo falló ❌
    $mysqli->rollback();
    die("Error: " . $e->getMessage());
}
?>
