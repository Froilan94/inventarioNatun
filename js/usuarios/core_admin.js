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

// ─────────────────────────────────────────────
// TOASTS
// ─────────────────────────────────────────────
function mostrarToast(tipo, mensaje, duracion = 3000) {
    const tipos = {
        'exito':   { id: 'toastExito',   spanId: 'mensajeExito'   },
        'error':   { id: 'toastError',   spanId: 'mensajeError'   },
        'warning': { id: 'toastWarning', spanId: 'mensajeWarning' },
        'info':    { id: 'toastInfo',    spanId: 'mensajeInfo'    },
    };
    const config = tipos[tipo];
    if (!config) return;

    document.getElementById(config.spanId).textContent = mensaje;
    const toast = new bootstrap.Toast(document.getElementById(config.id), {
        autohide: true,
        delay: duracion,
    });
    toast.show();
}