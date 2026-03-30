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
    <div class="menu-item" onclick="window.location.href='../../views/editor/indexartesanias.php'">🔄 Actualizar panel</div>
<?php endif; ?>

    <!-- MAESTROS -->
    <div class="menu-item" onclick="toggleMenu('maestros')">🛠️ Maestros</div>
    <div id="maestros" class="submenu">
        <!-- Submenú medicamentos anidado -->
        <div class="menu-item" style="padding-left:40px;" onclick="toggleMenu('medicamentos')">🧣📿Productos </div>
        <div id="medicamentos" class="sub-submenu">
            <a onclick="mostrarSeccion('vistaMedicamentos')">Ver Medicamentos</a>
<?php if (hasRole(['admin_super', 'operadormed'])): ?>            
            <a onclick="mostrarSeccion('vistaRegistrarMedicamentos')">Ingresar Medicamentos</a>
<?php endif; ?>            
        </div>

        <!-- Submenú unidades_de_medida anidado -->
<?php if (hasRole(['admin_super', 'operadormed'])): ?>           
        <div class="menu-item" style="padding-left:40px;" onclick="toggleMenu('firmas')"> Firmas</div>
        <div id="firmas" class="sub-submenu">
            <a onclick="mostrarSeccion('vistaVerFirmas')">Ver Unidades de Medida</a>                      
        </div>
<?php endif; ?> 

        <!-- Submenú unidades_de_medida anidado -->
        <div class="menu-item" style="padding-left:40px;" onclick="toggleMenu('unidades_de_medida')"> 📂Categorias </div>
        <div id="unidades_de_medida" class="sub-submenu">
            <a onclick="mostrarSeccion('vistaVerUnidades')">Ver Unidades de Medida</a>
<?php if (hasRole(['admin_super', 'operadormed'])): ?>            
            <a onclick="mostrarSeccion('vistaRegistrarUnidades')">Ingresar Unidades de Medida</a>
<?php endif; ?>             
        </div>

        <!-- Submenú Presentacion de medicinas anidado -->
        <div class="menu-item" style="padding-left:40px;" onclick="toggleMenu('presentaciones')"> 📐 Unidades de Medida </div>
        <div id="presentaciones" class="sub-submenu">
            <a onclick="mostrarSeccion('vistaVerPresentaciones')">Ver Presentacion de Medicamentos</a>
<?php if (hasRole(['admin_super', 'operadormed'])): ?> 
            <a onclick="mostrarSeccion('vistaRegistrarPresentaciones')">Ingresar Presentaciones</a>
<?php endif; ?> 
        </div>

        <!-- Submenú Presentacion de medicinas anidado -->
        <div class="menu-item" style="padding-left:40px;" onclick="toggleMenu('presentaciones')"> 🧵 Materiales </div>
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
        <div class="menu-item" style="padding-left:40px;" onclick="toggleMenu('beneficiarios')"> 🧑‍🎨 Participantes </div>
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
            <a onclick="mostrarSeccion('vistaActualizarEliminarBeneficiarios')">Ajustes</a>
        </div>
<?php endif; ?>

    <!-- REPORTES -->
    <div class="menu-item" onclick="toggleMenu('reportes')">📊 Reportes</div>
    <div id="reportes" class="submenu">
        <a onclick="mostrarSeccion('VistaReporteExistencias')">Existencias</a>
        <a onclick="mostrarSeccion('vistaReporteMovimientos')">Movimientos</a>
        <a onclick="mostrarSeccion('VistaReporteValorizacion')">Valorización</a>
    </div>

    <a class="menu-item" href="../../auth/logout.php" style="background:#dc2626;">🚪 Cerrar sesión</a>
</div>
<div class="content">
    <!-- ============================
         VER MEDICAMENTOS
    ===============================-->
</div> <!--cierre div content-->
<!-- ================== FIN REPORTE DE EXISTENCIAS ================== -->

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../../js/art_invent/core_art.js?v=<?= filemtime('../../js/art_invent/core_art.js') ?>"></script>
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
