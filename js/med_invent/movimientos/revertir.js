/**
 * revertir.js
 * Módulo para revertir ingresos y salidas de medicamentos.
 */

const _ENDPOINT = '../../api/inventarios/medicamentos/movimientos/revertir.php';

// ─────────────────────────────────────────────
// INICIALIZACIÓN
// ─────────────────────────────────────────────
async function initVistaRevertir() {
    await _cargarIngresos();
    await _cargarSalidas();
}

// ─────────────────────────────────────────────
// CARGAR TABLAS
// ─────────────────────────────────────────────
async function _cargarIngresos() {
    const tbody = document.getElementById('tbodyRevertirIngresos');
    tbody.innerHTML = `<tr><td colspan="7" class="text-center py-3">
        <div class="spinner-border spinner-border-sm text-primary me-2"></div>Cargando…</td></tr>`;

    try {
        const res  = await fetch(`${_ENDPOINT}?action=get_ingresos`);
        const json = await res.json();
        if (!json.ok) throw new Error(json.msg);
        _renderTablaIngresos(json.data);
    } catch (err) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-danger text-center py-3">
            ⚠️ ${err.message}</td></tr>`;
    }
}

async function _cargarSalidas() {
    const tbody = document.getElementById('tbodyRevertirSalidas');
    tbody.innerHTML = `<tr><td colspan="7" class="text-center py-3">
        <div class="spinner-border spinner-border-sm text-primary me-2"></div>Cargando…</td></tr>`;

    try {
        const res  = await fetch(`${_ENDPOINT}?action=get_salidas`);
        const json = await res.json();
        if (!json.ok) throw new Error(json.msg);
        _renderTablaSalidas(json.data);
    } catch (err) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-danger text-center py-3">
            ⚠️ ${err.message}</td></tr>`;
    }
}

// ─────────────────────────────────────────────
// RENDER TABLAS
// ─────────────────────────────────────────────
function _renderTablaIngresos(rows) {
    const tbody = document.getElementById('tbodyRevertirIngresos');
    if (!rows.length) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted py-4">
            Sin ingresos registrados.</td></tr>`;
        return;
    }

    const fmtFecha = f => f ? new Date(f + 'T00:00:00').toLocaleDateString('es-GT') : '—';
    const fmt      = n => Number(n).toLocaleString('es-GT', { minimumFractionDigits: 2 });

    tbody.innerHTML = rows.map(r => `
        <tr>
            <td><span class="badge bg-secondary">#${r.id}</span></td>
            <td>${fmtFecha(r.fecha)}</td>
            <td>${r.proveedor}</td>
            <td>${r.recibido_por}</td>
            <td><small>${r.tipo_documento} ${r.numero_documento !== '—' ? '· ' + r.numero_documento : ''}</small></td>
            <td class="text-center">${r.total_lineas} líneas / ${fmt(r.total_cantidad)} uds.</td>
            <td class="text-center">
                <button class="btn btn-danger btn-sm"
                        onclick="confirmarRevertirIngreso(${r.id})">
                    ↩ Revertir
                </button>
            </td>
        </tr>
    `).join('');
}

function _renderTablaSalidas(rows) {
    const tbody = document.getElementById('tbodyRevertirSalidas');
    if (!rows.length) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted py-4">
            Sin salidas registradas.</td></tr>`;
        return;
    }

    const fmtFecha = f => f ? new Date(f + 'T00:00:00').toLocaleDateString('es-GT') : '—';
    const fmt      = n => Number(n).toLocaleString('es-GT', { minimumFractionDigits: 2 });

    tbody.innerHTML = rows.map(r => `
        <tr>
            <td><span class="badge bg-secondary">#${r.id}</span></td>
            <td>${fmtFecha(r.fecha)}</td>
            <td>${r.programa}</td>
            <td>${r.entregado_por}</td>
            <td><small>${r.tipo_documento} ${r.numero_documento !== '—' ? '· ' + r.numero_documento : ''}</small></td>
            <td class="text-center">${r.total_lineas} líneas / ${fmt(r.total_cantidad)} uds.</td>
            <td class="text-center">
                <button class="btn btn-warning btn-sm"
                        onclick="confirmarRevertirSalida(${r.id})">
                    ↩ Revertir
                </button>
            </td>
        </tr>
    `).join('');
}

// ─────────────────────────────────────────────
// CONFIRMAR Y EJECUTAR
// ─────────────────────────────────────────────
async function confirmarRevertirIngreso(id) {
    const confirm = await Swal.fire({
        title:              '¿Revertir este ingreso?',
        html:               `Se eliminarán los lotes, detalles y documento del ingreso <b>#${id}</b>.<br>
                             <span class="text-danger fw-bold">Esta acción no se puede deshacer.</span>`,
        icon:               'warning',
        showCancelButton:   true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor:  '#6c757d',
        confirmButtonText:  'Sí, revertir',
        cancelButtonText:   'Cancelar',
    });

    if (!confirm.isConfirmed) return;
    await _ejecutarReversion('revertir_ingreso', { ingreso_id: id }, _cargarIngresos);
}

async function confirmarRevertirSalida(id) {
    const confirm = await Swal.fire({
        title:              '¿Revertir esta salida?',
        html:               `Se restaurará el stock de los lotes y se eliminará la salida <b>#${id}</b>.<br>
                             <span class="text-danger fw-bold">Esta acción no se puede deshacer.</span>`,
        icon:               'warning',
        showCancelButton:   true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor:  '#6c757d',
        confirmButtonText:  'Sí, revertir',
        cancelButtonText:   'Cancelar',
    });

    if (!confirm.isConfirmed) return;
    await _ejecutarReversion('revertir_salida', { salida_id: id }, _cargarSalidas);
}

async function _ejecutarReversion(action, payload, recargar) {
    try {
        const res  = await fetch(`${_ENDPOINT}?action=${action}`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(payload),
        });
        const json = await res.json();

        if (!json.ok) {
            // Error de negocio (ej: lotes con salidas) → mostrar sin recargar
            await Swal.fire({
                title: 'No se puede revertir',
                text:   json.msg,
                icon:  'error',
            });
            return;
        }

        mostrarToast('exito', json.msg);
        await recargar(); // refrescar solo la tabla afectada

    } catch (err) {
        mostrarToast('error', 'Error inesperado: ' + err.message);
        console.error('_ejecutarReversion:', err);
    }
}

// ─────────────────────────────────────────────
// SWITCH TAB
// ─────────────────────────────────────────────
function switchTabRevertir(tab) {
    const isIng = tab === 'ingresos';

    document.getElementById('vistaRevertirIngresos').style.display = isIng ? '' : 'none';
    document.getElementById('vistaRevertirSalidas').style.display  = isIng ? 'none' : '';

    document.getElementById('tabIngRevertir').className =
        'btn btn-sm px-4 py-2 tab-revertir' + (isIng ? ' active-ing' : '');
    document.getElementById('tabSalRevertir').className =
        'btn btn-sm px-4 py-2 tab-revertir' + (!isIng ? ' active-sal' : '');
}