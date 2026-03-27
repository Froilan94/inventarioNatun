/**
 * comunidades_programas.js
 * Maneja CRUD de Comunidades y Programas.
 * Patrón: igual que usuarios.js
 */

// ══════════════════════════════════════════════════════
// COMUNIDADES
// ══════════════════════════════════════════════════════

// ── Cargar tabla ──────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('btnCargarComunidades');
    if (btn) {
        btn.addEventListener('click', cargarComunidades);
    }

    const btnP = document.getElementById('btnCargarProgramas');
    if (btnP) {
        btnP.addEventListener('click', cargarProgramas);
    }
});

function cargarComunidades() {
    const btn = document.getElementById('btnCargarComunidades');
    btn.disabled = true;
    btn.textContent = 'Cargando...';

    fetch('../../api/inventarios/maestros/comunidades.php?action=get_all')
        .then(r => r.json())
        .then(json => {
            if (!json.ok) throw new Error(json.msg);

            let filas = '';
            if (!json.data.length) {
                filas = `<tr><td colspan="4" class="text-center">No hay comunidades registradas</td></tr>`;
            } else {
                json.data.forEach((c, i) => {
                    filas += `
                        <tr>
                            <td>${i + 1}</td>
                            <td>${c.nombre_comunidad}</td>
                            <td>${c.direccion ?? ''}</td>
                            <td>
                                <button class="btn btn-warning btn-sm"
                                        onclick="abrirModalEditarComunidad(${c.id_comunidad})">
                                    Editar
                                </button>
                                <button class="btn btn-danger btn-sm"
                                        onclick="eliminarComunidad(${c.id_comunidad})">
                                    Eliminar
                                </button>
                            </td>
                        </tr>`;
                });
            }
            document.getElementById('tablaComunidades').innerHTML = filas;
        })
        .catch(err => mostrarToast('error', 'Error: ' + err.message))
        .finally(() => {
            btn.disabled = false;
            btn.textContent = 'Cargar Comunidades';
        });
}

// ── Registrar ─────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formComunidad');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        fetch('../../api/inventarios/maestros/comunidades.php?action=insertar', {
            method: 'POST',
            body: new FormData(this),
        })
        .then(r => r.json())
        .then(json => {
            if (json.ok) {
                mostrarToast('exito', json.msg);
                form.reset();
            } else {
                mostrarToast('error', json.msg);
            }
        })
        .catch(() => mostrarToast('error', 'Error de conexión.'));
    });
});

// ── Editar ────────────────────────────────────────────
function abrirModalEditarComunidad(id) {
    fetch(`../../api/inventarios/maestros/comunidades.php?action=get_one&id=${id}`)
        .then(r => r.json())
        .then(json => {
            if (!json.ok) return mostrarToast('error', json.msg);
            const c = json.data;
            document.getElementById('editCom_id').value       = c.id_comunidad;
            document.getElementById('editCom_nombre').value   = c.nombre_comunidad;
            document.getElementById('editCom_direccion').value = c.direccion ?? '';
            new bootstrap.Modal(document.getElementById('modalEditarComunidad')).show();
        });
}

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formEditarComunidad');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const btn     = document.getElementById('btnGuardarComunidad');
        const texto   = document.getElementById('textoGuardarCom');
        const spinner = document.getElementById('spinnerGuardarCom');

        btn.disabled = true;
        texto.classList.add('d-none');
        spinner.classList.remove('d-none');

        fetch('../../api/inventarios/maestros/comunidades.php?action=actualizar', {
            method: 'POST',
            body: new FormData(this),
        })
        .then(r => r.json())
        .then(json => {
            if (json.ok) {
                mostrarToast('exito', json.msg);
                bootstrap.Modal.getInstance(
                    document.getElementById('modalEditarComunidad')
                ).hide();
                document.getElementById('btnCargarComunidades').click();
            } else {
                mostrarToast('error', json.msg);
            }
        })
        .catch(() => mostrarToast('error', 'Error de conexión.'))
        .finally(() => {
            btn.disabled = false;
            texto.classList.remove('d-none');
            spinner.classList.add('d-none');
        });
    });
});

// ── Eliminar ──────────────────────────────────────────
function eliminarComunidad(id) {
    Swal.fire({
        title: '¿Eliminar comunidad?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
    }).then(result => {
        if (!result.isConfirmed) return;

        Swal.fire({ title: 'Eliminando...', allowOutsideClick: false,
                    didOpen: () => Swal.showLoading() });

        fetch(`../../api/inventarios/maestros/comunidades.php?action=eliminar&id=${id}`)
            .then(r => r.json())
            .then(json => {
                Swal.close();
                if (json.ok) {
                    mostrarToast('exito', json.msg);
                    setTimeout(() => document.getElementById('btnCargarComunidades').click(), 600);
                } else {
                    mostrarToast('error', json.msg);
                }
            })
            .catch(() => { Swal.close(); mostrarToast('error', 'Error de conexión.'); });
    });
}

// ══════════════════════════════════════════════════════
// PROGRAMAS  (conectado a tabla `departamentos` en BD)
// ══════════════════════════════════════════════════════

// ── Cargar tabla ──────────────────────────────────────
function cargarProgramas() {
    const btn = document.getElementById('btnCargarProgramas');
    btn.disabled = true;
    btn.textContent = 'Cargando...';

    fetch('../../api/inventarios/maestros/programas.php?action=get_all')
        .then(r => r.json())
        .then(json => {
            if (!json.ok) throw new Error(json.msg);

            let filas = '';
            if (!json.data.length) {
                filas = `<tr><td colspan="4" class="text-center">No hay programas registrados</td></tr>`;
            } else {
                json.data.forEach((p, i) => {
                    filas += `
                        <tr>
                            <td>${i + 1}</td>
                            <td>${p.nombre_programa}</td>
                            <td>${p.descripcion ?? ''}</td>
                            <td>
                                <button class="btn btn-warning btn-sm"
                                        onclick="abrirModalEditarPrograma(${p.id_programa})">
                                    Editar
                                </button>
                                <button class="btn btn-danger btn-sm"
                                        onclick="eliminarPrograma(${p.id_programa})">
                                    Eliminar
                                </button>
                            </td>
                        </tr>`;
                });
            }
            document.getElementById('tablaProgramas').innerHTML = filas;
        })
        .catch(err => mostrarToast('error', 'Error: ' + err.message))
        .finally(() => {
            btn.disabled = false;
            btn.textContent = 'Cargar Programas';
        });
}

// ── Registrar ─────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formPrograma');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        fetch('../../api/inventarios/maestros/programas.php?action=insertar', {
            method: 'POST',
            body: new FormData(this),
        })
        .then(r => r.json())
        .then(json => {
            if (json.ok) {
                mostrarToast('exito', json.msg);
                this.reset();
            } else {
                mostrarToast('error', json.msg);
            }
        })
        .catch(() => mostrarToast('error', 'Error de conexión.'));
    });
});

// ── Editar ────────────────────────────────────────────
function abrirModalEditarPrograma(id) {
    fetch(`../../api/inventarios/maestros/programas.php?action=get_one&id=${id}`)
        .then(r => r.json())
        .then(json => {
            if (!json.ok) return mostrarToast('error', json.msg);
            const p = json.data;
            document.getElementById('editProg_id').value          = p.id_programa;
            document.getElementById('editProg_nombre').value      = p.nombre_programa;
            document.getElementById('editProg_descripcion').value = p.descripcion ?? '';
            new bootstrap.Modal(document.getElementById('modalEditarPrograma')).show();
        });
}

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formEditarPrograma');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const btn     = document.getElementById('btnGuardarPrograma');
        const texto   = document.getElementById('textoGuardarProg');
        const spinner = document.getElementById('spinnerGuardarProg');

        btn.disabled = true;
        texto.classList.add('d-none');
        spinner.classList.remove('d-none');

        fetch('../../api/inventarios/maestros/programas.php?action=actualizar', {
            method: 'POST',
            body: new FormData(this),
        })
        .then(r => r.json())
        .then(json => {
            if (json.ok) {
                mostrarToast('exito', json.msg);
                bootstrap.Modal.getInstance(
                    document.getElementById('modalEditarPrograma')
                ).hide();
                document.getElementById('btnCargarProgramas').click();
            } else {
                mostrarToast('error', json.msg);
            }
        })
        .catch(() => mostrarToast('error', 'Error de conexión.'))
        .finally(() => {
            btn.disabled = false;
            texto.classList.remove('d-none');
            spinner.classList.add('d-none');
        });
    });
});

// ── Eliminar ──────────────────────────────────────────
function eliminarPrograma(id) {
    Swal.fire({
        title: '¿Eliminar programa?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
    }).then(result => {
        if (!result.isConfirmed) return;

        Swal.fire({ title: 'Eliminando...', allowOutsideClick: false,
                    didOpen: () => Swal.showLoading() });

        fetch(`../../api/inventarios/maestros/programas.php?action=eliminar&id=${id}`)
            .then(r => r.json())
            .then(json => {
                Swal.close();
                if (json.ok) {
                    mostrarToast('exito', json.msg);
                    setTimeout(() => document.getElementById('btnCargarProgramas').click(), 600);
                } else {
                    mostrarToast('error', json.msg);
                }
            })
            .catch(() => { Swal.close(); mostrarToast('error', 'Error de conexión.'); });
    });
}
