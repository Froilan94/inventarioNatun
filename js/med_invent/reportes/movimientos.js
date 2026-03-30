/**
 * movimientos.js  →  js2/reportes/movimientos.js
 *
 * Reporte de Movimientos: ingresos + salidas con filtros,
 * selección por casilla, exportación PDF/Excel/CSV.
 */

// ─────────────────────────────────────────────
// ESTADO
// ─────────────────────────────────────────────
let _movData = []; // todos los registros actuales

// ─────────────────────────────────────────────
// INICIALIZACIÓN
// ─────────────────────────────────────────────
async function initVistaMovimientos() {
    await _cargarFiltrosMovimientos();
    await buscarMovimientos();
}

async function _cargarFiltrosMovimientos() {
    try {
        const res  = await fetch('../../api/inventarios/medicamentos/reportes/movimientos.php?action=get_filtros');
        const json = await res.json();
        if (!json.ok) throw new Error(json.msg);

        _poblarSelectMov('filtroMedMov',          json.medicamentos,  'Todos');
        _poblarSelectMov('filtroProvMov',          json.proveedores,   'Todos');
        _poblarSelectMov('filtroResponsableMov',   json.responsables,  'Todos');
        _poblarSelectMov('filtroComponenteMov',    json.componentes,   'Todos');

        // Lotes: input datalist
        const dl = document.getElementById('lotesDatalist');
        if (dl) {
            dl.innerHTML = '';
            json.lotes.forEach(l => {
                const opt = document.createElement('option');
                opt.value = l.lote;
                dl.appendChild(opt);
            });
        }
    } catch (err) {
        console.error('_cargarFiltrosMovimientos:', err);
    }
}

// Mostrar/ocultar filtros según tipo de movimiento
function cambiarFiltrosTipoMov(tipo) {
    const filtrosIng = document.getElementById('filtrosIngreso');
    const filtrosSal = document.getElementById('filtrosSalida');

    if (tipo === 'Ingresos') {
        filtrosIng.style.display = '';
        filtrosSal.style.display = 'none';
        // Limpiar filtros de salida
        document.getElementById('filtroResponsableMov').value = '';
        document.getElementById('filtroComponenteMov').value  = '';
    } else if (tipo === 'Salidas') {
        filtrosIng.style.display = 'none';
        filtrosSal.style.display = '';
        // Limpiar filtros de ingreso
        document.getElementById('filtroLoteMov').value  = '';
        document.getElementById('filtroProvMov').value  = '';
    } else {
        // Todos — mostrar ambos
        filtrosIng.style.display = '';
        filtrosSal.style.display = '';
    }
}

function _poblarSelectMov(selectId, items, placeholder) {
    const sel = document.getElementById(selectId);
    if (!sel) return;
    sel.innerHTML = `<option value="">${placeholder}</option>`;
    items.forEach(({ id, nombre }) => {
        const opt = document.createElement('option');
        opt.value = id;
        opt.textContent = nombre;
        sel.appendChild(opt);
    });
}

// ─────────────────────────────────────────────
// BUSCAR
// ─────────────────────────────────────────────
async function buscarMovimientos() {
    const params = new URLSearchParams({
        action:          'get_movimientos',
        tipo:            document.getElementById('filtroTipoMov')?.value          ?? '',
        fecha_inicio:    document.getElementById('filtroFechaIniMov')?.value      ?? '',
        fecha_fin:       document.getElementById('filtroFechaFinMov')?.value      ?? '',
        medicamento_id:  document.getElementById('filtroMedMov')?.value           ?? '',
        numero_lote:     document.getElementById('filtroLoteMov')?.value          ?? '',
        proveedor_id:    document.getElementById('filtroProvMov')?.value          ?? '',
        responsable_id:  document.getElementById('filtroResponsableMov')?.value   ?? '',
        componente_id:   document.getElementById('filtroComponenteMov')?.value    ?? '',
    });

    const tbody = document.getElementById('tbodyMovimientos');
    tbody.innerHTML = `
        <tr>
            <td colspan="13" class="text-center py-3">
                <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                Cargando…
            </td>
        </tr>`;

    try {
        const res  = await fetch(`../../api/inventarios/medicamentos/reportes/movimientos.php?${params}`);
        const json = await res.json();
        if (!json.ok) throw new Error(json.msg);

        _movData = json.data;
        _renderResumenMov(json);
        _renderTablaMov(json.data);

    } catch (err) {
        tbody.innerHTML = `
            <tr>
                <td colspan="13" class="text-danger text-center py-3">
                    ⚠️ Error: ${err.message}
                </td>
            </tr>`;
        console.error('buscarMovimientos:', err);
    }
}

// ─────────────────────────────────────────────
// RENDER RESUMEN
// ─────────────────────────────────────────────
function _renderResumenMov(json) {
    const fmt = n => 'Q ' + Number(n).toLocaleString('es-GT', { minimumFractionDigits: 2 });

    const cont = document.getElementById('resumenMovimientos');
    if (!cont) return;

    cont.innerHTML = `
        <div class="col-4">
            <div class="card text-center border-secondary h-100">
                <div class="card-body py-2">
                    <div class="fs-4 fw-bold">${json.total_registros}</div>
                    <div class="small text-muted">Registros</div>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="card text-center border-success h-100">
                <div class="card-body py-2">
                    <div class="fs-6 fw-bold text-success">${fmt(json.total_ingresos)}</div>
                    <div class="small text-muted">Total Ingresos</div>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="card text-center border-danger h-100">
                <div class="card-body py-2">
                    <div class="fs-6 fw-bold text-danger">${fmt(json.total_salidas)}</div>
                    <div class="small text-muted">Total Salidas</div>
                </div>
            </div>
        </div>
    `;
}

// ─────────────────────────────────────────────
// RENDER TABLA
// ─────────────────────────────────────────────
function _renderTablaMov(rows) {
    const tbody = document.getElementById('tbodyMovimientos');
    const fmt      = n => Number(n).toLocaleString('es-GT', { minimumFractionDigits: 2 });
    const fmtFecha = f => f ? new Date(f + 'T00:00:00').toLocaleDateString('es-GT') : '—';

    if (!rows.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="13" class="text-center text-muted py-4">
                    No se encontraron registros con los filtros aplicados.
                </td>
            </tr>`;
        return;
    }

    tbody.innerHTML = rows.map((r, i) => {
        const badgeTipo = r.tipo === 'Ingreso'
            ? '<span class="badge bg-success">Ingreso</span>'
            : '<span class="badge bg-danger">Salida</span>';

        return `
            <tr>
                <td class="text-center">
                    <input type="checkbox" class="form-check-input chk-mov"
                           data-idx="${i}" title="Seleccionar">
                </td>
                <td>${badgeTipo}</td>
                <td>${fmtFecha(r.fecha)}</td>
                <td>
                    <div class="fw-semibold">${r.medicamento}</div>
                    ${r.nombre_generico ? `<small class="text-muted">${r.nombre_generico}</small>` : ''}
                </td>
                <td><code>${r.lote}</code></td>
                <td>${fmtFecha(r.fecha_vencimiento)}</td>
                <td class="text-end">${fmt(r.cantidad)}</td>
                <td>${r.unidad}</td>
                <td class="text-end">Q ${fmt(r.precio_unitario)}</td>
                <td class="text-end">Q ${fmt(r.monto)}</td>
                <td>${r.proveedor_donante}</td>
                <td>${r.responsable}</td>
                <td>${r.beneficiario ?? '—'}</td>
            </tr>
        `;
    }).join('');

    // Checkbox "seleccionar todos"
    const chkAll = document.getElementById('chkTodosMov');
    if (chkAll) {
        chkAll.checked = false;
        chkAll.onchange = () => {
            document.querySelectorAll('.chk-mov').forEach(c => c.checked = chkAll.checked);
        };
    }
}

// ─────────────────────────────────────────────
// LIMPIAR FILTROS
// ─────────────────────────────────────────────
function limpiarFiltrosMov() {
    ['filtroTipoMov','filtroFechaIniMov','filtroFechaFinMov',
     'filtroMedMov','filtroLoteMov','filtroProvMov',
     'filtroResponsableMov','filtroComponenteMov'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
    // Mostrar todos los filtros al limpiar
    cambiarFiltrosTipoMov('');
    buscarMovimientos();
}

// ─────────────────────────────────────────────
// OBTENER SELECCIONADOS (o todos si ninguno marcado)
// ─────────────────────────────────────────────
function _getSeleccionados() {
    const checks = [...document.querySelectorAll('.chk-mov:checked')];
    if (!checks.length) return _movData; // si no hay selección → todos
    return checks.map(c => _movData[parseInt(c.dataset.idx)]);
}

// ─────────────────────────────────────────────
// EXPORTAR CSV
// ─────────────────────────────────────────────
function exportarMovCSV() {
    const rows = _getSeleccionados();
    if (!rows.length) return mostrarToast('error', 'No hay registros para exportar.');

    const cabecera = ['Tipo','Fecha','Medicamento','Nombre Genérico','Lote',
                      'Vencimiento','Cantidad','Unidad','Presentación',
                      'Precio Unitario','Monto','Proveedor/Componente',
                      'Responsable','Beneficiaria'];

    const lineas = [cabecera.join(',')];
    rows.forEach(r => {
        const fmtFecha = f => f ? new Date(f + 'T00:00:00').toLocaleDateString('es-GT') : '';
        lineas.push([
            r.tipo, fmtFecha(r.fecha), `"${r.medicamento}"`, `"${r.nombre_generico ?? ''}"`,
            r.lote, fmtFecha(r.fecha_vencimiento), r.cantidad, r.unidad, r.presentacion,
            r.precio_unitario, r.monto, `"${r.proveedor_donante}"`,
            `"${r.responsable}"`, `"${r.beneficiario ?? ''}"`
        ].join(','));
    });

    const blob = new Blob(['\uFEFF' + lineas.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href     = url;
    a.download = `movimientos_${new Date().toISOString().slice(0,10)}.csv`;
    a.click();
    URL.revokeObjectURL(url);
}

// ─────────────────────────────────────────────
// EXPORTAR EXCEL (via PHP)
// ─────────────────────────────────────────────
function exportarMovExcel() {
    const rows = _getSeleccionados();
    if (!rows.length) return mostrarToast('error', 'No hay registros para exportar.');

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '../../api/inventarios/medicamentos/reportes/exportar_movimientos_excel.php';
    form.target = '_blank';

    const input = document.createElement('input');
    input.type  = 'hidden';
    input.name  = 'datos';
    input.value = JSON.stringify(rows);
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

// ─────────────────────────────────────────────
// EXPORTAR PDF (via PHP)
// ─────────────────────────────────────────────
function exportarMovPDF() {
    const rows = _getSeleccionados();
    if (!rows.length) return mostrarToast('error', 'No hay registros para exportar.');

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '../../api/inventarios/medicamentos/reportes/exportar_movimientos_pdf.php';
    form.target = '_blank';

    const input = document.createElement('input');
    input.type  = 'hidden';
    input.name  = 'datos';
    input.value = JSON.stringify(rows);
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function generarPlanillaNatun() {
    // ⚠️ Sin selección → pedir que seleccione (no abrir todo)
    const checks = [...document.querySelectorAll('.chk-mov:checked')];
    if (!checks.length)
        return mostrarToast('error', 'Selecciona al menos una Salida para generar la planilla.');

    const seleccionados = checks.map(c => _movData[parseInt(c.dataset.idx)]);
    const salidas = seleccionados.filter(r => r.tipo === 'Salida');

    if (!salidas.length)
        return mostrarToast('error', 'Los registros seleccionados no contienen Salidas.');

    // IDs únicos de salida
    const ids = [...new Set(salidas.map(r => r.id_movimiento))];

    // Abrir con delay para evitar bloqueo del navegador
    ids.forEach((id, i) => {
        setTimeout(() => {
            window.open(
                `../../api/inventarios/medicamentos/reportes/planilla_salida.php?salida_id=${id}`,
                '_blank'
            );
        }, i * 300);
    });
}