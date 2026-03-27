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
}
    // Mostrar la sección de usuarios

document.addEventListener("DOMContentLoaded", function () {
    cargarRoles() 
    cargarDepartamentos()
  //  cargarCategorias()
});

function cargarCategorias() {
    fetch("get_categorias.php")
        .then(res => res.json())
        .then(data => {
            let select = document.querySelector("select[name='categoria_id']");
             //if (!select) return;

            select.innerHTML = `<option value="">Seleccione...</option>`;
            data.forEach(d => {
                select.innerHTML += `<option value="${d.id_categoria}">${d.nombre_categoria}</option>`;
            });
        });   
}        

function cargarRoles() {
    // Cargar Roles
    fetch("../get_roles.php")
        .then(res => res.json())
        .then(data => {
            let select = document.querySelector("select[name='rol_id']");
            select.innerHTML = `<option value="">Seleccione...</option>`;
            data.forEach(r => {
                select.innerHTML += `<option value="${r.id_rol}">${r.nombre_rol}</option>`;
            });
        });
}               

function cargarDepartamentos() {        
    // Cargar Departamentos
    fetch("../get_departamentos.php")
        .then(res => res.json())
        .then(data => {
            let select = document.querySelector("select[name='departamento_id']");
            select.innerHTML = `<option value="">Seleccione...</option>`;
            data.forEach(d => {
                select.innerHTML += `<option value="${d.id_departamento}">${d.nombre_departamento}</option>`;
            });
        });    
}                