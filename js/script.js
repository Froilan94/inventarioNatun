function agregarFila() {
    let tabla = document.querySelector("#tablaDetalles tbody");
    let fila = tabla.rows[0].cloneNode(true);

    fila.querySelectorAll("input").forEach(x => x.value = "");
    fila.querySelectorAll("select").forEach(x => x.selectedIndex = 0);

    tabla.appendChild(fila);
}

function eliminarFila(btn) {
    let tabla = document.querySelector("#tablaDetalles tbody");
    if (tabla.rows.length > 1) {
        btn.closest("tr").remove();
    }
}

// Calcular subtotales
document.addEventListener("input", function(e) {
    if (e.target.classList.contains("cantidad") || e.target.classList.contains("precio")) {
        let fila = e.target.closest("tr");
        let cantidad = parseFloat(fila.querySelector(".cantidad").value) || 0;
        let precio = parseFloat(fila.querySelector(".precio").value) || 0;

        fila.querySelector(".subtotal").value = (cantidad * precio).toFixed(4);
    }
});



function toggleMenu(id) {
    let submenu = document.getElementById(id);
    submenu.style.display = submenu.style.display === "block" ? "none" : "block";
}

function mostrarSeccion(id) {
    // Ocultar todas
    document.querySelectorAll(".seccion").forEach(s => s.style.display = "none");

    // Mostrar la sección seleccionada
    document.getElementById(id).style.display = "block";
 }
    // Mostrar la sección de usuarios

document.addEventListener("DOMContentLoaded", function () {
    
    // Cargar Roles
    fetch("get_roles.php")
        .then(res => res.json())
        .then(data => {
            let select = document.querySelector("select[name='rol_id']");
            select.innerHTML = `<option value="">Seleccione...</option>`;
            data.forEach(r => {
                select.innerHTML += `<option value="${r.id_rol}">${r.nombre_rol}</option>`;
            });
        });

    // Cargar Departamentos
    fetch("get_departamentos.php")
        .then(res => res.json())
        .then(data => {
            let select = document.querySelector("select[name='departamento_id']");
            select.innerHTML = `<option value="">Seleccione...</option>`;
            data.forEach(d => {
                select.innerHTML += `<option value="${d.id_departamento}">${d.nombre_departamento}</option>`;
            });
        });

});
