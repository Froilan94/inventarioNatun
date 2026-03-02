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