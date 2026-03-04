document.addEventListener("DOMContentLoaded", function () {
    console.log("JS cargado correctamente y DOM listo");
    /* ============================
       CARGAR MEDICAMENTOS (NUEVO)
       ============================ */
    const btnCargarMedicamentos = document.getElementById("btnCargarMedicamentos");

    if (btnCargarMedicamentos) {
        btnCargarMedicamentos.addEventListener("click", function () {
            // Mostrar feedback visual
            btnCargarMedicamentos.disabled = true;
            btnCargarMedicamentos.textContent = "Cargando...";

            fetch("api/inventarios/medicamentos/consultar_medicamentos.php")
                .then(res => res.json())
                .then(data => {
                    let filas = "";

                    if (data.length === 0) {
                        filas = `<tr>
                            <td colspan="4" class="text-center">
                                No hay medicamentos registrados
                            </td>
                        </tr>`;
                    } else {
                        data.forEach((m, i) => {
                            filas += `
                                <tr>
                                    <td>${i + 1}</td>
                                    <td>${m.nombre_comercial}</td>
                                    <td>${m.nombre_generico ?? ""}</td>
                                    <td>
                                        ${m.activo == 1
                                            ? '<span class="badge bg-success">Activo</span>'
                                            : '<span class="badge bg-danger">Inactivo</span>'
                                        }
                                    </td>                                        
                                            <td>${botonesMedicamentos(m.id_medicamento)}</td>
                                </tr>`;
                        });
                    }

                    document.getElementById("tablaMedicamentos").innerHTML = filas;
                })
                .catch(err => {
                    console.error(err);
                    alert("Error al cargar medicamentos");
                })
                .finally(() => {
                    // Restaurar botón
                    btnCargarMedicamentos.disabled = false;
                    btnCargarMedicamentos.textContent = "Cargar Medicamentos";
                });
        });
    }

});

document.getElementById("formMedicamento").addEventListener("submit", function(e) {
    e.preventDefault();

    const form = this;
    const formData = new FormData(form);

    fetch("api/inventarios/medicamentos/insert_medicamento.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {

        const msg = document.getElementById("mensajeMedicamento");

        if (data.status === "success") {
            msg.innerHTML = `
                <div style="color: green; padding: 10px; font-weight: bold;">
                    ✅ ${data.mensaje}
                </div>`;

            form.reset();
        } else {
            msg.innerHTML = `
                <div style="color: red; padding: 10px; font-weight: bold;">
                    ❌ ${(data.mensaje) ? data.mensaje : data.errores.join("<br>")}
                </div>`;
        }
    })
    .catch(() => {
        document.getElementById("mensajeMedicamento").innerHTML =
            `<div style="color: red;">❌ Error al conectar con el servidor.</div>`;
    });
});

function abrirModalEditarMed(id) {

    fetch("api/inventarios/medicamentos/obtener_medicamento.php?id=" + id)
        .then(res => res.json())
        .then(m => {

            document.getElementById("edit_id_medicamento").value = m.id_medicamento;
            document.getElementById("edit_nombre_comercial").value = m.nombre_comercial;
            document.getElementById("edit_nombre_generico").value = m.nombre_generico;
            document.getElementById("edit_activo").value = m.activo;

            let modal = new bootstrap.Modal(document.getElementById("modalEditarMedicamento"));
            modal.show();
        });
}

document.getElementById("formEditarMedicamento").addEventListener("submit", function(e){
    e.preventDefault();

    const btn = document.getElementById("btnGuardarCambios");
    const texto = document.getElementById("textoGuardar");
    const spinner = document.getElementById("spinnerGuardar");

    // UI: bloquea botón + spinner
    btn.disabled = true;
    texto.classList.add("d-none");
    spinner.classList.remove("d-none");

    const datos = new FormData(this);

    fetch("api/inventarios/medicamentos/update_medicamento.php", {
        method: "POST",
        body: datos
    })
    .then(res => res.text())
    .then(resp => {

        // UI reset
        btn.disabled = false;
        texto.classList.remove("d-none");
        spinner.classList.add("d-none");

        if (resp === "ok") {

            // Toast éxito
            new bootstrap.Toast(
                document.getElementById("toastExito")
            ).show();

            // Cerrar modal
            const modalEl = document.getElementById("modalEditarMedicamento");
            bootstrap.Modal.getInstance(modalEl).hide();

            // Mostrar vista Medicamentos
            toggleMenu('vistaMedicamentos');

            // Recargar tabla
            document.getElementById("btnCargarMedicamentos").click();

        } else {
            new bootstrap.Toast(
                document.getElementById("toastError")
            ).show();
        }
    })
    .catch(() => {
        btn.disabled = false;
        texto.classList.remove("d-none");
        spinner.classList.add("d-none");

        new bootstrap.Toast(
            document.getElementById("toastError")
        ).show();
    });
});


function eliminarMedicamento(id) {
    // Confirmación con SweetAlert2 (o confirm nativo)
    Swal.fire({
        title: '¿Eliminar Dato',
        text: "Esta acción no se puede deshacer",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar loading
            Swal.fire({
                title: 'Eliminando...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(`api/inventarios/medicamentos/eliminar_medicamento.php?id=${id}`)
                .then(response => response.text())
                .then(resp => {
                    Swal.close(); // Cerrar loading

                    if (resp === "ok") {
                        // ✅ Toast de éxito
                        mostrarToast('exito', 'Medicamento eliminado exitosamente');
                        
                        // Recargar tabla
                        setTimeout(() => {
                            document.getElementById("btnCargarMedicamentos").click();
                        }, 800);
                    } else {
                        // ❌ Toast de error
                        mostrarToast('error', 'No se pudo eliminar el Medicamento');
                    }
                })
                .catch(error => {
                    Swal.close();
                    console.error("Error:", error);
                    mostrarToast('error', 'Error de conexión con el servidor');
                });
        }
    });
}

function botonesMedicamentos(id) {
    let html = '';

    if (['admin_super', 'operadormed'].includes(USER_ROLE)) {
        html += `
            <button class="btn btn-warning btn-sm"
                onclick="abrirModalEditarMed(${id})">
                Editar
            </button>
        `;
    }

    if (USER_ROLE === 'admin_super') {
        html += `
            <button class="btn btn-danger btn-sm"
                onclick="eliminarMedicamento(${id})">
                Eliminar
            </button>
        `;
    }

    return html;
}