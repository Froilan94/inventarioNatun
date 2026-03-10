<?php
require_once 'auth/roles.php';

requireRoles([
    'admin_super',
    'operadormed',
    'supervisormed'
]);
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

<?php if (hasRole(['admin_super'])): ?>
    <div class="menu-item" onclick="window.location.href='index.php'">🗄️ Ir al Panel principal</div>
<?php else: ?>
    <div class="menu-item" onclick="window.location.href='indexmedicamentos.php'">🔄 Actualizar panel</div>
<?php endif; ?>

    <!-- MAESTROS -->
    <div class="menu-item" onclick="toggleMenu('maestros')">📘 Maestros</div>
    <div id="maestros" class="submenu">
        <!-- Submenú medicamentos anidado -->
        <div class="menu-item" style="padding-left:40px;" onclick="toggleMenu('medicamentos')">💊 Medicamentos </div>
        <div id="medicamentos" class="sub-submenu">
            <a onclick="mostrarSeccion('vistaMedicamentos')">Ver Medicamentos</a>
<?php if (hasRole(['admin_super', 'operadormed'])): ?>            
            <a onclick="mostrarSeccion('vistaRegistrarMedicamentos')">Ingresar Medicamentos</a>
<?php endif; ?>            
        </div>

        <!-- Submenú unidades_de_medida anidado -->
        <div class="menu-item" style="padding-left:40px;" onclick="toggleMenu('unidades_de_medida')"> &#x1F4D0 Unidades de Medida </div>
        <div id="unidades_de_medida" class="sub-submenu">
            <a onclick="mostrarSeccion('vistaVerUnidades')">Ver Unidades de Medida</a>
<?php if (hasRole(['admin_super', 'operadormed'])): ?>            
            <a onclick="mostrarSeccion('vistaRegistrarUnidades')">Ingresar Unidades de Medida</a>
<?php endif; ?>             
        </div>

        <!-- Submenú Lote de Medicamentos anidado -->
        <div class="menu-item" style="padding-left:40px;" onclick="toggleMenu('lote_de_medicamentos')"> &#x1F4DD Lote de Medicamentos </div>
        <div id="lote_de_medicamentos" class="sub-submenu">
            <a onclick="mostrarSeccion('vistaVerLotedeMedicamentos')">Ver Lote de Medicamentos</a>
<?php if (hasRole(['admin_super', 'operadormed'])): ?>              
            <a onclick="mostrarSeccion('vistaRegistrarLotedeMedicamentos')">Ingresar Numero de Lotes</a>
<?php endif; ?>  
        </div>

        <!-- Submenú Presentacion de medicinas anidado -->
        <div class="menu-item" style="padding-left:40px;" onclick="toggleMenu('presentaciones')"> &#x1F4C9 Presentacion de medicamentos </div>
        <div id="presentaciones" class="sub-submenu">
            <a onclick="mostrarSeccion('vistaVerPresentaciones')">Ver Presentacion de Medicamentos</a>
<?php if (hasRole(['admin_super', 'operadormed'])): ?> 
            <a onclick="mostrarSeccion('vistaRegistrarPresentaciones')">Ingresar Presentaciones</a>
<?php endif; ?> 
        </div>

        <!-- Submenú Proveedores anidado -->
        <div class="menu-item" style="padding-left:40px;" onclick="toggleMenu('proveedores')"> 🤵 Proveedores </div>
        <div id="proveedores" class="sub-submenu">
            <a onclick="mostrarSeccion('vistaVerProveedores')">Ver Proveedores</a>
<?php if (hasRole(['admin_super', 'operadormed'])): ?>    
            <a onclick="mostrarSeccion('vistaRegistrarProveedores')">Ingresar Proveedores</a>
<?php endif; ?> 
        </div>

        <!-- Submenú Benericiarios anidado -->
        <div class="menu-item" style="padding-left:40px;" onclick="toggleMenu('beneficiarios')"> 🙍‍♀ Participantes </div>
        <div id="beneficiarios" class="sub-submenu">
            <a onclick="mostrarSeccion('vistaVerBeneficiarios')">Ver Beneficiarios</a>
<?php if (hasRole(['admin_super', 'operadormed'])): ?> 
            <a onclick="mostrarSeccion('vistaRegistrarBeneficiarios')">Ingresar Beneficiarios</a>
<?php endif; ?>
        </div>
    </div>
    
       <!-- MOVIMIENTOS -->
<?php if (hasRole(['admin_super', 'operadormed'])): ?>        
       <div class="menu-item" onclick="toggleMenu('movimientos')">📦 Movimientos</div>
       <div id="movimientos" class="submenu">      
            <a onclick="mostrarSeccion('vistaRegistrarIngresoMed')">Entradas</a> 
            <a onclick="mostrarSeccion('vistaRegistrarEgresosMed')">Salidas</a>
            <a onclick="mostrarSeccion('vistaActualizarEliminarBeneficiarios')">Ajustes</a>
        </div>
<?php endif; ?>

    <!-- REPORTES -->
    <div class="menu-item" onclick="toggleMenu('reportes')">📊 Reportes</div>
    <div id="reportes" class="submenu">
        <a onclick="mostrarSeccion('VistaReporteExistencias')">Existencias</a>
        <a onclick="mostrarSeccion('VistaReporteMovimientos')">Movimientos</a>
        <a onclick="mostrarSeccion('VistaReporteValorizacion')">Valorización</a>
    </div>

    <a class="menu-item" href="logout.php" style="background:#dc2626;">🚪 Cerrar sesión</a>
</div>
<div class="content">
    <!-- ============================
         VER MEDICAMENTOS
    ===============================-->
<div id="vistaMedicamentos" class="seccion" style="display:none;">
    <h2 class="d-flex justify-content-center">Lista de Medicamentos</h2>

    <button id="btnCargarMedicamentos" class="btn btn-primary mt-3">
        Cargar Medicamentos
    </button>

    <table class="table table-bordered table-striped mt-4">
        <thead class="table-dark">
            <tr>
                <th>No.</th>
                <th>Nombre Comercial</th>
                <th>Nombre Genérico</th>
                <th>Estado</th>
                <th>Acciones</th>                 
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

        <form id="formMedicamento" class="mt-4 col-md-6 mx-auto p-4 shadow-sm rounded bg-light">
            <div class="mb-3">
                <label class="form-label">Nombre Comercial</label>
                <input type="text" name="nombre_comercial" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Nombre Generico</label>
                <input type="text" name="nombre_generico" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Registrar Medicamento</button>          
        </form>
    </div>

    <!-- ============================
         Modal Editar Medicamento
    ===============================-->
<div class="modal fade" id="modalEditarMedicamento" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Editar Medicamento</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <form id="formEditarMedicamento">

            <input type="hidden" name="id_medicamento" id="edit_id_medicamento">

            <div class="mb-3">
                <label class="form-label">Nombre Comercial</label>
                <input type="text" name="nombre_comercial" id="edit_nombre_comercial" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Nombre Genérico</label>
                <input type="text" name="nombre_generico" id="edit_nombre_generico" class="form-control">
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
        <button type="submit" form="formEditarMedicamento" class="btn btn-success" id="btnGuardarCambios">
        <span id="textoGuardar">Guardar Cambios</span>
        <span id="spinnerGuardar" class="spinner-border spinner-border-sm d-none"></span></button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>

    </div>
  </div>
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

    <!-- ============================
         Registro Documento
    ===============================-->
<div id="vistaRegistrarIngresoMed" class="seccion" style="display:none;">

    <h2 class="d-flex justify-content-center">Registrar Ingreso de Medicamentos</h2>

    <!-- Alertas dinámicas (el JS las inyecta aquí) -->
    <div id="alertaIngreso"></div>

    <form id="formIngresoMed" class="mt-4 col-md-15 mx-auto p-4 shadow-sm rounded bg-light">

        <!-- =====================================
             DATOS GENERALES DEL INGRESO
        ====================================== -->
        <h4 class="d-flex justify-content-center">Datos del Ingreso</h4>

        <div class="mb-3" style="display: flex; gap: 15px;">
            <div style="flex: 1;">
                <label class="form-label">Tipo de documento</label>
                <select id="ingresoTipoDoc" class="form-select" required>
                    <option value="">Seleccione...</option>
                    <option value="Factura">Factura</option>
                    <option value="Recibo_donacion">Recibo Donación</option>
                    <option value="Cardex">Cardex</option>
                    <option value="Acta">Acta</option>
                </select>
            </div>
            <div style="flex: 1;">
                <label class="form-label">Número documento</label>
                <input type="text" id="ingresoNumDoc" class="form-control" maxlength="30">
            </div>
        </div>

        <div class="mb-3" style="display: flex; gap: 15px;">
            <div style="flex: 1;">
                <label class="form-label">Serie</label>
                <input type="text" id="ingresoSerie" class="form-control" maxlength="30">
            </div>
            <div style="flex: 1;">
                <label class="form-label">Fecha de ingreso</label>
                <input type="date" id="ingresoFecha" class="form-control" required>
            </div>
        </div>

        <div class="mb-3" style="display: flex; gap: 15px;">
            <div style="flex: 1;">
                <label class="form-label">Proveedor</label>
                <!-- Poblado por JS con get_datos_iniciales -->
                <select id="ingresoProveedor" class="form-select">
                    <option value="">Cargando...</option>
                </select>
            </div>
            <div style="flex: 1;">
                <label class="form-label">Recibido por</label>
                <!-- Si hay sesión activa el JS lo fija y deshabilita;
                     si no, muestra lista de operadores -->
                <select id="ingresoRecibidoPor" class="form-select" required>
                    <option value="">Cargando...</option>
                </select>
            </div>
        </div>

        <!-- =====================================
             DETALLES DEL INGRESO
        ====================================== -->
        <h4 class="mt-4">Detalles del Ingreso</h4>

        <div class="table-responsive">
            <table class="table table-bordered" id="tablaDetalles">
                <thead class="table-light">
                    <tr>
                        <th>Medicamento</th>
                        <th>Lote</th>
                        <th>Fecha de Vencimiento</th>
                        <th>Cantidad</th>
                        <th>Unidad</th>
                        <th>Presentación</th>
                        <th>Precio Unitario</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <!-- El JS inserta y gestiona las filas aquí -->
                <tbody id="tbodyDetallesIngreso">
                </tbody>
            </table>
        </div>

        <button type="button"
                class="btn btn-success"
                onclick="agregarDetalleIngreso()">
            Agregar detalle
        </button>

        <br><br>

        <button type="button"
                id="btnRegistrarIngreso"
                class="btn btn-primary"
                onclick="registrarIngreso()">
            Registrar Ingreso
        </button>

    </form>
</div>

    <!-- ============================
         Registro de Egresos
    ===============================-->
<div id="vistaRegistrarEgresosMed" class="seccion" style="display:none;">

    <h2 class="d-flex justify-content-center">Registrar Salida de Medicamentos</h2>

    <!-- Alertas dinámicas -->
    <div id="alertaSalida"></div>

    <form id="formSalidaMed" class="mt-4 col-md-15 mx-auto p-4 shadow-sm rounded bg-light">

        <!-- =====================================
             DATOS GENERALES DEL EGRESO
        ====================================== -->
        <h4 class="d-flex justify-content-center">Datos del Egreso</h4>

        <div class="mb-3" style="display: flex; gap: 15px;">
            <div style="flex: 1;">
                <label class="form-label">Tipo de documento</label>
                <select id="salidaTipoDoc" class="form-select" required>
                    <option value="">Seleccione...</option>
                    <option value="Factura">Factura</option>
                    <option value="Recibo_donacion">Recibo Donación</option>
                    <option value="Cardex">Cardex</option>
                    <option value="Acta">Acta</option>
                </select>
            </div>
            <div style="flex: 1;">
                <label class="form-label">Número documento</label>
                <input type="text" id="salidaNumDoc" class="form-control" maxlength="30">
            </div>
        </div>

        <div class="mb-3" style="display: flex; gap: 15px;">
            <div style="flex: 1;">
                <label class="form-label">Serie / Año</label>
                <input type="text" id="salidaSerie" class="form-control" maxlength="30">
            </div>
            <div style="flex: 1;">
                <label class="form-label">Fecha de Salida</label>
                <input type="date" id="salidaFecha" class="form-control" required>
            </div>
        </div>

        <div class="mb-3" style="display: flex; gap: 15px;">
            <div style="flex: 1;">
                <label class="form-label">Componente</label>
                <!-- Poblado por JS con get_datos_iniciales -->
                <select id="salidaComponente" class="form-select" required>
                    <option value="">Cargando...</option>
                </select>
            </div>
            <div style="flex: 1;">
                <label class="form-label">Entregado por</label>
                <!-- Si hay sesión activa el JS lo fija; si no, muestra lista -->
                <select id="salidaEntregadoPor" class="form-select" required>
                    <option value="">Cargando...</option>
                </select>
            </div>
        </div>

        <!-- =====================================
             DETALLES DEL EGRESO
        ====================================== -->
        <h4 class="mt-4">Detalles del Egreso</h4>

        <div class="table-responsive">
            <table class="table table-bordered" id="tablaDetallesSalida">
                <thead class="table-light">
                    <tr>
                        <th>Medicamento</th>
                        <th>Lote</th>
                        <th>Cantidad</th>
                        <th>Unidad</th>
                        <th>Presentación</th>
                        <th>Beneficiaria</th>
                        <th>Precio Unitario</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <!-- El JS inserta y gestiona las filas aquí -->
                <tbody id="tbodyDetallesSalida">
                </tbody>
            </table>
        </div>

        <button type="button"
                class="btn btn-success"
                onclick="agregarDetalleSalida()">
            Agregar detalle
        </button>

        <br><br>

        <button type="button"
                id="btnRegistrarSalida"
                class="btn btn-primary"
                onclick="registrarSalida()">
            Registrar Salida
        </button>

    </form>
</div>    

<!-- ================== REPORTE DE EXISTENCIAS ================== -->
<div id="VistaReporteExistencias" class="seccion" style="display:none;">

<h2 class="d-flex justify-content-center">📦 Reporte de Existencias</h2>

<div class="card mb-4">
<div class="card-body">

<h5 class="card-title mb-3">🔍 Filtros</h5>

<div class="row" style="display:flex; gap:15px;">

<div style="flex:1;">
<label class="form-label">Medicamento</label>
<select id="filtroMedicamentoExistencia" class="form-select">
<option value="">Todosss</option>
</select>
</div>

<div style="flex:1;">
<label class="form-label">Presentación</label>
<select id="filtroPresentacionExistencia" class="form-select">
<option value="">Todas</option>
</select>
</div>

<div style="flex:1;">
<label class="form-label">Lote</label>
<select id="filtroLoteExistencia" class="form-select">
<option value="">Todos</option>
</select>
</div>

<div style="flex:1;">
<label class="form-label">Estado de Stock</label>
<select id="filtroStock" class="form-control">

<option value="">Todos</option>
<option value="agotado">Agotado</option>
<option value="bajo">Stock bajo</option>
<option value="normal">Stock normal</option>

</select>
</div>

<div style="flex:1;">
<label class="form-label">Vencimiento</label>
<select id="filtroVencimiento" class="form-control">

<option value="">Todos</option>
<option value="vencido">Vencidos</option>
<option value="proximo">Próximos a vencer</option>

</select>
</div>

</div>

<div class="mt-3 text-end">

<button class="btn btn-secondary" onclick="limpiarFiltrosExistencias()">🔄 Limpiar</button>
<button class="btn btn-primary" onclick="buscarExistencias()">🔍 Buscar</button>

</div>

</div>
</div>


<div class="card">

<div class="card-body">

<h5 class="card-title mb-3">📊 Inventario Actual</h5>
    <div class="d-flex justify-content-end mb-3">
        <button id="btnExportarExcel" class="btn btn-success" onclick="exportarReporte('excel')">📊 Excel</button>        
        <button id="btnExportarPDF" class="btn btn-danger me-2" onclick="exportarReporte('pdf')">📄 PDF</button>
        <button id="btnExportarCSV" class="btn btn-success" onclick="exportarReporte('csv')">📋 CSV</button>
    </div>

<div class="table-responsive">

<table class="table table-bordered table-hover">

<thead class="table-dark">

<tr>

<th>Medicamento</th>
<th>Presentación</th>
<th>Unidad</th>
<th>Lote</th>
<th>Vencimiento</th>
<th>Stock</th>
<th>Precio Unitario</th>
<th>Valor Total</th>
<th>Estado</th>

</tr>

</thead>

<tbody id="tablaExistencias">

</tbody>

</table>

</div>

</div>

</div>

</div>

<!-- ================== REPORTE DE MOVIMIENTOS ================== -->
<div id="VistaReporteMovimientos" class="seccion" style="display:none;">
    
    <h2 class="d-flex justify-content-center">Reporte de Movimientos</h2>

    <!-- ======= FILTROS ======= -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">🔍 Filtros de Búsqueda</h5>

            <div class="row" style="display: flex; gap: 15px;">
                <div style="flex: 1;">
                    <label class="form-label">Tipo de Movimiento</label>
                    <select id="filtro-tipo" class="form-control">
                        <option value="">Todos</option>
                        <option value="INGRESO">Ingresos</option>
                        <option value="SALIDA">Salidas</option>
                    </select>
                </div>
            </div>                

            <div class="row" style="display: flex; gap: 15px;">
                <div style="flex: 1;">
                    <label class="form-label">Fecha inicio</label>
                    <input type="date" id="fechaInicio" class="form-control">
                </div>

                <div style="flex: 1;">
                    <label class="form-label">Fecha fin</label>
                    <input type="date" id="fechaFin" class="form-control">
                </div>

                <div style="flex: 1;">
                    <label class="form-label">Medicamento</label>
                    <select id="filtroMedicamento" class="form-control">
                        <option value="">Todos</option>
                    </select>
                </div>

                <div style="flex: 1;">
                    <label class="form-label">No. Lote</label>
                    <select id="filtroLote" class="form-control">
                        <option value="">Todos</option>
                    </select>
                </div>

                <div style="flex: 1;">
                    <label class="form-label">Proveedor / Donante</label>
                    <select id="filtroProveedor" class="form-control">
                        <option value="">Todos</option>
                    </select>
                </div>
            </div>

            <div class="mt-3 text-end">
                <button class="btn btn-secondary" onclick="limpiarFiltros()">🔄 Limpiar</button>
                <button id="btnAplicarFiltros" class="btn btn-primary" onclick="aplicarFiltros()">🔍 Buscar</button>                
            </div>
        </div>
    </div>

    <!-- ======= BOTONES DE EXPORTACIÓN ======= -->

    <div class="card">
    <div class="card-body">

    <div class="d-flex justify-content-end mb-3">
        <button id="btnExportarExcel" class="btn btn-success" onclick="exportarReporte('excel')">📊 Excel</button>        
        <button id="btnExportarPDF" class="btn btn-danger me-2" onclick="exportarReporte('pdf')">📄 PDF</button>
        <button id="btnExportarCSV" class="btn btn-success" onclick="exportarReporte('csv')">📋 CSV</button>
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
</div>
</div>
<!-- ================== REPORTE DE VALORIZACIÓN-EXISTENCIAS ================== -->
<div id="VistaReporteValorizacion" class="seccion" style="display:none;">

    <h2 class="d-flex justify-content-center">📦 Reporte de Existencias</h2>

    <!-- ======= FILTROS ======= -->
    <div class="card mb-4">
        <div class="card-body">

            <h5 class="card-title mb-3">🔍 Filtros</h5>

            <div class="row" style="display:flex; gap:15px;">

                <div style="flex:1;">
                    <label class="form-label">Medicamento</label>
                    <select id="filtroMedicamentoExistencia" class="form-control">
                        <option value="">Todos</option>
                    </select>
                </div>

                <div style="flex:1;">
                    <label class="form-label">No. Lote</label>
                    <select id="filtroLoteExistencia" class="form-control">
                        <option value="">Todos</option>
                    </select>
                </div>

                <div style="flex:1;">
                    <label class="form-label">Estado de Stock</label>
                    <select id="filtroStock" class="form-control">
                        <option value="">Todos</option>
                        <option value="bajo">Stock bajo</option>
                        <option value="normal">Stock normal</option>
                        <option value="agotado">Agotado</option>
                    </select>
                </div>

                <div style="flex:1;">
                    <label class="form-label">Vencimiento</label>
                    <select id="filtroVencimiento" class="form-control">
                        <option value="">Todos</option>
                        <option value="vencido">Vencidos</option>
                        <option value="proximo">Próximos a vencer</option>
                    </select>
                </div>

            </div>

            <div class="mt-3 text-end">
                <button class="btn btn-secondary" onclick="limpiarFiltrosExistencias()">🔄 Limpiar</button>
                <button class="btn btn-primary" onclick="buscarExistencias()">🔍 Buscar</button>
            </div>

        </div>
    </div>


    <!-- ======= TABLA DE EXISTENCIAS ======= -->
    <div class="card">
        <div class="card-body">

            <h5 class="card-title mb-3">📊 Inventario Actual</h5>

            <div class="table-responsive">

                <table class="table table-bordered table-hover">

                    <thead class="table-dark">

                        <tr>
                            <th>Código</th>
                            <th>Medicamento</th>
                            <th>Presentación</th>
                            <th>Lote</th>
                            <th>Vencimiento</th>
                            <th>Stock Actual</th>
                            <th>Costo Unitario</th>
                            <th>Valor Total</th>
                            <th>Estado</th>
                        </tr>

                    </thead>

                    <tbody id="tablaExistencias">

                        <!-- Aquí se llenará con JS -->

                    </tbody>

                </table>

            </div>

        </div>
    </div>

</div>
</div> <!--cierre div content-->
<!-- ================== FIN REPORTE DE EXISTENCIAS ================== -->

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="js2/core.js?v=<?= filemtime('js2/core.js') ?>"></script>
<script src="js2/maestros/medicamentos.js?v=<?= filemtime('js2/maestros/medicamentos.js') ?>"></script>
<script src="js2/movimientos/entradas8.js?v=<?= filemtime('js2/movimientos/entradas8.js') ?>"></script>
<script src="js2/movimientos/salidas.js?v=<?= filemtime('js2/movimientos/salidas.js') ?>"></script>
<script src="js2/reportes/existencias.js?v=<?= filemtime('js2/reportes/existencias.js') ?>"></script>
<!--<script src="js/reporte_existencias.js"></script>-->
<script>
    window.USER_ROLE = "<?= $_SESSION['role_name'] ?>";
</script>
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
