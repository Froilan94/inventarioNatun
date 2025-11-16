<?php
include "config/db.php";
include "funciones_stock.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha = $_POST['fecha_salida'];
    $medicamento_id = $_POST['medicamento_id'];
    $lote_id = $_POST['lote_id'];
    $cantidad = $_POST['cantidad'];
    $precio_unitario = $_POST['precio_unitario'];

    // Crear salida
    $sqlSalida = "INSERT INTO salidas_med (fecha_salida, total, entregado_por)
                  VALUES (?, 0, 1)";
    $stmt = $mysqli->prepare($sqlSalida);
    $stmt->bind_param("s", $fecha);
    $stmt->execute();
    $salida_id = $mysqli->insert_id;
    $stmt->close();

    // Crear detalle
    $sqlDetalle = "INSERT INTO detalles_salida_med 
                   (salida_id, medicamento_id, lote_id, cantidad, precio_unitario)
                   VALUES (?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sqlDetalle);
    $stmt->bind_param("iiidd", $salida_id, $medicamento_id, $lote_id, $cantidad, $precio_unitario);
    $stmt->execute();
    $stmt->close();

    // Actualizar stock
    $respuesta = actualizarStockSalida($mysqli, $medicamento_id, $lote_id, $cantidad);

    if ($respuesta === "OK") {
        echo "<script>alert('Salida registrada exitosamente');</script>";
    } else {
        echo "<script>alert('$respuesta');</script>";
    }
}
?>

<h2>Registrar Salida</h2>

<form method="POST">
    Fecha: <input type="date" name="fecha_salida" required><br>

    Medicamento:
    <select name="medicamento_id" required>
        <?php
        $res = $mysqli->query("SELECT id_medicamento, nombre FROM medicamentos");
        while ($row = $res->fetch_assoc()) {
            echo "<option value='{$row['id_medicamento']}'>{$row['nombre']}</option>";
        }
        ?>
    </select><br>

    Lote:
    <select name="lote_id" required>
        <?php
        $res = $mysqli->query("SELECT id_lote_med, numero_lote, cantidad_actual 
                               FROM lotes_med ORDER BY fecha_vencimiento ASC");
        while ($row = $res->fetch_assoc()) {
            echo "<option value='{$row['id_lote_med']}'>
                    {$row['numero_lote']} — Disponible: {$row['cantidad_actual']}
                  </option>";
        }
        ?>
    </select><br>

    Cantidad: <input type="number" step="0.01" name="cantidad" required><br>
    Precio Unitario: <input type="number" step="0.01" name="precio_unitario"><br>

    <button type="submit">Guardar</button>
</form>

