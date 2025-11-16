<?php
include "config/db.php";      // aquí tienes $mysqli
include "funciones_stock.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha = $_POST['fecha_ingreso'];
    $medicamento_id = $_POST['medicamento_id'];
    $lote_id = $_POST['lote_id'];
    $cantidad = $_POST['cantidad'];
    $precio_unitario = $_POST['precio_unitario'];

    // Crear ingreso
    $sqlIngreso = "INSERT INTO ingresos_med (fecha_ingreso, total, recibido_por)
                   VALUES (?, 0, 1)";
    $stmt = $mysqli->prepare($sqlIngreso);
    $stmt->bind_param("s", $fecha);
    $stmt->execute();
    $ingreso_id = $mysqli->insert_id;
    $stmt->close();

    // Crear detalle
    $sqlDetalle = "INSERT INTO detalles_ingreso_med 
                   (ingreso_id, medicamento_id, lote_id, cantidad, precio_unitario)
                   VALUES (?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sqlDetalle);
    $stmt->bind_param("iiidd", $ingreso_id, $medicamento_id, $lote_id, $cantidad, $precio_unitario);
    $stmt->execute();
    $stmt->close();

    // Actualizar stock
    actualizarStockIngreso($mysqli, $medicamento_id, $lote_id, $cantidad);

    echo "<script>alert('Ingreso registrado exitosamente');</script>";
}
?>

<h2>Registrar Ingreso</h2>

<form method="POST">
    Fecha: <input type="date" name="fecha_ingreso" required><br>

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
        $res = $mysqli->query("SELECT id_lote_med, numero_lote FROM lotes_med");
        while ($row = $res->fetch_assoc()) {
            echo "<option value='{$row['id_lote_med']}'>{$row['numero_lote']}</option>";
        }
        ?>
    </select><br>

    Cantidad: <input type="number" step="0.01" name="cantidad" required><br>
    Precio Unitario: <input type="number" step="0.01" name="precio_unitario"><br>

    <button type="submit">Guardar</button>
</form>

