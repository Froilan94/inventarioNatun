/**
 * existencias.js
 * Lógica del Reporte de Existencias de Medicamentos.
 *
 * Requiere: Bootstrap 5, Bootstrap Icons (o ajusta los íconos).
 * Endpoint: existencias.php
 */

// ─────────────────────────────────────────────
// INICIALIZACIÓN (llamar al mostrar la sección)
// ─────────────────────────────────────────────
async function initVistaExistencias() {
    await cargarFiltrosExistencias();
    await buscarExistencias(); // carga inicial sin filtros
}

// ─────────────────────────────────────────────
// CARGAR OPCIONES EN LOS SELECTS
// ─────────────────────────────────────────────
async function cargarFiltrosExistencias() {
    try {
        const res  = await fetch('../../api/inventarios/medicamentos/reportes/existencias.php?action=get_filtros');
        const json = await res.json();
        if (!json.ok) throw new Error(json.msg);

        const { medicamentos, presentaciones, lotes } = json.data;

        _poblarSelectExistencias('filtroMedicamentoExistencia', medicamentos,  'id', 'nombre', 'Todos');
        _poblarSelectExistencias('filtroPresentacionExistencia', presentaciones, 'id', 'nombre', 'Todas');
        _poblarSelectLotes(lotes);

    } catch (err) {
        console.error('cargarFiltrosExistencias:', err);
    }
}

/** Rellena un <select> con un array de objetos */
function _poblarSelectExistencias(selectId, items, keyVal, keyLabel, placeholderLabel) {
    const sel = document.getElementById(selectId);
    if (!sel) return;
    // conservar sólo el primer option (placeholder)
    sel.innerHTML = `<option value="">${placeholderLabel}</option>`;
    items.forEach(item => {
        const opt = document.createElement('option');
        opt.value       = item[keyVal];
        opt.textContent = item[keyLabel];
        sel.appendChild(opt);
    });
}

/** Select de lotes: muestra "LOTE - Medicamento" */
function _poblarSelectLotes(lotes) {
    const sel = document.getElementById('filtroLoteExistencia');
    if (!sel) return;
    sel.innerHTML = '<option value="">Todos</option>';
    lotes.forEach(l => {
        const opt = document.createElement('option');
        opt.value       = l.id;
        opt.textContent = `${l.nombre} — ${l.medicamento}`;
        sel.appendChild(opt);
    });
}

// ─────────────────────────────────────────────
// BUSCAR / FILTRAR
// ─────────────────────────────────────────────
async function buscarExistencias() {
    const params = new URLSearchParams({
        action:          'get_existencias',
        medicamento_id:  document.getElementById('filtroMedicamentoExistencia')?.value  ?? '',
        presentacion_id: document.getElementById('filtroPresentacionExistencia')?.value ?? '',
        lote_id:         document.getElementById('filtroLoteExistencia')?.value         ?? '',
        filtro_stock:    document.getElementById('filtroStock')?.value                  ?? '',
        filtro_venc:     document.getElementById('filtroVencimiento')?.value            ?? '',
    });

    const tbody = document.getElementById('tablaExistencias');
    tbody.innerHTML = `
        <tr>
            <td colspan="9" class="text-center py-3">
                <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                Cargando…
            </td>
        </tr>`;

    try {
        const res  = await fetch(`../../api/inventarios/medicamentos/reportes/existencias.php?${params}`);
        const json = await res.json();
        if (!json.ok) throw new Error(json.msg);

        _renderResumen(json.resumen);
        _renderTabla(json.data);

    } catch (err) {
        tbody.innerHTML = `
            <tr>
                <td colspan="9" class="text-danger text-center py-3">
                    ⚠️ Error al cargar existencias: ${err.message}
                </td>
            </tr>`;
        console.error('buscarExistencias:', err);
    }
}

// ─────────────────────────────────────────────
// LIMPIAR FILTROS
// ─────────────────────────────────────────────
function limpiarFiltrosExistencias() {
    ['filtroMedicamentoExistencia',
     'filtroPresentacionExistencia',
     'filtroLoteExistencia',
     'filtroStock',
     'filtroVencimiento'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
    buscarExistencias();
}

// ─────────────────────────────────────────────
// RENDER: tarjetas de resumen
// ─────────────────────────────────────────────
function _renderResumen(r) {
    // Si no existe el contenedor de resumen en el HTML lo creamos dinámicamente
    let contenedor = document.getElementById('resumenExistencias');
    if (!contenedor) {
        contenedor = document.createElement('div');
        contenedor.id = 'resumenExistencias';
        contenedor.className = 'row g-2 mb-3';
        // Insertar antes de la card de la tabla
        const tablaCard = document.getElementById('tablaExistencias')?.closest('.card');
        if (tablaCard) tablaCard.before(contenedor);
    }

    const fmt = n => Number(n).toLocaleString('es-GT', { minimumFractionDigits: 2 });

    contenedor.innerHTML = `
        <div class="col-6 col-md-2">
            <div class="card text-center border-secondary h-100">
                <div class="card-body py-2 px-1">
                    <div class="fs-4 fw-bold">${r.total_filas}</div>
                    <div class="small text-muted">Lotes</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card text-center border-success h-100">
                <div class="card-body py-2 px-1">
                    <div class="fs-6 fw-bold text-success">Q ${fmt(r.total_valor)}</div>
                    <div class="small text-muted">Valor total</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card text-center border-danger h-100">
                <div class="card-body py-2 px-1">
                    <div class="fs-4 fw-bold text-danger">${r.agotados}</div>
                    <div class="small text-muted">Agotados</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card text-center border-warning h-100">
                <div class="card-body py-2 px-1">
                    <div class="fs-4 fw-bold text-warning">${r.stock_bajo}</div>
                    <div class="small text-muted">Stock bajo</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card text-center border-danger h-100">
                <div class="card-body py-2 px-1">
                    <div class="fs-4 fw-bold text-danger">${r.vencidos}</div>
                    <div class="small text-muted">Vencidos</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card text-center border-warning h-100">
                <div class="card-body py-2 px-1">
                    <div class="fs-4 fw-bold text-warning">${r.proximos_venc}</div>
                    <div class="small text-muted">Próx. a vencer</div>
                </div>
            </div>
        </div>
    `;
}

// ─────────────────────────────────────────────
// RENDER: filas de la tabla
// ─────────────────────────────────────────────
function _renderTabla(rows) {
    const tbody = document.getElementById('tablaExistencias');

    if (!rows.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center text-muted py-4">
                    No se encontraron registros con los filtros aplicados.
                </td>
            </tr>`;
        return;
    }

    const fmt    = n => Number(n).toLocaleString('es-GT', { minimumFractionDigits: 2 });
    const fmtFecha = f => f ? new Date(f + 'T00:00:00').toLocaleDateString('es-GT') : '—';

    tbody.innerHTML = rows.map(r => {
        // Badge de estado de stock
        const badgeStock = {
            agotado: '<span class="badge bg-danger">Agotado</span>',
            bajo:    '<span class="badge bg-warning text-dark">Stock bajo</span>',
            normal:  '<span class="badge bg-success">Normal</span>',
        }[r.estado] ?? '';

        // Badge de vencimiento
        const badgeVenc = {
            vencido:   '<span class="badge bg-danger ms-1">Vencido</span>',
            proximo:   '<span class="badge bg-warning text-dark ms-1">Próx. vencer</span>',
            vigente:   '',
            sin_fecha: '',
        }[r.estado_venc] ?? '';

        // Clase de fila según criticidad
        let trClass = '';
        if (r.estado === 'agotado' || r.estado_venc === 'vencido') trClass = 'table-danger';
        else if (r.estado === 'bajo' || r.estado_venc === 'proximo') trClass = 'table-warning';

        return `
            <tr class="${trClass}">
                <td>
                    <div class="fw-semibold">${r.medicamento}</div>
                    ${r.nombre_generico ? `<small class="text-muted">${r.nombre_generico}</small>` : ''}
                </td>
                <td>${r.presentacion}</td>
                <td>${r.unidad}</td>
                <td><code>${r.lote}</code></td>
                <td>${fmtFecha(r.fecha_vencimiento)}${badgeVenc}</td>
                <td class="text-end fw-semibold">${fmt(r.stock)}</td>
                <td class="text-end">Q ${fmt(r.precio_unitario)}</td>
                <td class="text-end">Q ${fmt(r.valor_total)}</td>
                <td>${badgeStock}</td>
            </tr>
        `;
    }).join('');
}
