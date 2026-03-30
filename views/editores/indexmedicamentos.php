<?php
require_once '../../auth/roles.php';

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
    <link rel="stylesheet" type="text/css" href="../../styles/style.css">
</head>
<body>

<div class="sidebar">   
    <h2>Inventario</h2>

<?php if (hasRole(['admin_super'])): ?>
    <div class="menu-item" onclick="window.location.href='../../index.php'">🗄️ Ir al Panel principal</div>
<?php else: ?>
    <div class="menu-item" onclick="window.location.href='../../views/editor/indexmedicamentos.php'">🔄 Actualizar panel</div>
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
<?php if (hasRole(['admin_super', 'operadormed'])): ?>           
        <div class="menu-item" style="padding-left:40px;" onclick="toggleMenu('firmas')"> 🖋️ Firmas</div>
        <div id="firmas" class="sub-submenu">
            <a onclick="mostrarSeccion('vistaVerFirmas')">Ver Unidades de Medida</a>                      
        </div>
<?php endif; ?> 

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
            <a onclick="mostrarSeccion('vistaVerLotes')">Ver Lote de Medicamentos</a>
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
            <a onclick="mostrarSeccion('vistaVerParticipantes')">Ver Beneficiarios</a>
<?php if (hasRole(['admin_super', 'operadormed'])): ?> 
            <a onclick="mostrarSeccion('vistaRegistrarParticipantes')">Ingresar Beneficiarios</a>
<?php endif; ?>
        </div>
    </div>
    
       <!-- MOVIMIENTOS -->
<?php if (hasRole(['admin_super', 'operadormed'])): ?>        
       <div class="menu-item" onclick="toggleMenu('movimientos')">📦 Movimientos</div>
       <div id="movimientos" class="submenu">      
            <a onclick="mostrarSeccion('vistaRegistrarIngresoMed')">Entradas</a> 
            <a onclick="mostrarSeccion('vistaRegistrarEgresosMed')">Salidas</a>
            <!--<a onclick="mostrarSeccion('vistaActualizarEliminarBeneficiarios')">Ajustes</a> -->
        </div>
<?php endif; ?>

    <!-- REPORTES -->
    <div class="menu-item" onclick="toggleMenu('reportes')">📊 Reportes</div>
    <div id="reportes" class="submenu">
        <a onclick="mostrarSeccion('VistaReporteExistencias')">Existencias</a>
        <a onclick="mostrarSeccion('vistaReporteMovimientos')">Movimientos</a>
        <!--<a onclick="mostrarSeccion('VistaReporteValorizacion')">Valorización</a> -->
    </div>

    <a class="menu-item" href="../../auth/logout.php" style="background:#dc2626;">🚪 Cerrar sesión</a>
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
    <div class="mt-4 col-md-15 mx-auto p-4 shadow-sm rounded bg-light"> 
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

<!-- ══════════════════════════════════════
     UNIDADES DE MEDIDA
══════════════════════════════════════ -->
<div id="vistaVerUnidades" class="seccion" style="display:none;">
    <h2>Unidades de Medida</h2>
    <button id="btnCargarUnidades" class="btn btn-primary mt-3">Cargar Unidades</button>
    <table class="table table-bordered table-striped mt-4">
        <thead class="table-dark">
            <tr><th>No.</th><th>Nombre</th><th>Acciones</th></tr>
        </thead>
        <tbody id="tablaUnidades"></tbody>
    </table>
</div>

<div id="vistaRegistrarUnidades" class="seccion" style="display:none;">
    <h2 class="d-flex justify-content-center">Registrar Unidad de Medida</h2>
    <form id="formUnidad" class="mt-4 col-md-5 mx-auto p-4 shadow-sm rounded bg-light">
        <div class="mb-3">
            <label class="form-label">Nombre de la Unidad</label>
            <input type="text" name="nombre_unidad" class="form-control" maxlength="50" required>
        </div>
        <button type="submit" class="btn btn-primary">Registrar</button>
        <div id="mensajeUnidad" style="margin-top:10px;"></div>
    </form>
</div>

<!-- Modal Editar Unidad -->
<div class="modal fade" id="modalEditarUnidad" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Editar Unidad</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <form id="formEditarUnidad">
                <input type="hidden" name="id_unidad_med" id="editU_id">
                <div class="mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre_unidad" id="editU_nombre" class="form-control" required>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="submit" form="formEditarUnidad" class="btn btn-success">Guardar</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
    </div></div>
</div>

<!-- ══════════════════════════════════════
     PRESENTACIONES
══════════════════════════════════════ -->
<div id="vistaVerPresentaciones" class="seccion" style="display:none;">
    <h2>Presentaciones</h2>
    <button id="btnCargarPresentaciones" class="btn btn-primary mt-3">Cargar Presentaciones</button>
    <table class="table table-bordered table-striped mt-4">
        <thead class="table-dark">
            <tr><th>No.</th><th>Nombre</th><th>Acciones</th></tr>
        </thead>
        <tbody id="tablaPresentaciones"></tbody>
    </table>
</div>

<div id="vistaRegistrarPresentaciones" class="seccion" style="display:none;">
    <h2 class="d-flex justify-content-center">Registrar Presentación</h2>
    <form id="formPresentacion" class="mt-4 col-md-5 mx-auto p-4 shadow-sm rounded bg-light">
        <div class="mb-3">
            <label class="form-label">Nombre de la Presentación</label>
            <input type="text" name="nombre_presentacion" class="form-control" maxlength="25" required>
        </div>
        <button type="submit" class="btn btn-primary">Registrar</button>
        <div id="mensajePresentacion" style="margin-top:10px;"></div>
    </form>
</div>

<!-- Modal Editar Presentación -->
<div class="modal fade" id="modalEditarPresentacion" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Editar Presentación</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <form id="formEditarPresentacion">
                <input type="hidden" name="id_presentacion_med" id="editP_id">
                <div class="mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre_presentacion" id="editP_nombre" class="form-control" required>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="submit" form="formEditarPresentacion" class="btn btn-success">Guardar</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
    </div></div>
</div>

<!-- ══════════════════════════════════════
     PROVEEDORES / DONANTES
══════════════════════════════════════ -->
<div id="vistaVerProveedores" class="seccion" style="display:none;">
    <h2>Proveedores / Donantes</h2>
    <button id="btnCargarProveedores" class="btn btn-primary mt-3">Cargar Proveedores</button>
    <div class="table-responsive mt-4">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr><th>No.</th><th>Nombre</th><th>NIT</th><th>Tipo</th><th>Teléfono</th><th>Correo</th><th>Estado</th><th>Acciones</th></tr>
            </thead>
            <tbody id="tablaProveedores"></tbody>
        </table>
    </div>
</div>

<div id="vistaRegistrarProveedores" class="seccion" style="display:none;">
    <h2 class="d-flex justify-content-center">Registrar Proveedor / Donante</h2>
    <form id="formProveedor" class="mt-4 col-md-7 mx-auto p-4 shadow-sm rounded bg-light">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nombre</label>
                <input type="text" name="nombre_proveedor" class="form-control" maxlength="50" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">NIT</label>
                <input type="text" name="nit_proveedor" class="form-control" maxlength="20">
            </div>
            <div class="col-md-6">
                <label class="form-label">Género</label>
                <select name="genero_proveedor" class="form-select" required>
                    <option value="">Seleccione...</option>
                    <option>Masculino</option><option>Femenino</option><option>Otros</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Tipo</label>
                <select name="tipo_proveedor" class="form-select" required>
                    <option value="">Seleccione...</option>
                    <option>Fabricante</option><option>Proveedor</option><option>Donante</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Teléfono</label>
                <input type="text" name="telefono" class="form-control" maxlength="20">
            </div>
            <div class="col-md-6">
                <label class="form-label">Correo</label>
                <input type="email" name="correo" class="form-control" maxlength="25">
            </div>
            <div class="col-12">
                <label class="form-label">Dirección</label>
                <input type="text" name="direccion" class="form-control" maxlength="150">
            </div>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Registrar</button>
        <div id="mensajeProveedor" style="margin-top:10px;"></div>
    </form>
</div>

<!-- Modal Editar Proveedor -->
<div class="modal fade" id="modalEditarProveedor" tabindex="-1">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Editar Proveedor</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <form id="formEditarProveedor">
                <input type="hidden" name="id_proveedor_med" id="editProv_id">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Nombre</label>
                        <input type="text" name="nombre_proveedor" id="editProv_nombre" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">NIT</label>
                        <input type="text" name="nit_proveedor" id="editProv_nit" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Género</label>
                        <select name="genero_proveedor" id="editProv_genero" class="form-select">
                            <option>Masculino</option><option>Femenino</option><option>Otros</option>
                        </select></div>
                    <div class="col-md-6"><label class="form-label">Tipo</label>
                        <select name="tipo_proveedor" id="editProv_tipo" class="form-select">
                            <option>Fabricante</option><option>Proveedor</option><option>Donante</option>
                        </select></div>
                    <div class="col-md-6"><label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" id="editProv_tel" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Correo</label>
                        <input type="email" name="correo" id="editProv_correo" class="form-control"></div>
                    <div class="col-md-8"><label class="form-label">Dirección</label>
                        <input type="text" name="direccion" id="editProv_dir" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Estado</label>
                        <select name="activo" id="editProv_activo" class="form-select">
                            <option value="1">Activo</option><option value="0">Inactivo</option>
                        </select></div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="submit" form="formEditarProveedor" class="btn btn-success">Guardar</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
    </div></div>
</div>

<!-- ══════════════════════════════════════
     PARTICIPANTES
══════════════════════════════════════ -->
<div id="vistaVerParticipantes" class="seccion" style="display:none;">
    <h2>Participantes</h2>
    <button id="btnCargarParticipantes" class="btn btn-primary mt-3">Cargar Participantes</button>
    <div class="table-responsive mt-4">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr><th>No.</th><th>Nombre</th><th>DPI</th><th>Teléfono</th><th>Género</th><th>Departamento</th><th>Estado</th><th>Acciones</th></tr>
            </thead>
            <tbody id="tablaParticipantes"></tbody>
        </table>
    </div>
</div>

<div id="vistaRegistrarParticipantes" class="seccion" style="display:none;">
    <h2 class="d-flex justify-content-center">Registrar Participante</h2>
    <form id="formParticipante" class="mt-4 col-md-7 mx-auto p-4 shadow-sm rounded bg-light">
        <div class="row g-3">
            <div class="col-md-8"><label class="form-label">Nombre Completo</label>
                <input type="text" name="nombre_beneficiario" class="form-control" maxlength="200" required></div>
            <div class="col-md-4"><label class="form-label">DPI</label>
                <input type="text" name="dpi_beneficiario" class="form-control" maxlength="12" required></div>
            <div class="col-md-6"><label class="form-label">Teléfono</label>
                <input type="text" name="telefono" class="form-control" maxlength="50"></div>
            <div class="col-md-6"><label class="form-label">Género</label>
                <select name="genero_beneficiario" class="form-select" required>
                    <option value="">Seleccione...</option>
                    <option>Masculino</option><option>Femenino</option><option>Otros</option>
                </select></div>
            <div class="col-md-6"><label class="form-label">Departamento</label>
                <select name="departamento_id" id="departamento_id" class="form-select">
                    <option value="">Seleccione...</option>
                </select></div>
            <div class="col-12"><label class="form-label">Dirección</label>
                <textarea name="direccion_beneficiario" class="form-control" rows="2"></textarea></div>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Registrar</button>
        <div id="mensajeParticipante" style="margin-top:10px;"></div>
    </form>
</div>

<!-- Modal Editar Participante -->
<div class="modal fade" id="modalEditarParticipante" tabindex="-1">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Editar Participante</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <form id="formEditarParticipante">
                <input type="hidden" name="id_beneficiario_med" id="editPart_id">
                <div class="row g-3">
                    <div class="col-md-8"><label class="form-label">Nombre</label>
                        <input type="text" name="nombre_beneficiario" id="editPart_nombre" class="form-control" required></div>
                    <div class="col-md-4"><label class="form-label">DPI</label>
                        <input type="text" name="dpi_beneficiario" id="editPart_dpi" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" id="editPart_tel" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Género</label>
                        <select name="genero_beneficiario" id="editPart_genero" class="form-select">
                            <option>Masculino</option><option>Femenino</option><option>Otros</option>
                        </select></div>
                    <div class="col-md-6"><label class="form-label">Departamento</label>
                        <select name="departamento_id" id="editPart_depto" class="form-select">
                            <option value="">Seleccione...</option>
                        </select></div>
                    <div class="col-md-6"><label class="form-label">Estado</label>
                        <select name="activo" id="editPart_activo" class="form-select">
                            <option value="1">Activo</option><option value="0">Inactivo</option>
                        </select></div>
                    <div class="col-12"><label class="form-label">Dirección</label>
                        <textarea name="direccion_beneficiario" id="editPart_dir" class="form-control" rows="2"></textarea></div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="submit" form="formEditarParticipante" class="btn btn-success">Guardar</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
    </div></div>
</div>

<!-- ══════════════════════════════════════
     LOTES — solo lectura
══════════════════════════════════════ -->
<div id="vistaVerLotes" class="seccion" style="display:none;">
    <h2>Lotes de Medicamentos</h2>
    <div class="d-flex gap-2 mt-3 align-items-end">
        <div>
            <label class="form-label">Filtrar por medicamento</label>
            <select id="filtroMedLotes" class="form-select form-select-sm" style="min-width:200px;">
                <option value="">Todos</option>
            </select>
        </div>
        <button id="btnCargarLotes" class="btn btn-primary btn-sm" onclick="cargarLotes()">Buscar</button>
    </div>
    <div class="table-responsive mt-4">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr><th>No.</th><th>Medicamento</th><th>No. Lote</th><th>Vencimiento</th><th>Cant. Inicial</th><th>Cant. Actual</th><th>Registrado</th></tr>
            </thead>
            <tbody id="tablaLotes"></tbody>
        </table>
    </div>
</div>

<!-- ══════════════════════════════════════
     FIRMAS DE PLANILLA — solo admin_super
══════════════════════════════════════ -->
<div id="vistaVerFirmas" class="seccion" style="display:none;">
    <h2>Firmas de Planilla</h2>
    <button id="btnCargarFirmas" class="btn btn-primary mt-3">Cargar Firmas</button>
    <table class="table table-bordered table-striped mt-4">
        <thead class="table-dark">
            <tr><th>Orden</th><th>Nombre</th><th>Cargo</th><th>Estado</th><th>Acciones</th></tr>
        </thead>
        <tbody id="tablaFirmas"></tbody>
    </table>
</div>

<div id="vistaRegistrarFirmas" class="seccion" style="display:none;">
    <h2 class="d-flex justify-content-center">Registrar Firma de Planilla</h2>
    <form id="formFirma" class="mt-4 col-md-6 mx-auto p-4 shadow-sm rounded bg-light">
        <div class="mb-3"><label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control" maxlength="100" required></div>
        <div class="mb-3"><label class="form-label">Cargo</label>
            <input type="text" name="cargo" class="form-control" maxlength="100" required></div>
        <div class="mb-3"><label class="form-label">Orden</label>
            <input type="number" name="orden" class="form-control" min="1" value="1" required></div>
        <button type="submit" class="btn btn-primary">Registrar</button>
        <div id="mensajeFirma" style="margin-top:10px;"></div>
    </form>
</div>

<!-- Modal Editar Firma -->
<div class="modal fade" id="modalEditarFirma" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Editar Firma</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <form id="formEditarFirma">
                <input type="hidden" name="id_firma" id="editF_id">
                <div class="mb-3"><label class="form-label">Nombre</label>
                    <input type="text" name="nombre" id="editF_nombre" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Cargo</label>
                    <input type="text" name="cargo" id="editF_cargo" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Orden</label>
                    <input type="number" name="orden" id="editF_orden" class="form-control" min="1"></div>
                <div class="mb-3"><label class="form-label">Estado</label>
                    <select name="activo" id="editF_activo" class="form-select">
                        <option value="1">Activo</option><option value="0">Inactivo</option>
                    </select></div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="submit" form="formEditarFirma" class="btn btn-success">Guardar</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
    </div></div>
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
<div id="vistaReporteMovimientos" class="seccion" style="display:none;">

    <h2 class="d-flex justify-content-center">📋 Reporte de Movimientos</h2>

    <!-- ══ FILTROS ══ -->
    <div class="card shadow-sm mb-3 mt-3 col-md-15 mx-auto">
        <div class="card-body">
            <h5 class="mb-3">🔍 Filtros de Búsqueda</h5>

            <!-- Fila 1: Tipo + Fechas (siempre visibles) -->
            <div class="row g-2 mb-2">
                <div class="col-md-3">
                    <label class="form-label">Tipo de Movimiento</label>
                    <select id="filtroTipoMov" class="form-select form-select-sm"
                            onchange="cambiarFiltrosTipoMov(this.value)">
                        <option value="">Todos</option>
                        <option value="Ingresos">Ingresos</option>
                        <option value="Salidas">Salidas</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Fecha desde</label>
                    <input type="date" id="filtroFechaIniMov" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Fecha hasta</label>
                    <input type="date" id="filtroFechaFinMov" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Medicamento</label>
                    <select id="filtroMedMov" class="form-select form-select-sm">
                        <option value="">Todos</option>
                    </select>
                </div>
            </div>

            <!-- Fila 2: Filtros de INGRESO -->
            <div class="row g-2 mb-2" id="filtrosIngreso">
                <div class="col-md-3">
                    <label class="form-label">No. Lote</label>
                    <input type="text" id="filtroLoteMov" class="form-control form-control-sm"
                           list="lotesDatalist" placeholder="Todos">
                    <datalist id="lotesDatalist"></datalist>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Proveedor / Donante</label>
                    <select id="filtroProvMov" class="form-select form-select-sm">
                        <option value="">Todos</option>
                    </select>
                </div>
            </div>

            <!-- Fila 3: Filtros de SALIDA -->
            <div class="row g-2 mb-2" id="filtrosSalida" style="display:none;">
                <div class="col-md-3">
                    <label class="form-label">Responsable (entregó)</label>
                    <select id="filtroResponsableMov" class="form-select form-select-sm">
                        <option value="">Todos</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Componente / Comunidad</label>
                    <select id="filtroComponenteMov" class="form-select form-select-sm">
                        <option value="">Todos</option>
                    </select>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-2">
                <button class="btn btn-secondary btn-sm" onclick="limpiarFiltrosMov()">
                    🔄 Limpiar
                </button>
                <button class="btn btn-primary btn-sm" onclick="buscarMovimientos()">
                    🔍 Buscar
                </button>
            </div>
        </div>
    </div>

    <!-- ══ RESUMEN ══ -->
    <div class="row g-2 mb-3 col-md-15 mx-auto" id="resumenMovimientos"></div>

    <!-- ══ TABLA ══ -->
    <div class="card shadow-sm col-md-15 mx-auto">
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-2">
                <small class="text-muted">
                    Marca las casillas para exportar registros específicos.
                    Sin selección → exporta todos.
                </small>
                <div class="d-flex gap-2">
                    <button class="btn btn-success btn-sm" onclick="exportarMovExcel()">
                        📊 Excel
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="exportarMovPDF()">
                        📄 PDF
                    </button>
                    <button class="btn btn-secondary btn-sm" onclick="exportarMovCSV()">
                        📋 CSV
                    </button>
                    <button class="btn btn-warning btn-sm" onclick="generarPlanillaNatun()">
                            📋 Planilla Natún
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="table-dark">
                        <tr>
                            <th style="width:35px;">
                                <input type="checkbox" class="form-check-input"
                                       id="chkTodosMov" title="Seleccionar todos">
                            </th>
                            <th>Tipo</th>
                            <th>Fecha</th>
                            <th>Medicamento</th>
                            <th>Lote</th>
                            <th>Vencimiento</th>
                            <th>Cantidad</th>
                            <th>Unidad</th>
                            <th>Precio Unit.</th>
                            <th>Monto</th>
                            <th>Proveedor / Componente</th>
                            <th>Responsable</th>
                            <th>Beneficiaria</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyMovimientos">
                        <tr>
                            <td colspan="13" class="text-center text-muted py-4">
                                Aplica los filtros y presiona Buscar.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

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
<script src="../../js/med_invent/core.js?v=<?= filemtime('../../js/med_invent/core.js') ?>"></script>
<script src="../../js/med_invent/maestros/medicamentos.js?v=<?= filemtime('../../js/med_invent/maestros/medicamentos.js') ?>"></script>
<script src="../../js/med_invent/maestros/maestros_med.js?v=<?= filemtime('../../js/med_invent/maestros/maestros_med.js') ?>"></script>
<script src="../../js/med_invent/movimientos/entradas.js?v=<?= filemtime('../../js/med_invent/movimientos/entradas.js') ?>"></script>
<script src="../../js/med_invent/movimientos/salidas.js?v=<?= filemtime('../../js/med_invent/movimientos/salidas.js') ?>"></script>
<script src="../../js/med_invent/reportes/existencias.js?v=<?= filemtime('../../js/med_invent/reportes/existencias.js') ?>"></script>
<script src="../../js/med_invent/reportes/movimientos.js?v=<?= filemtime('../../js/med_invent/reportes/movimientos.js') ?>"></script>
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
<div id="toastContainer"
     class="toast-container position-fixed top-0 end-0 p-3"
     style="z-index: 9999;">
</div>
</body>
</html>
