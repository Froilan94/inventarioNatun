<?php
// index.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$role = $_SESSION['role_name'] ?? '';

if ($role !== 'admin_super') {
    // si no es admin, lo mandamos a su dashboard el rol de cada usuario, esto para mantener la seguridad de la info.
    if (strpos($role, 'mp') !== false) header('Location: mod_mp/dashboard_mp.php');
    if (strpos($role, 'med') !== false) header('Location: mod_med/dashboard_med.php');
    if ($role === 'consultas_global') header('Location: consultas_global/dashboard_global.php');
}
//include ("consulta_usuarios.php")
// Consultar roles
//$roles = $mysqli->query("SELECT id_rol, nombre_rol FROM roles ORDER BY nombre_rol");

// Consultar departamentos
//$departamentos = $mysqli->query("SELECT id_departamento, nombre_departamento FROM departamentos ORDER //BY nombre_departamento");
//include 'includes/header.php';
//include 'includes/navbar.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema de Inventarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style/style.css">
</head>
<body>

<div class="sidebar">
    <h2>Inventarios</h2>

<div class="menu-item" onclick="window.location.href='index.php'">🗄️ Panel principal</div>

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

        <!-- Submenú medicamentos anidado -->
        <div class="menu-item" style="padding-left:40px;" onclick="toggleMenu('medicamentos')">💊 Medicamentos </div>
        <div id="medicamentos" class="sub-submenu">
            <a onclick="mostrarSeccion('vistaVerMedicamentos')">Ver Medicamentos</a>
            <a onclick="mostrarSeccion('vistaRegistrarMedicamentos')">Ingresar Medicamentos</a>
            <a onclick="mostrarSeccion('vistaActualizarEliminarRegistros')">Actualizar/eliminar Registros</a>
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
        <div class="menu-item" style="padding-left:40px;" onclick="toggleMenu('beneficiarios')"> 🙍‍♀ Beneficiarios </div>
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
        <a href="reporte_existencias.php">Existencias</a>
        <a href="reporte_movimientos.php">Movimientos</a>
        <a href="reporte_valorizacion.php">Valorización</a>
    </div>

    <!-- SEGURIDAD 
    <div class="menu-item" onclick="toggleMenu('seguridad')">🔐 Seguridad</div>
    <div id="seguridad" class="submenu">
        <a href="roles.php">Roles</a>
        <a href="permisos.php">Permisos</a>
    </div>-->

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
            </tr>
        </thead>
        <tbody id="tablaUsuarios"></tbody>
    </table>
</div>
    <!-- ============================
         REGISTRO DE USUARIOS
    ===============================-->
    <div id="vistaRegistrar" class="seccion" style="display:none;">
        <h2>Registro de Usuarios</h2>

        <form id="formRegistro" class="mt-4 col-md-8">

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
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Registrar Usuario</button>
           <div id="mensaje"style="margin-top:10px;"></div>
        </form>
    </div>

    <!-- ============================
         ELIMINAR USUARIOS
    ===============================-->
    <div id="vistaEliminar" class="seccion" style="display:none;">
        <h2>Eliminar Usuarios</h2>
        <p>Aquí irá el formulario de eliminación.</p>
    </div>

    <!-- ============================
         VER MEDICAMENTOS
    ===============================-->
    <div id="vistaVerMedicamentos" class="seccion" style="display:none;">
        <h2>Lista de Medicamentos</h2>
        <p>Aquí irá la tabla con los usuarios.</p>
    </div>

    <!-- ============================
         REGISTRO DE MEDICAMENTOS
    ===============================-->
    <div id="vistaRegistrarMedicamentos" class="seccion" style="display:none;">
        <h2>Registro de Medicamentos</h2>

        <form action="insert_medicamento.php" method="POST" class="mt-4 col-md-8">

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
                    <option value="1">Generico</option>
                    <option value="2">Originales</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Registrar Medicamento</button>
        </form>
    </div>

    <!-- ============================
         ELIMINAR REGISTRO MEDICAMENTOS
    ===============================-->
    <div id="vistaActualizarEliminarRegistros" class="seccion" style="display:none;">
        <h2>Eliminar Medicamento</h2>
        <p>Aquí irá el formulario de eliminación.</p>
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
                <label class="form-label">Cantidad Final</label>
                <input type="text" name="cantidad_final" class="form-control">
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
                <input type="text" name="telefono" class="form-control">
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
<?php include "config/db.php"; ?>

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

    <form action="insert_ingreso_med.php" method="POST" class="mt-4 col-md-10">

        <!-- =====================================
             DATOS GENERALES DEL INGRESO
        ====================================== -->
        <h4 class="mt-3">Datos del Ingreso</h4>

        <div class="mb-3">
            <label class="form-label">Fecha de ingreso</label>
            <input type="date" name="fecha_ingreso" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Documento</label>
            <select name="documento_id" class="form-select" required>
                <option value="">Seleccione...</option>
                <!-- Cargar con PHP -->
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Proveedor</label>
            <select name="proveedor_id" class="form-select" required>
                <option value="">Seleccione...</option>
                <!-- Cargar con PHP -->
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Recibido por</label>
            <select name="recibido_por" class="form-select" required>
                <option value="">Seleccione...</option>
                <!-- Cargar con PHP -->
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
                            <!-- PHP -->
                        </select>
                    </td>

                    <td>
                        <select name="lote_id[]" class="form-select">
                            <option value="">Seleccione...</option>
                            <!-- PHP -->
                        </select>
                    </td>

                    <td>
                        <input type="number" step="0.0001" name="cantidad[]" class="form-control cantidad" required>
                    </td>

                    <td>
                        <select name="unidad_id[]" class="form-select">
                            <option value="">Seleccione...</option>
                            <!-- PHP -->
                        </select>
                    </td>

                    <td>
                        <select name="presentacion_id[]" class="form-select">
                            <option value="">Seleccione...</option>
                            <!-- PHP -->
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
<script src="js/funciones.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/script.js"></script>
</body>
</html>
