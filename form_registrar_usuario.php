<?php
require_once "../config/db.php";

// Obtener roles
$roles = $conn->query("SELECT id_rol, nombre_rol FROM roles ORDER BY nombre_rol");

// Obtener departamentos
$departamentos = $conn->query("SELECT id_departamento, nombre_departamento FROM departamentos ORDER BY nombre_departamento");
?>

<form id="formUsuario" method="POST" action="insert_usuario.php">

    <label>Nombre completo</label>
    <input type="text" name="nombre_completo" required>

    <label>Nombre de usuario</label>
    <input type="text" name="nombre_usuario" maxlength="10" required>

    <label>Correo</label>
    <input type="email" name="correo">

    <label>Teléfono</label>
    <input type="text" name="telefono">

    <label>DPI</label>
    <input type="text" name="dpi_usuario" maxlength="12">

    <label>Género</label>
    <select name="genero_usuario" required>
        <option value="">Seleccione...</option>
        <option value="Masculino">Masculino</option>
        <option value="Femenino">Femenino</option>
        <option value="Otros">Otros</option>
    </select>

    <label>Rol</label>
    <select name="rol_id" required>
        <option value="">Seleccione un rol</option>
        <?php while ($r = $roles->fetch_assoc()): ?>
            <option value="<?= $r['id_rol'] ?>"><?= $r['nombre_rol'] ?></option>
        <?php endwhile; ?>
    </select>

    <label>Departamento</label>
    <select name="departamento_id">
        <option value="">Seleccione...</option>
        <?php while ($d = $departamentos->fetch_assoc()): ?>
            <option value="<?= $d['id_departamento'] ?>"><?= $d['nombre_departamento'] ?></option>
        <?php endwhile; ?>
    </select>

    <label>Contraseña</label>
    <input type="password" name="password" required>

    <button type="submit">Registrar</button>
</form>
