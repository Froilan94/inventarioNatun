<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$role = $_SESSION['role_name'] ?? '';

if ($role !== 'admin_super') {
    if (strpos($role, 'mp') !== false) header('Location: indexmateria_prima.php');
    if (strpos($role, 'med') !== false) header('Location: index_medicamentos.php');
    if ($role === 'consultas_global') header('Location: consultas_global/dashboard_global.php');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema de Inventarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="styles/style.css">
</head>
<body>

<div class="sidebar">   
    <h2>Inventario</h2>

<div class="menu-item" onclick="window.location.href='index.php'">🗄️ Ir al Panel principal</div>

    <!-- MAESTROS -->
    <div class="menu-item" onclick="toggleMenu('maestros')">📘 Maestros</div>
    <div id="maestros" class="submenu">

        <!-- Submenú usuarios anidado -->
        <div class="menu-item" style="padding-left:40px;" onclick="toggleMenu('usuarios')">👤 Usuarios</div>
        <div id="usuarios" class="sub-submenu">
            <a onclick="mostrarSeccion('vistaVerUsuarios')">Ver Usuarios</a>
            <a onclick="mostrarSeccion('vistaRegistrar')">Ingresar Usuarios</a>
        </div>

    <a class="menu-item" href="logout.php" style="background:#dc2626;">🚪 Cerrar sesión</a>
</div>

<div class="content">
    <h1>Gestion de Usurios y roles</h1>
    <!--<p>Seleccione una opción del menú para comenzar.</p>-->
</div>
<!-- Contenido principal -->
<div class="content">

    <!-- ============================
         VER USUARIOS
    ===============================-->
<div id="vistaVerUsuarios" class="seccion" style="display:none;">
    <h2>Lista de Usuarios</h2>

    <button id="btnCargarUsuarios" class="btn btn-primary mt-3">Cargar usuarios</button>

    <table class="table table-bordered table-striped mt-4">
        <thead class="table-dark">
            <tr>
                <th>No.</th>
                <th>Nombre completo</th>
                <th>Usuario</th>
                <th>Correo</th>
                <th>Teléfono</th>
                <th>DPI</th>
                <th>Género</th>
                <th>Estado</th>
                <th>Acciones</th>  <!--Acá aparecerán las acciones del js para editar y eliminar-->                
            </tr>
        </thead>
        <tbody id="tablaUsuarios"></tbody>
    </table>
</div>
    <!-- ============================
         REGISTRO DE USUARIOS
    ===============================-->
    <div id="vistaRegistrar" class="seccion" style="display:none;">
        <h2 class="d-flex justify-content-center">Registro de Usuarios</h2>

        <form id="formRegistro" class="mt-4 col-md-6 mx-auto p-4 shadow-sm rounded bg-light">

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
                    <option value="Femenino">Femenino</option>
                    <option value="Otros">Otros</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Departamento</label>
                <select name="departamento_id" class="form-select">
                    <option value="">Seleccione...</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Registrar Usuario</button>
           <div id="mensaje"style="margin-top:10px;"></div>
        </form>
    </div>

<!-- MODAL EDITAR USUARIO-->
<div class="modal fade" id="modalEditarUsuario" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Editar Usuario</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <form id="formEditarUsuario">

            <input type="hidden" name="id_usuario" id="edit_id_usuario">

            <div class="mb-3">
                <label class="form-label">Nombre Completo</label>
                <input type="text" name="nombre_completo" id="edit_nombre_completo" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Usuario</label>
                <input type="text" name="nombre_usuario" id="edit_nombre_usuario" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Correo</label>
                <input type="email" name="correo" id="edit_correo" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Teléfono</label>
                <input type="text" name="telefono" id="edit_telefono" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">DPI</label>
                <input type="text" name="dpi_usuario" id="edit_dpi_usuario" class="form-control" maxlength="12">
            </div>

            <div class="mb-3">
                <label class="form-label">Género</label>
                <select name="genero_usuario" id="edit_genero_usuario" class="form-control">
                    <option value="Masculino">Masculino</option>
                    <option value="Femenino">Femenino</option>
                    <option value="Otros">Otros</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Rol</label>
                <select name="rol_id" id="edit_rol_id" class="form-control"></select>
            </div>

            <div class="mb-3">
                <label class="form-label">Departamento</label>
                <select name="departamento_id" id="edit_departamento_id" class="form-control"></select>
            </div>

            <div class="mb-3">
                <label class="form-label">Estado</label>
                <select name="activo" id="edit_activo" class="form-control">
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>

        </form>
      </div>

      <div class="modal-footer">
        <button type="submit" form="formEditarUsuario" class="btn btn-success" id="btnGuardarCambios">
        <span id="textoGuardar">Guardar Cambios</span>
        <span id="spinnerGuardar" class="spinner-border spinner-border-sm d-none"></span></button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>

    </div>
  </div>
</div>


<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="js/funciones.js"></script>
<script src="js/script.js"></script>
<script src="js/reporte_existencias.js"></script>

<!-- Contenedor de Toasts -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999">
    <!-- Toast de Éxito -->
    <div id="toastExito" class="toast align-items-center text-bg-success border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                ✅ <span id="mensajeExito">Operación exitosa</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
    <!-- Toast de Error -->
    <div id="toastError" class="toast align-items-center text-bg-danger border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                ❌ <span id="mensajeError">Error en la operación</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>

    <!-- Toast de Advertencia -->
    <div id="toastWarning" class="toast align-items-center text-bg-warning border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                ⚠️ <span id="mensajeWarning">Advertencia</span>
            </div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>

    <!-- Toast de Info -->
    <div id="toastInfo" class="toast align-items-center text-bg-info border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body text-white">
                ℹ️ <span id="mensajeInfo">Información</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
</body>
</html>
