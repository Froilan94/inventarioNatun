<?php
require "config/db.php";

$id = intval($_GET['id']);
$sql = "SELECT * FROM usuarios WHERE id_usuario = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

$data = $stmt->get_result()->fetch_assoc();

echo json_encode($data);
