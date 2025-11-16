<?php
// funciones_stock.php

function actualizarStockIngreso($mysqli, $medicamento_id, $lote_id, $cantidad) {

    // Actualizar lote
    $sqlLote = "UPDATE lotes_med 
                SET cantidad_actual = cantidad_actual + ? 
                WHERE id_lote_med = ?";
    $stmt = $mysqli->prepare($sqlLote);
    $stmt->bind_param("di", $cantidad, $lote_id);
    $stmt->execute();
    $stmt->close();

    // Actualizar stock general
    $sqlStock = "INSERT INTO stock_med (medicamento_id, cantidad)
                 VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE cantidad = cantidad + VALUES(cantidad)";
    $stmt = $mysqli->prepare($sqlStock);
    $stmt->bind_param("id", $medicamento_id, $cantidad);
    $stmt->execute();
    $stmt->close();
}

function actualizarStockSalida($mysqli, $medicamento_id, $lote_id, $cantidad) {

    // Validar disponibilidad en lote
    $sqlCheck = "SELECT cantidad_actual FROM lotes_med WHERE id_lote_med = ?";
    $stmt = $mysqli->prepare($sqlCheck);
    $stmt->bind_param("i", $lote_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($result['cantidad_actual'] < $cantidad) {
        return "ERROR: No hay stock suficiente en el lote.";
    }

    // Descontar del lote
    $sqlLote = "UPDATE lotes_med 
                SET cantidad_actual = cantidad_actual - ? 
                WHERE id_lote_med = ?";
    $stmt = $mysqli->prepare($sqlLote);
    $stmt->bind_param("di", $cantidad, $lote_id);
    $stmt->execute();
    $stmt->close();

    // Descontar del stock general
    $sqlStock = "UPDATE stock_med 
                 SET cantidad = cantidad - ?
                 WHERE medicamento_id = ?";
    $stmt = $mysqli->prepare($sqlStock);
    $stmt->bind_param("di", $cantidad, $medicamento_id);
    $stmt->execute();
    $stmt->close();

    return "OK";
}
?>
