<?php
include "../../../config/db.php";

$sql = "
    SELECT 
        m.id_medicamento,
        m.nombre_comercial,
        m.nombre_generico,
        c.nombre_categoria,
        m.activo
    FROM medicamentos m
    LEFT JOIN categorias_med c 
        ON c.id_categoria_med = m.categoria_id
    ORDER BY m.id_medicamento ASC
";

$result = $mysqli->query($sql);

$medicamentos = [];

while ($row = $result->fetch_assoc()) {
    $medicamentos[] = $row;
}

echo json_encode($medicamentos);

