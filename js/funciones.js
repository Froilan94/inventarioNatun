document.addEventListener("DOMContentLoaded", function () {
    const btnCargar = document.getElementById("btnCargarUsuarios");

    if (btnCargar) {
        btnCargar.addEventListener("click", function () {

            fetch("consultar_usuarios.php")
                .then(response => response.json())
                .then(data => {
                    let filas = "";

                    if (data.length === 0) {
                        filas = `<tr><td colspan="8" class="text-center">No hay usuarios registrados</td></tr>`;
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

document.getElementById("formRegistro").addEventListener("submit", function(e) {
    e.preventDefault(); // evita que vaya al PHP directamente

    const formData = new FormData(this);

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
            this.reset();
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
