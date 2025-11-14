<?php
// index.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$role = $_SESSION['role_name'] ?? '';

if ($role !== 'admin_super') {
    // si no es admin, lo mandamos a su dashboard según rol
    if (strpos($role, 'mp') !== false) header('Location: mod_mp/dashboard_mp.php');
    if (strpos($role, 'med') !== false) header('Location: mod_med/dashboard_med.php');
    if ($role === 'consultas_global') header('Location: consultas_global/dashboard_global.php');
}

//include 'includes/header.php';
//include 'includes/navbar.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema de Inventarios</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f4f4;
        }

        .sidebar {
            width: 240px;
            height: 100vh;
            background: #1e3a8a;
            color: white;
            position: fixed;
            padding-top: 20px;
            overflow-y: auto;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 22px;
        }

        .menu-item {
            padding: 14px 20px;
            display: block;
            color: white;
            text-decoration: none;
            cursor: pointer;
            transition: 0.3s;
        }

        .menu-item:hover {
            background: #314fb1;
        }

        .submenu {
            background: #2a46a3;
            display: none;
        }

        .submenu a {
            display: block;
            padding: 12px 40px;
            font-size: 14px;
            color: white;
            text-decoration: none;
            cursor: pointer;
        }

        .submenu a:hover {
            background: #3b5cd3;
        }

        /* Submenú "usuarios" dentro de Maestros */
        .sub-submenu {
            padding-left: 30px;
            display: none;
            background: #334fb6;
        }

        .content {
            margin-left: 240px;
            padding: 25px;
        }

        h1 {
            color: #333;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Inventarios</h2>

    <div class="menu-item" onclick="toggleMenu('dashboard')">🏠 Dashboard</div>

    <!-- MAESTROS -->
    <div class="menu-item" onclick="toggleMenu('maestros')">📘 Maestros</div>
    <div id="maestros" class="submenu">

        <!-- Submenú usuarios anidado -->
        <div class="menu-item" style="padding-left:40px;" onclick="toggleMenu('usuarios')">👤 Usuarios</div>
        <div id="usuarios" class="sub-submenu">
            <a onclick="mostrarSeccion('vistaVerUsuarios')">Ver Usuarios</a>
            <a onclick="mostrarSeccion('vistaRegistrar')">Ingresar Usuarios</a>
            <a onclick="mostrarSeccion('vistaEliminar')">Eliminar Usuarios</a>
        </div>

        <a href="Medicamentos.php">Medicamentos</a>
        <a href="categorias.php">Categorías</a>
        <a href="Unidad_de_Medida.php">Unidad de Medida</a>
        <a href="lotes.php">Lotes de Medicamentos</a>
        <a href="Presentaciónes.php">Presentaciones</a>
        <a href="proveedores.php">Proveedores</a>
        <a href="beneficiarios.php">Beneficiarias</a>
    </div>

    <!-- MOVIMIENTOS -->
    <div class="menu-item" onclick="toggleMenu('movimientos')">📦 Movimientos</div>
    <div id="movimientos" class="submenu">
        <a href="entradas.php">Entradas</a>
        <a href="salidas.php">Salidas</a>
        <a href="ajustes.php">Ajustes</a>
    </div>

    <!-- REPORTES -->
    <div class="menu-item" onclick="toggleMenu('reportes')">📊 Reportes</div>
    <div id="reportes" class="submenu">
        <a href="reporte_existencias.php">Existencias</a>
        <a href="reporte_movimientos.php">Movimientos</a>
        <a href="reporte_valorizacion.php">Valorización</a>
    </div>

    <!-- SEGURIDAD -->
    <div class="menu-item" onclick="toggleMenu('seguridad')">🔐 Seguridad</div>
    <div id="seguridad" class="submenu">
        <a href="roles.php">Roles</a>
        <a href="permisos.php">Permisos</a>
    </div>

    <a class="menu-item" href="logout.php" style="background:#dc2626;">🚪 Cerrar sesión</a>
</div>

<div class="content">
    <h1>Bienvenido al Sistema de Inventarios</h1>
    <p>Seleccione una opción del menú para comenzar.</p>
</div>
<!-- Contenido principal -->
<div class="content">

    <!-- ============================
         VER USUARIOS
    ===============================-->
    <div id="vistaVerUsuarios" class="seccion" style="display:none;">
        <h2>Lista de Usuarios</h2>
        <p>Aquí irá la tabla con los usuarios.</p>
    </div>

    <!-- ============================
         REGISTRO DE USUARIOS
    ===============================-->
    <div id="vistaRegistrar" class="seccion" style="display:none;">
        <h2>Registro de Usuarios</h2>

        <form action="insert_usuario.php" method="POST" class="mt-4 col-md-8">

            <div class="mb-3">
                <label class="form-label">Nombre Completo</label>
                <input type="text" name="nombre_completo" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Nombre de Usuario</label>
                <input type="text" name="nombre_usuario" class="form-control" maxlength="10" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Correo</label>
                <input type="email" name="correo" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Teléfono</label>
                <input type="text" name="telefono" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Rol</label>
                <select name="rol_id" class="form-select" required>
                    <option value="">Seleccione...</option>
                    <option value="1">Med_Operador</option>
                    <option value="2">Med_Supervisor</option>
                    <option value="3">MP_Operador</option>
		    <option value="4">MP_Supervisor</option>
                    <option value="5">Supervisor_General</option>
                    <option value="6">Super_usuario</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">DPI</label>
                <input type="text" name="dpi_usuario" class="form-control" maxlength="12">
            </div>

            <div class="mb-3">
                <label class="form-label">Género</label>
                <select name="genero_usuario" class="form-select" required>
                    <option value="">Seleccione...</option>
                    <option value="Masculino">Masculino</option>
                    <option value="Femimenino">Femenino</option>
                    <option value="Otros">Otros</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Departamento</label>
                <select name="departamento_id" class="form-select">
                    <option value="">Seleccione...</option>
                    <option value="1">Salud Y Nutrición</option>
                    <option value="2">Desarrollo Economico</option>
                    <option value="3">Operaciones</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Registrar Usuario</button>
        </form>
    </div>

    <!-- ============================
         ELIMINAR USUARIOS
    ===============================-->
    <div id="vistaEliminar" class="seccion" style="display:none;">
        <h2>Eliminar Usuarios</h2>
        <p>Aquí irá el formulario de eliminación.</p>
    </div>

</div>


<script>
    function toggleMenu(id) {
        let submenu = document.getElementById(id);
        submenu.style.display = submenu.style.display === "block" ? "none" : "block";
    }
</script>


<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function mostrarSeccion(id) {
            // Ocultar todas
            document.querySelectorAll(".seccion").forEach(s => s.style.display = "none");

            // Mostrar la sección seleccionada
            document.getElementById(id).style.display = "block";
        }
    </script>
</body>
</html>

