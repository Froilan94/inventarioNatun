document.addEventListener("DOMContentLoaded", function () {
    const btnCargar = document.getElementById("btnCargarUsuarios");

    if (btnCargar) {
        btnCargar.addEventListener("click", function () {

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
                });
        });
    }
});

// =============================
//   FUNCIÓN ELIMINAR
// =============================
function eliminarUsuario(id) {

    if (!confirm("¿Seguro que desea eliminar este usuario?")) return;

    fetch("eliminar_usuario.php?id=" + id)
        .then(res => res.text())
        .then(resp => {
            if (resp === "ok") {
                alert("Usuario eliminado.");
                document.getElementById("btnCargarUsuarios").click();
            } else {
                alert("Error al eliminar");
            }
        });
}

/*// Cargar roles y departamentos cuando se abre el modal
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
}*/


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



// =============================
//   FUNCIÓN EDITAR
// =============================
function editarUsuario(id) {

    fetch("obtener_usuario.php?id=" + id)
        .then(response => response.json())
        .then(u => {

            document.getElementById("edit_id_usuario").value = u.id_usuario;
            document.getElementById("edit_nombre_completo").value = u.nombre_completo;
            document.getElementById("edit_nombre_usuario").value = u.nombre_usuario;
            document.getElementById("edit_correo").value = u.correo;
            document.getElementById("edit_telefono").value = u.telefono;
            document.getElementById("edit_dpi_usuario").value = u.dpi_usuario;
            document.getElementById("edit_genero_usuario").value = u.genero_usuario;
            document.getElementById("edit_activo").value = u.activo;

            toggleMenu('vistaEditarUsuario');
        });
}

document.getElementById("formEditarUsuario").addEventListener("submit", function(e){
    e.preventDefault();

    const datos = new FormData(this);

    fetch("update_usuario.php", {
        method: "POST",
        body: datos
    })
    .then(res => res.text())
    .then(resp => {

        if (resp === "ok") {
            alert("Usuario actualizado correctamente");
            toggleMenu('vistaVerUsuarios');
            document.getElementById("btnCargarUsuarios").click(); // refresca la tabla
        } else {
            alert("Error al actualizar");
        }
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

document.getElementById("formRegistro_med").addEventListener("submit", function(e) {
    e.preventDefault();

    const form = this;     // ← IMPORTANTE
    const formData = new FormData(form);

    fetch("insert_medicamento.php", {
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