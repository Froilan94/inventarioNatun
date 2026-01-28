//llenar tabla usuarios de manera automatica cuando se carga la pagina, lo hará una vez y permanentemente, Correcto?
document.addEventListener("DOMContentLoaded", function () {
    const btnCargar = document.getElementById("btnCargarUsuarios");

    if (btnCargar) {
        btnCargar.addEventListener("click", function () {
            // Mostrar feedback visual
            btnCargar.disabled = true;
            btnCargar.textContent = "Cargando...";

            fetch("consultar_usuarios.php")
                .then(response => response.json())
                .then(data => {
                    let filas = "";

                    if (data.length === 0) {
                        filas = `<tr><td colspan="9" class="text-center">No hay usuarios registrados</td></tr>`;
                    } else {
                        data.forEach((u, i) => {
                            filas += `
                                <tr>
                                    <td>${i + 1}</td>
                                    <td>${u.nombre_completo}</td>
                                    <td>${u.nombre_usuario}</td>
                                    <td>${u.correo ?? ""}</td>
                                    <td>${u.telefono ?? ""}</td>
                                    <td>${u.dpi_usuario ?? ""}</td>
                                    <td>${u.genero_usuario}</td>
                                    <td>
                                        ${u.activo == 1
                                            ? '<span class="badge bg-success">Activo</span>'
                                            : '<span class="badge bg-danger">Inactivo</span>'
                                        }
                                    </td>
                                    <td>
                                        <button class="btn btn-warning btn-sm" onclick="abrirModalEditar(${u.id_usuario})">Editar</button>
                                        <button class="btn btn-danger btn-sm" onclick="eliminarUsuario(${u.id_usuario})">Eliminar</button>
                                    </td>
                                </tr>`;
                        });
                    }

                    document.getElementById("tablaUsuarios").innerHTML = filas;
                })
                .catch(error => {
                    console.error("Error:", error);
                    alert("No se pudo cargar la lista de usuarios.");
                })
                .finally(() => {
                    // Restaurar botón
                    btnCargar.disabled = false;
                    btnCargar.textContent = "Cargar Usuarios";
                });
        });
    }
});

// =============================
//   FUNCIÓN ELIMINAR
// =============================
function eliminarUsuario(id) {
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

            // Eliminar usuario
            fetch(`eliminar_usuario.php?id=${id}`)
                .then(response => response.text())
                .then(resp => {
                    Swal.close(); // Cerrar loading

                    if (resp === "ok") {
                        // ✅ Toast de éxito
                        mostrarToast('exito', 'Usuario eliminado exitosamente');
                        
                        // Recargar tabla
                        setTimeout(() => {
                            document.getElementById("btnCargarUsuarios").click();
                        }, 800);
                    } else {
                        // ❌ Toast de error
                        mostrarToast('error', 'No se pudo eliminar el usuario');
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

// Cargar roles y departamentos cuando se abre el modal
function cargarCombosEditar() {

    // Cargar roles
    fetch("get_roles.php")
        .then(r => r.json())
        .then(roles => {
            let html = "";
            roles.forEach(r => {
                html += `<option value="${r.id_rol}">${r.nombre_rol}</option>`;
            });
            document.getElementById("edit_rol_id").innerHTML = html;
        });

    // Cargar departamentos
    fetch("get_departamentos.php")
        .then(r => r.json())
        .then(departamentos => {
            let html = "";
            departamentos.forEach(r => {
                html += `<option value="${r.id_departamento}">${r.nombre_departamento}</option>`;
            });
            document.getElementById("edit_departamento_id").innerHTML = html;
        });
}


function abrirModalEditar(id) {

    cargarCombosEditar(); // Cargar roles y departamentos

    fetch("obtener_usuario.php?id=" + id)
        .then(res => res.json())
        .then(u => {

            document.getElementById("edit_id_usuario").value = u.id_usuario;
            document.getElementById("edit_nombre_completo").value = u.nombre_completo;
            document.getElementById("edit_nombre_usuario").value = u.nombre_usuario;
            document.getElementById("edit_correo").value = u.correo;
            document.getElementById("edit_telefono").value = u.telefono;
            document.getElementById("edit_dpi_usuario").value = u.dpi_usuario;
            document.getElementById("edit_genero_usuario").value = u.genero_usuario;
            document.getElementById("edit_activo").value = u.activo;

            // Esperar 300 ms para asegurar que roles y deptos están cargados
            setTimeout(() => {
                document.getElementById("edit_rol_id").value = u.rol_id;
                document.getElementById("edit_departamento_id").value = u.departamento_id;
            }, 300);

            let modal = new bootstrap.Modal(document.getElementById("modalEditarUsuario"));
            modal.show();
        });
}


document.getElementById("formEditarUsuario").addEventListener("submit", function(e){
    e.preventDefault();

    const btn = document.getElementById("btnGuardarCambios");
    const texto = document.getElementById("textoGuardar");
    const spinner = document.getElementById("spinnerGuardar");

    // UI: bloquea botón + spinner
    btn.disabled = true;
    texto.classList.add("d-none");
    spinner.classList.remove("d-none");

    const datos = new FormData(this);

    fetch("update_usuario.php", {
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
            const modalEl = document.getElementById("modalEditarUsuario");
            bootstrap.Modal.getInstance(modalEl).hide();

            // Mostrar vista usuarios
            toggleMenu('vistaVerUsuarios');

            // Recargar tabla
            document.getElementById("btnCargarUsuarios").click();

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




document.getElementById("formRegistro").addEventListener("submit", function(e) {
    e.preventDefault();

    const form = this;     // ← IMPORTANTE
    const formData = new FormData(form);

    fetch("insert_usuario.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {

        const msg = document.getElementById("mensaje");

        if (data.status === "success") {
            msg.innerHTML = `
                <div style="color: green; padding: 10px; font-weight: bold;">
                    ✅ ${data.mensaje}
                </div>`;

            form.reset();   // ← ahora sí funciona bien
        } else {
            msg.innerHTML = `
                <div style="color: red; padding: 10px; font-weight: bold;">
                    ❌ ${(data.mensaje) ? data.mensaje : data.errores.join("<br>")}
                </div>`;
        }
    })
    .catch(() => {
        document.getElementById("mensaje").innerHTML =
            `<div style="color: red;">❌ Error al conectar con el servidor.</div>`;
    });
});


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