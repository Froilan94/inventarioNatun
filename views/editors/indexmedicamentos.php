<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}
$role = $_SESSION['role_name'] ?? '';

if ($role !== 'med') {
    if (strpos($role, 'admin_super') !== false) header('Location: ../index.php');
    if (strpos($role, 'mp') !== false) header('Location: ../indexmateria_prima.php');
    if ($role === 'consultas_global') header('Location: ../consultas_global/dashboard_global.php');
}
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

        <!-- Submenú medicamentos anidado -->
        <div class="menu-item" style="padding-left:40px;" onclick="toggleMenu('medicamentos')">💊 Medicamentos </div>
        <div id="medicamentos" class="sub-submenu">
            <a onclick="mostrarSeccion('vistaMedicamentos')">Ver Medicamentos</a>
            <a onclick="mostrarSeccion('vistaRegistrarMedicamentos')">Ingresar Medicamentos</a>
        </div>

        <!-- Submenú Categorias anidado -->
        <div class="menu-item" style="padding-left:40px;" onclick="toggleMenu('categorias')"> 📦Categorias </div>
        <div id="categorias" class="sub-submenu">
            <a onclick="mostrarSeccion('vistaVerCategorias')">Ver Categorias</a>
            <a onclick="mostrarSeccion('vistaRegistrarCategorias')">Ingresar Categorias</a>
            <a onclick="mostrarSeccion('vistaActualizarEliminarCategorias')">Actualizar/eliminar Categorias</a>
        </div>

        <!-- Submenú unidades_de_medida anidado -->
        <div class="menu-item" style="padding-left:40px;" onclick="toggleMenu('unidades_de_medida')"> &#x1F4D0 Unidades de Medida </div>
        <div id="unidades_de_medida" class="sub-submenu">
            <a onclick="mostrarSeccion('vistaVerUnidades')">Ver Unidades de Medida</a>
            <a onclick="mostrarSeccion('vistaRegistrarUnidades')">Ingresar Unidades de Medida</a>
            <a onclick="mostrarSeccion('vistaActualizarEliminarUnidades')">Actualizar/eliminar Unidades de Medida</a>
        </div>

        <!-- Submenú Lote de Medicamentos anidado -->
        <div class="menu-item" style="padding-left:40px;" onclick="toggleMenu('lote_de_medicamentos')"> &#x1F4DD Lote de Medicamentos </div>
        <div id="lote_de_medicamentos" class="sub-submenu">
            <a onclick="mostrarSeccion('vistaVerLotedeMedicamentos')">Ver Lote de Medicamentos</a>
            <a onclick="mostrarSeccion('vistaRegistrarLotedeMedicamentos')">Ingresar Numero de Lotes</a>
            <a onclick="mostrarSeccion('vistaActualizarEliminarLotes')">Actualizar/eliminar Lotes</a>
        </div>

        <!-- Submenú Presentacion de medicinas anidado -->
        <div class="menu-item" style="padding-left:40px;" onclick="toggleMenu('presentaciones')"> &#x1F4C9 Presentacion de medicamentos </div>
        <div id="presentaciones" class="sub-submenu">
            <a onclick="mostrarSeccion('vistaVerPresentaciones')">Ver Presentacion de Medicamentos</a>
            <a onclick="mostrarSeccion('vistaRegistrarPresentaciones')">Ingresar Presentaciones</a>
            <a onclick="mostrarSeccion('vistaActualizarEliminarPresentaciones')">Actualizar/eliminar Presentaciones</a>
        </div>

        <!-- Submenú Proveedores anidado -->
        <div class="menu-item" style="padding-left:40px;" onclick="toggleMenu('proveedores')"> 🤵 Proveedores </div>
        <div id="proveedores" class="sub-submenu">
            <a onclick="mostrarSeccion('vistaVerProveedores')">Ver Proveedores</a>
            <a onclick="mostrarSeccion('vistaRegistrarProveedores')">Ingresar Proveedores</a>
            <a onclick="mostrarSeccion('vistaActualizarEliminarProveedores')">Actualizar/eliminar Proveedores</a>
        </div>

        <!-- Submenú Benericiarios anidado -->
        <div class="menu-item" style="padding-left:40px;" onclick="toggleMenu('beneficiarios')"> 🙍‍♀ Participante</div>
        <div id="beneficiarios" class="sub-submenu">
            <a onclick="mostrarSeccion('vistaVerBeneficiarios')">Ver Beneficiarios</a>
            <a onclick="mostrarSeccion('vistaRegistrarBeneficiarios')">Ingresar Beneficiarios</a>
            <a onclick="mostrarSeccion('vistaActualizarEliminarBeneficiarios')">Actualizar/eliminar Beneficiarios</a>
        </div>
    </div>

       <!-- MOVIMIENTOS -->
       <div class="menu-item" onclick="toggleMenu('movimientos')">📦 Movimientos</div>
       <div id="movimientos" class="submenu">
            <a onclick="mostrarSeccion('vistaRegistrarDocumentoMed')">Documentos</a>       
            <a onclick="mostrarSeccion('vistaRegistrarIngresoMed')">Entradas</a> 
            <a onclick="mostrarSeccion('vistaRegistrarBeneficiarios')">Salidas</a>
            <a onclick="mostrarSeccion('vistaActualizarEliminarBeneficiarios')">Ajustes</a>
        </div>

    <!-- REPORTES -->
    <div class="menu-item" onclick="toggleMenu('reportes')">📊 Reportes</div>
    <div id="reportes" class="submenu">
        <a onclick="mostrarSeccion('vistaVerUsuarios')">Existencias</a>
        <a onclick="mostrarSeccion('VistaReporteExistencias')">Movimientos</a>
        <a onclick="mostrarSeccion('VistaReporteExistencias')">Valorización</a>
    </div>

    <!-- SEGURIDAD VistaReporteExistencias
    <div class="menu-item" onclick="toggleMenu('seguridad')">🔐 Seguridad</div>
    <div id="seguridad" class="submenu">
        <a href="roles.php">Roles</a>
        <a href="permisos.php">Permisos</a>
    </div>-->

    <a class="menu-item" href="../../logout.php" style="background:#dc2626;">🚪 Cerrar sesión</a>
</div>

<div class="content">
    <h1>Bienvenido al Sistema de Inventarios Medicamentos</h1>
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


    <!-- ============================
         VER MEDICAMENTOS
    ===============================-->
<div id="vistaMedicamentos" class="seccion" style="display:none;">
    <h2>Lista de Medicamentos</h2>

    <button id="btnCargarMedicamentos" class="btn btn-primary mt-3">
        Cargar Medicamentos
    </button>

    <table class="table table-bordered table-striped mt-4">
        <thead class="table-dark">
            <tr>
                <th>No.</th>
                <th>Nombre Comercial</th>
                <th>Nombre Genérico</th>
                <th>Categoría</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody id="tablaMedicamentos"></tbody>
    </table>
</div>

    <!-- ============================
         REGISTRO DE MEDICAMENTOS
    ===============================-->
    <div id="vistaRegistrarMedicamentos" class="seccion" style="display:none;">
        <h2 class="d-flex justify-content-center">Registro de Medicamentos</h2>

        <form id="formRegistro_med" class="mt-4 col-md-6 mx-auto p-4 shadow-sm rounded bg-light">
            <div class="mb-3">
                <label class="form-label">Nombre Comercial</label>
                <input type="text" name="nombre_comercial" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Nombre Generico</label>
                <input type="text" name="nombre_generico" class="form-control" maxlength="10" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Categoria</label>
                <select name="categoria_id" class="form-select" required>
                    <option value="">Seleccione...</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Registrar Medicamento</button>
        </form>
    </div>

    <!-- ============================
         VER CATEGORIAS
    ===============================-->
    <div id="vistaVerCategorias" class="seccion" style="display:none;">
        <h2>Lista de Categorias</h2>
        <p>Aquí irá la tabla con los usuarios.</p>
    </div>

    <!-- ============================
         REGISTRO DE CATEGORIAS
    ===============================-->
    <div id="vistaRegistrarCategorias" class="seccion" style="display:none;">
        <h2>Registro de Categorias</h2>

        <form action="insert_categoria.php" method="POST" class="mt-4 col-md-8">

            <div class="mb-3">
                <label class="form-label">Nombre Categoría</label>
                <input type="text" name="nombre_categoria" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary">Registrar Usuario</button>
        </form>
    </div>

    <!-- ============================
         ELIMINAR CATEGORIAS
    ===============================-->
    <div id="vistaActualizarEliminarCategorias" class="seccion" style="display:none;">
        <h2>Actualizar/Eliminar Unidades de Medida</h2>
        <p>Aquí irá el formulario de eliminación.</p>
    </div>

    <!-- ============================
         VER UNIDAD DE MEDIDA
    ===============================-->
    <div id="vistaVerUnidades" class="seccion" style="display:none;">
        <h2>Lista de Unidades de Medida</h2>
        <p>Aquí irá la tabla con las Unidades de medida.</p>
    </div>

    <!-- ============================
         REGISTRO UNIDAD DE MEDIDA
    ===============================-->
    <div id="vistaRegistrarUnidades" class="seccion" style="display:none;">
        <h2>Registro de Unidades de Medida</h2>

        <form action="insert_unidad.php" method="POST" class="mt-4 col-md-8">

            <div class="mb-3">
                <label class="form-label">Nombre Unidad de Medida</label>
                <input type="text" name="nombre_unidad" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary">Registrar Unidad de Medida</button>
        </form>
    </div>

    <!-- ============================
         ELIMINAR UNIDAD DE MEDIDA
    ===============================-->
    <div id="vistaActualizarEliminarUnidades" class="seccion" style="display:none;">
        <h2>Actualizar/Eliminar Unidades de Medida</h2>
        <p>Aquí irá el formulario de eliminación.</p>
    </div>

    <!-- ============================
         VER LOTES DE MEDICAMENTOS
    ===============================-->
    <div id="vistaVerLotedeMedicamentos" class="seccion" style="display:none;">
        <h2>Lista de Lote de Medicamentos</h2>
        <p>Aquí irá la tabla con lotes de medicamentos.</p>
    </div>

    <!-- ============================
         REGISTRO LOTE DE MEDICAMENTOS
    ===============================-->
    <div id="vistaRegistrarLotedeMedicamentos" class="seccion" style="display:none;">
        <h2>Registro de Lotes de Medicamentos</h2>

        <form action="insert_lote.php" method="POST" class="mt-4 col-md-8">

            <div class="mb-3">
                <label class="form-label">Medicamento</label>
                <select name="medicamento_id" class="form-select">
                    <option value="">Seleccione...</option>
                    <option value="1">Aspirina</option>
                    <option value="2">Acetaminofen</option>
                    <option value="3">Loratadina</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Numero_lote</label>
                <input type="text" name="numero_lote" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Fecha de vencimiento</label>
                <input type="date" name="fecha_vencimiento" class="form-control" maxlength="10" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Cantidad Inicial</label>
                <input type="text" name="cantidad_inicial" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Cantidad Actual</label>
                <input type="text" name="cantidad_actual" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary">Registrar Lotes</button>
        </form>
    </div>

    <!-- ============================
         ELIMINAR LOTES
    ===============================-->
    <div id="vistaActualizarEliminarLotes" class="seccion" style="display:none;">
        <h2>Actualizar/Eliminar Lotes de Medicamentos</h2>
        <p>Aquí irá el formulario de eliminación.</p>
    </div>

    <!-- ============================
         VER PRESENTACION DE MEDICAMENTOS
    ===============================-->
    <div id="vistaVerPresentaciones" class="seccion" style="display:none;">
        <h2>Lista de Presentacion de medicamentos</h2>
        <p>Aquí irá la tabla con las Presentaciones.</p>
    </div>

    <!-- ============================
         REGISTRO PRESENTACION DE MEDICAMENTOS
    ===============================-->
    <div id="vistaRegistrarPresentaciones" class="seccion" style="display:none;">
        <h2>Registro de Presentacion de medicamentos</h2>

        <form action="insert_presentacion.php" method="POST" class="mt-4 col-md-8">

            <div class="mb-3">
                <label class="form-label">Nombre Presentacion de medicamentos</label>
                <input type="text" name="nombre_presentacion" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary">Registrar Presentaciones</button>
        </form>
    </div>

    <!-- ============================
         ELIMINAR PRESENTACION DE MEDICAMENTOS
    ===============================-->
    <div id="vistaActualizarEliminarPresentaciones" class="seccion" style="display:none;">
        <h2>Actualizar/Eliminar Presentaciones</h2>
        <p>Aquí irá el formulario de eliminación.</p>
    </div>

    <!-- ============================
         VER PROVEEDORES
    ===============================-->
    <div id="vistaVerProveedores" class="seccion" style="display:none;">
        <h2>Lista de Proveedores</h2>
        <p>Aquí irá la tabla con los Proveedores.</p>
    </div>

    <!-- ============================
         REGISTRO DE PROVEEDORES
    ===============================-->
    <div id="vistaRegistrarProveedores" class="seccion" style="display:none;">
        <h2>Registro de Proveedores</h2>

        <form action="insert_proveedor.php" method="POST" class="mt-4 col-md-8">

            <div class="mb-3">
                <label class="form-label">Nit Proveedor</label>
                <input type="text" name="nit_proveedor" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Nombre del Proveedor</label>
                <input type="text" name="nombre_proveedor" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Género</label>
                <select name="genero_proveedor" class="form-select" required>
                    <option value="">Seleccione...</option>
                    <option value="Masculino">Masculino</option>
                    <option value="Femimenino">Femenino</option>
                    <option value="Otros">Otros</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Tipo de Proveedor</label>
                <select name="tipo_proveedor" class="form-select" required>
                    <option value="">Seleccione...</option>
                    <option value="Fabricante">Fabricante</option>
                    <option value="Proveedor">Proveedor</option>
                    <option value="Donante">Donante</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Teléfono</label>
                <input type="text" name="telefono" class="form-control" maxlength="8">
            </div>

            <div class="mb-3">
                <label class="form-label">Direccion Domiciliar</label>
                <input type="text" name="direccion" class="form-control" >
            </div>

            <div class="mb-3">
                <label class="form-label">Correo</label>
                <input type="email" name="correo" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary">Registrar Proveedor</button>
        </form>
    </div>

    <!-- ============================
         ELIMINAR PROVEEDORES
    ===============================-->
    <div id="vistaActualizarEliminarProveedores" class="seccion" style="display:none;">
        <h2>Eliminar Proveedores</h2>
        <p>Aquí irá el formulario de eliminación.</p>
    </div>


    <!-- ============================
         VER BENEFICIARIO
    ===============================-->
    <div id="vistaVerBeneficiarios" class="seccion" style="display:none;">
        <h2>Lista de Beneficiarios</h2>
        <p>Aquí irá la tabla con los Beneficiarios.</p>
    </div>

    <!-- ============================
         REGISTRO DE BENEFICIARIO
    ===============================-->
    <div id="vistaRegistrarBeneficiarios" class="seccion" style="display:none;">
        <h2>Registro de Beneficiarios</h2>

        <form action="insert_beneficiario.php" method="POST" class="mt-4 col-md-8">

            <div class="mb-3">
                <label class="form-label">Nombre del Beneficiario</label>
                <input type="text" name="nombre_beneficiario" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">DPI del Beneficiario</label>
                <input type="text" name="dpi_beneficiario" class="form-control" maxlength="12">
            </div>

            <div class="mb-3">
                <label class="form-label">Direccion Domiciliar</label>
                <input type="text" name="direccion_beneficiario" class="form-control" >
            </div>

            <div class="mb-3">
                <label class="form-label">Teléfono del Beneficiario</label>
                <input type="text" name="telefono" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Género</label>
                <select name="genero_beneficiario" class="form-select" required>
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
                    <option value="1">Salud Y Nutrición</option>
                    <option value="2">Desarrollo Economico</option>
                    <option value="3">Operaciones</option>
                    <option value="4">Contabilidad</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Registrar Beneficiario</button>
        </form>
    </div>

    <!-- ============================
         ELIMINAR BENEFICIARIOS
    ===============================-->
    <div id="vistaActualizarEliminarBeneficiarios" class="seccion" style="display:none;">
        <h2>Eliminar Beneficiarios</h2>
        <p>Aquí irá el formulario de eliminación.</p>
    </div>
<?php include "../../config/db.php"; ?>

    <!-- ============================
         Registro Documento
    ===============================-->

<div id="vistaRegistrarDocumentoMed" class="seccion" style="display:none;">
    <h2>Registro de Documento</h2>

    <form action="insert_documento_med.php" method="POST" class="mt-4 col-md-8">

        <div class="mb-3">
            <label class="form-label">Tipo de documento</label>
            <select name="tipo_documento" class="form-select" required>
                <option value="">Seleccione...</option>
                <option value="Factura">Factura</option>
                <option value="Recibo_donacion">Recibo Donación</option>
                <option value="Cardex">Cardex</option>
                <option value="Acta">Acta</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Número documento</label>
            <input type="text" name="numero_documento" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Serie</label>
            <input type="text" name="serie_documento" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Registrar Documento</button>
    </form>
</div>

<div id="vistaRegistrarIngresoMed" class="seccion" style="display:none;">
    <h2>Registrar Ingreso de Medicamentos</h2>

    <form action="insertar_ingreso_med.php" method="POST" class="mt-4 col-md-10">

        <!-- =====================================
             DATOS GENERALES DEL INGRESO
        ====================================== -->
        <h4 class="mt-3">Datos del Ingreso</h4>

        <div class="mb-3">
            <label class="form-label">Tipo de documento</label>
            <select name="tipo_documento" class="form-select" required>
                <option value="">Seleccione...</option>
                <option value="Factura">Factura</option>
                <option value="Recibo_donacion">Recibo Donación</option>
                <option value="Cardex">Cardex</option>
                <option value="Acta">Acta</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Número documento</label>
            <input type="text" name="numero_documento" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Serie</label>
            <input type="text" name="serie_documento" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Fecha de ingreso</label>
            <input type="date" name="fecha_ingreso" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Proveedor</label>
            <select name="proveedor_id" class="form-select" required>
                <option value="">Seleccione...</option>
                <?php include "registromed/get_proveedores.php"; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Recibido por</label>
            <select name="recibido_por" class="form-select" required>
                <option value="">Seleccione...</option>
                <?php include "registromed/get_usuarios.php"; ?>
            </select>
        </div>

        <!-- =====================================
             DETALLES DEL INGRESO
        ====================================== -->
        <h4 class="mt-4">Detalles del Ingreso</h4>

        <table class="table table-bordered" id="tablaDetalles">
            <thead class="table-light">
                <tr>
                    <th>Medicamento</th>
                    <th>Lote</th>
                    <th>Cantidad</th>
                    <th>Unidad</th>
                    <th>Presentación</th>
                    <th>Precio Unitario</th>
                    <th>Subtotal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>

                <tr>
                    <td>
                        <select name="medicamento_id[]" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <?php include "registromed/get_medicamentos.php"; ?>
                        </select>
                    </td>

                    <td>
                        <select name="lote_id[]" class="form-select">
                            <option value="">Seleccione...</option>
                            <?php include "registromed/get_lotes.php"; ?>
                        </select>
                    </td>

                    <td>
                        <input type="number" step="0.0001" name="cantidad[]" class="form-control cantidad" required>
                    </td>

                    <td>
                        <select name="unidad_id[]" class="form-select">
                            <option value="">Seleccione...</option>
                            <?php include "registromed/get_unidades.php"; ?>
                        </select>
                    </td>

                    <td>
                        <select name="presentacion_id[]" class="form-select">
                            <option value="">Seleccione...</option>
                            <?php include "registromed/get_presentaciones.php"; ?>
                        </select>
                    </td>

                    <td>
                        <input type="number" step="0.0001" name="precio_unitario[]" class="form-control precio" required>
                    </td>

                    <td>
                        <input type="text" name="subtotal[]" class="form-control subtotal" readonly>
                    </td>

                    <td>
                        <button type="button" class="btn btn-danger btn-sm" onclick="eliminarFila(this)">X</button>
                    </td>
                </tr>

            </tbody>
        </table>

        <button type="button" class="btn btn-success" onclick="agregarFila()">Agregar detalle</button>

        <br><br>

        <button type="submit" class="btn btn-primary">Registrar Ingreso</button>
    </form>
</div>    
    <!-- Reporte Existencias -->
<!-- ================== REPORTE DE EXISTENCIAS ================== -->
<div id="VistaReporteExistencias" class="seccion" style="display:none;">
    
    <h2 class="mb-3">Reporte de Existencias</h2>

    <!-- ======= FILTROS ======= -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">Filtros de búsqueda</h5>

            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">Fecha inicio</label>
                    <input type="date" id="fechaInicio" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Fecha fin</label>
                    <input type="date" id="fechaFin" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Medicamento</label>
                    <select id="filtroMedicamento" class="form-control">
                        <option value="">Todos</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Proveedor / Donante</label>
                    <select id="filtroProveedor" class="form-control">
                        <option value="">Todos</option>
                    </select>
                </div>
            </div>

            <div class="mt-3 text-end">
                <button id="btnAplicarFiltros" class="btn btn-primary">
                    Aplicar filtros
                </button>
            </div>
        </div>
    </div>

    <!-- ======= BOTONES DE EXPORTACIÓN ======= -->
    <div class="d-flex justify-content-end mb-3">
        <button id="btnExportarPDF" class="btn btn-danger me-2">
            Exportar PDF
        </button>
        <button id="btnExportarExcel" class="btn btn-success">
            Exportar Excel
        </button>
    </div>

    <!-- ======= TABLA DE REPORTE ======= -->
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>No.</th>
                <th>Medicamento</th>
                <th>Nombre genérico</th>
                <th>Lote</th>
                <th>Fecha ingreso</th>
                <th>Fecha vencimiento</th>
                <th>Cantidad existente</th>
                <th>Valor unitario</th>
                <th>Monto existente</th>
                <th>Proveedor / Donante</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody id="tablaReporteExistencias">
            <!-- Contenido dinámico -->
        </tbody>
    </table>

</div>
<!-- ================== FIN REPORTE DE EXISTENCIAS ================== -->


<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../../js/funciones.js"></script>
<script src="../../js/script.js"></script>
<script src="../../js/reporte_existencias.js"></script>

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
