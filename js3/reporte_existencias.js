document.getElementById("btnAplicarFiltros").addEventListener("click", cargarReporte);

function cargarReporte() {
    const params = new URLSearchParams({
        fechaInicio: document.getElementById("fechaInicio").value,
        fechaFin: document.getElementById("fechaFin").value,
        medicamento: document.getElementById("filtroMedicamento").value,
        proveedor: document.getElementById("filtroProveedor").value
    });

    fetch("api/reporte_existencias.php?" + params.toString())
        .then(res => res.json())
        .then(data => {
            let html = "";
            data.forEach((row, i) => {
                let badge = "bg-success";
                if (row.estado_vencimiento === "CRITICO") badge = "bg-danger";
                else if (row.estado_vencimiento === "ADVERTENCIA") badge = "bg-warning";

                html += `
                <tr>
                    <td>${i + 1}</td>
                    <td>${row.medicamento}</td>
                    <td>${row.nombre_generico ?? ""}</td>
                    <td>${row.numero_lote}</td>
                    <td>${row.fecha_ingreso}</td>
                    <td>${row.fecha_vencimiento}</td>
                    <td>${row.cantidad_existente}</td>
                    <td>Q ${row.valor_unitario}</td>
                    <td>Q ${row.monto_existente}</td>
                    <td>${row.proveedor_donante ?? ""}</td>
                    <td><span class="badge ${badge}">${row.estado_vencimiento}</span></td>
                </tr>`;
            });
            document.getElementById("tablaReporteExistencias").innerHTML = html;
        });
}

/* Exportaciones */
document.getElementById("btnExportarPDF").onclick = () => exportar("pdf");
document.getElementById("btnExportarExcel").onclick = () => exportar("excel");

function exportar(tipo) {
    const params = new URLSearchParams({
        fechaInicio: fechaInicio.value,
        fechaFin: fechaFin.value,
        medicamento: filtroMedicamento.value,
        proveedor: filtroProveedor.value
    });
    window.open(`reportes/existencias_${tipo}.php?` + params.toString(), "_blank");
}
