/**
 * maestros_med.js
 * JS para: Unidades, Presentaciones, Proveedores, Participantes, Lotes, Firmas
 * Permisos manejados desde el PHP — el JS solo controla visibilidad de botones
 * usando USER_ROLE definido en core.js
 */

// ══════════════════════════════════════════════════════
// UNIDADES DE MEDIDA
// ══════════════════════════════════════════════════════
function initVerUnidades() {
    cargarUnidades();
}

function cargarUnidades() {
    const btn = document.getElementById('btnCargarUnidades');
    if (btn) { btn.disabled = true; btn.textContent = 'Cargando...'; }

    fetch('../../api/inventarios/medicamentos/maestros/unidades_med.php?action=get_all')
        .then(r => r.json())
        .then(json => {
            if (!json.ok) throw new Error(json.msg);
            const puedeEditar = ['admin_super','operadormed'].includes(USER_ROLE);

            let filas = '';
            if (!json.data.length) {
                filas = `<tr><td colspan="3" class="text-center">No hay unidades registradas</td></tr>`;
            } else {
                json.data.forEach((u, i) => {
                    filas += `<tr>
                        <td>${i+1}</td>
                        <td>${u.nombre_unidad}</td>
                        <td>
                            ${puedeEditar ? `<button class="btn btn-warning btn-sm me-1" onclick="abrirEditarUnidad(${u.id_unidad_med})">Editar</button>` : ''}
                            ${USER_ROLE === 'admin_super' ? `<button class="btn btn-danger btn-sm" onclick="eliminarUnidad(${u.id_unidad_med})">Eliminar</button>` : ''}
                        </td>
                    </tr>`;
                });
            }
            document.getElementById('tablaUnidades').innerHTML = filas;
        })
        .catch(err => mostrarToast('error', err.message))
        .finally(() => { if (btn) { btn.disabled = false; btn.textContent = 'Cargar Unidades'; } });
}

function initRegistrarUnidad() { /* form listo en HTML */ }

document.addEventListener('DOMContentLoaded', () => {
    // Unidades
    const formU = document.getElementById('formUnidad');
    if (formU) formU.addEventListener('submit', e => {
        e.preventDefault();
        const msg = document.getElementById('mensajeUnidad');
        fetch('../../api/inventarios/medicamentos/maestros/unidades_med.php?action=insertar', { method:'POST', body: new FormData(formU) })
            .then(r => r.json())
            .then(json => {
                if (json.ok) { mostrarToast('exito', json.msg); formU.reset(); }
                else mostrarToast('error', json.msg);
            })
            .catch(() => mostrarToast('error', 'Error de conexión.'));
    });

    // Editar Unidades
    const formEU = document.getElementById('formEditarUnidad');
    if (formEU) formEU.addEventListener('submit', e => {
        e.preventDefault();
        _submitModal(formEU, '../../api/inventarios/medicamentos/maestros/unidades_med.php?action=actualizar',
            'modalEditarUnidad', cargarUnidades);
    });

    // Presentaciones
    const formP = document.getElementById('formPresentacion');
    if (formP) formP.addEventListener('submit', e => {
        e.preventDefault();
        const msg = document.getElementById('mensajePresentacion');
        fetch('../../api/inventarios/medicamentos/maestros/presentaciones_med.php?action=insertar', { method:'POST', body: new FormData(formP) })
            .then(r => r.json())
            .then(json => {
                if (json.ok) { mostrarToast('exito', json.msg); formP.reset(); }
                else mostrarToast('error', json.msg);
            })
            .catch(() => mostrarToast('error', 'Error de conexión.'));
    });

    const formEP = document.getElementById('formEditarPresentacion');
    if (formEP) formEP.addEventListener('submit', e => {
        e.preventDefault();
        _submitModal(formEP, '../../api/inventarios/medicamentos/maestros/presentaciones_med.php?action=actualizar',
            'modalEditarPresentacion', cargarPresentaciones);
    });

    // Proveedores
    const formProv = document.getElementById('formProveedor');
    if (formProv) formProv.addEventListener('submit', e => {
        e.preventDefault();
        const msg = document.getElementById('mensajeProveedor');
        fetch('../../api/inventarios/medicamentos/maestros/proveedores_med.php?action=insertar', { method:'POST', body: new FormData(formProv) })
            .then(r => r.json())
            .then(json => {
                if (json.ok) { mostrarToast('exito', json.msg); formProv.reset(); }
                else mostrarToast('error', json.msg);
            })
            .catch(() => mostrarToast('error', 'Error de conexión.'));
    });

    const formEProv = document.getElementById('formEditarProveedor');
    if (formEProv) formEProv.addEventListener('submit', e => {
        e.preventDefault();
        _submitModal(formEProv, '../../api/inventarios/medicamentos/maestros/proveedores_med.php?action=actualizar',
            'modalEditarProveedor', cargarProveedores);
    });

    // Participantes
    const formPart = document.getElementById('formParticipante');
    if (formPart) formPart.addEventListener('submit', e => {
        e.preventDefault();
        const msg = document.getElementById('mensajeParticipante');
        fetch('../../api/inventarios/medicamentos/maestros/participantes_med.php?action=insertar', { method:'POST', body: new FormData(formPart) })
            .then(r => r.json())
            .then(json => {
                if (json.ok) { mostrarToast('exito', json.msg); formPart.reset(); }
                else mostrarToast('error', json.msg);
            })
            .catch(() => mostrarToast('error', 'Error de conexión.'));
    });

    const formEPart = document.getElementById('formEditarParticipante');
    if (formEPart) formEPart.addEventListener('submit', e => {
        e.preventDefault();
        _submitModal(formEPart, '../../api/inventarios/medicamentos/maestros/participantes_med.php?action=actualizar',
            'modalEditarParticipante', cargarParticipantes);
    });

    // Firmas
    const formF = document.getElementById('formFirma');
    if (formF) formF.addEventListener('submit', e => {
        e.preventDefault();
        const msg = document.getElementById('mensajeFirma');
        fetch('../../api/inventarios/medicamentos/maestros/firmas_planilla.php?action=insertar', { method:'POST', body: new FormData(formF) })
            .then(r => r.json())
            .then(json => {
                if (json.ok) { mostrarToast('exito', json.msg); formF.reset(); }
                else mostrarToast('error', json.msg);
            })
            .catch(() => mostrarToast('error', 'Error de conexión.'));
    });

    const formEF = document.getElementById('formEditarFirma');
    if (formEF) formEF.addEventListener('submit', e => {
        e.preventDefault();
        _submitModal(formEF, '../../api/inventarios/medicamentos/maestros/firmas_planilla.php?action=actualizar',
            'modalEditarFirma', cargarFirmas);
    });

    // Programas
const formProg = document.getElementById('formPrograma');
if (formProg) formProg.addEventListener('submit', e => {
    e.preventDefault();

    fetch('../../api/inventarios/medicamentos/maestros/programas.php?action=insertar', {
        method: 'POST',
        body: new FormData(formProg)
    })
    .then(r => r.json())
    .then(json => {
        if (json.ok) {
            mostrarToast('exito', json.msg);
            formProg.reset();
        } else {
            mostrarToast('error', json.msg);
        }
    })
    .catch(() => mostrarToast('error', 'Error de conexión.'));
});

const formEProg = document.getElementById('formEditarPrograma');
if (formEProg) formEProg.addEventListener('submit', e => {
    e.preventDefault();

    _submitModal(
        formEProg,
        '../../api/inventarios/medicamentos/maestros/programas.php?action=actualizar',
        'modalEditarPrograma',
        cargarProgramas
    );
});
});

// Helper modal submit
function _submitModal(form, url, modalId, recargar) {
    fetch(url, { method:'POST', body: new FormData(form) })
        .then(r => r.json())
        .then(json => {
            if (json.ok) {
                mostrarToast('exito', json.msg);
                bootstrap.Modal.getInstance(document.getElementById(modalId))?.hide();
                recargar();
            } else {
                mostrarToast('error', json.msg);
            }
        })
        .catch(() => mostrarToast('error', 'Error de conexión.'));
}

function abrirEditarUnidad(id) {
    fetch(`../../api/inventarios/medicamentos/maestros/unidades_med.php?action=get_one&id=${id}`)
        .then(r => r.json()).then(json => {
            if (!json.ok) return mostrarToast('error', json.msg);
            document.getElementById('editU_id').value     = json.data.id_unidad_med;
            document.getElementById('editU_nombre').value = json.data.nombre_unidad;
            new bootstrap.Modal(document.getElementById('modalEditarUnidad')).show();
        });
}

function eliminarUnidad(id) { _eliminar(id, '../../api/inventarios/medicamentos/maestros/unidades_med.php', cargarUnidades, 'unidad'); }

// ══════════════════════════════════════════════════════
// PRESENTACIONES
// ══════════════════════════════════════════════════════
function initVerPresentaciones() { cargarPresentaciones(); }

function cargarPresentaciones() {
    const btn = document.getElementById('btnCargarPresentaciones');
    if (btn) { btn.disabled = true; btn.textContent = 'Cargando...'; }
    const puedeEditar = ['admin_super','operadormed'].includes(USER_ROLE);

    fetch('../../api/inventarios/medicamentos/maestros/presentaciones_med.php?action=get_all')
        .then(r => r.json())
        .then(json => {
            if (!json.ok) throw new Error(json.msg);
            let filas = '';
            if (!json.data.length) {
                filas = `<tr><td colspan="3" class="text-center">No hay presentaciones</td></tr>`;
            } else {
                json.data.forEach((p, i) => {
                    filas += `<tr>
                        <td>${i+1}</td><td>${p.nombre_presentacion}</td>
                        <td>
                            ${puedeEditar ? `<button class="btn btn-warning btn-sm me-1" onclick="abrirEditarPresentacion(${p.id_presentacion_med})">Editar</button>` : ''}
                            ${USER_ROLE === 'admin_super' ? `<button class="btn btn-danger btn-sm" onclick="eliminarPresentacion(${p.id_presentacion_med})">Eliminar</button>` : ''}
                        </td>
                    </tr>`;
                });
            }
            document.getElementById('tablaPresentaciones').innerHTML = filas;
        })
        .catch(err => mostrarToast('error', err.message))
        .finally(() => { if (btn) { btn.disabled = false; btn.textContent = 'Cargar Presentaciones'; } });
}

function abrirEditarPresentacion(id) {
    fetch(`../../api/inventarios/medicamentos/maestros/presentaciones_med.php?action=get_one&id=${id}`)
        .then(r => r.json()).then(json => {
            if (!json.ok) return mostrarToast('error', json.msg);
            document.getElementById('editP_id').value     = json.data.id_presentacion_med;
            document.getElementById('editP_nombre').value = json.data.nombre_presentacion;
            new bootstrap.Modal(document.getElementById('modalEditarPresentacion')).show();
        });
}

function eliminarPresentacion(id) { _eliminar(id, '../../api/inventarios/medicamentos/maestros/presentaciones_med.php', cargarPresentaciones, 'presentación'); }

// ══════════════════════════════════════════════════════
// PROVEEDORES / DONANTES
// ══════════════════════════════════════════════════════
function initVerProveedores() { cargarProveedores(); }

function cargarProveedores() {
    const btn = document.getElementById('btnCargarProveedores');
    if (btn) { btn.disabled = true; btn.textContent = 'Cargando...'; }
    const puedeEditar = ['admin_super','operadormed'].includes(USER_ROLE);

    fetch('../../api/inventarios/medicamentos/maestros/proveedores_med.php?action=get_all')
        .then(r => r.json())
        .then(json => {
            if (!json.ok) throw new Error(json.msg);
            let filas = '';
            if (!json.data.length) {
                filas = `<tr><td colspan="8" class="text-center">No hay proveedores</td></tr>`;
            } else {
                json.data.forEach((p, i) => {
                    filas += `<tr>
                        <td>${i+1}</td>
                        <td>${p.nombre_proveedor}</td>
                        <td>${p.nit_proveedor ?? '—'}</td>
                        <td>${p.tipo_proveedor}</td>
                        <td>${p.telefono ?? '—'}</td>
                        <td>${p.direccion ?? '—'}</td>                        
                        <td>${p.correo ?? '—'}</td>
                        <td>${p.observacion_proveedor ?? '—'}</td>                        
                        <td>${p.activo == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>'}</td>
                        <td>
                            ${puedeEditar ? `<button class="btn btn-warning btn-sm me-1" onclick="abrirEditarProveedor(${p.id_proveedor_med})">Editar</button>` : ''}
                            ${USER_ROLE === 'admin_super' ? `<button class="btn btn-danger btn-sm" onclick="eliminarProveedor(${p.id_proveedor_med})">Eliminar</button>` : ''}
                        </td>
                    </tr>`;
                });
            }
            document.getElementById('tablaProveedores').innerHTML = filas;
        })
        .catch(err => mostrarToast('error', err.message))
        .finally(() => { if (btn) { btn.disabled = false; btn.textContent = 'Cargar Proveedores'; } });
}

function abrirEditarProveedor(id) {
    fetch(`../../api/inventarios/medicamentos/maestros/proveedores_med.php?action=get_one&id=${id}`)
        .then(r => r.json()).then(json => {
            if (!json.ok) return mostrarToast('error', json.msg);
            const p = json.data;
            document.getElementById('editProv_id').value     = p.id_proveedor_med;
            document.getElementById('editProv_nombre').value = p.nombre_proveedor;
            document.getElementById('editProv_nit').value    = p.nit_proveedor ?? '';
            document.getElementById('editProv_genero').value = p.genero_proveedor;
            document.getElementById('editProv_tipo').value   = p.tipo_proveedor;
            document.getElementById('editProv_tel').value    = p.telefono ?? '';
            document.getElementById('editProv_dir').value    = p.direccion ?? '';
            document.getElementById('editProv_correo').value = p.correo ?? '';
            document.getElementById('editProv_obs').value    = p.observacion_proveedor ?? '';
            document.getElementById('editProv_activo').value = p.activo;
            new bootstrap.Modal(document.getElementById('modalEditarProveedor')).show();
        });
}

function eliminarProveedor(id) { _eliminar(id, '../../api/inventarios/medicamentos/maestros/proveedores_med.php', cargarProveedores, 'proveedor'); }

// ══════════════════════════════════════════════════════
// PARTICIPANTES
// ══════════════════════════════════════════════════════
function initVerParticipantes() { cargarParticipantes(); }
function initRegistrarParticipante() {
    fetch('../../api/inventarios/medicamentos/maestros/participantes_med.php?action=get_departamentos')
        .then(r => r.json()).then(json => {
            if (!json.ok) return;
            ['departamento_id','editPart_depto'].forEach(selId => {
                const sel = document.getElementById(selId);
                if (!sel) return;
                sel.innerHTML = '<option value="">Seleccione...</option>';
                json.data.forEach(d => sel.innerHTML += `<option value="${d.id}">${d.nombre}</option>`);
            });
        });
}

function cargarParticipantes() {
    const btn = document.getElementById('btnCargarParticipantes');
    if (btn) { btn.disabled = true; btn.textContent = 'Cargando...'; }
    const puedeEditar = ['admin_super','operadormed'].includes(USER_ROLE);

    fetch('../../api/inventarios/medicamentos/maestros/participantes_med.php?action=get_all')
        .then(r => r.json())
        .then(json => {
            if (!json.ok) throw new Error(json.msg);
            let filas = '';
            if (!json.data.length) {
                filas = `<tr><td colspan="8" class="text-center">No hay participantes</td></tr>`;
            } else {
                json.data.forEach((p, i) => {
                    filas += `<tr>
                        <td>${i+1}</td>
                        <td>${p.nombre_beneficiario}</td>
                        <td>${p.dpi_beneficiario}</td>
                        <td>${p.telefono ?? '—'}</td>
                        <td>${p.genero_beneficiario}</td>
                        <td>${p.departamento}</td>
                        <td>${p.activo == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>'}</td>
                        <td>
                            ${puedeEditar ? `<button class="btn btn-warning btn-sm me-1" onclick="abrirEditarParticipante(${p.id_beneficiario_med})">Editar</button>` : ''}
                            ${USER_ROLE === 'admin_super' ? `<button class="btn btn-danger btn-sm" onclick="eliminarParticipante(${p.id_beneficiario_med})">Eliminar</button>` : ''}
                        </td>
                    </tr>`;
                });
            }
            document.getElementById('tablaParticipantes').innerHTML = filas;
        })
        .catch(err => mostrarToast('error', err.message))
        .finally(() => { if (btn) { btn.disabled = false; btn.textContent = 'Cargar Participantes'; } });
}

function abrirEditarParticipante(id) {
    initRegistrarParticipante();
    fetch(`../../api/inventarios/medicamentos/maestros/participantes_med.php?action=get_one&id=${id}`)
        .then(r => r.json()).then(json => {
            if (!json.ok) return mostrarToast('error', json.msg);
            const p = json.data;
            document.getElementById('editPart_id').value     = p.id_beneficiario_med;
            document.getElementById('editPart_nombre').value = p.nombre_beneficiario;
            document.getElementById('editPart_dpi').value    = p.dpi_beneficiario;
            document.getElementById('editPart_dir').value    = p.direccion_beneficiario ?? '';
            document.getElementById('editPart_tel').value    = p.telefono ?? '';
            document.getElementById('editPart_genero').value = p.genero_beneficiario;
            document.getElementById('editPart_activo').value = p.activo;
            setTimeout(() => { document.getElementById('editPart_depto').value = p.departamento_id ?? ''; }, 400);
            new bootstrap.Modal(document.getElementById('modalEditarParticipante')).show();
        });
}

function eliminarParticipante(id) { _eliminar(id, '../../api/inventarios/medicamentos/maestros/participantes_med.php', cargarParticipantes, 'participante'); }

// ══════════════════════════════════════════════════════
// LOTES — solo lectura
// ══════════════════════════════════════════════════════
function initVerLotes() {
    fetch('../../api/inventarios/medicamentos/maestros/lotes_med.php?action=get_medicamentos')
        .then(r => r.json()).then(json => {
            if (!json.ok) return;
            const sel = document.getElementById('filtroMedLotes');
            if (!sel) return;
            sel.innerHTML = '<option value="">Todos</option>';
            json.data.forEach(m => sel.innerHTML += `<option value="${m.id}">${m.nombre}</option>`);
        });
    cargarLotes();
}

function cargarLotes() {
    const btn    = document.getElementById('btnCargarLotes');
    const med_id = document.getElementById('filtroMedLotes')?.value ?? '';
    if (btn) { btn.disabled = true; btn.textContent = 'Cargando...'; }

    fetch(`../../api/inventarios/medicamentos/maestros/lotes_med.php?action=get_all&medicamento_id=${med_id}`)
        .then(r => r.json())
        .then(json => {
            if (!json.ok) throw new Error(json.msg);
            const fmt = f => f ? new Date(f+'T00:00:00').toLocaleDateString('es-GT') : '—';
            let filas = '';
            if (!json.data.length) {
                filas = `<tr><td colspan="7" class="text-center">No hay lotes</td></tr>`;
            } else {
                json.data.forEach((l, i) => {
                    const venc = l.fecha_vencimiento
                        ? new Date(l.fecha_vencimiento+'T00:00:00') < new Date()
                            ? '<span class="badge bg-danger">Vencido</span>'
                            : '<span class="badge bg-success">Vigente</span>'
                        : '—';
                    filas += `<tr>
                        <td>${i+1}</td>
                        <td>${l.medicamento}</td>
                        <td><code>${l.numero_lote ?? '—'}</code></td>
                        <td>${fmt(l.fecha_vencimiento)} ${venc}</td>
                        <td class="text-end">${Number(l.cantidad_inicial).toFixed(2)}</td>
                        <td class="text-end">${Number(l.cantidad_actual).toFixed(2)}</td>
                        <td>${fmt(l.creado_en?.split(' ')[0])}</td>
                    </tr>`;
                });
            }
            document.getElementById('tablaLotes').innerHTML = filas;
        })
        .catch(err => mostrarToast('error', err.message))
        .finally(() => { if (btn) { btn.disabled = false; btn.textContent = 'Buscar'; } });
}

// ══════════════════════════════════════════════════════
// FIRMAS DE PLANILLA — solo admin_super
// ══════════════════════════════════════════════════════
function initVerFirmas() { cargarFirmas(); }

function cargarFirmas() {
    const btn = document.getElementById('btnCargarFirmas');
    if (btn) { btn.disabled = true; btn.textContent = 'Cargando...'; }

    fetch('../../api/inventarios/medicamentos/maestros/firmas_planilla.php?action=get_all')
        .then(r => r.json())
        .then(json => {
            if (!json.ok) throw new Error(json.msg);
            let filas = '';
            if (!json.data.length) {
                filas = `<tr><td colspan="5" class="text-center">No hay firmas registradas</td></tr>`;
            } else {
                json.data.forEach((f, i) => {
                    filas += `<tr>
                        <td>${f.orden}</td>
                        <td>${f.nombre}</td>
                        <td>${f.cargo}</td>
                        <td>${f.activo == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>'}</td>
                        <td>
                            <button class="btn btn-warning btn-sm me-1" onclick="abrirEditarFirma(${f.id_firma})">Editar</button>
                            <button class="btn btn-danger btn-sm" onclick="eliminarFirma(${f.id_firma})">Eliminar</button>
                        </td>
                    </tr>`;
                });
            }
            document.getElementById('tablaFirmas').innerHTML = filas;
        })
        .catch(err => mostrarToast('error', err.message))
        .finally(() => { if (btn) { btn.disabled = false; btn.textContent = 'Cargar Firmas'; } });
}

function abrirEditarFirma(id) {
    fetch(`../../api/inventarios/medicamentos/maestros/firmas_planilla.php?action=get_one&id=${id}`)
        .then(r => r.json()).then(json => {
            if (!json.ok) return mostrarToast('error', json.msg);
            const f = json.data;
            document.getElementById('editF_id').value     = f.id_firma;
            document.getElementById('editF_cargo').value  = f.cargo;
            document.getElementById('editF_nombre').value = f.nombre;
            document.getElementById('editF_orden').value  = f.orden;
            document.getElementById('editF_activo').value = f.activo;
            new bootstrap.Modal(document.getElementById('modalEditarFirma')).show();
        });
}

function eliminarFirma(id) { _eliminar(id, '../../api/inventarios/medicamentos/maestros/firmas_planilla.php', cargarFirmas, 'firma'); }
// ══════════════════════════════════════════════════════
// PROGRAMAS / COMPONENTES
// ══════════════════════════════════════════════════════

function initVerProgramas() { 
    cargarProgramas(); 
}

function cargarProgramas() {
    const btn = document.getElementById('btnCargarProgramas');
    if (btn) { btn.disabled = true; btn.textContent = 'Cargando...'; }

    const puedeEditar = ['admin_super','operadormed'].includes(USER_ROLE);

    fetch('../../api/inventarios/medicamentos/maestros/programas.php?action=get_all')
        .then(r => r.json())
        .then(json => {
            if (!json.ok) throw new Error(json.msg);

            let filas = '';

            if (!json.data.length) {
                filas = `<tr><td colspan="4" class="text-center">No hay programas</td></tr>`;
            } else {
                json.data.forEach((p, i) => {
                    filas += `<tr>
                        <td>${i+1}</td>
                        <td>${p.nombre_programa}</td>
                        <td>${p.descripcion ?? '—'}</td>
                        <td>
                            ${puedeEditar ? `<button class="btn btn-warning btn-sm me-1" onclick="abrirEditarPrograma(${p.id_programa})">Editar</button>` : ''}
                            ${USER_ROLE === 'admin_super' ? `<button class="btn btn-danger btn-sm" onclick="eliminarPrograma(${p.id_programa})">Eliminar</button>` : ''}
                        </td>
                    </tr>`;
                });
            }

            document.getElementById('tablaProgramas').innerHTML = filas;
        })
        .catch(err => mostrarToast('error', err.message))
        .finally(() => { 
            if (btn) { btn.disabled = false; btn.textContent = 'Cargar Programas'; } 
        });
}

function abrirEditarPrograma(id) {
    fetch(`../../api/inventarios/medicamentos/maestros/programas.php?action=get_one&id=${id}`)
        .then(r => r.json())
        .then(json => {
            if (!json.ok) return mostrarToast('error', json.msg);

            const p = json.data;
            document.getElementById('editProg_id').value = p.id_programa;
            document.getElementById('editProg_nombre').value = p.nombre_programa;
            document.getElementById('editProg_desc').value = p.descripcion ?? '';

            new bootstrap.Modal(document.getElementById('modalEditarPrograma')).show();
        });
}

function eliminarPrograma(id) {
    _eliminar(
        id,
        '../../api/inventarios/medicamentos/maestros/programas.php',
        cargarProgramas,
        'programa'
    );
}
// ══════════════════════════════════════════════════════
// HELPER ELIMINAR GENÉRICO
// ══════════════════════════════════════════════════════
function _eliminar(id, baseUrl, recargar, nombre) {
    Swal.fire({
        title: `¿Eliminar ${nombre}?`,
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
        Swal.fire({ title: 'Eliminando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        fetch(`${baseUrl}?action=eliminar&id=${id}`)
            .then(r => r.json())
            .then(json => {
                Swal.close();
                if (json.ok) {
                    mostrarToast('exito', json.msg);
                    setTimeout(recargar, 600);
                } else {
                    mostrarToast('error', json.msg);
                }
            })
            .catch(() => { Swal.close(); mostrarToast('error', 'Error de conexión.'); });
    });
}