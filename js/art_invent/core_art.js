function toggleMenu(id) {
    let submenu = document.getElementById(id);
    if (!submenu) return; // ✅ El menú no existe en el DOM

    // Verificar si está visible actualmente
    let estabaVisible = (submenu.style.display === "block");
    
    // Identificar el tipo de menú
    let menusPrincipales = ['maestros', 'movimientos', 'reportes'];
    let esMenuPrincipal = menusPrincipales.includes(id);
    
    if (esMenuPrincipal) {
        // Cerrar todos los menús principales
        menusPrincipales.forEach(function(menuId) {
            let el = document.getElementById(menuId);
            if (el) el.style.display = "none";
        });
        
        // Cerrar todos los sub-submenús
        let todosSubSubMenus = document.querySelectorAll('.sub-submenu');
        todosSubSubMenus.forEach(function(elem) {
            elem.style.display = "none";
        });
        
        // Abrir el menú clickeado solo si estaba cerrado
        if (!estabaVisible) {
            submenu.style.display = "block";
        }
    } else {
        // Es un sub-submenú
        let todosSubSubMenus = document.querySelectorAll('.sub-submenu');
        todosSubSubMenus.forEach(function(elem) {
            elem.style.display = "none";
        });
        
        // Abrir el sub-submenú clickeado solo si estaba cerrado
        if (!estabaVisible) {
            submenu.style.display = "block";
        }
    }
}

function mostrarSeccion(id) {
    // Ocultar todas
    document.querySelectorAll(".seccion").forEach(sec => {
        sec.style.display = "none";
    });

    const seccion = document.getElementById(id);
    if (!seccion) {
        console.warn("No existe la sección:", id);
        return;
    }

    seccion.style.display = "block";

// Unidades
    if (id === 'vistaVerUnidades')           initVerUnidades();
    if (id === 'vistaRegistrarUnidades')       initRegistrarUnidad();
// Presentaciones
    if (id === 'vistaVerPresentaciones')     initVerPresentaciones();
// Proveedores
    if (id === 'vistaVerProveedores')        initVerProveedores();
// Participantes
    if (id === 'vistaVerParticipantes')      initVerParticipantes();
    if (id === 'vistaRegistrarParticipantes') initRegistrarParticipante();
// Lotes
    if (id === 'vistaVerLotes')              initVerLotes();
// Firmas
    if (id === 'vistaVerFirmas')             initVerFirmas();    
        // ── Inicializadores por sección ──────────────────
    if (id === 'vistaRegistrarIngresoMed') initFormIngresoMed();
    if (id === 'vistaRegistrarEgresosMed')  initFormSalidaMed();
    if (id === 'VistaReporteExistencias')  initVistaExistencias();
    if (id === 'vistaReporteMovimientos') initVistaMovimientos();
}

/**
 * Muestra un toast de Bootstrap
 * @param {string} tipo - 'exito', 'error', 'warning', 'info'
 * @param {string} mensaje - Mensaje a mostrar
 * @param {number} duracion - Duración en ms (opcional, default 3000)
 */
function mostrarToast(tipo, mensaje, duracion = 3000) {
    const tipos = {
        'exito': { id: 'toastExito', spanId: 'mensajeExito' },
        'error': { id: 'toastError', spanId: 'mensajeError' },
        'warning': { id: 'toastWarning', spanId: 'mensajeWarning' },
        'info': { id: 'toastInfo', spanId: 'mensajeInfo' }
    };

    const config = tipos[tipo];
    if (!config) {
        console.error('Tipo de toast no válido');
        return;
    }

    const toastEl = document.getElementById(config.id);
    const mensajeSpan = document.getElementById(config.spanId);
    
    // Actualizar mensaje
    mensajeSpan.textContent = mensaje;

    // Crear y mostrar toast
    const toast = new bootstrap.Toast(toastEl, {
        autohide: true,
        delay: duracion
    });

    toast.show();
}