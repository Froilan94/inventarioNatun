<?php
require_once '../../auth/roles.php';

requireRoles([
    'admin_super'
]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema de Inventarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../../styles/style.css">
</head>
<body>

<div class="sidebar">   
    <h2>Inventario</h2>

<div class="menu-item" onclick="window.location.href='../../index.php'">🗄️ Ir al Panel principal</div>

    <!-- MAESTROS -->
    <div class="menu-item" onclick="toggleMenu('maestros')">📘 Maestros</div>
    <div id="maestros" class="submenu">

        <!-- Submenú usuarios anidado -->
        <div class="menu-item" style="padding-left:40px;" onclick="toggleMenu('usuarios')">👤 Usuarios</div>
        <div id="usuarios" class="sub-submenu">
            <a onclick="mostrarSeccion('vistaVerUsuarios')">Ver Usuarios</a>
            <a onclick="mostrarSeccion('vistaRegistrar')">Ingresar Usuarios</a>
        </div>

        <!-- Submenú Departamentos anidado -->
        <div class="menu-item" style="padding-left:40px;" onclick="toggleMenu('programas')">📋 Programas </div>
        <div id="programas" class="sub-submenu">
            <a onclick="mostrarSeccion('vistaVerProgramas')">Ver Programas</a>
            <a onclick="mostrarSeccion('vistaRegistrarProgramas')">Ingresar Programas</a>
        </div>

        <!-- Submenú Categorias anidado -->
        <div class="menu-item" style="padding-left:40px;" onclick="toggleMenu('comunidades')"> 🤝👥Comunidades </div>
        <div id="comunidades" class="sub-submenu">
            <a onclick="mostrarSeccion('vistaVerComunidades')">Ver Comunidades</a>
            <a onclick="mostrarSeccion('vistaRegistrarComunidades')">Ingresar Categorias</a>
        </div>
    </div>
    <a class="menu-item" href="../../logout.php" style="background:#dc2626;">🚪 Cerrar sesión</a>
</div>

<div class="content">
    <h1>Bienvenido al Sistema de Inventarios Medicamentos</h1>

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
                <input type="text" name="telefono" class="form-control" maxlength="8">
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

<!-- ============================
     VER COMUNIDADES
==============================-->
<div id="vistaVerComunidades" class="seccion" style="display:none;">
    <h2>Lista de Comunidades</h2>

    <button id="btnCargarComunidades" class="btn btn-primary mt-3">Cargar Comunidades</button>

    <table class="table table-bordered table-striped mt-4">
        <thead class="table-dark">
            <tr>
                <th>No.</th>
                <th>Nombre</th>
                <th>Dirección</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="tablaComunidades"></tbody>
    </table>
</div>

<!-- ============================
     REGISTRAR COMUNIDAD
==============================-->
<div id="vistaRegistrarComunidades" class="seccion" style="display:none;">
    <h2 class="d-flex justify-content-center">Registrar Comunidad</h2>

    <form id="formComunidad" class="mt-4 col-md-6 mx-auto p-4 shadow-sm rounded bg-light">

        <div class="mb-3">
            <label class="form-label">Nombre de la Comunidad</label>
            <input type="text" name="nombre_comunidad" class="form-control" maxlength="75" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Dirección</label>
            <textarea name="direccion" class="form-control" rows="3"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Registrar Comunidad</button>
        <div id="mensajeComunidad" style="margin-top:10px;"></div>
    </form>
</div>

<!-- MODAL EDITAR COMUNIDAD -->
<div class="modal fade" id="modalEditarComunidad" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Comunidad</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarComunidad">
                    <input type="hidden" name="id_comunidad" id="editCom_id">

                    <div class="mb-3">
                        <label class="form-label">Nombre de la Comunidad</label>
                        <input type="text" name="nombre_comunidad" id="editCom_nombre"
                               class="form-control" maxlength="75" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Dirección</label>
                        <textarea name="direccion" id="editCom_direccion"
                                  class="form-control" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="submit" form="formEditarComunidad" class="btn btn-success"
                        id="btnGuardarComunidad">
                    <span id="textoGuardarCom">Guardar Cambios</span>
                    <span id="spinnerGuardarCom" class="spinner-border spinner-border-sm d-none"></span>
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<!-- ============================
     VER PROGRAMAS
==============================-->
<div id="vistaVerProgramas" class="seccion" style="display:none;">
    <h2>Lista de Programas</h2>

    <button id="btnCargarProgramas" class="btn btn-primary mt-3">Cargar Programas</button>

    <table class="table table-bordered table-striped mt-4">
        <thead class="table-dark">
            <tr>
                <th>No.</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="tablaProgramas"></tbody>
    </table>
</div>

<!-- ============================
     REGISTRAR PROGRAMA
==============================-->
<div id="vistaRegistrarProgramas" class="seccion" style="display:none;">
    <h2 class="d-flex justify-content-center">Registrar Programa</h2>

    <form id="formPrograma" class="mt-4 col-md-6 mx-auto p-4 shadow-sm rounded bg-light">

        <div class="mb-3">
            <label class="form-label">Nombre del Programa</label>
            <input type="text" name="nombre_programa" class="form-control" maxlength="20" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" class="form-control" rows="3"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Registrar Programa</button>
        <div id="mensajePrograma" style="margin-top:10px;"></div>
    </form>
</div>

<!-- MODAL EDITAR PROGRAMA -->
<div class="modal fade" id="modalEditarPrograma" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Programa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarPrograma">
                    <input type="hidden" name="id_programa" id="editProg_id">

                    <div class="mb-3">
                        <label class="form-label">Nombre del Programa</label>
                        <input type="text" name="nombre_programa" id="editProg_nombre"
                               class="form-control" maxlength="20" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" id="editProg_descripcion"
                                  class="form-control" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="submit" form="formEditarPrograma" class="btn btn-success"
                        id="btnGuardarPrograma">
                    <span id="textoGuardarProg">Guardar Cambios</span>
                    <span id="spinnerGuardarProg" class="spinner-border spinner-border-sm d-none"></span>
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../../js/usuarios/core_admin.js"></script>
<script src="../../js/usuarios/us_ad.js"></script>
<script src="../../js/usuarios/comunidades_programas.js"></script>

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
