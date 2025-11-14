<?php
// config/db.php
// Conexión MySQL - ajustar según XAMPP
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = ''; // si usas contraseña, colócala aquí
$DB_NAME = 'control_inventarios';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    die("Error al conectar con la base de datos: " . $mysqli->connect_error);
}
$mysqli->set_charset("utf8mb4");
